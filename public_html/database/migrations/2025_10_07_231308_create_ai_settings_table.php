<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAiSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, text, password, select
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('ai_settings')->insert([
            [
                'key' => 'ai_provider',
                'value' => 'gemini',
                'type' => 'select',
                'description' => 'AI Provider (openai or gemini)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'openai_api_key',
                'value' => '',
                'type' => 'password',
                'description' => 'OpenAI API Key',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'openai_model',
                'value' => 'gpt-4o',
                'type' => 'string',
                'description' => 'OpenAI Chat Model',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'openai_embedding_model',
                'value' => 'text-embedding-3-small',
                'type' => 'string',
                'description' => 'OpenAI Embedding Model',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'gemini_api_key',
                'value' => '',
                'type' => 'password',
                'description' => 'Google Gemini API Key',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'gemini_model',
                'value' => 'gemini-flash-latest',
                'type' => 'string',
                'description' => 'Gemini Chat Model (gemini-flash-latest = Gemini 2.5 Flash, the most powerful Flash model)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'gemini_embedding_model',
                'value' => 'text-embedding-004',
                'type' => 'string',
                'description' => 'Gemini Embedding Model (latest and most powerful)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'system_prompt',
                'value' => 'You are a helpful AI assistant for JobCare platform.',
                'type' => 'text',
                'description' => 'System Prompt for AI Assistant',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_settings');
    }
}
