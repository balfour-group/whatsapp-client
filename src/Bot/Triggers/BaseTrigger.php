<?php

namespace Balfour\WhatsApp\Bot\Triggers;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;

abstract class BaseTrigger implements TriggerInterface
{
    /**
     * @var ActionInterface
     */
    protected $action;

    /**
     * @param ActionInterface $action
     */
    public function __construct(ActionInterface $action)
    {
        $this->action = $action;
    }

    /**
     * @return ActionInterface
     */
    public function getAction(): ActionInterface
    {
        return $this->action;
    }
}
