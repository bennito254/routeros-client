# RouterOS Client

A modern, production-grade PHP client for communicating with the MikroTik RouterOS API over raw sockets. Built for modern PHP 8.2+ with PSR-4 autoloading, strong typing, robust error handling, and proxy support out-of-the-box.

## Features

- **Modern Architecture**: Clean object-oriented design, completely uncoupled from heavy libraries.
- **Direct Sockets**: Communicates natively using raw TCP sockets, fully implementing the RouterOS length-encoded protocol. No cURL needed.
- **Proxy Support built-in**: First-class support for routing connections through SOCKS5 and HTTP CONNECT proxies. Handshake is performed natively.
- **TLS/SSL encryption**: Support for API-SSL with options to configure certificates and peer verification.
- **Query Builder**: Fluent `Query` builder for crafting RouterOS commands and filters easily.
- **Collections**: Responses are encapsulated in `ResponseCollection` containing `ResponseSentence` objects.
- **Service Facades**: Ready-to-use Facade classes to manage IP addresses, Hotspot clients, and PPP accounts fluently.
- **Laravel Integration**: Out-of-the-box support for Laravel with zero configuration required, complete with a service provider, facade, and published config.

## Installation

Install via Composer:

```bash
composer require bennito254/routeros-client
```

## Basic Usage

```php
use Bennito254\RouterOS\Client\Client;
use Bennito254\RouterOS\Query\Query;

require 'vendor/autoload.php';

// Configure and connect
$client = new Client([
    'host' => '192.168.88.1',
    'username' => 'admin',
    'password' => 'password',
    'port' => 8728,
]);

// Send a command
$response = $client->query('/system/resource/print');

// Access results
foreach ($response->toArray() as $item) {
    echo "CPU Load: " . ($item['cpu-load'] ?? 'N/A') . "%\n";
}
```

## Laravel Integration

The package automatically discovers its Laravel service provider and registers itself. You can easily publish the configuration file to customize your connection settings:

```bash
php artisan vendor:publish --tag="routeros-config"
```

Once published to `config/routeros.php`, update your `.env` file:
```env
ROUTEROS_HOST=192.168.88.1
ROUTEROS_USERNAME=admin
ROUTEROS_PASSWORD=secret
```

You can then utilize the Laravel `RouterOS` Facade anywhere in your application:

```php
use Bennito254\RouterOS\Laravel\Facade as RouterOS;

$response = RouterOS::query('/ip/address/print');
```

## Service Facades

The package provides dedicated Service Facades allowing you to rapidly query, add, modify, or toggle states on RouterOS entities (such as IP Addresses, Hotspot Users, and PPP Secrets) without manually constructing queries.

```php
use Bennito254\RouterOS\Facade\IpAddress;
use Bennito254\RouterOS\Facade\HotspotUser;

// Using the IpAddress Facade
$ipFacade = new IpAddress($client);

// Get all IP Addresses
$addresses = $ipFacade->getAll();

// Add a new IP Address
$ipFacade->add([
    'address'   => '10.0.0.1/24',
    'interface' => 'ether2',
]);

// Disable a Hotspot user by their RouterOS `.id`
$hotspotFacade = new HotspotUser($client);
$hotspotFacade->disable('*1F');
```

The available built-in Facades under `Bennito254\RouterOS\Facade\` are:
- `IpAddress` (`/ip/address`)
- `HotspotUser` (`/ip/hotspot/user`)
- `HotspotActive` (`/ip/hotspot/active`)
- `PppSecret` (`/ppp/secret`)
- `PppActive` (`/ppp/active`)

## Using the Query Builder

```php
$query = Query::make('/ip/address/print')
    ->where('interface', 'ether1')
    ->equal('disabled', false)
    ->tag('my-request');

$response = $client->query($query);
```

## Connecting Through a Proxy

Connect to your Mikrotik behind a restrictive network using a SOCKS5 proxy:

```php
$client = new Client([
    'host' => '192.168.88.1',
    'username' => 'admin',
    'password' => 'password',
    'proxy' => [
        'type' => 'socks5',
        'host' => '127.0.0.1',
        'port' => 1080,
        // 'username' => 'proxyuser',
        // 'password' => 'proxypass',
    ],
]);
```

You can also use an HTTP CONNECT proxy by changing `type` to `'http'`.

## Connecting via API-SSL

Enable SSL to connect securely using the API-SSL port (default 8729).

```php
$client = new Client([
    'host' => '192.168.88.1',
    'username' => 'admin',
    'password' => 'password',
    'port' => 8729,
    'ssl' => [
        'enabled' => true,
        'verify_peer' => false, // Set to true in production
    ],
]);
```

## Error Handling

The package provides a robust exception hierarchy under `Bennito254\RouterOS\Exception`:

- `ConnectionException`: Network issues, timeouts, and socket errors.
- `AuthenticationException`: Invalid credentials.
- `ProxyException`: Errors during SOCKS5 or HTTP CONNECT handshake.
- `ProtocolException`: Corrupted length encoding or malformed sentences.
- `TrapException`: Errors returned by RouterOS (`!trap`).
- `FatalException`: Fatal errors from RouterOS (`!fatal`).

Example:

```php
use Bennito254\RouterOS\Exception\TrapException;

try {
    $client->query('/invalid/command');
} catch (TrapException $e) {
    echo "RouterOS Error: " . $e->getMessage();
}
```
