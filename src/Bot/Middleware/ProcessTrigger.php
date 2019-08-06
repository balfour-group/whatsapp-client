<?php

namespace Balfour\WhatsApp\Bot\Middleware;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;
use Balfour\WhatsApp\Bot\Triggers\TriggerInterface;
use Balfour\WhatsApp\Bot\Triggers\TriggerRegistry;
use Balfour\WhatsApp\Message;

class ProcessTrigger implements MiddlewareInterface
{
    /**
     * @var TriggerRegistry
     */
    protected $triggers;

    /**
     * @param TriggerRegistry $triggers
     */
    public function __construct(TriggerRegistry $triggers)
    {
        $this->triggers = $triggers;
    }

    /**
     * @return TriggerRegistry
     */
    public function getTriggers(): TriggerRegistry
    {
        return $this->triggers;
    }

    /**
     * @param Message $message
     * @return ActionInterface|null
     */
    public function getAction(Message $message): ?ActionInterface
    {
        if ($message->isInbound()) {
            $text = $message->getMessage() ?? '';

            /** @var TriggerInterface $trigger */
            foreach ($this->triggers->all() as $trigger) {
                if ($trigger->matches($text)) {
                    return $trigger->getAction();
                }
            }
        }

        return null;
    }
}
