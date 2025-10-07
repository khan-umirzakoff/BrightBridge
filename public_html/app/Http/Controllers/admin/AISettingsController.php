<?php

namespace App\Http\Controllers\admin;

use App\AiSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AISettingsController extends Controller
{
    public function index()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $settings = AiSetting::orderBy('key')->get()->groupBy(function($setting) {
            if (str_starts_with($setting->key, 'openai_')) return 'openai';
            if (str_starts_with($setting->key, 'gemini_')) return 'gemini';
            if ($setting->key === 'ai_provider') return 'general';
            if ($setting->key === 'system_prompt') return 'system';
            return 'other';
        });

        $currentProvider = AiSetting::getProvider();
        $systemPrompt = AiSetting::getSystemPrompt();

        return view('admin.pages.ai_settings', compact('settings', 'currentProvider', 'systemPrompt'));
    }

    public function update(Request $request)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $validated = $request->validate([
            'ai_provider' => 'required|in:openai,gemini',
            'openai_api_key' => 'nullable|string',
            'openai_model' => 'nullable|string',
            'openai_embedding_model' => 'nullable|string',
            'gemini_api_key' => 'nullable|string',
            'gemini_model' => 'nullable|string',
            'gemini_embedding_model' => 'nullable|string',
            'system_prompt' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                AiSetting::set($key, $value);
            }
        }

        // Clear all AI-related caches
        Cache::flush();

        return redirect()->back()->with('success', 'AI Settings updated successfully');
    }

    public function testConnection(Request $request)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $provider = $request->input('provider');
        $apiKey = $request->input('api_key');

        try {
            // Test API connection based on provider
            if ($provider === 'openai') {
                // Simple test request to OpenAI
                $ch = curl_init('https://api.openai.com/v1/models');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiKey,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    return response()->json(['success' => true, 'message' => 'OpenAI connection successful']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid OpenAI API key'], 400);
                }
            } else {
                // Test Gemini connection
                $url = "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    return response()->json(['success' => true, 'message' => 'Gemini connection successful']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid Gemini API key'], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
