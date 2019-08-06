<?php

namespace Balfour\WhatsApp\Bot\Actions;

use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

interface ActionInterface
{
    /**
     * @param WhatsApp $client
     * @param Message $message
     */
    public function execute(WhatsApp $client, Message $message): void;

    /**
     * @param Message $message
     * @return string|null
     */
    public function emulate(Message $message): ?string;
}
