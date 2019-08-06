<?php

namespace Balfour\WhatsApp\Bot\Actions;

use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

class SendMessageAction implements ActionInterface
{
    /**
     * @var string|callable
     */
    protected $message;

    /**
     * @param string|callable $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @param Message $message
     * @return string
     */
    protected function getResolvedResponse(Message $message): string
    {
        return (is_callable($this->message)) ?
            call_user_func($this->message, $message) :
            $this->message;
    }

    /**
     * @param WhatsApp $client
     * @param Message $message
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(WhatsApp $client, Message $message): void
    {
        $client->sendMessage($message->getPhoneNumber(), $this->getResolvedResponse($message));
    }

    /**
     * @param Message $message
     * @return string|null
     */
    public function emulate(Message $message): ?string
    {
        return sprintf(
            "Sending message to <%s>:\n\n%s",
            $message->getPhoneNumber(),
            $this->getResolvedResponse($message)
        );
    }
}
