<?php

namespace Balfour\WhatsApp\Bot\Menu;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class InMemoryMenuStateStore implements MenuStateStoreInterface
{
    /**
     * @var int|null
     */
    protected $ttl;

    /**
     * @var mixed[]
     */
    protected $store = [];

    /**
     * @param int|null $ttl
     */
    public function __construct(?int $ttl = 60)
    {
        $this->ttl = $ttl;
    }

    /**
     * @param string $phoneNumber
     * @return Menu|null
     */
    public function getActiveMenu(string $phoneNumber): ?Menu
    {
        if (isset($this->store[$phoneNumber])) {
            $state = $this->store[$phoneNumber];
            /** @var CarbonInterface $expiry */
            $expiry = $state['expires_at'];
            if ($expiry->isFuture()) {
                return $state['menu'];
            } else {
                unset($this->store[$phoneNumber]);
            }
        }

        return null;
    }

    /**
     * @param string $phoneNumber
     * @param Menu|null $menu
     */
    public function setActiveMenu(string $phoneNumber, ?Menu $menu): void
    {
        // no expiry, just set a super long ttl
        $ttl = $this->ttl ?? 60 * 60 * 24 * 365 * 3;

        if ($menu === null) {
            unset($this->store[$phoneNumber]);
        } else {
            $this->store[$phoneNumber] = [
                'menu' => $menu,
                'expires_at' => Carbon::now()->addSecond($ttl),
            ];
        }
    }
}
