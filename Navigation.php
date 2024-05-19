<?php

namespace MaplePHP\Prompts;

use InvalidArgumentException;

/**
 * Class Navigation
 * @package MaplePHP\Prompts
 */
class Navigation
{
    const HELPER_TEXT = "Use arrow keys to navigate and press (%s) to select item.";

    private $command;
    private int $index = 0;
    private array $items = [];
    private array $values = [];
    private $input;
    private string $acceptKey;
    private ?string $helperText = null;

    /**
     * Navigation constructor.
     * @param Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
        $this->setAcceptKey($this->command->getAnsi()->keyEnter());
    }

    /**
     * Change exit key from default "Enter".
     * 
     * @param string $key
     */
    public function setAcceptKey(string $key): void 
    {
        $this->acceptKey = strtolower($key);
    }

    /**
     * Set a helper text that tells the user how it works.
     * You can add "%s" to your text which will represent the expected exit key.
     * 
     * @example Text: Use arrow keys to navigate and press (%s) to select.
     * @example Usage: $this->setHelperText(self::HELPER_TEXT);
     * 
     * @param string $text
     */
    public function setHelperText(string $text): void
    {
        $this->helperText = $text;
    }

    /**
     * Navigate questions
     * 
     * @param string $message
     * @param array $items
     * @param callable $call
     * @return void
     */
    public function navigation(string $message, array $items, callable $call): void 
    {
        $this->values = array_keys($items);
        $this->items = array_values($items);
        $start = $this->command->getStty()->toggleEnable(false, "echo")->toggleEnable(true, "cbreak");
        system($start);

        $this->command->getStream()->write($this->command->getAnsi()->bold($message) . "\n");
        $call($this->index, $this->items);
        $this->streamHelperText();
        $this->input(function() use ($call) {
            $call($this->index, $this->items);
            $this->streamHelperText();
        });

        $end = $this->command->getStty()->toggleEnable(true, "echo")->toggleEnable(false, "cbreak");
        system($end);
    }

    /**
     * Get the interactive prompts value
     * 
     * @return int|string
     */
    public function getValue(): int|string
    {
        return $this->values[$this->index];
    }

    /**
     * Get the interactive prompts item
     * 
     * @return string
     */
    public function getItem(): string 
    {
        return $this->items[$this->index];
    }

    /**
     * Interactive navigate between choices
     * 
     * @param callable $call Will prompt to callable
     * @return void
     */
    public function input(callable $call): void 
    {
        while (true) {
            $input = $this->command->getStream()->read(3);
            $key = $this->getKeyName($input);

            if ($input === $this->command->getAnsi()->keyUp()) {
                $this->index = max(0, $this->index - 1);
            } elseif ($input === $this->command->getAnsi()->keyDown()) {
                $this->index = min(count($this->items) - 1, $this->index + 1);
            } elseif ($input === $this->acceptKey) {
                break;
            }

            $lines = count($this->items) + 3;
            $this->clearLines($lines);
            $call($this->index);
        }
    }

    /**
     * Get the expected navigation key
     * 
     * @param string $key
     * @return string
     */
    protected function getKeyName(string $key): string
    {
        $check = $this->escBreaker($key);
        return $this->command->getAnsi()::NAV[$check] ?? $key;
    }

    /**
     * Get helper text if set/enabled
     * 
     * @return void
     */
    protected function streamHelperText(): void 
    {
        if (is_string($this->helperText)) {
            $message = sprintf($this->helperText, ucfirst($this->getKeyName($this->acceptKey)));
            $output = $this->command->getAnsi()->style(["italic"], "\n" . $message) . "\n\n";
            $this->command->getStream()->write($output);
        }
    }

    /**
     * Will escape breaks so they are showable
     * 
     * @param string $string
     * @return string
     */
    protected function escBreaker(string $string): string 
    {
        return str_replace(["\n", "\r", "\t"], ['\n', '\r', '\t'], $string);
    }

    /**
     * Clear lines
     * 
     * @param int $lines Total lines to clear
     * @return void
     */
    public function clearLines(int $lines): void
    {
        // Move the cursor up to the start
        $this->command->getStream()->write($this->command->getAnsi()->moveCursorTo($lines));
        for ($i = 0; $i < $lines; $i++) {
            $this->command->getStream()->write($this->command->getAnsi()->clearDown());
        }
        // Move the cursor "back" up to the start
        $this->command->getStream()->write($this->command->getAnsi()->moveCursorTo($lines));
    }
}
