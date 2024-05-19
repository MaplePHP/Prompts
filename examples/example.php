#!/usr/bin/env php
<?php
/**
 * You will need to composer install for the root directory directory 
 * e.g. where "composer.json" exists for the example to work.
 *
 * @example cd /path/to/Prompts/examples/
 *          php example.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dir = realpath(dirname(__FILE__)."/..");


if(!is_file("{$dir}/vendor/autoload.php")) {
    echo "\nYou will need to run `composer install` in the root directory of\n";
    echo "the Promps library where composer.json exists for the example to work.\n\n";
    die();
}

require_once("{$dir}/vendor/autoload.php");
require_once("{$dir}/SttyWrapper.php");
require_once("{$dir}/Ansi.php");
require_once("{$dir}/Command.php");
require_once("{$dir}/Navigation.php");
require_once("{$dir}/Prompt.php");

use MaplePHP\Prompts\Prompt;
use MaplePHP\Prompts\Command;
use MaplePHP\Prompts\SttyWrapper;

$command = new Command();
$inp = new Prompt();

$inp->setTitle("Your prompt title");
$inp->setDescription("Your prompt description, lorem ipsum dolor");

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
        "error" => "The last name must be more then 2 characters!",
        "validate" => function($input) {
            return (strlen($input) < 3);
        }
    ],
    "email" => [
        "type" => "text",
        "message" => "email",
        "validate" => [
            "length" => [1,200],
            "email" => [],
        ]
    ],
    "ssl" => [
        "type" => "toggle",
        "message" => "Do you want SSL",
    ],
    "message" => [
        "type" => "message", // Will be exclude form the end result array!
        "message" => "Lorem ipsum dolor",
    ],
    "select" => [
        "type" => "select",
        "message" => "Select item bellow",
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
    "confirm" => [
        "type" => "confirm",
        "message" => "Do you wish to continue?",
        "confirm" => "Continuing.."
    ]
]);


$prompt = $inp->prompt();
$command->progress(1, 100, function($i, $length) {
    return 20;
});
print_r($prompt);

