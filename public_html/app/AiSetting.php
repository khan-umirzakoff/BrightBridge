<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getSystemPrompt()
    {
        return self::where('key', 'system_prompt')->value('value') ?? 'You are a helpful assistant.';
    }

    public static function setSystemPrompt($prompt)
    {
        return self::updateOrCreate(
            ['key' => 'system_prompt'],
            ['value' => $prompt]
        );
    }
}
