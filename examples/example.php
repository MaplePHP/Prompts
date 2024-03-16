#!/usr/bin/env php
<?php

/*

//\u2713
//\027[13m
function showMenu($options, $selectedIndex, $initial = true) {
    if (!$initial) {
        // Calculate the number of lines the menu occupies (options + 2 for the instruction lines)
        $lines = count($options) + 2;
        
        // Move the cursor up to the beginning of the menu
        echo "\033[{$lines}A";

        // Overwrite the menu area with blank lines to clear it
        for ($i = 0; $i < $lines; $i++) {
            echo "\033[2K";  // Clear the entire line
            echo "\033[1B";  // Move down one line
        }

        // Move the cursor back up to the start position to redraw the menu
        echo "\033[{$lines}A";
    }

    // Redraw the menu
    echo "Use arrow keys to navigate and press Enter to select.\n\n";
    foreach ($options as $index => $option) {
        if ($index === $selectedIndex) {
            echo "\033[1;33m[\xE2\x9C\x94] $option (selected)\033[0m\n";
        } else {
            echo "[ ] $option\n";
        }
    }
}

$options = ["Option 1", "Option 2", "Option 3", "Option 4", "Option 4", "Option 4"];
$selectedIndex = 0;

// Disable terminal echo and set to raw mode to read characters as they are typed
system('stty -echo');
system('stty cbreak');

// Initially display the menu using the showMenu function
showMenu($options, $selectedIndex);

while (true) {
    $input = fread(STDIN, 3);

    if ($input == "\033[A") {
        // Up arrow
        $selectedIndex = max(0, $selectedIndex - 1);
    } elseif ($input == "\033[B") {
        // Down arrow
        $selectedIndex = min(count($options) - 1, $selectedIndex + 1);
    } elseif ($input == "\n") {
        // Enter key, break the loop
        break;
    }

    showMenu($options, $selectedIndex, false);
}

// Restore terminal settings
system('stty echo');
system('stty -cbreak');

// Move the cursor below the menu for final output
echo "\033[2K";  // Clear the line
echo "\033[1B";  // Move down one line



echo "You selected: " . $options[$selectedIndex] . "\n";

die;
 */


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dir = realpath(dirname(__FILE__)."/..");
require_once("{$dir}/vendor/autoload.php");
require_once("{$dir}/SttyWrapper.php");
require_once("{$dir}/Ansi.php");
require_once("{$dir}/Command.php");
require_once("{$dir}/Prompt.php");

use MaplePHP\Prompts\Prompt;
use MaplePHP\Prompts\Command;
use MaplePHP\Prompts\SttyWrapper;



$command = new Command();
$options = ["Option 1", "Option 2", "Option 3", "Option 4", "Option 4", "Option 4"];
$selectedIndex = 0;

// Disable terminal echo and set to raw mode to read characters as they are typed
system('stty -echo');
system('stty cbreak');

// Initially display the menu using the showMenu function
$command->tetst($options, $selectedIndex);

// Restore terminal settings
system('stty echo');
system('stty -cbreak');

// Move the cursor below the menu for final output
echo "\033[2K";  // Clear the line
echo "\033[1B";  // Move down one line



echo "You selected: " . $options[$selectedIndex] . "\n";

die;

/*

$system = new SttyWrapper();
$test = system($system->maskInput());
var_dump($test);
die;
 */

$inp = new Prompt();

$inp->set([
    "firstname" => [
        "type" => "text",
        "message" => "First name",
        "validate" => [
            "length" => [1,200]
        ],
        "error" => "Required"
    ],
    "lastname" => [
        "type" => "text",
        "message" => "Last name",
        "default" => "Doe",
        "error" => "EROROROR",
        "validate" => function($input) {

            return (strlen($input) < 3);
        }
    ],
    "ssl" => [
        "type" => "toggle",
        "message" => "Do you want SSL"
    ],
    "message" => [
        "type" => "message", // Will be exclude form the end result array!
        "message" => "Lorem ipsum dolor",
    ],
    "select" => [
        "type" => "select",
        "message" => "Select item bellow (%d-%d)",
        "items" => [
            "Lorem 1",
            "Lorem 2",
            "Lorem 3"
        ],
    ],
    "keyword" => [
        "type" => "list",
        "message" => "Keywords",
        "validate" => [
            "length" => [1,200],
            "number" => []
        ],
        "error" => function($errorType, $input, $row) {
            if($errorType === "length") {
                return "Is required";
            }
            return "Must be a number";

        }
    ],
    "password" => [
        "type" => "password",
        "message" => "Password",
        "validate" => [
            "length" => [1,200]
        ]
    ],
    "test" => [
        "type" => "confirm",
        "message" => "Do you wish to continue?",
        "confirm" => "Continuing.."
    ],
    "email" => [
        "type" => "text",
        "message" => "email",
        "validate" => [
            "length" => [1,200],
            "email" => [],
        ]
    ]
]);


$prompt = $inp->prompt();

$Command = new Command();
$Command->progress(1, 100, function($i, $length) {
    return 20;
});

print_r($prompt);

