<?php

namespace MaplePHP\Prompts;

use MaplePHP\Prompts\Command;
use MaplePHP\Validate\Inp;
use InvalidArgumentException;

class Prompt
{
    private Command $command;
    private array $data = [];
    private ?string $title = null;
    private ?string $description = null;
    private string $helperText = 'Exit the prompt with [CTRL + C]';

    public function __construct()
    {
        $this->command = new Command();
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    /**
     * Set prompt title (headline)
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set prompt description
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set prompt helper text
     * @param string $text
     * @return self
     */
    public function setHelperText(string $text): self
    {
        $this->helperText = $text;
        return $this;
    }

    /**
     * Add a line to be prompted
     * @param array $data Prompt data to validate against
     * @return self
     */
    public function set(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Add a line to be prompted
     * @param string $name Name the line
     * @param array $data Prompt data to validate against
     * @return self
     */
    public function add(string $name, array $data): self
    {
        $this->data[$name] = $data;
        return $this;
    }

    /**
     * Prompt line, directly prompt line without data
     * @param array $row
     * @return mixed
     */
    public function promptLine(array $row): mixed
    {
        $input = false;
        $default = $row['default'] ?? "";
        $validate = $row['validate'] ?? [];
        $message = $row['message'] ?? "";
        $error = $row['error'] ?? false;
        $items = $row['items'] ?? [];
        $rowType = $row['type'] ?? "text";

        if (!empty($default) && is_string($default)) {
            $message .= " ({$default})";
        }

        if ($this->isEmpty($row['message'] ?? "")) {
            throw new InvalidArgumentException("The message cannot be empty!", 1);
        }

        switch ($rowType) {
            case "message":
                $input = $this->command->message($message);
                break;
            case "text":
                $input = $this->command->message($message, true);
                break;
            case "invisible":
            case "mask":
            case "password":
                $input = $this->command->mask($message);
                break;
            case "list":
                $input = $this->command->list($message);
                break;
            case "select":
                $input = $this->command->select($message, $items);
                break;
            case "toggle":
                $input = $this->command->toggle($message);
                break;
            case "confirm":
                $input = (int) $this->command->confirm($message);
                if (!$input) {
                    $this->command->message($this->command->getAnsi()->style(["red", "bold"], "Aborted..."));
                    return false;
                }
                break;
        }

        if (is_string($row['confirm'] ?? false)) {
            $this->command->message($this->command->getAnsi()->style(["green", "bold"], $row['confirm']));
            $this->command->message("...");
        }

        if ($this->isEmpty($input)) {
            $input = $default;
        }
        
        if (!$this->validateItems($validate, $input, $errorType)) {
            if (is_string($error) && $error !== "") {
                $this->command->message($this->command->getAnsi()->red($error));
            } elseif (is_callable($error)) {
                $errorMsg = $error($errorType, $input, $row);
                if (!is_string($errorMsg)) {
                    throw new InvalidArgumentException("The error callable has to return a string!", 1);
                }
                $this->command->message($this->command->getAnsi()->red($errorMsg));
            }
            return $this->promptLine($row);
        }
        return $input;
    }

    /**
     * Prompt output and get result as array or false if aborted
     * @return array|false
     */
    public function prompt(): array|false
    {
        $result = [];
        $this->getHeaderInfo();
        foreach ($this->data as $name => $row) {
            $input = $this->promptLine($row);
            if ($input === false) {
                return false;
            }
            if (($row['type'] ?? "text") !== "message") {
                $result[$name] = $input;
            }
            unset($this->data[$name]);
        }
        return $result;
    }

    /**
     * Prompt header information
     * @return void
     */
    protected function getHeaderInfo(): void 
    {
        $this->command->message("\n" . $this->command->getAnsi()->italic($this->helperText . "\n"));

        if (is_string($this->title)) {
            $this->command->message($this->command->getAnsi()->bold($this->title));
        }

        if (is_string($this->description)) {
            $this->command->message($this->description);
        }

        if (is_string($this->title) || is_string($this->description)) {
            $this->command->message("");
        }
    }
    
    /**
     * Check if input is empty
     * @param mixed $input
     * @return bool
     */
    protected function isEmpty($input): bool
    {
        return ($input === false || $input === "");
    }

    /**
     * Validate a set of items
     * @param array|callable $validate
     * @param string|array $input
     * @param array $error
     * @return bool
     */
    protected function validateItems(array|callable $validate, string|array $input, &$error = []): bool
    {
        $input = is_string($input) ? [$input] : $input;
        foreach ($input as $value) {
            if (is_callable($validate)) {
                $isValid = $validate($value);
                if (!is_bool($isValid)) {
                    throw new InvalidArgumentException("The callable validate function must return a boolean!", 1);
                }
                if ($isValid) {
                    return false;
                }
            } else {
                foreach ($validate as $method => $args) {
                    $args = is_array($args) ? $args : [];
                    if (!$this->validate($value, $method, $args)) {
                        $error = $method;
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Validate input
     * @param string $value
     * @param string $method
     * @param array $args
     * @return bool
     */
    final protected function validate(string $value, string $method, array $args = []): bool
    {
        $inp = new Inp($value);
        if (!method_exists($inp, $method)) {
            throw new InvalidArgumentException("The validation method does not exist", 1);
        }
        return call_user_func_array([$inp, $method], $args);
    }
}
