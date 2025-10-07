<?php

namespace App\Http\Controllers\admin;

use App\AiKnowledge;
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
                'with_embedding' => AiKnowledge::whereNotNull('embedding')->count(),
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

        AiKnowledge::create($data);

        return redirect()->back()->with('success', 'Ma\'lumot muvaffaqiyatli qo\'shildi va embedding yaratildi');
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

        return redirect()->back()->with('success', 'Ma\'lumot va embedding yangilandi');
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

        // Faqat ai_knowledge uchun bulk embedding
        $knowledges = AiKnowledge::whereNull('embedding')->orWhere('embedding', '')->get();

        if ($knowledges->isEmpty()) {
            return redirect()->back()->with('info', 'Embedding yaratish uchun ma\'lumot yo\'q.');
        }

        // Bulk embedding boshlandi flagini o'rnatish
        Cache::put('bulk_knowledge_embedding_in_progress', true, now()->addMinutes(60)); // 1 soat

        // Har bir ma'lumot uchun job yaratish
        foreach ($knowledges as $knowledge) {
            \App\Jobs\ProcessKnowledgeEmbedding::dispatch($knowledge);
        }

        $count = $knowledges->count();
        return redirect()->back()->with('success', "{$count} ta ma'lumot uchun embedding yaratish boshlandi. Jarayon fon rejimida davom etadi.");
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

        return redirect()->back()->with('success', 'Standart ma\'lumotlar yuklandi');
    }
}