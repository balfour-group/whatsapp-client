<?php

namespace Balfour\WhatsApp;

use Carbon\Carbon;
use Exception;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class WhatsApp
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param Client $client
     * @param string|null $uri
     * @param string|null $token
     */
    public function __construct(
        Client $client,
        ?string $uri = null,
        ?string $token = null
    ) {
        $this->client = $client;
        $this->uri = $uri;
        $this->token = $token;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $endpoint
     * @param mixed[] $params
     * @return string
     */
    protected function getBaseUri(string $endpoint, array $params = []): string
    {
        $params['token'] = $this->token;

        $uri = $this->uri;
        $uri = rtrim($uri, '/');
        $uri .= '/' . ltrim($endpoint, '/');

        if (count($params) > 0) {
            $uri .= '?' . http_build_query($params);
        }

        return $uri;
    }

    /**
     * @return mixed[]
     */
    protected function getDefaultRequestOptions(): array
    {
        return [
            'connect_timeout' => 2000,
            'timeout' => 6000,
        ];
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest(Request $request)
    {
        $options = $this->getDefaultRequestOptions();
        $response = $this->client->send($request, $options);
        $body = (string) $response->getBody();
        return json_decode($body, true);
    }

    /**
     * @param string $number
     * @return string
     */
    protected function normalisePhoneNumber(string $number): string
    {
        $number = preg_replace('/\s+/', '', $number);
        $number = str_replace('+', '', $number);
        return $number;
    }

    /**
     * @param string $endpoint
     * @param mixed[] $payload
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(string $endpoint, array $payload = [])
    {
        $request = new Request(
            'POST',
            $this->getBaseUri($endpoint),
            [
                'Content-type' => 'application/json',
            ],
            json_encode($payload)
        );
        return $this->sendRequest($request);
    }

    /**
     * @param string $endpoint
     * @param mixed[] $params
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $endpoint, array $params = [])
    {
        $request = new Request('GET', $this->getBaseUri($endpoint, $params));
        return $this->sendRequest($request);
    }

    /**
     * @param string $number
     * @param string $message
     * @return mixed[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function sendMessage(string $number, string $message): array
    {
        $payload = [
            'phone' => $this->normalisePhoneNumber($number),
            'body' => $message,
        ];

        $response = $this->post('sendMessage', $payload);
        static::assertMessageWasSent($response);

        return $response;
    }

    /**
     * @param string $number
     * @param File $file
     * @return mixed[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function sendFile(string $number, File $file): array
    {
        $payload = [
            'phone' => $this->normalisePhoneNumber($number),
            'body' => $file->getUrl(),
            'filename' => $file->getFilename(),
        ];

        $response = $this->post('sendFile', $payload);
        static::assertMessageWasSent($response);

        return $response;
    }

    /**
     * @param int|null $lastMessageNumber
     * @return Generator
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function getMessages(?int $lastMessageNumber = null): Generator
    {
        $params = [];
        if ($lastMessageNumber) {
            $params['lastMessageNumber'] = $lastMessageNumber;
        }

        $response = $this->get('messages', $params);
        $messages = $response['messages'] ?? [];

        // there was a stage when the api stored messages in a broken format
        // we filter those out at a client level here

        $messages = array_filter($messages, function ($message) {
            return isset($message['author'])
                && $message['author'] !== '[object Object]'
                && $message['chatId'] !== '[object Object]';
        });

        $messages = array_values($messages);

        foreach ($messages as $message) {
            yield $this->parseMessage($message);
        }
    }

    /**
     * @param mixed[] $message
     * @return Message
     * @throws Exception
     */
    protected function parseMessage(array $message): Message
    {
        if (!preg_match('/^(\d+)(-\d+)?@(.+)$/', $message['chatId'], $matches)) {
            throw new Exception(sprintf('The chat id "%s" cannot be parsed to a phone number.', $message['chatId']));
        }

        $number = '+' . $matches[1];

        $isMedia = in_array($message['type'], static::getMediaTypes());

        return new Message(
            $message['chatId'],
            $message['id'],
            $number,
            $message['type'],
            (!$isMedia) ? $message['body'] : null,
            ($isMedia) ? $message['body'] : null,
            Carbon::createFromTimestamp($message['time']),
            $message['fromMe'],
            (int) $message['messageNumber']
        );
    }

    /**
     * @return mixed[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function reboot(): array
    {
        $response = $this->post('reboot');

        $success = $response['success'] ?? false;
        if (!$success) {
            throw new Exception('The reboot call failed to execute.');
        }

        return $response;
    }

    /**
     * @return string[]
     */
    public static function getMediaTypes(): array
    {
        return [
            'image',
            'ptt',
            'document',
            'audio',
            'video',
        ];
    }

    /**
     * @param mixed[] $response
     * @throws Exception
     */
    protected static function assertMessageWasSent(array $response): void
    {
        $isSent = $response['sent'] ?? false;

        if (!$isSent) {
            $message = $response['message'] ?? 'The message failed to send.';
            throw new Exception($message);
        }
    }
}
