<?php

namespace Balfour\WhatsApp\Bot\Triggers;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;

class RegexTrigger extends BaseTrigger
{
    /**
     * @var string
     */
    protected $regex;

    /**
     * @param string $regex
     * @param ActionInterface $action
     */
    public function __construct(
        string $regex,
        ActionInterface $action
    ) {
        $this->regex = $regex;

        parent::__construct($action);
    }

    /**
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * @param string $message
     * @return bool
     */
    public function matches(string $message): bool
    {
        return (bool) preg_match($this->regex, $message);
    }
}
