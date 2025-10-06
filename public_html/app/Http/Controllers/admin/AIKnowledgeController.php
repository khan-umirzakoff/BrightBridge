<?php

namespace App\Http\Controllers\admin;

use App\AiKnowledge;
use App\Contracts\AIService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIKnowledgeController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }
    public function index()
    {
        $knowledge = AiKnowledge::orderBy('category')
            ->orderBy('priority', 'desc')
            ->paginate(20);
            
        return view('admin.pages.ai_knowledge', compact('knowledge'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:0|max:100',
        ]);

        $data = $request->all();
        
        // Embedding yaratish
        try {
            $embedding = $this->aiService->embed($request->value);
            $data['embedding'] = json_encode($embedding);
        } catch (\Exception $e) {
            Log::error('Embedding creation failed', ['error' => $e->getMessage()]);
        }

        AiKnowledge::create($data);

        return redirect()->back()->with('success', 'Ma\'lumot muvaffaqiyatli qo\'shildi va embedding yaratildi');
    }

    public function update(Request $request, $id)
    {
        $knowledge = AiKnowledge::findOrFail($id);
        
        $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:0|max:100',
        ]);

        $data = $request->all();
        
        // Embedding yangilash (agar value o'zgargan bo'lsa)
        if ($knowledge->value !== $request->value) {
            try {
                $embedding = $this->aiService->embed($request->value);
                $data['embedding'] = json_encode($embedding);
            } catch (\Exception $e) {
                Log::error('Embedding update failed', ['error' => $e->getMessage()]);
            }
        }

        $knowledge->update($data);

        return redirect()->back()->with('success', 'Ma\'lumot va embedding yangilandi');
    }

    public function destroy($id)
    {
        AiKnowledge::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Ma\'lumot o\'chirildi');
    }

    public function seedDefault()
    {
        $defaultKnowledge = [
            [
                'category' => 'contact',
                'key' => 'Telefon raqam',
                'value' => '+998 71 123 45 67',
                'description' => 'Asosiy qo\'ng\'iroq raqami',
                'priority' => 100,
            ],
            [
                'category' => 'contact',
                'key' => 'Email',
                'value' => 'info@jobcare.uz',
                'description' => 'Email manzili',
                'priority' => 90,
            ],
            [
                'category' => 'contact',
                'key' => 'Ish vaqti',
                'value' => 'Dushanba-Juma: 9:00-18:00',
                'description' => 'Ofis ish vaqti',
                'priority' => 80,
            ],
            [
                'category' => 'about',
                'key' => 'Platforma haqida',
                'value' => 'JobCare - O\'zbekistondagi eng yirik ish topish platformasi. Biz ishchilar va ish beruvchilarni bog\'lab beramiz.',
                'description' => 'Qisqacha tavsif',
                'priority' => 95,
            ],
            [
                'category' => 'service',
                'key' => 'Bepul xizmatlar',
                'value' => 'Ish e\'lonlarini ko\'rish, CV yuklash, vakansiyalarga murojaat qilish - mutlaqo bepul!',
                'description' => 'Bepul xizmatlar',
                'priority' => 85,
            ],
            [
                'category' => 'faq',
                'key' => 'Qanday ro\'yxatdan o\'tish mumkin?',
                'value' => 'Saytning yuqori qismidagi "Ro\'yxatdan o\'tish" tugmasini bosing va ma\'lumotlaringizni kiriting.',
                'description' => 'Ro\'yxatdan o\'tish',
                'priority' => 75,
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
