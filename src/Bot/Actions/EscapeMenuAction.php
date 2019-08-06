<?php

namespace Balfour\WhatsApp\Bot\Actions;

use Balfour\WhatsApp\Bot\Menu\MenuStateStoreInterface;
use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

class EscapeMenuAction implements ActionInterface
{
    /**
     * @var MenuStateStoreInterface
     */
    protected $store;

    /**
     * @param MenuStateStoreInterface $store
     */
    public function __construct(MenuStateStoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * @param WhatsApp $client
     * @param Message $message
     */
    public function execute(WhatsApp $client, Message $message): void
    {
        $this->store->setActiveMenu($message->getPhoneNumber(), null);
    }

    /**
     * @param Message $message
     * @return string|null
     */
    public function emulate(Message $message): ?string
    {
        $this->store->setActiveMenu($message->getPhoneNumber(), null);

        return null;
    }
}
