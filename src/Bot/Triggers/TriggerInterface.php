<?php

namespace Balfour\WhatsApp\Bot\Triggers;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;

interface TriggerInterface
{
    /**
     * @param string $message
     * @return bool
     */
    public function matches(string $message): bool;

    /**
     * @return ActionInterface
     */
    public function getAction(): ActionInterface;
}
