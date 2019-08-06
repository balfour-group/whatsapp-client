<?php

namespace Balfour\WhatsApp\Bot\Middleware;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;
use Balfour\WhatsApp\Message;

interface MiddlewareInterface
{
    /**
     * @param Message $message
     * @return ActionInterface|null
     */
    public function getAction(Message $message): ?ActionInterface;
}
