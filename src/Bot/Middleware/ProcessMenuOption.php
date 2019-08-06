<?php

namespace Balfour\WhatsApp\Bot\Middleware;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;
use Balfour\WhatsApp\Bot\Actions\EscapeMenuAction;
use Balfour\WhatsApp\Bot\Actions\SendAndActivateMenuAction;
use Balfour\WhatsApp\Bot\Actions\SendMessageAction;
use Balfour\WhatsApp\Bot\Menu\Menu;
use Balfour\WhatsApp\Bot\Menu\MenuStateStoreInterface;
use Balfour\WhatsApp\Message;
use Exception;

class ProcessMenuOption implements MiddlewareInterface
{
    /**
     * @var Menu
     */
    protected $rootMenu;

    /**
     * @var MenuStateStoreInterface
     */
    protected $store;

    /**
     * @var bool
     */
    protected $isEscapeKeysEnabled;

    /**
     * @var array
     */
    protected $escapeKeys = [
        'RETURN_TO_PREVIOUS_MENU' => '@',
        'RETURN_TO_MAIN_MENU' => '#',
        'QUIT' => 'q',
    ];

    /**
     * @param Menu $rootMenu
     * @param MenuStateStoreInterface $store
     * @param bool $isEscapeKeysEnabled
     */
    public function __construct(
        Menu $rootMenu,
        MenuStateStoreInterface $store,
        $isEscapeKeysEnabled = true
    ) {
        $this->rootMenu = $rootMenu;
        $this->store = $store;
        $this->isEscapeKeysEnabled = $isEscapeKeysEnabled;
    }

    /**
     * @return Menu
     */
    public function getRootMenu(): Menu
    {
        return $this->rootMenu;
    }

    /**
     * @return MenuStateStoreInterface
     */
    public function getStore(): MenuStateStoreInterface
    {
        return $this->store;
    }

    /**
     * @param Message $message
     * @return ActionInterface|null
     * @throws Exception
     */
    public function getAction(Message $message): ?ActionInterface
    {
        if ($message->isInbound()) {
            $key = $message->getMessage() ?? '';
            $phoneNumber = $message->getPhoneNumber();

            $menu = $this->store->getActiveMenu($phoneNumber);

            if ($menu) {
                $action = null;

                if ($this->isEscapeKeysEnabled && $this->isEscapeKey($key)) {
                    return $this->getEscapeKeyAction($key, $phoneNumber);
                } elseif ($menu->isValidOption($key)) {
                    $action = $menu->getOption($key)->getAction();

                    // make sure menu stays active
                    $this->store->setActiveMenu($phoneNumber, $menu);
                } elseif ($menu->isInvalidOptionWarningEnabled()) {
                    $action = new SendMessageAction($menu->getInvalidOptionText());
                }

                return $action;
            }
        }

        return null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isEscapeKey(string $key): bool
    {
        $key = strtolower($key);

        return in_array($key, $this->escapeKeys);
    }

    /**
     * @param string $key
     * @param string $phoneNumber
     * @return ?ActionInterface
     * @throws Exception
     */
    public function getEscapeKeyAction(string $key, string $phoneNumber): ?ActionInterface
    {
        $key = strtolower($key);

        switch ($key) {
            case $this->escapeKeys['RETURN_TO_PREVIOUS_MENU']:
                return $this->getReturnToPreviousMenuEscapeOption($phoneNumber);
            case $this->escapeKeys['RETURN_TO_MAIN_MENU']:
                return $this->getReturnToMainMenuEscapeOption();
            case $this->escapeKeys['QUIT']:
                return $this->getQuitEscapeOption();
        }

        return null;
    }

    /**
     * @param string $phoneNumber
     * @return ActionInterface
     */
    protected function getReturnToPreviousMenuEscapeOption(string $phoneNumber): ActionInterface
    {
        $menu = $this->store->getActiveMenu($phoneNumber);

        if ($menu) {
            $parent = $menu->getParent();

            if ($parent) {
                return new SendAndActivateMenuAction($parent, $this->store);
            }
        }

        return $this->getReturnToMainMenuEscapeOption();
    }

    /**
     * @return SendAndActivateMenuAction
     */
    protected function getReturnToMainMenuEscapeOption(): SendAndActivateMenuAction
    {
        return new SendAndActivateMenuAction($this->rootMenu, $this->store);
    }

    /**
     * @return EscapeMenuAction
     */
    protected function getQuitEscapeOption(): EscapeMenuAction
    {
        return new EscapeMenuAction($this->store);
    }
}
