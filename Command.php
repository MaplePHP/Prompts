<?php

namespace MaplePHP\Prompts;

use MaplePHP\Http\Stream;
use MaplePHP\Prompts\SttyWrapper;
use MaplePHP\Prompts\Ansi;
use InvalidArgumentException;

class Command
{
    private $stream;
    private $stty;
    private $ansi;

    const NAV = [
        '\033[A' => 'up',
        '\033[B' => 'down',
        '\n' => 'enter'
    ];

    public function __construct()
    {
        $this->stream = new Stream(Stream::STDIN, "r");
        $this->stty = new SttyWrapper();
        $this->ansi = new Ansi();
    }

    public function getAnsi(): Ansi
    {
        return $this->ansi;
    }

    /**
     * Read line
     * @param  string $message
     * @return string
     */
    public function readline($message): string
    {
        //$this->stream->write($message);
        if (function_exists("readline")) {
            return readline("{$message}: ");
        }
        $this->stream->write(":");
        return $this->stream->getLine();
    }

    /**
     * Message
     * @param  string       $message
     * @param  bool|boolean $getLine Stop and get line
     * @return string|false
     */
    public function message(string $message, bool $getLine = false): string|false
    {
        $line = false;
        if ($getLine) {
            $line = $this->readline($message);
        } else {
            $this->stream->write($message);
            $this->stream->write("\n");
        }
        return $line;
    }
    
    /**
     * Prompt for a comma separated list
     * @param  string $message
     * @return array
     */
    public function list($message): array
    {
        $line = $this->message("{$message} (comma separate)", true);
        $items = array_map("trim", explode(",", $line));
        return $items;
    }

    /**
     * Will give you multiple option to choose between
     * @param  array  $choises
     * @return string
     */
    public function select(string $message, array $choises): string
    {
        if (count($choises) === 0) {
            throw new InvalidArgumentException("You cannot input an empty array!", 1);
        }

        $int = 1;
        $length = count($choises);
        $this->message(sprintf($this->getAnsi()->bold($message), $int, $length));
        foreach ($choises as $value) {
            $this->message($this->getAnsi()->style(["blue", "bold"], "{$int}:")." {$value}");
            $int++;
        }
        $line = $this->message("Input your answer", true);

        if ($line > 0 && $line <= $length) {
            return $line;
        }
        return $this->select($message, $choises);
    }

    /**
     * Choose between ues or no answer
     * @param  string   $message
     * @return string
     */
    public function toggle(string $message): string
    {

        $this->message($this->getAnsi()->bold($message));
        $line = $this->message("Type 'yes' or 'no'", true);
        $line = strtolower($line);

        if ($line === "yes" || $line === "no") {
            return (int)($line === "yes");
        }
        return $this->toggle($message);
    }

    /**
     * Will return an masked input if supported,
     * if not it will still return an input but it will not be masked!
     * @param  string $message
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
        $this->message("Warning: The input will not be mask. The PHP function \"system\" is disabled.");
        return $this->message($message, true);
    }

    /**
     * Confirmation
     * @param  string   $message
     * @param  callable $call
     * @return bool
     */
    public function confirm(string $message): bool
    {
        $this->message($this->getAnsi()->style(["yellow", "bold"], $message));
        $line = $this->message("Type 'yes' to continue and 'no' to abort'", true);
        $line = trim(strtolower($line));

        if ($line === "yes" || $line === "no") {
            if ($line === "yes") {
                return true;
            }
            return false;
        }
        $this->confirm($message);
        return false;
    }
    
    /**
     * Create a progress bar
     * @param  int           $expectedRows The maximum expected row you are expected
     * @param  int|integer   $maxLength    The maximum bra length
     * @param  callable|null $sleep        int for sleep in milliseconds (3000 ms = 1 second) and 
     *                                     callaback with returned int for sleep in milliseconds
     * @return void
     */
    public function progress(int $expectedRows, int $maxLength = 100, null|int|callable $sleep = null): void
    {
        $i = 0;
        $char = "-";
        $ratio = ($maxLength > $expectedRows) ? $expectedRows/$maxLength : $maxLength/$expectedRows;
        $length = ($maxLength-0.5) * $ratio;
        $inc = round($ratio, 4);
        while ($i < $length) {
            if (is_callable($sleep)) {
                $sleep = $sleep($i, $length);
            }
            if (is_int($sleep)) {
                usleep($sleep*1000);
            }
            $this->stream->write($this->progAppear($char, $i, $length));
            $i+=$inc;
        }
        $this->stream->write("\n");
    }

    /**
     * Progress appearance
     * @param  string   $char
     * @param  int      $length
     * @return string
     */
    private function progAppear($char, $i, $length): string
    {
        $dot = $this->getAnsi()->red($char);
        if ($i > ($length*0.7)) {
            $dot = $this->getAnsi()->green($char);

        } else if ($i > ($length*0.3)) {
            $dot = $this->getAnsi()->yellow($char);
        }
        return $this->getAnsi()->bold($dot);
    }


    

    // MENU
    function showMenu($options, $selectedIndex, $initial = true) {
        $this->stream->write("Use arrow keys to navigate and press Enter to select.\n\n");
        foreach ($options as $index => $option) {
            if ($index === $selectedIndex) {
                $this->stream->write("\033[1;33m[\xE2\x9C\x94] $option (selected)\033[0m\n");
            } else {
                $this->stream->write("[ ] $option\n");
            }
        }
    }

    function clearCanvas(int $lines): void
    {
        // Move the cursor up to the start
        $this->stream->write($this->ansi->moveCursorTo($lines));
        for ($i = 0; $i < $lines; $i++) {
            $this->stream->write($this->ansi->clearDown());
        }
        // Move the cursor "back" up to the start
        $this->stream->write($this->ansi->moveCursorTo($lines));
    }

    function tetst($options, $selectedIndex) {
        $this->showMenu($options, $selectedIndex);
        while (true) {
            $input = $this->stream->read(3);
            $key = (self::NAV[$input] ?? $input);

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

            $lines = count($options) + 2;
            $this->clearCanvas($lines);
            $this->showMenu($options, $selectedIndex, false);
        }

    }

}
