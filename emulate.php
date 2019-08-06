<?php

require __DIR__ . '/vendor/autoload.php';

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
