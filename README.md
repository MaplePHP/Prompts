
# MaplePHP Prompts

PHP Prompts is an interactive, lightweight, and easy-to-use CLI (Command Line Interface) PHP library. It provides a way to create and manage user prompts in a command-line environment, enabling the collection of various types of user input with validation and feedback.


## Usage Example

Below is an example demonstrating how to use the PHP Prompts library to create interactive prompts, validate user input, and display a progress bar.

### Step-by-Step Guide

1. **Initialize the Command and Prompt Objects**

```php
$prompt = new Prompt();
```

2. **Set the Title and Description for the Prompts**

```php
$prompt->setTitle("Your prompt title");
$prompt->setDescription("Your prompt description, lorem ipsum dolor");
```

3. **Define the Prompts**

Define the prompts and their respective settings, including type, message, validation rules, and error messages.

```php
$prompt->set([
    "firstname" => [
        "type" => "text",
        "message" => "First name",
        "validate" => [
            "length" => [1, 200]
        ],
        "error" => "Required" // Validation error message
    ],
    "lastname" => [
        "type" => "text",
        "message" => "Last name",
        "default" => "Doe",
        "error" => "The last name must be more than 2 characters!",
        "validate" => function($promptut) {
            return (strlen($promptut) >= 3);
        }
    ],
    "email" => [
        "type" => "text",
        "message" => "Email",
        "validate" => [
            "length" => [1, 200],
            "email" => []
        ]
    ],
    "ssl" => [
        "type" => "toggle",
        "message" => "Do you want SSL?",
    ],
    "message" => [
        "type" => "message", // Will be excluded from the end result array
        "message" => "Lorem ipsum dolor",
    ],
    "select" => [
        "type" => "select",
        "message" => "Select an item below",
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
            "length" => [1, 200],
            "number" => []
        ],
        "error" => function($errorType, $promptut, $row) {
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
            "length" => [1, 200]
        ]
    ],
    "confirm" => [
        "type" => "confirm",
        "message" => "Do you wish to continue?",
        "confirm" => "Continuing..."
    ]
]);
```

4. **Execute the Prompt**

Prompt the user for input based on the defined prompts.

```php
$prompt = $prompt->prompt();
```

5. **Display a Progress Bar**

Display a progress bar to indicate progress. This is certaintly not required but it is a fun feature.

```php
$command = new Command();
$command->progress(1, 100, function($i, $length) {
    return 20;
});
```

6. **Print the User Input**

Print the collected user input.

```php
print_r($prompt);
```

## Detailed Explanation of Prompts above

- **Text Prompt**
  - **firstname**: Requires a non-empty string with a maximum length of 200 characters.
  - **lastname**: Requires a string with at least 3 characters, default value "Doe".

- **Email Prompt**
  - **email**: Requires a valid email address with a maximum length of 200 characters.

- **Toggle Prompt**
  - **ssl**: Asks the user if they want SSL enabled (yes/no).

- **Message Prompt**
  - **message**: Displays a message "Lorem ipsum dolor".

- **Select Prompt**
  - **select**: Provides a list of items for the user to select from.

- **List Prompt**
  - **keyword**: Prompts for a comma-separated list of keywords, each must be a number with a maximum length of 200 characters.

- **Password Prompt**
  - **password**: Requires a non-empty password with a maximum length of 200 characters.

- **Confirm Prompt**
  - **confirm**: Asks the user for confirmation before continuing.

## Validation and Error Handling

Each prompt can have validation rules and custom error messages. Validation can be defined using built-in rules (e.g., length, email) or custom functions. Errors can be specified as static messages or dynamic functions based on the error type.

## Progress Bar

The `progress` method of the `Command` class allows displaying a progress bar with customizable sleep intervals to indicate ongoing operations.

## Conclusion

PHP Prompts provides a straightforward way to create interactive command-line interfaces in PHP. By defining prompts, validation rules, and error messages, developers can easily gather and validate user input in a CLI environment.
