<?php

namespace Balfour\WhatsApp\Bot\Triggers;

class TriggerRegistry
{
    /**
     * @var TriggerInterface[]
     */
    protected $triggers = [];

    /**
     * @param TriggerInterface $trigger
     */
    public function register(TriggerInterface $trigger): void
    {
        $this->triggers[] = $trigger;
    }

    /**
     * @return TriggerInterface[]
     */
    public function all(): array
    {
        return $this->triggers;
    }
}
