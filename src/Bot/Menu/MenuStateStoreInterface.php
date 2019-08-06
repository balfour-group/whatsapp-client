<?php

namespace Balfour\WhatsApp\Bot\Menu;

interface MenuStateStoreInterface
{
    /**
     * @param string $phoneNumber
     * @return Menu|null
     */
    public function getActiveMenu(string $phoneNumber): ?Menu;

    /**
     * @param string $phoneNumber
     * @param Menu|null $menu
     */
    public function setActiveMenu(string $phoneNumber, ?Menu $menu): void;
}
