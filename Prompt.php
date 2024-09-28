<?php

namespace MaplePHP\Prompts;
//use MaplePHP\Prompts\Command;
use ErrorException;
use Exception;
use MaplePHP\Validate\Inp;
use InvalidArgumentException;

class Prompt
{
    private Command $command;
    private array $data = [];
    private ?string $title = null;
    private ?string $description = null;
    private ?string $helperText = 'Exit the prompt with [CTRL + C]';
    private int $index = 0;
    private mixed $prevVal = null;
    private bool $disableHeaderInfo = false;

    public function __construct()
    {
        $this->command = new Command();
    }

    /**
     * Get command instance
     *
     * @return Command
     */
    public function getCommand(): Command
    {
        return $this->command;
    }

    /**
     * Set prompt title (headline)
     *
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
     *
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Disable all header information
     * @param bool $disable
     * @return $this
     */
    public function disableHeaderInfo(bool $disable): self
    {
        $this->disableHeaderInfo = $disable;
        return $this;
    }

    /**
     * Add a line to be prompted
     *
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
     *
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
     *
     * @param array $row
     * @return string|array|bool|int
     * @throws PromptException
     * @throws Exception
     */
    public function promptLine(array $row): string|array|bool|int
    {
        $input = false;
        $default = $row['default'] ?? "";
        $validate = $row['validate'] ?? [];
        $message = (string)($row['message'] ?? "");
        $error = $row['error'] ?? false;
        $items = $row['items'] ?? [];
        $rowType = $row['type'] ?? "text";
        $confirm = $row['confirm'] ?? false;

        if (isset($default) && is_string($default)) {
            $message .= " ($default)";
        }

        if ($rowType !== "continue" && $this->isEmpty($row['message'] ?? "")) {
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
                if (!is_array($items)) {
                   throw new InvalidArgumentException("The items must be an array!", 1);
                }
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
            case "continue":
                if(is_callable($items)) {
                    $items = $items($this->prevVal, $this->index);
                    if($items === false) {
                        return true;
                    }
                }
                if(!is_array($items)) {
                    throw new InvalidArgumentException("The items must return a a valid array");
                }
                $inst = new self();
                $inst->disableHeaderInfo(true);
                $inst->data = $items;
                $input = $inst->prompt();
                break;
        }

        if (is_string($confirm)) {
            $this->command->message($this->command->getAnsi()->style(["green", "bold"], $confirm));
            $this->command->message("...");
        }

        if ($this->isEmpty($input)) {
            $input = $default;
        }
        
        if (!(is_array($input) || is_string($input))) {
            throw new InvalidArgumentException("The input item is wrong input data type", 1);
        }
        if (!(is_array($validate) || is_callable($validate))) {
            throw new InvalidArgumentException("The validate item is wrong input data type", 1);
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
        $this->prevVal = $input;
        return $input;
    }

    /**
     * Prompt output and get result as array or false if aborted
     *
     * @return array|false
     * @throws PromptException
     */
    public function prompt(): array|false
    {
        $result = [];
        $this->index = 0;
        if(!$this->disableHeaderInfo) {
            $this->getHeaderInfo();
        }
        foreach ($this->data as $name => $row) {
            if(!is_array($row)) {
                throw new PromptException("The data array has to return an array!", 1);
            }
            $input = $this->promptLine($row);
            if ($input === false) {
                return false;
            }
            if (($row['type'] ?? "text") !== "message") {
                $result[$name] = $input;
            }
            unset($this->data[$name]);
            $this->index++;
        }
        return $result;
    }

    /**
     * Prompt header information
     *
     * @return void
     */
    protected function getHeaderInfo(): void 
    {
        if(!is_null($this->helperText)) {
            $this->command->message("\n" . $this->command->getAnsi()->italic($this->helperText . "\n"));
        }

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
     *
     * @param mixed $input
     * @return bool
     */
    protected function isEmpty(mixed $input): bool
    {
        return ($input === false || $input === "");
    }

    /**
     * Validate a set of items
     *
     * @param array|callable $validate
     * @param string|array $input
     * @param array|null $error
     * @param-out null|string $error Error message or method that caused validation failure.
     * @return bool
     * @throws ErrorException
     */
    protected function validateItems(array|callable $validate, string|array $input, ?array &$error = []): bool
    {
        $input = is_string($input) ? [$input] : $input;
        foreach ($input as $value) {
            if (is_callable($validate)) {
                $isValid = $validate($value);
                if (!is_bool($isValid)) {
                    throw new InvalidArgumentException("The callable validate function must return a boolean!", 1);
                }
                if ($isValid) {
                    $error = null;
                    return false;
                }
            } else {
                foreach ($validate as $method => $args) {
                    $method = (string)$method;
                    $args = is_array($args) ? $args : [];
                    if (!$this->validate((string)$value, $method, $args)) {
                        $error = $method;
                        return false;
                    }
                }
            }
        }
        $error = null;
        return true;
    }

    /**
     * Validate input
     * @param string $value
     * @param string $method
     * @param array $args
     * @return bool
     * @throws ErrorException
     */
    final protected function validate(string $value, string $method, array $args = []): bool
    {
        $inp = new Inp($value);
        if (!method_exists($inp, $method)) {
            throw new InvalidArgumentException("The validation method \"$method\" does not exist", 1);
        }
        return (bool)call_user_func_array([$inp, $method], $args);
    }
}
