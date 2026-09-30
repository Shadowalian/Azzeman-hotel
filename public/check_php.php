<?php
/**
 * PHP Configuration Diagnostic Script
 * 
 * This script checks if all required PHP extensions are installed.
 * Access this file via: https://yourdomain.com/check_php.php
 * 
 * IMPORTANT: Delete this file after checking, or protect it with .htaccess
 */

// Security: Only allow access in development or with a secret key
$secretKey = isset($_GET['key']) ? $_GET['key'] : '';
$allowedKey = 'azzeman_diagnostic_2024'; // Change this to a random string

if ($secretKey !== $allowedKey && !defined('APP_DEBUG')) {
    die('Access denied. Add ?key=azzeman_diagnostic_2024 to the URL.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Configuration Diagnostic - Azzeman Hotel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0E8040;
            border-bottom: 3px solid #0E8040;
            padding-bottom: 10px;
        }
        h2 {
            color: #333;
            margin-top: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .check {
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #0E8040;
            color: white;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PHP Configuration Diagnostic</h1>
        <p>This diagnostic tool checks if your PHP installation has all required extensions for Azzeman Hotel.</p>
        
        <h2>Required Extensions</h2>
        <?php
        $required = [
            'pdo' => 'PDO (PHP Data Objects)',
            'pdo_mysql' => 'PDO MySQL Driver',
            'mysqli' => 'MySQLi Extension (backup)',
            'mbstring' => 'Multibyte String',
            'json' => 'JSON',
            'session' => 'Session',
            'gd' => 'GD Image Library',
            'curl' => 'cURL',
            'openssl' => 'OpenSSL',
        ];
        
        $allGood = true;
        foreach ($required as $ext => $name) {
            $loaded = extension_loaded($ext);
            $class = $loaded ? 'success' : 'error';
            $icon = $loaded ? '✅' : '❌';
            
            if (!$loaded && ($ext === 'pdo' || $ext === 'pdo_mysql')) {
                $allGood = false;
            }
            
            echo "<div class='check $class'>";
            echo "<strong>$icon $name</strong> ";
            if ($loaded) {
                echo "is <strong>installed</strong>";
            } else {
                echo "is <strong>NOT installed</strong>";
            }
            echo "</div>";
        }
        ?>
        
        <h2>PDO Drivers</h2>
        <?php
        if (extension_loaded('pdo')) {
            $drivers = PDO::getAvailableDrivers();
            if (in_array('mysql', $drivers)) {
                echo "<div class='check success'>✅ PDO MySQL driver is available</div>";
            } else {
                echo "<div class='check error'>❌ PDO MySQL driver is NOT available</div>";
                $allGood = false;
            }
            echo "<div class='check info'>Available PDO drivers: " . implode(', ', $drivers) . "</div>";
        } else {
            echo "<div class='check error'>❌ PDO extension is not loaded</div>";
            $allGood = false;
        }
        ?>
        
        <h2>PHP Version</h2>
        <div class="check info">
            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
            <?php
            if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
                echo " ✅ (Recommended: 7.4+)";
            } else {
                echo " ⚠️ (Recommended: 7.4 or higher)";
            }
            ?>
        </div>
        
        <h2>PHP Configuration</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>error_reporting</td>
                <td><?php echo error_reporting(); ?></td>
            </tr>
            <tr>
                <td>display_errors</td>
                <td><?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></td>
            </tr>
            <tr>
                <td>upload_max_filesize</td>
                <td><?php echo ini_get('upload_max_filesize'); ?></td>
            </tr>
            <tr>
                <td>post_max_size</td>
                <td><?php echo ini_get('post_max_size'); ?></td>
            </tr>
            <tr>
                <td>memory_limit</td>
                <td><?php echo ini_get('memory_limit'); ?></td>
            </tr>
            <tr>
                <td>max_execution_time</td>
                <td><?php echo ini_get('max_execution_time'); ?> seconds</td>
            </tr>
        </table>
        
        <h2>Loaded Extensions</h2>
        <div class="check info">
            <strong>Total loaded extensions:</strong> <?php echo count(get_loaded_extensions()); ?>
        </div>
        <details>
            <summary style="cursor: pointer; color: #0E8040; margin-top: 10px;">Show all loaded extensions</summary>
            <div style="margin-top: 10px; max-height: 300px; overflow-y: auto;">
                <?php
                $extensions = get_loaded_extensions();
                sort($extensions);
                echo '<code>' . implode(', ', $extensions) . '</code>';
                ?>
            </div>
        </details>
        
        <?php if (!$allGood): ?>
        <h2>⚠️ Issues Found</h2>
        <div class="check error">
            <strong>Critical Issue:</strong> PDO MySQL extension is not installed or enabled.
            <br><br>
            <strong>How to fix on cPanel:</strong>
            <ol style="margin-top: 10px; padding-left: 20px;">
                <li>Log in to your cPanel account</li>
                <li>Go to <strong>"Select PHP Version"</strong> or <strong>"PHP Selector"</strong></li>
                <li>Select your PHP version (7.4 or higher recommended)</li>
                <li>Click on <strong>"Extensions"</strong> tab</li>
                <li>Find and enable <code>pdo_mysql</code> extension</li>
                <li>Also enable <code>pdo</code> if it's not already enabled</li>
                <li>Click <strong>"Save"</strong> or <strong>"Apply"</strong></li>
                <li>Refresh this page to verify the fix</li>
            </ol>
            <br>
            <strong>Alternative method (if PHP Selector is not available):</strong>
            <ol style="margin-top: 10px; padding-left: 20px;">
                <li>Create or edit <code>.htaccess</code> file in your <code>public_html</code> directory</li>
                <li>Add this line: <code>php_value extension pdo_mysql</code></li>
                <li>If that doesn't work, contact your hosting provider to enable the extension</li>
            </ol>
        </div>
        <?php else: ?>
        <h2>✅ All Checks Passed</h2>
        <div class="check success">
            <strong>Great!</strong> All required PHP extensions are installed and enabled.
            Your PHP configuration looks good for running Azzeman Hotel.
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p><strong>Security Note:</strong> Delete this file (<code>check_php.php</code>) after checking your configuration, or protect it with a strong secret key.</p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>


