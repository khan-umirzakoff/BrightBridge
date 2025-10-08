<?php

namespace App\Http\Controllers\admin;

use App\AiKnowledge;
use App\AiSetting;
use App\Contracts\AIService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AIKnowledgeController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }
    public function index()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $knowledge = AiKnowledge::orderBy('category')
            ->orderBy('priority', 'desc')
            ->paginate(20);

        $categories = AiKnowledge::select('category')->distinct()->pluck('category');

        // Embedding statistikasi
        $stats = [
            'ai_knowledge' => [
                'total' => AiKnowledge::count(),
                'with_embedding' => AiKnowledge::whereNotNull('embedding')->where('embedding', '!=', '[]')->where('embedding', '!=', '')->count(),
            ],
            'jobs' => [
                'total' => \App\Jobs::count(),
                'with_embedding' => \App\Jobs::whereNotNull('embedding')->count(),
            ],
            'news' => [
                'total' => \App\News::count(),
                'with_embedding' => \App\News::whereNotNull('embedding')->count(),
            ],
            'trainings' => [
                'total' => \App\Trainings::count(),
                'with_embedding' => \App\Trainings::whereNotNull('embedding')->count(),
            ],
            'ai_documents' => [
                'total' => DB::table('ai_documents')->count(),
                'with_embedding' => DB::table('ai_documents')->whereNotNull('embedding')->count(),
            ],
        ];

        return view('admin.pages.ai_knowledge', compact('knowledge', 'categories', 'stats'));
    }

    public function store(Request $request)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:0|max:5',
        ]);

        $data = $request->all();

        $knowledge = AiKnowledge::create($data);

        // Generate embedding after creation
        try {
            $text = "Kategoriya: {$knowledge->category}. " .
                    "Kalit: {$knowledge->key}. " .
                    "Qiymat: {$knowledge->value}. " .
                    "Izoh: {$knowledge->description}";

            $embedding = $this->aiService->embed($text);

            $knowledge->update(['embedding' => json_encode($embedding)]);

            return redirect()->back()->with('success', 'Ma\'lumot muvaffaqiyatli qo\'shildi va embedding yaratildi');

        } catch (\Exception $e) {
            Log::error('Embedding generation failed on store', [
                'knowledge_id' => $knowledge->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('success', 'Ma\'lumot qo\'shildi, lekin embedding yaratishda xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $knowledge = AiKnowledge::findOrFail($id);

        $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:0|max:5',
        ]);

        $data = $request->all();

        $knowledge->update($data);

        // Re-generate embedding after update
        try {
            $text = "Kategoriya: {$knowledge->category}. " .
                    "Kalit: {$knowledge->key}. " .
                    "Qiymat: {$knowledge->value}. " .
                    "Izoh: {$knowledge->description}";

            $embedding = $this->aiService->embed($text);

            $knowledge->update(['embedding' => json_encode($embedding)]);

            return redirect()->back()->with('success', 'Ma\'lumot va embedding muvaffaqiyatli yangilandi');

        } catch (\Exception $e) {
            Log::error('Embedding generation failed on update', [
                'knowledge_id' => $knowledge->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('success', 'Ma\'lumot yangilandi, lekin embedding yaratishda xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        AiKnowledge::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Ma\'lumot o\'chirildi');
    }

    public function generateEmbedding($id)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $knowledge = AiKnowledge::findOrFail($id);

        try {
            $text = "Kategoriya: {$knowledge->category}. " .
                    "Kalit: {$knowledge->key}. " .
                    "Qiymat: {$knowledge->value}. " .
                    "Izoh: {$knowledge->description}";

            $embedding = $this->aiService->embed($text);

            $knowledge->update(['embedding' => json_encode($embedding)]);

            return redirect()->back()->with('success', 'Embedding muvaffaqiyatli yaratildi');

        } catch (\Exception $e) {
            Log::error('Manual embedding generation failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Embedding yaratishda xatolik: ' . $e->getMessage());
        }
    }

    public function generateAllEmbeddings()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        try {
            // Set max execution time
            set_time_limit(600); // 10 minutes
            ini_set('max_execution_time', '600');

            // Count missing embeddings
            $jobsCount = \App\Jobs::whereNull('embedding')->count();
            $newsCount = \App\News::whereNull('embedding')->count();
            $trainingsCount = \App\Trainings::whereNull('embedding')->count();
            $knowledgeCount = AiKnowledge::whereNull('embedding')->orWhere('embedding', '')->count();
            $documentsCount = DB::table('ai_documents')->whereNull('embedding')->count();

            $totalCount = $jobsCount + $newsCount + $trainingsCount + $knowledgeCount + $documentsCount;

            if ($totalCount === 0) {
                return redirect()->back()->with('info', 'Barcha ma\'lumotlar allaqachon embedding qilingan!');
            }

            Log::info('Batch embedding started', [
                'jobs' => $jobsCount,
                'news' => $newsCount,
                'trainings' => $trainingsCount,
                'knowledge' => $knowledgeCount,
                'documents' => $documentsCount,
                'total' => $totalCount
            ]);

            $processed = 0;
            $failed = 0;

            // Process Jobs
            if ($jobsCount > 0) {
                $jobs = \App\Jobs::whereNull('embedding')->limit(10)->get();
                foreach ($jobs as $job) {
                    try {
                        $text = "{$job->title} {$job->company} {$job->location} {$job->type} " .
                                strip_tags($job->info ?? '') . " " . strip_tags($job->quals ?? '') . " " . strip_tags($job->benefits ?? '');

                        $embedding = $this->aiService->embed($text);

                        DB::table('jobs')->where('id', $job->id)->update([
                            'embedding' => json_encode($embedding)
                        ]);

                        $processed++;
                        usleep(300000); // 0.3 second delay
                    } catch (\Exception $e) {
                        $failed++;
                        Log::error("Job embedding failed: {$job->id}", ['error' => $e->getMessage()]);
                    }
                }
            }

            // Process News
            if ($newsCount > 0) {
                $newsList = \App\News::whereNull('embedding')->limit(10)->get();
                foreach ($newsList as $item) {
                    try {
                        $text = "{$item->title} {$item->about} " . strip_tags($item->info ?? '');

                        $embedding = $this->aiService->embed($text);

                        DB::table('news')->where('id', $item->id)->update([
                            'embedding' => json_encode($embedding)
                        ]);

                        $processed++;
                        usleep(300000);
                    } catch (\Exception $e) {
                        $failed++;
                        Log::error("News embedding failed: {$item->id}", ['error' => $e->getMessage()]);
                    }
                }
            }

            // Process Trainings
            if ($trainingsCount > 0) {
                $trainings = \App\Trainings::whereNull('embedding')->limit(10)->get();
                foreach ($trainings as $item) {
                    try {
                        $text = "Training: {$item->title}";

                        $embedding = $this->aiService->embed($text);

                        DB::table('trainings')->where('id', $item->id)->update([
                            'embedding' => json_encode($embedding)
                        ]);

                        $processed++;
                        usleep(300000);
                    } catch (\Exception $e) {
                        $failed++;
                        Log::error("Training embedding failed: {$item->id}", ['error' => $e->getMessage()]);
                    }
                }
            }

            // Process AI Knowledge
            if ($knowledgeCount > 0) {
                $knowledges = AiKnowledge::whereNull('embedding')->orWhere('embedding', '')->limit(10)->get();
                foreach ($knowledges as $item) {
                    try {
                        $text = "Kategoriya: {$item->category}. Kalit: {$item->key}. Qiymat: {$item->value}. Izoh: {$item->description}";

                        $embedding = $this->aiService->embed($text);

                        DB::table('ai_knowledge')->where('id', $item->id)->update([
                            'embedding' => json_encode($embedding)
                        ]);

                        $processed++;
                        usleep(300000);
                    } catch (\Exception $e) {
                        $failed++;
                        Log::error("Knowledge embedding failed: {$item->id}", ['error' => $e->getMessage()]);
                    }
                }
            }

            Log::info("Batch embedding completed", [
                'processed' => $processed,
                'failed' => $failed,
                'total' => $totalCount
            ]);

            $remaining = $totalCount - $processed;
            $message = "Muvaffaqiyatli! {$processed} ta embedding yaratildi.";
            if ($failed > 0) {
                $message .= " {$failed} ta xatolik.";
            }
            if ($remaining > 0) {
                $message .= " Yana {$remaining} ta qoldi. Tugmani qayta bosing davom ettirish uchun.";
            } else {
                $message .= " Hammasi tayyor!";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Batch embedding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function seedDefault()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $defaultKnowledge = [
            [
                'category' => 'contact',
                'key' => 'Telefon raqam',
                'value' => '+998 71 123 45 67',
                'description' => 'Asosiy qo\'ng\'iroq raqami',
                'priority' => 5,
            ],
            [
                'category' => 'contact',
                'key' => 'Email',
                'value' => 'info@jobcare.uz',
                'description' => 'Email manzili',
                'priority' => 5,
            ],
            [
                'category' => 'contact',
                'key' => 'Ish vaqti',
                'value' => 'Dushanba-Juma: 9:00-18:00',
                'description' => 'Ofis ish vaqti',
                'priority' => 4,
            ],
            [
                'category' => 'about',
                'key' => 'Platforma haqida',
                'value' => 'JobCare - O\'zbekistondagi eng yirik ish topish platformasi. Biz ishchilar va ish beruvchilarni bog\'lab beramiz.',
                'description' => 'Qisqacha tavsif',
                'priority' => 5,
            ],
            [
                'category' => 'service',
                'key' => 'Bepul xizmatlar',
                'value' => 'Ish e\'lonlarini ko\'rish, CV yuklash, vakansiyalarga murojaat qilish - mutlaqo bepul!',
                'description' => 'Bepul xizmatlar',
                'priority' => 4,
            ],
            [
                'category' => 'faq',
                'key' => 'Qanday ro\'yxatdan o\'tish mumkin?',
                'value' => 'Saytning yuqori qismidagi "Ro\'yxatdan o\'tish" tugmasini bosing va ma\'lumotlaringizni kiriting.',
                'description' => 'Ro\'yxatdan o\'tish',
                'priority' => 3,
            ],
        ];

        foreach ($defaultKnowledge as $item) {
            AiKnowledge::updateOrCreate(
                ['category' => $item['category'], 'key' => $item['key']],
                $item
            );
        }

        // Now, generate embeddings for the seeded data
        $seededKeys = array_column($defaultKnowledge, 'key');
        $seededItems = AiKnowledge::whereIn('key', $seededKeys)->get();

        foreach ($seededItems as $knowledge) {
            try {
                $text = "Kategoriya: {$knowledge->category}. " .
                        "Kalit: {$knowledge->key}. " .
                        "Qiymat: {$knowledge->value}. " .
                        "Izoh: {$knowledge->description}";

                $embedding = $this->aiService->embed($text);

                $knowledge->update(['embedding' => json_encode($embedding)]);
                usleep(300000); // Prevent rate limiting
            } catch (\Exception $e) {
                Log::error('Embedding generation failed on seed', [
                    'knowledge_id' => $knowledge->id,
                    'error' => $e->getMessage()
                ]);
                // Continue to the next item even if one fails
            }
        }

        return redirect()->back()->with('success', 'Standart ma\'lumotlar yuklandi va embeddinglar yaratildi');
    }
}