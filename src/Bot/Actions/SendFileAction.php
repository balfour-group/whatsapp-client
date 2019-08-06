<?php

namespace Balfour\WhatsApp\Bot\Actions;

use Balfour\WhatsApp\File;
use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

class SendFileAction implements ActionInterface
{
    /**
     * @var File
     */
    protected $file;

    /**
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @param WhatsApp $client
     * @param Message $message
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(WhatsApp $client, Message $message): void
    {
        $client->sendFile($message->getPhoneNumber(), $this->file);
    }

    /**
     * @param Message $message
     * @return string|null
     */
    public function emulate(Message $message): ?string
    {
        return sprintf("Sending file to <%s>:\n\n%s", $message->getPhoneNumber(), $this->file->getFilename());
    }
}
