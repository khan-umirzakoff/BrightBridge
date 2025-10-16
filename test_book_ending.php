<?php
require __DIR__ . '/public_html/vendor/autoload.php';

$app = require_once __DIR__ . '/public_html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Book Ending Search ===\n\n";

$ragService = app(\App\Services\RAGService::class);

// Test queries for ending
$queries = [
    'kitobning oxirgi qismi',
    'Preparing Perfect CV ending conclusion',
    'yakuniy maslahat xulosa',
    'CV qo\'llanma oxiri'
];

foreach ($queries as $query) {
    echo "Query: '$query'\n";
    try {
        $result = $ragService->executeTool('search_general', ['query' => $query]);
        $content = $result['content'] ?? 'No content';

        echo "  Result length: " . mb_strlen($content) . " chars\n";
        echo "  First 800 chars:\n";
        echo "  ---\n";
        echo "  " . str_replace("\n", "\n  ", mb_substr($content, 0, 800)) . "\n";
        echo "  ---\n";
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "\n=== Testing Multiple Books Detection ===\n\n";

$result = $ragService->executeTool('search_general', ['query' => 'barcha CV kitoblar ro\'yxati']);
$content = $result['content'] ?? 'No content';

echo "Query: 'barcha CV kitoblar ro'yxati'\n";
echo "Result length: " . mb_strlen($content) . " chars\n";
echo "First 1000 chars:\n";
echo "---\n";
echo str_replace("\n", "\n", mb_substr($content, 0, 1000)) . "\n";
echo "---\n";

// Check actual documents in database
echo "\n=== Documents in Database ===\n\n";
$docs = DB::table('ai_documents')->select('id', 'title', 'category')->get();
foreach ($docs as $doc) {
    echo "- {$doc->title} (ID: {$doc->id}, Category: {$doc->category})\n";
}

echo "\n=== Done ===\n";
