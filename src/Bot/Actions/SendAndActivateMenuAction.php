<?php

namespace Balfour\WhatsApp\Bot\Actions;

use Balfour\WhatsApp\Bot\Menu\Menu;
use Balfour\WhatsApp\Bot\Menu\MenuStateStoreInterface;
use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

class SendAndActivateMenuAction implements ActionInterface
{
    /**
     * @var Menu
     */
    protected $menu;

    /**
     * @var MenuStateStoreInterface
     */
    protected $store;

    /**
     * @param Menu $menu
     * @param MenuStateStoreInterface $store
     */
    public function __construct(Menu $menu, MenuStateStoreInterface $store)
    {
        $this->menu = $menu;
        $this->store = $store;
    }

    /**
     * @param WhatsApp $client
     * @param Message $message
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(WhatsApp $client, Message $message): void
    {
        $this->store->setActiveMenu($message->getPhoneNumber(), $this->menu);

        $client->sendMessage($message->getPhoneNumber(), $this->menu->toMessage());
    }

    /**
     * @param Message $message
     * @return string|null
     */
    public function emulate(Message $message): ?string
    {
        $this->store->setActiveMenu($message->getPhoneNumber(), $this->menu);

        return sprintf("Sending menu to <%s>:\n\n%s", $message->getPhoneNumber(), $this->menu->toMessage());
    }
}
