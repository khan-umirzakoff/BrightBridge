<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Contracts\AIService;

class ProcessDocumentEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $documentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AIService $aiService)
    {
        $document = DB::table('ai_documents')->where('id', $this->documentId)->first();
        if (!$document) {
            return;
        }

        $content = $document->content;

        // Split content into chunks for embedding
        $chunkSize = 2000;
        $chunks = str_split($content, $chunkSize);
        $embeddings = [];

        // Limit to max 20 chunks for better semantic search granularity
        $chunks = array_slice($chunks, 0, 20);
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            if (trim($chunk)) {
                $embeddings[] = $this->retryEmbed($aiService, $chunk, $index);
            }

            // Update progress
            $progress = intval((($index + 1) / $totalChunks) * 100);
            Cache::put('document_progress_' . $this->documentId, [
                'progress' => $progress,
                'chunks_processed' => $index + 1,
                'total_chunks' => $totalChunks
            ], 3600); // 1 hour

            // Sleep to simulate processing time
            sleep(2);
        }

        // Update document with embeddings
        DB::table('ai_documents')->where('id', $this->documentId)->update([
            'embedding' => json_encode($embeddings),
            'updated_at' => now(),
        ]);

        // Clear progress cache
        Cache::forget('document_progress_' . $this->documentId);
    }

    protected function retryEmbed($aiService, $chunk, $index)
    {
        $maxRetries = 3;
        $retryDelay = 15; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $aiService->embed($chunk);
            } catch (\Exception $e) {
                Log::warning("Embedding attempt {$attempt} failed for chunk {$index}", [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt
                ]);

                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                } else {
                    Log::error("Embedding failed after {$maxRetries} attempts for chunk {$index}", [
                        'error' => $e->getMessage()
                    ]);
                    // Return empty array or skip
                    return [];
                }
            }
        }
    }
}
