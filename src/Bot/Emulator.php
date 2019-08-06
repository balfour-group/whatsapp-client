<?php

namespace Balfour\WhatsApp\Bot;

use Balfour\WhatsApp\Message;
use Carbon\Carbon;

class Emulator
{
    /**
     * @var Bot
     */
    protected $bot;

    /**
     * @param Bot $bot
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @return Bot
     */
    public function getBot(): Bot
    {
        return $this->bot;
    }

    /**
     * @param string $text
     * @param string|null $number
     * @return string|null
     */
    public function say(string $text, ?string $number = null): ?string
    {
        $message = $this->makeMessage($text, $number);

        $action = $this->bot->getAction($message);

        return ($action) ? $action->emulate($message) : null;
    }

    /**
     * @param string $text
     * @param string|null $number
     * @return Message
     */
    protected function makeMessage(string $text, ?string $number = null): Message
    {
        $number = $number ?? '+442071838750';

        return new Message(
            'foobar@c.us',
            'true_foobar@c.us_3EB05BCF216E6ABFC6AA',
            $number,
            'chat',
            $text,
            null,
            Carbon::now(),
            false,
            1
        );
    }

    public function run(): void
    {
        echo "You are now running in WhatsApp emulation mode!!!\n";

        $fh = fopen('php://stdin', 'r');

        fwrite($fh, '>> ');

        while ($input = fgets($fh)) {
            $input = trim($input);

            if ($input === 'quit') {
                exit;
            }

            $response = $this->say($input);

            if ($response) {
                fwrite($fh, "--------------------------------------------\n");
                fwrite($fh, $response . "\n");
                fwrite($fh, "--------------------------------------------\n");
            }

            fwrite($fh, '>> ');
        }
    }
}
