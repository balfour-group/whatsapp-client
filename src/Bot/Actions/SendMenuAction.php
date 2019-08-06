<?php

namespace Balfour\WhatsApp\Bot\Actions;

use Balfour\WhatsApp\Bot\Menu\Menu;
use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

class SendMenuAction implements ActionInterface
{
    /**
     * @var Menu
     */
    protected $menu;

    /**
     * @param Menu $menu
     */
    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @param WhatsApp $client
     * @param Message $message
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(WhatsApp $client, Message $message): void
    {
        $client->sendMessage($message->getPhoneNumber(), $this->menu->toMessage());
    }

    /**
     * @param Message $message
     * @return string|null
     */
    public function emulate(Message $message): ?string
    {
        return sprintf("Sending menu to <%s>:\n\n%s", $message->getPhoneNumber(), $this->menu->toMessage());
    }
}
