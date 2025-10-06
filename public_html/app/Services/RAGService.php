<?php

namespace App\Services;

use App\Contracts\AIService;
use App\Jobs;
use App\Company;
use App\Registry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RAGService
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function buildContext(string $userMessage): string
    {
        $context = [];
        $messageLower = mb_strtolower($userMessage);

        // 1. Ish qidirish
        $jobsContext = $this->searchJobs($messageLower);
        if (!empty($jobsContext)) {
            $context[] = $jobsContext;
        }

        // 2. Kompaniya qidirish
        if ($this->isCompanyQuery($messageLower)) {
            $companyContext = $this->searchCompanies($messageLower);
            if (!empty($companyContext)) {
                $context[] = $companyContext;
            }
        }

        // 3. Ishchilar qidirish (agar kompaniya qidirsa)
        if ($this->isCandidateQuery($messageLower)) {
            $candidateContext = $this->searchCandidates($messageLower);
            if (!empty($candidateContext)) {
                $context[] = $candidateContext;
            }
        }

        // 4. News/Yangiliklar
        if ($this->isNewsQuery($messageLower)) {
            $newsContext = $this->searchNews($messageLower);
            if (!empty($newsContext)) {
                $context[] = $newsContext;
            }
        }

        // 5. Trainings/Treninglar
        if ($this->isTrainingQuery($messageLower)) {
            $trainingContext = $this->searchTrainings($messageLower);
            if (!empty($trainingContext)) {
                $context[] = $trainingContext;
            }
        }

        // 6. Custom Documents (AI Knowledge Base)
        $documentContext = $this->searchDocuments($messageLower);
        if (!empty($documentContext)) {
            $context[] = $documentContext;
        }

        // 7. Umumiy statistika
        $statsContext = $this->getStatsContext();
        if (!empty($statsContext)) {
            $context[] = $statsContext;
        }

        return implode("\n\n---\n\n", $context);
    }

    protected function searchJobs(string $query): string
    {
        // SEMANTIC SEARCH (agar embedding mavjud bo'lsa)
        try {
            Log::info('Semantic search boshlandi', ['query' => $query]);
            $queryEmbedding = $this->aiService->embed($query);
            Log::info('Query embedding yaratildi', ['dimension' => count($queryEmbedding)]);
            
            $semanticResults = Jobs::searchSimilar($queryEmbedding, 5);
            Log::info('Semantic results', ['count' => count($semanticResults)]);
            
            if (!empty($semanticResults)) {
                foreach ($semanticResults as $result) {
                    Log::info('Similarity', [
                        'job' => $result['job']->title,
                        'similarity' => $result['similarity']
                    ]);
                }
                
                $jobs = collect($semanticResults)
                    ->filter(fn($r) => $r['similarity'] > 0.4)
                    ->pluck('job');
                
                if ($jobs->isNotEmpty()) {
                    Log::info('Semantic search topdi', ['count' => $jobs->count()]);
                    return $this->formatJobsContext($jobs, "Semantic Search - Eng mos");
                }
            }
        } catch (\Exception $e) {
            Log::error('Semantic search xato', ['error' => $e->getMessage()]);
        }

        // KEYWORD SEARCH (fallback yoki embedding bo'lmasa)
        $location = $this->extractLocation($query);
        $jobType = $this->extractJobType($query);
        $skills = $this->extractSkills($query);

        $jobsQuery = Jobs::where('status', 1);

        // Location filter
        if ($location) {
            $jobsQuery->where('location', 'LIKE', "%{$location}%");
        }

        // Type filter
        if ($jobType) {
            $jobsQuery->where('type', 'LIKE', "%{$jobType}%");
        }

        // Skills/Title filter
        if (!empty($skills)) {
            $jobsQuery->where(function($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhere('title', 'LIKE', "%{$skill}%")
                      ->orWhere('info', 'LIKE', "%{$skill}%")
                      ->orWhere('quals', 'LIKE', "%{$skill}%");
                }
            });
        }

        $jobs = $jobsQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'company', 'location', 'type', 'salary', 'info']);

        if ($jobs->isEmpty()) {
            // Agar aniq topilmasa, eng yangi 3tani ko'rsat
            $jobs = Jobs::where('status', 1)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get(['id', 'title', 'company', 'location', 'type', 'salary', 'info']);
        }

        if ($jobs->isEmpty()) {
            return '';
        }

        return $this->formatJobsContext($jobs, "Keyword Search");
    }

    protected function formatJobsContext($jobs, $searchType = "Topilgan"): string
    {
        $context = "## {$searchType} Ish E'lonlari:\n\n";
        $baseUrl = env('APP_URL', 'http://localhost:8000');
        
        foreach ($jobs as $job) {
            $jobUrl = "{$baseUrl}/job_details/{$job->id}";
            
            $context .= "**{$job->title}**\n";
            $context .= "- Kompaniya: {$job->company}\n";
            $context .= "- Joylashuv: {$job->location}\n";
            $context .= "- Turi: {$job->type}\n";
            if (!empty($job->salary)) {
                $context .= "- Maosh: {$job->salary}\n";
            }
            if (!empty($job->info)) {
                $context .= "- Info: " . mb_substr(strip_tags($job->info), 0, 150) . "...\n";
            }
            $context .= "- [Batafsil ko'rish]({$jobUrl})\n\n";
        }

        return $context;
    }

    protected function searchCompanies(string $query): string
    {
        $companies = Company::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->limit(3)
            ->get(['id', 'name', 'email', 'phone', 'address']);

        if ($companies->isEmpty()) {
            return '';
        }

        $context = "## Topilgan Kompaniyalar:\n\n";
        
        foreach ($companies as $company) {
            $context .= "**{$company->name}**\n";
            if (!empty($company->phone)) $context .= "- Telefon: {$company->phone}\n";
            if (!empty($company->email)) $context .= "- Email: {$company->email}\n";
            if (!empty($company->address)) $context .= "- Manzil: {$company->address}\n";
            $context .= "\n";
        }

        return $context;
    }

    protected function searchCandidates(string $query): string
    {
        $candidates = Registry::whereNotNull('cv')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'surname', 'email', 'phone', 'skills']);

        if ($candidates->isEmpty()) {
            return '';
        }

        $context = "## Mavjud Nomzodlar:\n\n";
        
        foreach ($candidates as $candidate) {
            $context .= "**{$candidate->name} {$candidate->surname}**\n";
            if (!empty($candidate->skills)) $context .= "- Ko'nikmalar: {$candidate->skills}\n";
            if (!empty($candidate->email)) $context .= "- Email: {$candidate->email}\n";
            $context .= "\n";
        }

        return $context;
    }

    protected function extractLocation(string $query): ?string
    {
        $locations = ['toshkent', 'samarqand', 'buxoro', 'farg\'ona', 'namangan', 'andijon', 'qarshi', 'nukus'];
        
        foreach ($locations as $location) {
            if (mb_strpos($query, $location) !== false) {
                return $location;
            }
        }
        
        return null;
    }

    protected function extractJobType(string $query): ?string
    {
        $types = [
            'full time' => 'full',
            'part time' => 'part',
            'remote' => 'remote',
            'masofaviy' => 'remote',
            'to\'liq' => 'full',
        ];
        
        foreach ($types as $keyword => $type) {
            if (mb_strpos($query, $keyword) !== false) {
                return $type;
            }
        }
        
        return null;
    }

    protected function extractSkills(string $query): array
    {
        $skills = [
            'php', 'laravel', 'javascript', 'react', 'vue', 'node', 'python', 'java',
            'developer', 'dasturchi', 'dizayner', 'designer', 'manager', 'menejer',
            'accountant', 'buxgalter', 'marketing', 'sales', 'sotuv'
        ];
        
        $found = [];
        
        foreach ($skills as $skill) {
            if (mb_strpos($query, $skill) !== false) {
                $found[] = $skill;
            }
        }
        
        return $found;
    }

    protected function isCompanyQuery(string $message): bool
    {
        $keywords = ['kompaniya', 'company', 'firma', 'tashkilot'];
        foreach ($keywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) return true;
        }
        return false;
    }

    protected function isCandidateQuery(string $message): bool
    {
        $keywords = ['ishchi', 'nomzod', 'candidate', 'xodim', 'kadr'];
        foreach ($keywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) return true;
        }
        return false;
    }

    protected function searchNews(string $query): string
    {
        try {
            $queryEmbedding = $this->aiService->embed($query);
            
            $news = DB::table('news')->whereNotNull('embedding')->get();
            $results = $this->calculateSimilarity($news, $queryEmbedding);
            
            if (!empty($results)) {
                $context = "## Tegishli Yangiliklar:\n\n";
                foreach ($results as $r) {
                    if ($r['similarity'] > 0.5) {
                        $context .= "**{$r['item']->title}**\n";
                        $context .= mb_substr(strip_tags($r['item']->desc ?? ''), 0, 200) . "...\n\n";
                    }
                }
                return $context;
            }
        } catch (\Exception $e) {}
        
        return '';
    }

    protected function searchTrainings(string $query): string
    {
        try {
            $queryEmbedding = $this->aiService->embed($query);
            
            $trainings = DB::table('trainings')->whereNotNull('embedding')->get();
            $results = $this->calculateSimilarity($trainings, $queryEmbedding);
            
            if (!empty($results)) {
                $context = "## Tegishli Treninglar:\n\n";
                foreach ($results as $r) {
                    if ($r['similarity'] > 0.5) {
                        $context .= "**{$r['item']->title}**\n";
                        $context .= mb_substr(strip_tags($r['item']->desc ?? ''), 0, 200) . "...\n\n";
                    }
                }
                return $context;
            }
        } catch (\Exception $e) {}
        
        return '';
    }

    protected function calculateSimilarity($items, $queryEmbedding): array
    {
        $results = [];
        
        foreach ($items as $item) {
            $itemEmbedding = json_decode($item->embedding, true);
            if (!is_array($itemEmbedding)) continue;
            
            $similarity = $this->cosineSimilarity($queryEmbedding, $itemEmbedding);
            $results[] = ['item' => $item, 'similarity' => $similarity];
        }

        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        return array_slice($results, 0, 3);
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) return 0.0;
        $dot = $magA = $magB = 0;
        for ($i = 0; $i < count($a); $i++) {
            $dot += $a[$i] * $b[$i];
            $magA += $a[$i] * $a[$i];
            $magB += $b[$i] * $b[$i];
        }
        $magA = sqrt($magA);
        $magB = sqrt($magB);
        return ($magA && $magB) ? $dot / ($magA * $magB) : 0.0;
    }

    protected function isNewsQuery(string $message): bool
    {
        $keywords = ['yangilik', 'news', 'xabar', 'voqea'];
        foreach ($keywords as $kw) {
            if (mb_strpos($message, $kw) !== false) return true;
        }
        return false;
    }

    protected function isTrainingQuery(string $message): bool
    {
        $keywords = ['trening', 'training', 'o\'quv', 'kurs', 'ta\'lim'];
        foreach ($keywords as $kw) {
            if (mb_strpos($message, $kw) !== false) return true;
        }
        return false;
    }

    protected function getStatsContext(): string
    {
        try {
            $totalJobs = Jobs::where('status', 1)->count();
            $totalNews = DB::table('news')->count();
            $totalTrainings = DB::table('trainings')->count();
            
            return "## Platforma Statistikasi:\n" .
                   "- Aktiv vakansiyalar: {$totalJobs}\n" .
                   "- Yangiliklar: {$totalNews}\n" .
                   "- Treninglar: {$totalTrainings}\n";
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function searchDocuments(string $query): string
    {
        try {
            $queryEmbedding = $this->aiService->embed($query);
            
            $documents = DB::table('ai_documents')->whereNotNull('embedding')->get();
            $results = $this->calculateSimilarity($documents, $queryEmbedding);
            
            if (!empty($results)) {
                $context = "## Tegishli Hujjatlar:\n\n";
                foreach ($results as $r) {
                    if ($r['similarity'] > 0.6) {
                        $context .= "**{$r['item']->title}**\n";
                        if (!empty($r['item']->category)) {
                            $context .= "- Kategoriya: {$r['item']->category}\n";
                        }
                        if (!empty($r['item']->description)) {
                            $context .= "- Tavsif: {$r['item']->description}\n";
                        }
                        $context .= "- Mazmun: " . mb_substr(strip_tags($r['item']->content ?? ''), 0, 300) . "...\n\n";
                    }
                }
                return $context;
            }
        } catch (\Exception $e) {
            Log::error('Document search error', ['error' => $e->getMessage()]);
        }
        
        return '';
    }
}
