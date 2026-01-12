# MaplePHP Prompts

PHP Prompts is a pretty, interactive, lightweight, and user-friendly CLI (Command Line Interface) PHP library designed for seamless use across Linux, Unix, Darwin, and Windows. It allows you to create and manage user prompts in a command-line environment, enabling the collection of various types of input with built-in validation and feedback.

![Prompt demo](http://wazabii.se/github-assets/maplephp-prompt-v2.gif)

_Note that the prompt may not look as polished on Windows as on Unix, but it will work just as well._

### Table of Contents
- [Installation](#installation)
- [Usage Example](#usage-example)
- [Available Options](#available-options)
- [Available Prompt Types](#available-prompt-types)
- [Command instance](#command-instance)
- [Progress Bar](#progress-bar)
- [Validation](#validation-and-error-handling)

## Installation

To install PHP Prompts, use Composer:

```bash
composer require maplephp/prompts
```

## Usage Example

Below is an example demonstrating how to use the PHP Prompts library to create interactive prompts and validate user input.

### Step-by-Step Guide

1. **Initialize**

```php
$prompt = new \MaplePHP\Prompts\Prompt();
```

2. **Set the Title and Description for the Prompts**

   This step is optional but provides context for the user.

   ```php
   $prompt->setTitle("Hello there!");
   $prompt->setDescription("We need your contact information to stay in touch, thank you.");
   ```

3. **Define the Prompts**

    Define the prompts and their respective settings, including type, message, validation rules, and error messages.
   ```php
   $prompt->set([
        "name" => [
            "type" => "text",
            "message" => "Full Name",
            "validate" => [
                "length" => [1,200]
            ]
        ],
        "email" => [
            "type" => "text",
            "message" => "Email",
            "validate" => [
                "length" => [1,200],
                "email" => []
            ],
            "error" => "The email address entered is not valid."
        ],
        "newsletter" => [
            "type" => "toggle",
            "message" => "Receive newsletter?",
        ],
        "method" => [
            "type" => "select",
            "message" => "Preferred contact method?",
            "items" => [
                "email" => "Email",
                "phone" => "Phone"
            ],
        ],
        "contactByPhone" => [
            "type" => "continue",
            "items" => function($prevVal) {
                if($prevVal === "phone") {
                    return [
                        "phone" => [
                            "type" => "text",
                            "message" => "Phone",
                            "validate" => [
                                "length" => [1,30],
                                "phone" => []
                            ]
                        ]
                    ];
                }
                return false;
            }
        ],
        "confirm" => [
            "type" => "confirm",
            "message" => "Do you wish to continue?"
        ]
   ]);
      ```
4. **Execute the Prompt**

    Prompt the user for input based on the defined prompts above. The collected user input will be saved in the `$prompt` variable.

    ```php
    // Execute the prompt
    $prompt = $prompt->prompt();
    // Print out the user inputs
    print_r($prompt); 
    ```


## Available Options

1. **type**
   - **Description**: Specifies the type of the prompt (e.g., `text`, `password`, `toggle`, `select`, `list`, `continue`, `message`, `confirm`).
   - **Example**: `"type" => "text"`

2. **message**
   - **Description**: The message or question displayed to the user.
   - **Example**: `"message" => "Enter your full name"`

3. **default**
   - **Description**: A default value for the prompt, used if the user does not provide input.
   - **Example**: `"default" => "Your default value"`

4. **items**
   - **Description**: An array of items to choose from, used with `select` type prompts.
   - **Example**: `"items" => ["Option 1", "Option 2", "Option 3"]`

5. **validate**
   - **Description**: Validation rules for the input. Can be an array of rules or a custom function.
   - **Examples**:
     - Length validation: `"validate" => ["length" => [1, 200]]`
     - Custom function validation: 
       ```php
       "validate" => function($input) {
           return strlen($input) >= 3;
       }
       ```

6. **error**
   - **Description**: Error message or function to display when validation fails.
   - **Examples**:
     - Static message: `"error" => "Input is required"`
     - Custom function error:
       ```php
       "error" => function($errorType, $input, $row) {
           if ($errorType === "length") {
               return "Is required";
           }
           return "Must be a number";
       }
       ```

7. **confirm**
   - **Description**: Message to display upon user confirmation, used with `confirm` type prompts.
   - **Example**: `"confirm" => "Are you sure you want to continue?"`

## Available Prompt Types

### Summary

- **text**: Prompts for regular text input.
- **password**: Prompts for masked password input.
- **toggle**: Asks a yes/no question.
- **select**: Provides a list of options for selection.
- **list**: Prompts for a comma-separated list of values.
- **continue**: Continue with input dependent on previous value 
- **message**: Displays a message (no input).
- **confirm**: Asks for confirmation before proceeding.

### Details

1. **text**
   - **Description**: Prompts the user for regular text input.
   - **Usage Example**:
     ```php
     "firstname" => [
         "type" => "text",
         "message" => "First name"
     ]
     ```

2. **password**
   - **Description**: Prompts the user for password input, masking the characters as they are typed.
   - **Usage Example**:
     ```php
     "password" => [
         "type" => "password",
         "message" => "Password"
     ]
     ```

3. **toggle**
   - **Description**: Asks the user a yes/no question.
   - **Usage Example**:
     ```php
     "ssl" => [
         "type" => "toggle",
         "message" => "Do you want SSL?"
     ]
     ```

4. **select**
   - **Description**: Provides a list of options for the user to choose from.
   - **Usage Example**:
     ```php
     "select" => [
         "type" => "select",
         "message" => "Select an item below",
         "items" => [
             "Lorem 1",
             "Lorem 2",
             "Lorem 3"
         ]
     ]
     ```

5. **list**
   - **Description**: Prompts the user for a comma-separated list of values.
   - **Usage Example**:
     ```php
     "keyword" => [
         "type" => "list",
         "message" => "Keywords"
     ]
     ```
5. **continue**
    - **Description**: Continue with input dependent on previous value.
    - **Usage Example**:
      ```php
      "continue" => [
         "type" => "continue",
         "items" => function($prevVal) {
            if($prevVal === "phone") {
                return [
                    "phone" => [
                        "type" => "text",
                        "message" => "Phone",
                        "validate" => [
                            "length" => [1,30],
                            "phone" => []
                        ]
                    ]
                ];
            }
            return false;
        }
      ]
      ```
6. **message**
   - **Description**: Displays a message to the user. This type does not collect input and will be excluded from the end result array.
   - **Usage Example**:
     ```php
     "message" => [
         "type" => "message",
         "message" => "Lorem ipsum dolor"
     ]
     ```

7. **confirm**
   - **Description**: Asks the user for confirmation before proceeding. Can display a custom confirmation message.
   - **Usage Example**:
     ```php
     "confirm" => [
         "type" => "confirm",
         "message" => "Do you wish to continue?"
     ]
     ```

These prompt types enable a variety of user interactions in a CLI environment, making it easy to collect and validate different kinds of input using the PHP Prompts library.

## Command instance
You also execute some of the prompt above command directly with the Command class.

```php
$command = new \MaplePHP\Prompts\Command();
// Print messages and return input
$input = $command->message("Text field", true);
$input = $command->mask("Masked input (password)");
$input = $command->toggle("Toggle yes/no");
$input = $command->select("Choose a value", [
    //...
]);
$input = $command->list("Comma seperated list");
$input = $command->confirm("Confirm");

// Print messages
$command->message("Print message");
$command->title("Print bolded message");
$command->statusMsg("Print blue status message");
$command->approve("Print green approve message");
$command->approve("Print red error message");
```

## Progress Bar

The `progress` method of the `Command` class allows displaying a progress bar with customizable sleep intervals to indicate ongoing operations.

```php
$command = new \MaplePHP\Prompts\Command();
$command->progress(1, 100, function($i, $length) {
    return 20;
});
```

## Validation and Error Handling

Each prompt can have validation rules and custom error messages. Validation can be defined using built-in rules (e.g., length, email) or custom functions. Errors can be specified as static messages or dynamic functions based on the error type.

### Validation List

1. **required**
   - **Description**: Checks if the value is not empty (e.g., not `""`, `0`, `NULL`).
   - **Usage**: `"required" => []`

2. **length**
   - **Description**: Checks if the string length is between a specified start and end length.
   - **Usage**: `"length" => [1, 200]`

3. **email**
   - **Description**: Validates email addresses.
   - **Usage**: `"email" => []`

4. **number**
   - **Description**: Checks if the value is numeric.
   - **Usage**: `"number" => []`

5. **min**
   - **Description**: Checks if the value is greater than or equal to a specified minimum.
   - **Usage**: `"min" => [10]`

6. **max**
   - **Description**: Checks if the value is less than or equal to a specified maximum.
   - **Usage**: `"max" => [100]`

7. **url**
   - **Description**: Checks if the value is a valid URL (http|https is required).
   - **Usage**: `"url" => []`

8. **phone**
   - **Description**: Validates phone numbers.
   - **Usage**: `"phone" => []`

9. **date**
   - **Description**: Checks if the value is a valid date with the specified format.
   - **Usage**: `"date" => ["Y-m-d"]`

10. **dateTime**
    - **Description**: Checks if the value is a valid date and time with the specified format.
    - **Usage**: `"dateTime" => ["Y-m-d H:i"]`

11. **bool**
    - **Description**: Checks if the value is a boolean.
    - **Usage**: `"bool" => []`

12. **oneOf**
    - **Description**: Validates if one of the provided conditions is met.
    - **Usage**: `"oneOf" => [["length", [1, 200]], "email"]`

13. **allOf**
    - **Description**: Validates if all of the provided conditions are met.
    - **Usage**: `"allOf" => [["length", [1, 200]], "email"]`

14. **float**
    - **Description**: Checks if the value is a float.
    - **Usage**: `"float" => []`

15. **int**
    - **Description**: Checks if the value is an integer.
    - **Usage**: `"int" => []`

16. **positive**
    - **Description**: Checks if the value is a positive number.
    - **Usage**: `"positive" => []`

17. **negative**
    - **Description**: Checks if the value is a negative number.
    - **Usage**: `"negative" => []`

18. **validVersion**
    - **Description**: Checks if the value is a valid version number.
    - **Usage**: `"validVersion" => [true]`

19. **versionCompare**
    - **Description**: Validates and compares if a version is equal/more/equalMore/less... e.g., than withVersion.
    - **Usage**: `"versionCompare" => ["1.0.0", ">="]`

20. **zip**
    - **Description**: Validates ZIP codes within a specified length range.
    - **Usage**: `"zip" => [5, 9]`

21. **hex**
    - **Description**: Checks if the value is a valid hex color code.
    - **Usage**: `"hex" => []`

22. **age**
    - **Description**: Checks if the value represents an age equal to or greater than the specified minimum.
    - **Usage**: `"age" => [18]`

23. **domain**
    - **Description**: Checks if the value is a valid domain.
    - **Usage**: `"domain" => [true]`

24. **dns**
    - **Description**: Checks if the host/domain has a valid DNS record (A, AAAA, MX).
    - **Usage**: `"dns" => []`

25. **matchDNS**
    - **Description**: Matches DNS records by searching for a specific type and value.
    - **Usage**: `"matchDNS" => [DNS_A]`

26. **equal**
    - **Description**: Checks if the value is equal to a specified value.
    - **Usage**: `"equal" => ["someValue"]`

27. **notEqual**
    - **Description**: Checks if the value is not equal to a specified value.
    - **Usage**: `"notEqual" => ["someValue"]`

28. **string**
    - **Description**: Checks if the value is a string.
    - **Usage**: `"string" => []`

29. **equalLength**
    - **Description**: Checks if the string length is equal to a specified length.
    - **Usage**: `"equalLength" => [10]`

30. **lossyPassword**
    - **Description**: Validates password with allowed characters `[a-zA-Z\d$@$!%*?&]` and a minimum length.
    - **Usage**: `"lossyPassword" => [8]`

31. **strictPassword**
    - **Description**: Validates strict password with specific character requirements and a minimum length.
    - **Usage**: `"strictPassword" => [8]`

32. **pregMatch**
    - **Description**: Validates if the value matches a given regular expression pattern.
    - **Usage**: `"pregMatch" => ["a-zA-Z"]`

33. **atoZ**
    - **Description**: Checks if the value consists of characters between `a-z` or `A-Z`.
    - **Usage**: `"atoZ" => []`

34. **lowerAtoZ**
    - **Description**: Checks if the value consists of lowercase characters between `a-z`.
    - **Usage**: `"lowerAtoZ" => []`

35. **upperAtoZ**
    - **Description**: Checks if the value consists of uppercase characters between `A-Z`.
    - **Usage**: `"upperAtoZ" => []`

36. **isArray**
    - **Description**: Checks if the value is an array.
    - **Usage**: `"isArray" => []`

37. **isObject**
    - **Description**: Checks if the value is an object.
    - **Usage**: `"isObject" => []`

38. **boolVal**
    - **Description**: Checks if the value is a boolean-like value (e.g., "on", "yes", "1", "true").
    - **Usage**: `"boolVal" => []`

### Usage Example

Here is an example of how to use the validation functions in the prompt library:

```php
$inp->set([
    "firstname" => [
        "type" => "text",
        "message" => "First name",
        "validate" => [
            "length" => [1, 200],
            "required" => []
        ],
        "error" => "Required"
    ],
    "email" => [
        "type" => "text",
        "message" => "Email",
        "validate" => [
            "length" => [1, 200],
            "email" => []
        ]
    ],
    "age" => [
        "type" => "text",
        "message" => "Age",
        "validate" => [
            "number" => [],
            "min" => [18]
        ]
    ]
]);
```

## Conclusion

PHP Prompts provides a straightforward way to create interactive command-line interfaces in PHP. By defining prompts, validation rules, and error messages, developers can easily gather and validate user input in a CLI environment.