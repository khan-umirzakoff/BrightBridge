<?php

namespace App\Services;

use App\Contracts\AIService;
use App\Jobs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RAGService
{
    protected $aiService;
    protected $baseUrl;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
        $this->baseUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/');
    }

    /**
     * Declares the tools (functions) available to the AI.
     */
    public function getToolDeclarations(): array
    {
        return [
            [
                'functionDeclarations' => [
                    [
                        'name' => 'search_general',
                        'description' => 'Umumiy qidiruv vositasi. Ish, vakansiya, yangilik, trening, hujjat yoki kitoblardan ma\'lumot qidiradi. Kitoblarning har bir bo\'limini alohida izlaydi va eng mos keladigan qismlarini topadi. Foydalanuvchi "kitobda nima yozilgan", "hujjatning qayerida aytilgan" kabi savollar bersa ham aniq javob beradi.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'query' => [
                                    'type' => 'string',
                                    'description' => 'Qidiruv so\'rovi. Masalan: "PHP dasturchi Toshkentda", "so\'nggi yangiliklar", "marketing treninglari", "kitobda motivatsiya haqida nima yozilgan", "hujjatning 5-bo\'limida qanday ma\'lumot bor".'
                                ]
                            ],
                            'required' => ['query']
                        ]
                    ],
                    [
                        'name' => 'get_contact_info',
                        'description' => 'Foydalanuvchi aniq kontakt ma\'lumotlarini (telefon, manzil, email) so\'raganda ishlatiladi.',
                        'parameters' => (object)['type' => 'object', 'properties' => (object)[]]
                    ],
                    [
                        'name' => 'get_platform_stats',
                        'description' => 'Foydalanuvchi platforma statistikasi (ishlar soni, yangiliklar soni, kitoblar soni va hokazo) haqida so\'raganda ishlatiladi.',
                        'parameters' => (object)['type' => 'object', 'properties' => (object)[]]
                    ]
                ]
            ]
        ];
    }

    /**
     * Executes the tool requested by the AI.
     */
    public function executeTool(string $functionName, array $args): array
    {
        Log::info('Executing tool', ['name' => $functionName, 'args' => $args]);
        switch ($functionName) {
            case 'search_general':
                return $this->searchGeneral($args['query'] ?? '');
            case 'get_contact_info':
                return $this->getContactInfo();
            case 'get_platform_stats':
                return $this->getPlatformStats();
            default:
                Log::warning('Unknown tool called', ['name' => $functionName]);
                return ['content' => 'Noma\'lum buyruq.', 'sources' => []];
        }
    }

    /**
     * Performs a general semantic search across multiple tables.
     * Now includes DEEP chunk-level search for ai_documents!
     */
    private function searchGeneral(string $query): array
    {
        if (empty($query)) {
            return ['content' => 'Qidiruv uchun so\'rov bo\'sh.', 'sources' => []];
        }

        $allResults = [];
        $processedIds = [];

        try {
            $queryEmbedding = $this->aiService->embed($query);

            // Search in simple tables (jobs, news, trainings)
            $simpleTables = ['jobs', 'news', 'trainings'];
            foreach ($simpleTables as $table) {
                $items = DB::table($table)->whereNotNull('embedding')->get();
                if ($items->isEmpty()) continue;

                $results = $this->calculateSimilarity($items, $queryEmbedding);
                foreach ($results as $r) {
                    $itemId = "{$table}_{$r['item']->id}";
                    if ($r['similarity'] > 0.4 && !in_array($itemId, $processedIds)) {
                        $allResults[] = array_merge(
                            $this->formatItemContent($r['item'], $table),
                            ['similarity' => $r['similarity']]
                        );
                        $processedIds[] = $itemId;
                    }
                }
            }

            // PROFESSIONAL CHUNK-LEVEL SEARCH for ai_documents
            $documentChunks = $this->searchDocumentChunks($queryEmbedding, $query);
            foreach ($documentChunks as $chunk) {
                $allResults[] = $chunk;
            }

            if (empty($allResults)) {
                return ['content' => 'Bu mavzuda hech narsa topilmadi.', 'sources' => []];
            }

            // Sort by similarity and take top 5
            usort($allResults, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
            $topResults = array_slice($allResults, 0, 5);

            $content = "Topilgan ma'lumotlar:\n\n";
            foreach ($topResults as $result) {
                $content .= $result['content'] . "\n\n";
            }

            $sources = array_map(function($result) {
                return ['url' => $result['url'], 'title' => $result['title']];
            }, array_filter($topResults, fn($r) => !empty($r['url'])));

            return ['content' => trim($content), 'sources' => $sources];

        } catch (\Exception $e) {
            Log::error('RAG searchGeneral error', ['error' => $e->getMessage()]);
            return ['content' => 'Qidiruvda xatolik yuz berdi.', 'sources' => []];
        }
    }

    /**
     * PROFESSIONAL CHUNK-LEVEL SEMANTIC SEARCH
     * Searches through EVERY chunk of EVERY document to find the most relevant pieces.
     * This allows AI to answer questions about specific parts of books/documents.
     *
     * @param array $queryEmbedding The embedding vector of the user's query
     * @param string $query Original query text for context detection
     * @param float $threshold Minimum similarity score (default: 0.35)
     * @return array Array of relevant chunks with metadata
     */
    private function searchDocumentChunks(array $queryEmbedding, string $query = '', float $threshold = 0.3): array
    {
        $documents = DB::table('ai_documents')
            ->whereNotNull('embedding')
            ->whereNotNull('content')
            ->get();

        if ($documents->isEmpty()) {
            return [];
        }

        // Detect query context
        $queryLower = mb_strtolower($query);
        $seekingEnd = preg_match('/(oxir|xulos|ending|conclusion|last|final|yakuniy)/ui', $queryLower);
        $seekingBeginning = preg_match('/(bosh|kirish|introduction|first|start|beginning)/ui', $queryLower);
        $seekingAll = preg_match('/(barcha|hamma|all|every|ro.yxat|list)/ui', $queryLower);

        // Special case: if seeking ALL books, return first chunk from EACH document without similarity filtering
        if ($seekingAll) {
            $allDocsResults = [];
            foreach ($documents as $doc) {
                $embeddings = json_decode($doc->embedding, true);
                if (!is_array($embeddings) || empty($embeddings)) {
                    continue;
                }

                $isMultiChunk = isset($embeddings[0]) && is_array($embeddings[0]);
                $totalChunks = $isMultiChunk ? count($embeddings) : 1;

                // Get first chunk text
                $displayText = mb_substr(trim($doc->content), 0, 800);
                if (mb_strlen($doc->content) > 800) {
                    $displayText .= '...';
                }

                $allDocsResults[] = [
                    'content' => $this->formatDocumentChunk($doc, 1, $totalChunks, $displayText),
                    'similarity' => 0.9, // High similarity for "list all" requests
                    'url' => null,
                    'title' => $doc->title ?? 'Noma\'lum hujjat',
                    'document_id' => $doc->id,
                    'chunk_index' => 0
                ];
            }
            return $allDocsResults;
        }

        $chunkResults = [];
        $documentsFound = []; // Track unique documents

        foreach ($documents as $doc) {
            $embeddings = json_decode($doc->embedding, true);

            if (!is_array($embeddings) || empty($embeddings)) {
                continue;
            }

            $isMultiChunk = isset($embeddings[0]) && is_array($embeddings[0]);

            if (!$isMultiChunk) {
                $similarity = $this->cosineSimilarity($queryEmbedding, $embeddings);

                if ($similarity > $threshold) {
                    $chunkResults[] = [
                        'content' => $this->formatDocumentChunk($doc, 0, 1, mb_substr($doc->content, 0, 800)),
                        'similarity' => $similarity,
                        'url' => null,
                        'title' => $doc->title ?? 'Noma\'lum hujjat',
                        'document_id' => $doc->id
                    ];
                    $documentsFound[$doc->id] = true;
                }
            } else {
                $totalChunks = count($embeddings);
                $chunkSize = intval(ceil(mb_strlen($doc->content) / $totalChunks));
                $docBestChunk = null;

                foreach ($embeddings as $chunkIndex => $chunkEmbedding) {
                    if (!is_array($chunkEmbedding) || empty($chunkEmbedding)) {
                        continue;
                    }

                    $similarity = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);

                    // Boost last chunks if query seeks ending
                    if ($seekingEnd && $chunkIndex >= ($totalChunks - 3)) {
                        $similarity += 0.15; // Significant boost for last 3 chunks
                    }

                    // Boost first chunks if query seeks beginning
                    if ($seekingBeginning && $chunkIndex < 3) {
                        $similarity += 0.15;
                    }

                    if ($similarity > $threshold) {
                        $startPos = $chunkIndex * $chunkSize;
                        $chunkText = mb_substr($doc->content, $startPos, $chunkSize);

                        $displayText = mb_substr(trim($chunkText), 0, 800);
                        if (mb_strlen($chunkText) > 800) {
                            $displayText .= '...';
                        }

                        $result = [
                            'content' => $this->formatDocumentChunk($doc, $chunkIndex + 1, $totalChunks, $displayText),
                            'similarity' => $similarity,
                            'url' => null,
                            'title' => $doc->title ?? 'Noma\'lum hujjat',
                            'chunk_index' => $chunkIndex,
                            'document_id' => $doc->id
                        ];

                        $chunkResults[] = $result;

                        // Track best chunk for this document
                        if (!isset($docBestChunk[$doc->id]) || $similarity > $docBestChunk[$doc->id]['similarity']) {
                            $docBestChunk[$doc->id] = $result;
                        }
                    }
                }

                if ($docBestChunk) {
                    $documentsFound[$doc->id] = true;
                }
            }
        }

        // Sort by similarity
        usort($chunkResults, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        // If seeking all books, ensure at least 1 chunk from each document
        if ($seekingAll && count($documentsFound) >= 1) {
            $uniqueDocs = [];
            $finalResults = [];

            // First pass: get one chunk from each unique document
            foreach ($chunkResults as $chunk) {
                if (!isset($uniqueDocs[$chunk['document_id']])) {
                    $finalResults[] = $chunk;
                    $uniqueDocs[$chunk['document_id']] = true;

                    // If we found all documents, stop
                    if (count($uniqueDocs) == count($documentsFound)) {
                        break;
                    }
                }
            }

            // Second pass: fill remaining slots with best matches
            foreach ($chunkResults as $chunk) {
                if (count($finalResults) >= 5) break;
                if (!in_array($chunk, $finalResults, true)) {
                    $finalResults[] = $chunk;
                }
            }

            return $finalResults;
        }

        // Default: return top 3 chunks
        return array_slice($chunkResults, 0, 3);
    }

    /**
     * Formats a document chunk for display
     */
    private function formatDocumentChunk($document, int $chunkNumber, int $totalChunks, string $text): string
    {
        $title = $document->title ?? 'Noma\'lum hujjat';
        $category = $document->category ?? 'umumiy';
        $description = $document->description ? mb_substr(strip_tags($document->description), 0, 200) : '';

        $metadata = "📚 **{$title}**\n" .
                   "Kategoriya: {$category}\n";

        if ($description) {
            $metadata .= "Qisqacha: {$description}...\n";
        }

        if ($totalChunks > 1) {
            $metadata .= "Hujjat hajmi: {$totalChunks} ta bo'limga ajratilgan\n";
            $metadata .= "Topilgan bo'lim: {$chunkNumber}/{$totalChunks}\n\n";
        } else {
            $metadata .= "\n";
        }

        return $metadata . $text;
    }

    /**
     * Retrieves high-priority contact information.
     */
    private function getContactInfo(): array
    {
        $facts = DB::table('ai_knowledge')
            ->where('is_active', true)
            ->where('category', 'contact')
            ->orderBy('priority', 'desc')
            ->get();

        if ($facts->isEmpty()) {
            return ['content' => 'Kontakt ma\'lumotlari topilmadi.', 'sources' => []];
        }

        $content = "Asosiy kontakt ma'lumotlari:\n";
        foreach ($facts as $fact) {
            $content .= "- {$fact->key}: {$fact->value}\n";
        }
        return ['content' => trim($content), 'sources' => []];
    }

    /**
     * Retrieves platform statistics.
     */
    private function getPlatformStats(): array
    {
        try {
            $stats = [
                "Aktiv vakansiyalar" => DB::table('jobs')->where('status', 1)->count(),
                "Yangiliklar" => DB::table('news')->count(),
                "Treninglar" => DB::table('trainings')->count(),
                "Hujjatlar/Kitoblar" => DB::table('ai_documents')->count(),
            ];
            $content = "Platforma bo'yicha umumiy statistika:\n";
            foreach ($stats as $key => $value) {
                $content .= "- {$key}: {$value} ta\n";
            }
            return ['content' => trim($content), 'sources' => []];
        } catch (\Exception $e) {
            Log::error('get_platform_stats error', ['error' => $e->getMessage()]);
            return ['content' => 'Statistikani olishda xatolik.', 'sources' => []];
        }
    }

    /**
     * Formats a single item from a table into a structured array.
     */
    protected function formatItemContent($item, string $table): array
    {
        $content = '';
        $url = null;
        $title = $item->title ?? 'Noma\'lum';

        switch ($table) {
            case 'jobs':
                $url = "{$this->baseUrl}/job_details/{$item->id}";
                $content = "Ish e'loni: **{$item->title}**\n- Kompaniya: {$item->company}\n- Joylashuv: {$item->location}";
                break;
            case 'news':
                $url = "{$this->baseUrl}/single-blog/{$item->id}"; // Assuming single-blog is the correct route for news
                $content = "Yangilik: **{$item->title}**\n" . mb_substr(strip_tags($item->desc ?? ''), 0, 150) . "...";
                break;
            case 'trainings':
                $url = "{$this->baseUrl}/trainings/{$item->id}";
                $content = "Trening: **{$item->title}**";
                break;
            case 'ai_documents':
                $content = "Hujjat: **{$item->title}**\n- Kategoriya: {$item->category}\n" . mb_substr(strip_tags($item->description ?? ''), 0, 150) . "...";
                break;
        }

        return ['content' => $content, 'url' => $url, 'title' => $title];
    }

    private function calculateSimilarity($items, $queryEmbedding): array
    {
        $results = [];
        foreach ($items as $item) {
            $itemEmbedding = json_decode($item->embedding, true);
            if (is_array($itemEmbedding) && !empty($itemEmbedding)) {
                 $results[] = ['item' => $item, 'similarity' => $this->cosineSimilarity($queryEmbedding, $itemEmbedding)];
            }
        }
        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        return array_slice($results, 0, 5);
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = $magA = $magB = 0.0;
        $len = min(count($a), count($b));
        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $magA += $a[$i] * $a[$i];
            $magB += $b[$i] * $b[$i];
        }
        $magA = sqrt($magA);
        $magB = sqrt($magB);
        return ($magA > 0 && $magB > 0) ? $dot / ($magA * $magB) : 0.0;
    }
}