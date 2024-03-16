<?php

namespace MaplePHP\Prompts;

use MaplePHP\Prompts\Command;
use MaplePHP\Validate\Inp;
use InvalidArgumentException;

class Prompt
{
    private $command;
    private $data;

    public function __construct()
    {
        $this->command = new Command();
    }

    public function getCommand() {
        return $this->command;
    }

    /**
     * Add a line to be prompted
     * @param string $name Name the line
     * @param array  $data Prompt data to validate against
     */
    public function set(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Add a line to be prompted
     * @param string $name Name the line
     * @param array  $data Prompt data to validate against
     */
    public function add(string $name, array $data): self
    {
        $this->data[$name] = $data;
        return $this;
    }

    /**
     * Prompt line, directly prompt line without data
     * @param  array  $row
     * @return mixed
     */
    public function promptLine(array $row): mixed
    {
        $input = false;
        $default = ($row['default'] ?? "");
        $validate = ($row['validate'] ?? []);
        $message = ($row['message'] ?? "");
        $error = ($row['error'] ?? false);
        $items = ($row['items'] ?? []);

        if (!empty($default) && is_string($default)) {
            $message .= " ({$default})";
        }

        if ($this->isEmpty($row['message'] ?? "")) {
            throw new InvalidArgumentException("The message cannot be empty!", 1);
        }

        switch ($row['type'] ?? "text") {
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
                $input = (int)$this->command->confirm($message);
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
            }
            if (is_callable($error)) {
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
     * prompt output and get result as array or false if aborted
     * @return array|false
     */
    public function prompt(): array|false
    {
        $result = array();
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
     * Cehck if empty
     * @param  mixed  $input
     * @return bool
     */
    protected function isEmpty($input): bool
    {
        return ($input === false || $input === "");
    }

    /**
     * Validate a set of items
     * @param  array  $validate
     * @param  array  $input
     * @return bool
     */
    protected function validateItems(array|callable $validate, string|array $input, &$error = array()): bool
    {
        if (is_string($input)) {
            $input = array($input);
        }
        foreach ($input as $value) {

            if (is_callable($validate)) {
                $bool = $validate($value);
                if (!is_bool($bool)) {
                    throw new InvalidArgumentException("You callable validate function has to return a boolean type!", 1);
                }
                if ($bool) {
                    return false;
                }

            } else {
                foreach ($validate as $method => $args) {
                    if (!is_array($args)) {
                        $args = array();
                    }
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
     * @return bool
     */
    final protected function validate(string $value, string $method, array $args = []): bool
    {
        $inp = new Inp($value);
        if (!method_exists($inp, $method)) {
            throw new InvalidArgumentException("The validation do not exists", 1);
        }
        return call_user_func_array([$inp, $method], $args);
    }
}
