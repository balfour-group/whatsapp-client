# whatsapp-client

A library for handling WhatsApp communication via the unofficial [chat-api.com](https://chat-api.com)
WhatsApp API.

*This library is in early release and is pending unit tests.*

**Legal Notice**

This code is in no way affiliated with, authorized, maintained, sponsored or endorsed by WhatsApp
or any of its affiliates or subsidiaries.  This is an independent and unofficial API written for
educational purposes only and should be used at your own risk.

## Table of Contents

* [Installation](#installation)
* [Usage](#usage)
    * [Creating a Client](#creating-a-client)
    * [Sending a Message](#sending-a-message)
    * [Sending a File](#sending-a-file)
    * [Retrieving Messages](#retrieving-messages)
    * [Rebooting the VM](#rebooting-the-vm)
* [Building a Bot](#building-a-bot)
    * [Actions](#actions)
        * [SendMessageAction](#sendmessageaction)
        * [SendFileAction](#sendfileaction)
        * [SendMenuAction](#sendmenuaction)
        * [EscapeMenuAction](#escapemenuaction)
        * [SendAndActivateMenuAction](#sendandactivatemenuaction)
        * [Custom](#custom)
    * [Triggers](#triggers)
        * [StringTrigger](#stringtrigger)
        * [StartsWithTrigger](#startswithtrigger)
        * [RegexTrigger](#regextrigger)
    * [Menus](#menus)
        * [Single Menu](#single-menu)
        * [Nested Menus](#nested-menus)
    * [Middleware](#middleware)
    * [Full Example](#full-example)
    * [Emulator](#emulator)

## Installation

```bash
composer require balfour/whatsapp-client
```

## Usage

For further documentation on chat-api, please see https://chat-api.com/en/swagger.html

### Creating a Client

```php
use Balfour\WhatsApp\WhatsApp;
use GuzzleHttp\Client;

$guzzle = new Client();
$client = new WhatsApp(
    $guzzle,
    'https://euXXXX.chat-api.com/instanceXXXXXX',
    'your-api-token'
);
```

### Sending a Message

```php
$client->sendMessage('+27111111111', 'This is a test message.');
```

### Sending a File

```php
use Balfour\WhatsApp\File;

$file = new File('https://placehold.it/600x600', 'my_image.png');

$client->sendFile('+27111111111', $file);
```

### Retrieving Messages

```php
$messages = $client->getMessages();

foreach ($messages as $message) {
    var_dump($message->getChatId());
    var_dump($message->getMessageId());
    var_dump($message->getPhoneNumber());
    var_dump($message->getType());
    var_dump($message->getMessage());
    var_dump($message->getMediaUrl());
    var_dump($message->getDate());
    var_dump($message->isOutbound());
    var_dump($message->isInbound());
    var_dump($message->getMessageNumber());
}

// in order to only retrieve messages from a specific message number, you can pass a message number into
// the getMessages() call

$messages = $client->getMessages(12345);
```

### Rebooting the VM

```php
$client->reboot();
```

## Building a Bot

A full usage example connecting triggers, actions & middleware is available below.

### Actions

An action is a response which is executed when a trigger or menu option is matched.

The packages comes bundled with the following actions:

#### SendMessageAction

This action responds with a message.

```php
use Balfour\WhatsApp\Bot\Actions\SendMessageAction;

$action = new SendMessageAction('The weather in Cape Town, South Africa is 14°C.');

// or

$callable = function ($message) {
    // retrieve weather conditions
    $city = 'Cape Town, South Africa';
    $temperature = $myWeatherService->getTemperature($city);
    return sprintf('The weather in %s is %s', $city, $temperature);
};

$action = new SendMessageAction($callable);
```

#### SendFileAction

This action responds with a file (or image) attached.

```php
use Balfour\WhatsApp\Bot\Actions\SendFileAction;
use Balfour\WhatsApp\File;

$file = new File('https://placehold.it/600x600', 'my_image.png');

$action = new SendFileAction($file);
```

#### SendMenuAction

see menus

#### EscapeMenuAction

see menus

#### SendAndActivateMenuAction

see menus

#### Custom

You can build your own actions by creatin a custom class which implements the `ActionInterface`.

```php
use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

interface ActionInterface
{
    /**
     * @param WhatsApp $client
     * @param Message $message
     */
    public function execute(WhatsApp $client, Message $message): void;

    /**
     * @param Message $message
     * @return string|null
     */
    public function emulate(Message $message): ?string;
}
```

Here is an example action which repeats the inbound message.

```php
use Balfour\WhatsApp\Bot\Actions\ActionInterface;
use Balfour\WhatsApp\Message;
use Balfour\WhatsApp\WhatsApp;

class RepeatAndSendMessageAction implements ActionInterface
{
    /**
     * @param Message $message
     * @return string
     */
    protected function getResponse(Message $message): string
    {
        return sprintf('You said: %s', $message->getMessage());
    }
    
    /**
     * @param WhatsApp $client
     * @param Message $message
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(WhatsApp $client, Message $message): void
    {
        $client->sendMessage($message->getPhoneNumber(), $this->getResponse($message));
    }

    /**
     * @param Message $message
     * @return string|null
     */
    public function emulate(Message $message): ?string
    {
        return sprintf(
            "Sending message to <%s>:\n\n%s",
            $message->getPhoneNumber(),
            $this->getResponse($message)
        );
    }
}
```

### Triggers

A trigger is an inbound keyword or phrase which executes an action.

The following triggers are supported:

#### StringTrigger

The inbound message must be an exact match.

```php
use Balfour\WhatsApp\Bot\Actions\SendMessageAction;
use Balfour\WhatsApp\Bot\Triggers\StringTrigger;

// when the bot sees "!weather", it will respond with the current temperature
// the default is a case insensitive string comparison
// eg: will match !WEATHER, !weather or !Weather
$trigger = new StringTrigger(
    '!weather',
    new SendMessageAction('The weather in Cape Town, South Africa is 14°C.')
);

// if you want to do a case sensitive match, you can pass in true as the 3rd parameter
$trigger = new StringTrigger(
    '!weather',
    new SendMessageAction('The weather in Cape Town, South Africa is 14°C.'),
    true
);
```

#### StartsWithTrigger

The inbound message must start with the specified string.

```php
use Balfour\WhatsApp\Bot\Actions\SendMessageAction;
use Balfour\WhatsApp\Bot\Triggers\StartsWithTrigger;

$trigger = new StartsWithTrigger(
    'call',
    new SendMessageAction("We'll give you a call shortly!")
);

// this will match any inbound message which starts with 'call', eg: 'call', or 'call me'
// just like the StringTrigger, the matching is case insensitive by default
// you can force a case sensitive match by passing true as the 3rd parameter
```

#### RegexTrigger

The inbound message must match the given regex pattern.

```php
use Balfour\WhatsApp\Bot\Actions\SendMessageAction;
use Balfour\WhatsApp\Bot\Triggers\RegexTrigger;

$trigger = new RegexTrigger(
    '/^(hi|hello|howzit)$/i',
    new SendMessageAction("Hi There!")
);
```

### Menus

#### Single Menu

This is an example of a single menu.

The menu must be activated using a trigger. (see full example at end)

```php
use Balfour\WhatsApp\Bot\Actions\SendMessageAction;
use Balfour\WhatsApp\Bot\Menu\Menu;
use Balfour\WhatsApp\Bot\Menu\Option;

$options = [
    new Option(
        '1',
        'Get a random cat fact',
        new SendMessageAction('Cats can rotate their ears 180 degrees.')
    ),
    new Option(
        '2',
        'Fetch my available balance',
        new SendMessageAction('Your available balance is $10.00.')
    ),
];

$menu = new Menu(
    "Hello! I'm your friendly WhatsApp bot.",
    "You can press # to return to the main menu, or 'quit' to exit the menu.",
    $options
);

// this example returns an error message when the user messages an invalid menu option

$menu = new Menu(
    "Hello! I'm your friendly WhatsApp bot.",
    "You can press # to return to the main menu, or 'quit' to exit the menu.",
    $options,
    null,
    true,
    "I don't understand what you mean"
);
```

#### Nested Menus

This is a more complicated example with multiple nested menus.

In order to keep state of which menu the user is currently in, we must use an implemntation of
a `MenuStateStoreInterface`.  For testing purposes, this package comes bundled with a
`InMemoryMenuStateStore`; however in the real world, you'll likely want to use a store which
persists across multiple php processes, such as a redis cache.

```php
use Balfour\WhatsApp\Bot\Actions\SendMessageAction;
use Balfour\WhatsApp\Bot\Actions\SendAndActivateMenuAction;
use Balfour\WhatsApp\Bot\Menu\InMemoryMenuStateStore;
use Balfour\WhatsApp\Bot\Menu\Menu;
use Balfour\WhatsApp\Bot\Menu\Option;

$menuStateStore = new InMemoryMenuStateStore();

// first, build parent menu without any options
$root = new Menu(
    "Hello! I'm your friendly WhatsApp bot.",
    "You can press # to return to the main menu, or 'quit' to exit the menu."
);

// now, create weather menu, linking it to parent menu
$weatherOptions = [
    new Option(
        '1',
        'Cape Town, South Africa',
        new SendMessageAction('The weather in Cape Town, South Africa is 14°C.')
    ),
    new Option(
        '2',
        'Johannesburg, South Africa',
        new SendMessageAction('The weather in Johannesburg, South Africa is 9°C.')
    ),
];

$weatherMenu = new Menu(
    "Please select your city.",
    "You can press # to return to the main menu, @ to return to the previous menu or 'quit' to exit the menu.",
    $weatherOptions,
    $root
);

// add options to parent menu
$root->addOptions([
    new Option(
        '1',
        'Check the weather',
        new SendAndActivateMenuAction($weatherMenu, $menuStateStore)
    ),
    new Option(
        '2',
        'Fetch my available balance',
        new SendMessageAction('Your available balance is $10.00.')
    ),
]);
```

### Middleware

The bot uses middleware to match the inbound message to a trigger or a menu option.  These are
called `ProcessTrigger` and `ProcessMenuOption` respectively.

You can also write your own middleware by creating a class which implements the `MiddlewareInterface`.

```php
use Balfour\WhatsApp\Bot\Actions\ActionInterface;
use Balfour\WhatsApp\Message;

interface MiddlewareInterface
{
    /**
     * @param Message $message
     * @return ActionInterface|null
     */
    public function getAction(Message $message): ?ActionInterface;
}
```

### Full Example

This example is copied from the `emulate.php` script and can be run locally using
`php emulate.php`.

```php
use Balfour\WhatsApp\Bot\Actions\SendMessageAction;
use Balfour\WhatsApp\Bot\Actions\SendAndActivateMenuAction;
use Balfour\WhatsApp\Bot\Bot;
use Balfour\WhatsApp\Bot\Emulator;
use Balfour\WhatsApp\Bot\Menu\InMemoryMenuStateStore;
use Balfour\WhatsApp\Bot\Menu\Menu;
use Balfour\WhatsApp\Bot\Menu\Option;
use Balfour\WhatsApp\Bot\Middleware\ProcessMenuOption;
use Balfour\WhatsApp\Bot\Middleware\ProcessTrigger;
use Balfour\WhatsApp\Bot\Triggers\StringTrigger;
use Balfour\WhatsApp\Bot\Triggers\TriggerRegistry;

// create menus
$menuStateStore = new InMemoryMenuStateStore();

// first, build parent menu without any options
$root = new Menu(
    "Hello! I'm your friendly WhatsApp bot.",
    "You can press # to return to the main menu, or 'quit' to exit the menu."
);

// now, create weather menu, linking it to parent menu
$weatherOptions = [
    new Option(
        '1',
        'Cape Town, South Africa',
        new SendMessageAction('The weather in Cape Town, South Africa is 14°C.')
    ),
    new Option(
        '2',
        'Johannesburg, South Africa',
        new SendMessageAction('The weather in Johannesburg, South Africa is 9°C.')
    ),
];

$weatherMenu = new Menu(
    "Please select your city.",
    "You can press # to return to the main menu, @ to return to the previous menu or 'quit' to exit the menu.",
    $weatherOptions,
    $root
);

// add options to parent menu
$root->addOptions([
    new Option(
        '1',
        'Check the weather',
        new SendAndActivateMenuAction($weatherMenu, $menuStateStore)
    ),
    new Option(
        '2',
        'Fetch my available balance',
        new SendMessageAction('Your available balance is $10.00.')
    ),
]);


// create triggers
$triggers = new TriggerRegistry();

// when we get 'ping', we'll respond 'pong'
$triggers->register(new StringTrigger('ping', new SendMessageAction('pong!')));

// we'll want to bring up our root menu when the user types "menu"
$triggers->register(new StringTrigger('menu', new SendAndActivateMenuAction($root, $menuStateStore)));

// create bot with middleware loaded
$middleware = [
    new ProcessTrigger($triggers),
    new ProcessMenuOption($root, $menuStateStore)
];
$bot = new Bot($middleware);

// create & run emulator
$emulator = new Emulator($bot);
$emulator->run();

// if we were running this in the real world, we'll want to process any inbound message through the bot
// eg: when a message is received (via polling or web hook)
// $bot->process($message);
```

### Emulator

The package includes an emulator script which allows you to test your menu options and triggers
without sending real world messages.

You can run the emulator using `php emulate.php`
