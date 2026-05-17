<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Bennito254\RouterOS\Client\Client;
use Bennito254\RouterOS\Config\ClientConfig;
use Bennito254\RouterOS\Config\ProxyConfig;
use Bennito254\RouterOS\Config\SslConfig;
use Bennito254\RouterOS\Exception\RouterOSException;
use Bennito254\RouterOS\Exception\TrapException;
use Bennito254\RouterOS\Query\Query;

// ============================================================================
// 1. Configuration
// ============================================================================

// Adjust these to match your RouterOS instance
$host = getenv('ROUTEROS_HOST') ?: '10.8.0.2';
$user = getenv('ROUTEROS_USER') ?: 'admin';
$pass = getenv('ROUTEROS_PASS') ?: 'password';
$port = (int)(getenv('ROUTEROS_PORT') ?: 8728);

// Example configuration showing all possible options.
// Most of these are optional and can be passed as an array directly to Client.
$config = new ClientConfig(
    host: $host,
    username: $user,
    password: $pass,
    port: $port,
    timeout: 10,
    // Uncomment to use a proxy:
    //  proxy: new ProxyConfig(
    //      type: ProxyConfig::TYPE_SOCKS5,
    //      host: '127.0.0.1',
    //      port: 1080,
    //      username: 'proxy_user',
    //      password: 'proxy_pass',
    //  ),
    // Uncomment to use SSL/TLS (make sure port is 8729):
    // ssl: new SslConfig(
    //     enabled: true,
    //     verifyPeer: false,
    // )
);

$client = new Client($config);

// Optional: You can inject a PSR-3 logger to debug sentences and connections.
// class SimpleLogger extends \Psr\Log\AbstractLogger {
//     public function log($level, \Stringable|string $message, array $context = []): void {
//         echo "[$level] $message\n";
//         if (!empty($context)) print_r($context);
//     }
// }
// $client->setLogger(new SimpleLogger());

try {
    echo "Connecting to RouterOS at {$host}:{$port}...\n";
    $client->connect();
    echo "Successfully connected and authenticated!\n\n";

    // ============================================================================
    // 2. Simple String Query
    // ============================================================================
    echo "--- Test 1: Simple Print Command ---\n";
    $resourceResponse = $client->query('/system/resource/print');
    $resourceData = $resourceResponse->first()?->getAttributes();
    
    echo "Board Name: " . ($resourceData['board-name'] ?? 'Unknown') . "\n";
    echo "RouterOS Version: " . ($resourceData['version'] ?? 'Unknown') . "\n";
    echo "CPU Load: " . ($resourceData['cpu-load'] ?? 'Unknown') . "%\n\n";


    // ============================================================================
    // 3. Query Builder (Filtering & Tags)
    // ============================================================================
    echo "--- Test 2: Query Builder with Filters & Tags ---\n";
    $query = Query::make('/ip/address/print')
        // Get only dynamic addresses
        ->where('dynamic', 'true')
        // Give the request a tag to track it easily
        ->tag('my-dynamic-ips');

    $addresses = $client->query($query);
    
    if ($addresses->count() === 0) {
        echo "No dynamic IP addresses found.\n";
    } else {
        foreach ($addresses->toArray() as $ip) {
            echo "Interface: {$ip['interface']} | Address: {$ip['address']}\n";
        }
    }
    echo "\n";


    // ============================================================================
    // 4. Modifying Data (Add & Remove)
    // ============================================================================
    echo "--- Test 3: Modifying Configuration ---\n";
    
    // Add a dummy script
    echo "Adding a dummy script...\n";
    $addQuery = Query::make('/system/script/add')
        ->equal('name', 'test-script-123')
        ->equal('source', ':put "Hello World";')
        ->equal('comment', 'Created by API');
        
    $addResponse = $client->query($addQuery);
    
    // The response `!done` contains the `.id` of the newly created item in `ret`
    $scriptId = $addResponse->last()?->getAttribute('ret');
    echo "Script created with ID: {$scriptId}\n";

    if ($scriptId) {
        // Remove the script using the ID
        echo "Cleaning up script...\n";
        $client->query(Query::make('/system/script/remove')->equal('numbers', $scriptId));
        echo "Script removed.\n";
    }
    echo "\n";


    // ============================================================================
    // 5. Exception Handling & Traps
    // ============================================================================
    echo "--- Test 4: Exception Handling ---\n";
    try {
        echo "Attempting to run an invalid command...\n";
        $client->query('/invalid/command/print');
    } catch (TrapException $e) {
        echo "Caught expected TrapException!\n";
        echo "Error message from RouterOS: " . $e->getMessage() . "\n";
    } catch (RouterOSException $e) {
        echo "Caught general RouterOS exception: " . $e->getMessage() . "\n";
    }

} catch (RouterOSException $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // Always clean up the connection
    $client->disconnect();
    echo "\nDisconnected.\n";
}
