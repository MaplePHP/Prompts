<?php

namespace MaplePHP\Prompts;

use MaplePHP\Http\Stream;
use MaplePHP\Prompts\SttyWrapper;
use MaplePHP\Prompts\Ansi;
use MaplePHP\Prompts\Navigation;
use InvalidArgumentException;

/**
 * Class Command
 * @package MaplePHP\Prompts
 */
class Command
{
    private Stream $stream;
    private SttyWrapper $stty;
    private Ansi $ansi;

    public function __construct()
    {
        $this->stream = new Stream(Stream::STDIN, "r");
        $this->stty = new SttyWrapper();
        $this->ansi = new Ansi();
    }

    /**
     * Get stream
     * 
     * @return Stream
     */
    public function getStream(): Stream
    {
        return $this->stream;
    }

    /**
     * Get SttyWrapper
     * 
     * @return SttyWrapper
     */
    public function getStty(): SttyWrapper
    {
        return $this->stty;
    }

    /**
     * Get Ansi
     * 
     * @return Ansi
     */
    public function getAnsi(): Ansi
    {
        return $this->ansi;
    }

    /**
     * Read line
     * 
     * @param string $message
     * @return string
     */
    public function readline(string $message): string
    {
        if (function_exists("readline")) {
            return readline("{$message}: ");
        }

        $this->stream->write("{$message}: ");
        return $this->stream->getLine();
    }

    /**
     * Display a message
     * 
     * @param string $message
     * @param bool $getLine Stop and get line
     * @return string|false
     */
    public function message(string $message, bool $getLine = false): string|false
    {
        if ($getLine) {
            return $this->readline($message);
        }

        $this->stream->write($message);
        $this->stream->write("\n");
        return false;
    }
    
    /**
     * Prompt for a comma-separated list
     * 
     * @param string $message
     * @return array
     */
    public function list(string $message): array
    {
        $line = $this->message("{$message} (comma separate)", true);
        return array_map("trim", explode(",", $line));
    }

    /**
     * Display an interactive prompt with multiple options
     * If interactive prompt is not supported, use "inputSelect"
     * 
     * @param string $message
     * @param array $items
     * @return int|string
     */
    public function select(string $message, array $items): int|string
    {
        if ($this->stty->hasSttySupport()) {
            $command = new Navigation($this);
            $command->setHelperText($command::HELPER_TEXT);
            $command->navigation($message, $items, function ($index, $items) {
                $this->showMenu($index, $items);
            });
            return $command->getValue();
        }
        return $this->inputSelect($message, $items);
    }

    /**
     * Display a non-interactive prompt with multiple options
     * 
     * @param string $message
     * @param array $choices
     * @return int|string
     */
    public function inputSelect(string $message, array $choices): int|string
    {
        if (count($choices) === 0) {
            throw new InvalidArgumentException("You cannot input an empty array!", 1);
        }

        $int = 1;
        $length = count($choices);
        $this->message(sprintf($this->getAnsi()->bold($message . " (%d-%d)"), $int, $length));
        foreach ($choices as $value) {
            $this->message($this->getAnsi()->style(["blue", "bold"], "{$int}:") . " {$value}");
            $int++;
        }
        $line = $this->message("Input your answer", true);
        if ($line > 0 && $line <= $length) {
            $index = $line - 1;
            $values = array_keys($choices);
            return $values[$index] ?? $index;
        }
        return $this->inputSelect($message, $choices);
    }

    /**
     * Interactive prompt, choose between yes or no
     * 
     * @param string $message
     * @return int|string
     */
    public function toggle(string $message): int|string
    {
        if ($this->stty->hasSttySupport()) {
            $items = [1 => "Yes", 0 => "No"];
            $command = new Navigation($this);
            $command->setHelperText(Navigation::HELPER_TEXT);
            $command->navigation($message, $items, function ($index, $items) {
                $this->showMenu($index, $items);
            });
            return $command->getValue();
        }
        return $this->inputToggle($message);
    }

    /**
     * Non-interactive prompt, choose between yes or no
     * 
     * @param string $message
     * @return string
     */
    public function inputToggle(string $message): string
    {
        $this->message($this->getAnsi()->bold($message));
        $line = strtolower($this->message("Type 'yes' or 'no'", true));

        if ($line === "yes" || $line === "no") {
            return (int)($line === "yes");
        }
        return $this->toggle($message);
    }

    /**
     * Get a masked input if supported, otherwise get unmasked input
     * 
     * @param string $message
     * @return string
     */
    public function mask(string $message): string
    {
        if (function_exists("system")) {
            $this->stream->write("{$message} (masked input): ");
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Not yet tested. But should work if my research is right
                $input = rtrim((string)system("powershell -Command \$input = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR(\$input))"));
            } else {
                $input = rtrim(system($this->stty->maskInput()));
            }
            $this->stream->write("\n");
            return $input;
        }
        $this->message("Warning: The input will not be masked. The PHP function \"system\" is disabled.");
        return $this->message($message, true);
    }

    /**
     * Display a confirmation prompt
     * 
     * @param string $message
     * @return bool
     */
    public function confirm(string $message): bool
    {
        $this->message($this->getAnsi()->style(["yellow", "bold"], $message));
        $line = trim(strtolower($this->message("Type 'yes' to continue and 'no' to abort'", true)));

        if ($line === "yes") {
            return true;
        } elseif ($line === "no") {
            return false;
        }
        return $this->confirm($message);
    }
    
    /**
     * Create a progress bar
     * 
     * @param int $expectedRows The maximum expected rows
     * @param int $maxLength The maximum bar length
     * @param int|callable|null $sleep int for sleep in milliseconds (3000 ms = 1 second) or callback with returned int for sleep in milliseconds
     * @return void
     */
    public function progress(int $expectedRows, int $maxLength = 100, null|int|callable $sleep = null): void
    {
        $i = 0;
        $char = "-";
        $ratio = ($maxLength > $expectedRows) ? $expectedRows / $maxLength : $maxLength / $expectedRows;
        $length = ($maxLength - 0.5) * $ratio;
        $inc = round($ratio, 4);
        while ($i < $length) {
            if (is_callable($sleep)) {
                $sleep = $sleep($i, $length);
            }
            if (is_int($sleep)) {
                usleep($sleep * 1000);
            }
            $this->stream->write($this->progAppear($char, $i, $length));
            $i += $inc;
        }
        $this->stream->write("\n");
    }

    /**
     * Progress appearance
     * 
     * @param string $char
     * @param float $i
     * @param float $length
     * @return string
     */
    private function progAppear(string $char, float $i, float $length): string
    {
        $dot = $this->getAnsi()->red($char);
        if ($i > ($length * 0.7)) {
            $dot = $this->getAnsi()->green($char);
        } elseif ($i > ($length * 0.3)) {
            $dot = $this->getAnsi()->yellow($char);
        }
        return $this->getAnsi()->bold($dot);
    }

    /**
     * Show interactive menu
     * 
     * @param int $selIndex
     * @param array $items
     * @return void
     */
    protected function showMenu(int $selIndex, array $items): void
    {
        foreach ($items as $index => $item) {
            if ($index === $selIndex) {
                $this->stream->write("[" . $this->ansi->style(["blue"], $this->ansi->checkbox($item)) . "] " . $this->ansi->selectedItem($item) . "\n");
            } else {
                $this->stream->write("[ ] $item\n");
            }
        }
    }
}
