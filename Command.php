<?php

namespace MaplePHP\Prompts;

use MaplePHP\Http\Stream;
use MaplePHP\Prompts\Ansi;
use InvalidArgumentException;

class Command
{
    private $stream;
    private $ansi;

    public function __construct()
    {
        $this->stream = new Stream(Stream::STDIN, "r");
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
        if (function_exists("shell_exec")) {
            $this->stream->write("{$message} (masked input): ");
            // Mask input
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Not yet tested. But should work if my research is right
                $input = rtrim((string)shell_exec("powershell -Command \$input = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR(\$input))"));
            } else {
                // Tested and works
                $input = rtrim((string)shell_exec('stty -echo; read input; stty echo; echo $input'));
            }

            $this->stream->write("\n");
            return $input;
        }

        $this->message("Warning: The input will not be mask. The PHP function \"exec\" is disabled.");
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
     * Check if terminal is modern (Not foolproof)
     * This function will tell if terminal support ANSI
     * @return bool
     */
    public function modernTerminal(): bool
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            $osVersion = php_uname('v');
            if (preg_match('/build (\d+)/i', $osVersion, $matches)) {
                $buildNumber = (int)$matches[1];
                return ($buildNumber >= 10586);
            }
        } else {
            return (getenv('TERM') && strpos(getenv('TERM'), 'xterm') !== false);
        }
    }
}
