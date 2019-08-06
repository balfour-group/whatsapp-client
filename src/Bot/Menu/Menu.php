<?php

namespace Balfour\WhatsApp\Bot\Menu;

use Exception;
use UnexpectedValueException;

class Menu
{
    /**
     * @var string|null
     */
    protected $header;

    /**
     * @var string|null
     */
    protected $footer;

    /**
     * @var Option[]
     */
    protected $options = [];

    /**
     * @var Menu|null
     */
    protected $parent;

    /**
     * @var bool
     */
    protected $isInvalidOptionWarningEnabled;

    /**
     * @var string|null
     */
    protected $invalidOptionText;

    /**
     * @param string|null $header
     * @param string|null $footer
     * @param Option[] $options
     * @param Menu|null $parent
     * @param bool $isInvalidOptionWarningEnabled
     * @param string|null $invalidOptionText
     * @throws Exception
     */
    public function __construct(
        ?string $header = null,
        ?string $footer = null,
        array $options = [],
        ?Menu $parent = null,
        bool $isInvalidOptionWarningEnabled = false,
        ?string $invalidOptionText = null
    ) {
        $this->header = $header;
        $this->footer = $footer;
        $this->parent = $parent;
        $this->isInvalidOptionWarningEnabled = $isInvalidOptionWarningEnabled;
        $this->invalidOptionText = $invalidOptionText;

        $this->addOptions($options);
    }

    /**
     * @param string $header
     * @return $this
     */
    public function setHeader(string $header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHeader(): ?string
    {
        return $this->header;
    }

    /**
     * @param string $footer
     * @return $this
     */
    public function setFooter(string $footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFooter(): ?string
    {
        return $this->footer;
    }

    /**
     * @param Menu $parent
     * @return $this
     */
    public function setParent(Menu $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Menu|null
     */
    public function getParent(): ?Menu
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function isInvalidOptionWarningEnabled(): bool
    {
        return $this->isInvalidOptionWarningEnabled;
    }

    /**
     * @return $this
     */
    public function enableInvalidOptionWarning()
    {
        $this->isInvalidOptionWarningEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableInvalidOptionWarning()
    {
        $this->isInvalidOptionWarningEnabled = false;

        return $this;
    }

    /**
     * @param string $invalidOptionText
     * @return $this
     */
    public function setInvalidOptionText(string $invalidOptionText)
    {
        $this->invalidOptionText = $invalidOptionText;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvalidOptionText(): string
    {
        return $this->invalidOptionText ?? 'You have specified an invalid menu option.';
    }

    /**
     * @param Option $option
     * @return $this
     * @throws Exception
     */
    public function addOption(Option $option)
    {
        $key = strtolower($option->getKey());

        if (isset($this->options[$key])) {
            throw new Exception(sprintf('The menu option "%s" already exists.', $key));
        }

        $this->options[$key] = $option;

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     * @throws Exception
     */
    public function addOptions(array $options)
    {
        /** @var Option $option */
        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isValidOption(string $key): bool
    {
        $key = strtolower($key);

        return isset($this->options[$key]);
    }

    /**
     * @param string $key
     * @return Option
     */
    public function getOption(string $key): Option
    {
        $key = strtolower($key);

        if (!$this->isValidOption($key)) {
            throw new UnexpectedValueException(sprintf('The menu option "%s" is invalid.', $key));
        }

        return $this->options[$key];
    }

    /**
     * @return string
     */
    public function toMessage(): string
    {
        $message = '';

        if ($this->header) {
            $message .= $this->header . "\n\n";
        }

        /** @var Option $option */
        foreach ($this->options as $option) {
            $message .= sprintf("%s. %s\n", $option->getKey(), $option->getText());
        }

        if ($this->footer) {
            $message .= "\n\n" . $this->footer;
        }

        return $message;
    }
}
