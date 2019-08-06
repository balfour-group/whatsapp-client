<?php

namespace Balfour\WhatsApp\Bot;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;
use Balfour\WhatsApp\Bot\Middleware\MiddlewareInterface;
use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

class Bot
{
    /**
     * @var MiddlewareInterface[]
     */
    protected $middleware;

    /**
     * @var WhatsApp
     */
    protected $client;

    /**
     * @param MiddlewareInterface[] $middleware
     * @param WhatsApp|null $client
     */
    public function __construct(array $middleware = [], ?WhatsApp $client = null)
    {
        $this->middleware = $middleware;
        $this->client = $client;
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @return WhatsApp
     */
    public function getClient(): WhatsApp
    {
        return $this->client;
    }

    /**
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * @param Message $message
     * @return ActionInterface|null
     */
    public function process(Message $message): ?ActionInterface
    {
        $action = $this->getAction($message);

        if ($action) {
            $action->execute($this->client, $message);
        }

        return $action;
    }

    /**
     * @param Message $message
     * @return ActionInterface|null
     */
    public function getAction(Message $message): ?ActionInterface
    {
        /** @var MiddlewareInterface $middleware */
        foreach ($this->middleware as $middleware) {
            $action = $middleware->getAction($message);

            if ($action) {
                return $action;
            }
        }

        return null;
    }
}
