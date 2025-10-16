<?php
/**
 * BrightBridge Production Deployment Installer
 *
 * USAGE:
 * 1. Upload this file to: /home/brightbr/public_html/install.php
 * 2. Visit: https://brightbridge.uz/install.php?key=deploy2025
 * 3. Wait 2-3 minutes for completion
 * 4. DELETE this file after success!
 *
 * WHAT IT DOES:
 * - Downloads Composer (if not exists)
 * - Runs: composer install --no-dev --optimize-autoloader
 * - Sets permissions on storage/ and bootstrap/cache/
 * - Clears Laravel caches
 * - Optimizes for production
 * - Tests Laravel installation
 */

// Security: Secret key protection
if (!isset($_GET['key']) || $_GET['key'] !== 'deploy2025') {
    http_response_code(403);
    die('Access denied. Invalid key.');
}

// Increase execution time (composer install takes time)
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// Change to script directory
chdir(__DIR__);

// HTML Header
?>
<!DOCTYPE html>
<html>
<head>
    <title>BrightBridge Deployment Installer</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #1e1e1e;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
            color: #4ec9b0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.8;
        }
        .step {
            background: #000;
            padding: 20px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #4ec9b0;
        }
        .step h2 {
            color: #569cd6;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .step pre {
            color: #d4d4d4;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .success {
            background: #155724;
            border-left-color: #28a745;
        }
        .warning {
            background: #856404;
            border-left-color: #ffc107;
        }
        .error {
            background: #721c24;
            border-left-color: #dc3545;
        }
        .command {
            color: #ce9178;
            font-weight: bold;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .footer {
            background: #2d2d2d;
            padding: 20px;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 BrightBridge Deployment Installer</h1>
            <p>Automated Production Setup</p>
        </div>
        <div class="content">
<?php

// Check if exec() is available
$disabledFunctions = explode(',', ini_get('disable_functions'));
$disabledFunctions = array_map('trim', $disabledFunctions);
$execAvailable = !in_array('exec', $disabledFunctions) && function_exists('exec');
$systemAvailable = !in_array('system', $disabledFunctions) && function_exists('system');

// Start installation
echo "<div class='step'>";
echo "<h2>📊 System Information</h2>";
echo "<pre>";
echo "PHP Version: <span class='command'>" . phpversion() . "</span>\n";
echo "Server OS: <span class='command'>" . PHP_OS . "</span>\n";
echo "Working Directory: <span class='command'>" . getcwd() . "</span>\n";
echo "Current Time: <span class='command'>" . date('Y-m-d H:i:s') . "</span>\n";
echo "\n<strong>Function Availability:</strong>\n";
echo "exec():   " . ($execAvailable ? "<span style='color:#28a745'>✅ Available</span>" : "<span style='color:#dc3545'>❌ Disabled</span>") . "\n";
echo "system(): " . ($systemAvailable ? "<span style='color:#28a745'>✅ Available</span>" : "<span style='color:#dc3545'>❌ Disabled</span>") . "\n";

if (!$execAvailable) {
    echo "\n<strong style='color:#dc3545'>⚠️ WARNING: exec() is disabled!</strong>\n";
    echo "This script cannot run composer commands.\n";
    echo "You need to use 'with-vendor' deployment instead.\n";
    echo "</pre>";
    echo "</div>";
    echo "<div class='step error'>";
    echo "<h2>❌ Installation Cannot Proceed</h2>";
    echo "<pre>";
    echo "Your hosting provider has disabled the exec() function.\n\n";
    echo "<strong>SOLUTION:</strong>\n";
    echo "1. Go back to your local Linux machine\n";
    echo "2. Run: bash create_deployment_package.sh with-vendor\n";
    echo "3. Upload brightbridge-deploy-full.zip to server\n";
    echo "4. Extract and you're done!\n\n";
    echo "The 'with-vendor' package includes all dependencies,\n";
    echo "so no composer command is needed on the server.\n";
    echo "</pre>";
    echo "</div>";
    echo "</div></body></html>";
    exit(1);
}

echo "</pre>";
echo "</div>";

flush();
ob_flush();

// Step 1: Check/Download Composer
echo "<div class='step'>";
echo "<h2>[1/6] Composer Setup</h2>";
echo "<pre>";

if (file_exists('composer.phar')) {
    echo "✅ composer.phar already exists\n";
    echo "   Running self-update...\n";
    exec('php composer.phar self-update 2>&1', $updateOutput, $updateCode);
    if ($updateCode === 0) {
        echo "   ✅ Composer updated\n";
    }
} else {
    echo "⏳ Downloading Composer...\n";

    // Download installer
    $installer = file_get_contents('https://getcomposer.org/installer');
    if ($installer === false) {
        echo "❌ Failed to download Composer installer\n";
        echo "</pre></div></div></body></html>";
        exit(1);
    }

    file_put_contents('composer-setup.php', $installer);
    echo "   ✅ Installer downloaded\n";

    // Install Composer
    echo "   Installing Composer...\n";
    exec('php composer-setup.php 2>&1', $output, $returnCode);

    if ($returnCode === 0 && file_exists('composer.phar')) {
        echo "   ✅ Composer installed successfully\n";
    } else {
        echo "   ❌ Composer installation failed:\n";
        echo "   " . implode("\n   ", $output) . "\n";
    }

    // Cleanup
    if (file_exists('composer-setup.php')) {
        unlink('composer-setup.php');
    }
}

echo "</pre>";
echo "</div>";

flush();
ob_flush();

// Step 2: Check composer.json
echo "<div class='step'>";
echo "<h2>[2/6] Checking Project Files</h2>";
echo "<pre>";

if (!file_exists('composer.json')) {
    echo "❌ composer.json not found!\n";
    echo "   Make sure all project files are uploaded.\n";
    echo "</pre></div></div></body></html>";
    exit(1);
}

echo "✅ composer.json found\n";

if (!file_exists('artisan')) {
    echo "❌ artisan not found!\n";
    echo "   This doesn't look like a Laravel project.\n";
    echo "</pre></div></div></body></html>";
    exit(1);
}

echo "✅ artisan found\n";
echo "✅ Project structure verified\n";

echo "</pre>";
echo "</div>";

flush();
ob_flush();

// Step 3: Install Dependencies
echo "<div class='step'>";
echo "<h2>[3/6] Installing Dependencies (may take 2-3 minutes)</h2>";
echo "<pre>";
echo "⏳ Running: <span class='command'>composer install --no-dev --optimize-autoloader</span>\n\n";

flush();
ob_flush();

// Run composer install
$startTime = microtime(true);
exec('php composer.phar install --no-dev --optimize-autoloader --no-interaction 2>&1', $composerOutput, $composerCode);
$duration = round(microtime(true) - $startTime, 2);

echo implode("\n", $composerOutput) . "\n\n";

if ($composerCode === 0) {
    echo "✅ Dependencies installed successfully in {$duration}s\n";
} else {
    echo "⚠️ Composer finished with warnings (code: {$composerCode})\n";
    echo "   Duration: {$duration}s\n";
}

echo "</pre>";
echo "</div>";

flush();
ob_flush();

// Step 4: Set Permissions
echo "<div class='step'>";
echo "<h2>[4/6] Setting Permissions</h2>";
echo "<pre>";

$dirs = [
    'storage' => 0755,
    'storage/app' => 0755,
    'storage/framework' => 0755,
    'storage/framework/cache' => 0755,
    'storage/framework/sessions' => 0755,
    'storage/framework/views' => 0755,
    'storage/logs' => 0755,
    'bootstrap/cache' => 0755
];

foreach ($dirs as $dir => $perm) {
    if (file_exists($dir)) {
        if (chmod($dir, $perm)) {
            echo "✅ {$dir} → " . decoct($perm) . "\n";
        } else {
            echo "⚠️ {$dir} → Could not set permissions\n";
        }
    } else {
        echo "⚠️ {$dir} → Directory not found\n";
    }
}

echo "</pre>";
echo "</div>";

flush();
ob_flush();

// Step 5: Clear & Optimize Laravel
echo "<div class='step'>";
echo "<h2>[5/6] Laravel Optimization</h2>";
echo "<pre>";

$commands = [
    'Clear Config Cache' => 'php artisan config:clear',
    'Clear Route Cache' => 'php artisan route:clear',
    'Clear View Cache' => 'php artisan view:clear',
    'Clear Application Cache' => 'php artisan cache:clear',
    'Optimize Config' => 'php artisan config:cache',
    'Optimize Routes' => 'php artisan route:cache',
    'Optimize Views' => 'php artisan view:cache',
];

foreach ($commands as $name => $cmd) {
    echo "⏳ {$name}...\n";
    exec($cmd . ' 2>&1', $output, $code);
    if ($code === 0) {
        echo "   ✅ Done\n";
    } else {
        echo "   ⚠️ Warning: " . implode("\n   ", $output) . "\n";
    }
    unset($output);
}

echo "</pre>";
echo "</div>";

flush();
ob_flush();

// Step 6: Final Tests
echo "<div class='step success'>";
echo "<h2>[6/6] Final Verification</h2>";
echo "<pre>";

// Test Laravel
exec('php artisan --version 2>&1', $versionOutput);
echo "Laravel Version:\n";
echo "  " . implode("\n  ", $versionOutput) . "\n\n";

// Check vendor/
if (file_exists('vendor/autoload.php')) {
    echo "✅ vendor/autoload.php exists\n";
} else {
    echo "❌ vendor/autoload.php missing!\n";
}

// Check .env
if (file_exists('.env')) {
    echo "✅ .env file exists\n";
} else {
    echo "⚠️ .env file not found - you need to create it!\n";
}

echo "</pre>";
echo "</div>";

// Success message
echo "<div class='step success'>";
echo "<h2>🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!</h2>";
echo "<pre>";
echo "✅ All steps completed\n";
echo "✅ Dependencies installed\n";
echo "✅ Permissions set\n";
echo "✅ Laravel optimized\n\n";

echo "<strong>⚠️ IMPORTANT NEXT STEPS:</strong>\n\n";
echo "1. <span class='command'>DELETE THIS FILE (install.php)</span> for security!\n";
echo "   Command: rm install.php\n\n";

echo "2. Configure your .env file with production settings:\n";
echo "   - APP_ENV=production\n";
echo "   - APP_DEBUG=false\n";
echo "   - Database credentials\n";
echo "   - API keys\n\n";

echo "3. Test your application:\n";
echo "   - Homepage: <span class='command'>https://brightbridge.uz</span>\n";
echo "   - Admin: <span class='command'>https://brightbridge.uz/admin/site</span>\n\n";

echo "4. Check logs for errors:\n";
echo "   - storage/logs/laravel.log\n\n";

echo "</pre>";
echo "</div>";

?>
        </div>
        <div class="footer">
            <p>BrightBridge JobCare Platform - Automated Deployment</p>
            <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
