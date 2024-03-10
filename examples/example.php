#!/usr/bin/env php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dir = realpath(dirname(__FILE__)."/..");
require_once("{$dir}/vendor/autoload.php");
require_once("{$dir}/Ansi.php");
require_once("{$dir}/Command.php");
require_once("{$dir}/Prompt.php");

use MaplePHP\Prompts\Prompt;
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
    "lastname" => [
        "type" => "text",
        "message" => "Last name",
        "default" => "Doe",
        "validate" => [
            "length" => [1,200]
        ]
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
var_dump($inp->prompt());

