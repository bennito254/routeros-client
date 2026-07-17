<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Client;

use Bennito254\RouterOS\Config\ClientConfig;
use Bennito254\RouterOS\Config\ProxyConfig;
use Bennito254\RouterOS\Contracts\TransportInterface;
use Bennito254\RouterOS\Exception\AuthenticationException;
use Bennito254\RouterOS\Exception\ConnectionException;
use Bennito254\RouterOS\Exception\FatalException;
use Bennito254\RouterOS\Exception\ProtocolException;
use Bennito254\RouterOS\Exception\TrapException;
use Bennito254\RouterOS\Protocol\Decoder;
use Bennito254\RouterOS\Protocol\Encoder;
use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;
use Bennito254\RouterOS\Response\ResponseSentence;
use Bennito254\RouterOS\Transport\DirectTransport;
use Bennito254\RouterOS\Transport\HttpConnectTransport;
use Bennito254\RouterOS\Transport\Socks5Transport;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ClientConfig $config;
    private ?TransportInterface $transport = null;
    private ?Encoder $encoder = null;
    private ?Decoder $decoder = null;

    public function __construct(array|ClientConfig $config)
    {
        if (is_array($config)) {
            $this->config = ClientConfig::fromArray($config);
        } else {
            $this->config = $config;
        }

        $this->logger = new NullLogger();
    }

    public function connect(): void
    {
        if ($this->transport !== null && $this->transport->isConnected()) {
            return;
        }

        $attempts = 0;
        $maxAttempts = max(1, $this->config->reconnectAttempts);

        while (true) {
            try {
                $this->logger->info(
                    "Connecting to RouterOS API at {host}:{port} (Attempt {attempt}/{max})",
                    [
                        'host' => $this->config->host,
                        'port' => $this->config->port,
                        'attempt' => $attempts + 1,
                        'max' => $maxAttempts,
                    ]
                );

                $this->transport = $this->createTransport();
                $this->transport->connect();

                $this->encoder = new Encoder();
                $this->decoder = new Decoder($this->transport);

                $this->authenticate();
                break;
            } catch (ConnectionException $e) {
                $attempts++;
                $this->disconnect();

                if ($attempts >= $maxAttempts) {
                    $this->logger->error(
                        "Failed to connect to RouterOS API after {attempts} attempts. Error: {error}",
                        [
                            'attempts' => $attempts,
                            'error' => $e->getMessage()
                        ]
                    );
                    throw $e;
                }

                $this->logger->warning(
                    "Connection attempt {attempt} failed, retrying in {delay}ms...",
                    [
                        'attempt' => $attempts,
                        'delay' => $attempts * 250
                    ]
                );
                usleep($attempts * 250000); // 250ms, 500ms, etc.
            }
        }
    }

    public function disconnect(): void
    {
        if ($this->transport !== null) {
            $this->transport->close();
            $this->transport = null;
        }
    }

    public function isConnected(): bool
    {
        return $this->transport !== null && $this->transport->isConnected();
    }

    /**
     * Send a query and wait for the response.
     */
    public function query(Query|string $query): ResponseCollection
    {
        $this->connect();

        if (is_string($query)) {
            $query = Query::make($query);
        }

        $words = $query->getWords();
        $this->sendWords($words);

        return $this->readResponse();
    }

    private function authenticate(): void
    {
        $this->logger->debug("Authenticating...");

        // Try modern authentication first
        $words = [
            '/login',
            '=name=' . $this->config->username,
            '=password=' . $this->config->password,
        ];

        $this->sendWords($words);
        $responses = $this->readResponse(false);

        $last = $responses->last();

        if ($last && $last->isType(ResponseSentence::TYPE_DONE)) {
            $ret = $last->getAttribute('ret');
            if ($ret !== null) {
                // Older RouterOS style challenge-response
                $this->logger->debug("Falling back to challenge-response authentication.");

                $responseStr = md5(chr(0) . $this->config->password . pack('H*', $ret));

                $challengeWords = [
                    '/login',
                    '=name=' . $this->config->username,
                    '=response=00' . $responseStr,
                ];

                $this->sendWords($challengeWords);
                $challengeResponses = $this->readResponse(false);

                $challengeLast = $challengeResponses->last();
                if (!$challengeLast || !$challengeLast->isType(ResponseSentence::TYPE_DONE)) {
                    $this->handleTraps($challengeResponses, AuthenticationException::class);
                    throw new AuthenticationException("Authentication failed (challenge-response).");
                }
            }
        } else {
            $this->handleTraps($responses, AuthenticationException::class);
            throw new AuthenticationException("Authentication failed.");
        }

        $this->logger->info("Authenticated successfully.");
    }

    private function sendWords(array $words): void
    {
        if ($this->encoder === null || $this->transport === null) {
            throw new ConnectionException("Cannot send data, client is not connected.");
        }

        $this->logger->debug("Sending words:", $words);
        $encoded = $this->encoder->encodeSentence($words);

        $this->transport->write($encoded);
    }

    private function readResponse(bool $throwOnTrap = true): ResponseCollection
    {

        if ($this->decoder === null) {
            throw new ConnectionException("Cannot read data, client is not connected.");
        }

        $collection = new ResponseCollection();

        while (true) {
            $words = $this->decoder->readSentence();

            if (empty($words)) {
                // Stream closed or empty sentence
                break;
            }

            $this->logger->debug("Received words:", $words);

            $sentence = ResponseSentence::parse($words);

            $collection->add($sentence);
            
            if ($sentence->isType(ResponseSentence::TYPE_FATAL)) {
                throw new FatalException("Fatal error from RouterOS: " . $sentence->getAttribute('message', 'Unknown'));
            }

            if ($sentence->isType(ResponseSentence::TYPE_DONE)) {
                break;
            }
        }

        if ($throwOnTrap) {
            $this->handleTraps($collection, TrapException::class);
        }

        return $collection;
    }

    /**
     * @param class-string<\Exception> $exceptionClass
     */
    private function handleTraps(ResponseCollection $responses, string $exceptionClass): void
    {
        foreach ($responses as $sentence) {
            if ($sentence->isType(ResponseSentence::TYPE_TRAP)) {
                $message = $sentence->getAttribute('message', 'Unknown trap error');
                $category = $sentence->getAttribute('category');

                $fullMessage = $message;
                if ($category !== null) {
                    $fullMessage .= " (Category: {$category})";
                }

                //throw new $exceptionClass($fullMessage, 0, null, $sentence->getAttributes());
                throw new $exceptionClass($fullMessage);
            }
        }
    }

    private function createTransport(): TransportInterface
    {
        if ($this->config->proxy !== null) {
            if ($this->config->proxy->type === ProxyConfig::TYPE_SOCKS5) {
                return new Socks5Transport($this->config);
            }
            if ($this->config->proxy->type === ProxyConfig::TYPE_HTTP) {
                return new HttpConnectTransport($this->config);
            }
        }

        return new DirectTransport($this->config);
    }
}
