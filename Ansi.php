<?php

namespace MaplePHP\Prompts;

use InvalidArgumentException;

/**
 * Class Ansi
 * @package MaplePHP\Prompts
 */
class Ansi
{
    public const NAV = [
        '\033[A' => 'up',
        '\033[B' => 'down',
        '\n' => 'enter'
    ];

    private static ?bool $hasAnsi = null;

    /**
     * Set one or more styles
     * 
     * @param string|array $styles
     * @param string $message
     * @return string
     */
    public function style(string|array $styles, string $message): string
    {
        if (is_string($styles)) {
            $styles = [$styles];
        }
        foreach ($styles as $style) {
            if (!method_exists($this, $style)) {
                throw new InvalidArgumentException("The style $style does not exist!", 1);
            }
            $message = $this->{$style}($message);
        }
        return $message;
    }

    /**
     * Set a custom ansi style
     * 
     * @param int $ansiNum
     * @param string $message
     * @return string
     */
    public function ansiStyle(int $ansiNum, string $message): string
    {
        if (self::isSupported()) {
            return "\033[{$ansiNum}m{$message}\033[0m";
        }
        return $message;
    }

    /**
     * Bold input
     * 
     * @param string $message
     * @return string
     */
    public function bold(string $message): string
    {
        return $this->ansiStyle(1, $message);
    }

    /**
     * Italic input
     * 
     * @param string $message
     * @return string
     */
    public function italic(string $message): string
    {
        return $this->ansiStyle(3, $message);
    }

    /**
     * Red input color
     * 
     * @param string $message
     * @return string
     */
    public function red(string $message): string
    {
        return $this->ansiStyle(31, $message);
    }

    /**
     * Green input color
     * 
     * @param string $message
     * @return string
     */
    public function green(string $message): string
    {
        return $this->ansiStyle(32, $message);
    }

    /**
     * Yellow input color
     * 
     * @param string $message
     * @return string
     */
    public function yellow(string $message): string
    {
        return $this->ansiStyle(33, $message);
    }

    /**
     * Blue input color
     * 
     * @param string $message
     * @return string
     */
    public function blue(string $message): string
    {
        return $this->ansiStyle(34, $message);
    }

    /**
     * Style selected item
     * 
     * @param string $message
     * @return string
     */
    public function selectedItem(string $message): string
    {
        return $this->style(['blue', 'bold'], $message);
    }
    
    /**
     * Clear line and move down
     * 
     * @return string
     */
    public function clearDown(): string
    {
        return $this->clearLine() . $this->cursorDown();
    }

    /**
     * Clear line
     * 
     * @return string
     */
    public function clearLine(): string
    {
        return "\033[2K";
    }

    /**
     * Move cursor to specified line
     * 
     * @param int $line
     * @return string
     */
    public function moveCursorTo(int $line): string
    {
        return "\033[{$line}A";
    }

    /**
     * Move cursor down
     * 
     * @return string
     */
    public function cursorDown(): string
    {
        return "\033[1B";
    }

    /**
     * Arrow up key
     * 
     * @return string
     */
    public function keyUp(): string 
    {
        return "\033[A";
    }

    /**
     * Arrow down key
     * 
     * @return string
     */
    public function keyDown(): string 
    {
        return "\033[B";
    }

    /**
     * Enter key
     * 
     * @return string
     */
    public function keyEnter(): string 
    {
        return "\n";
    }

    /**
     * Escape key
     * 
     * @return string
     */
    public function keyEscape(): string 
    {
        return "\033";
    }

    /**
     * Symbol: checkbox
     * 
     * @return string
     */
    public function checkbox(): string 
    {
        return "\xE2\x9C\x94";
    }

    /**
     * Check if terminal is modern (Not foolproof)
     * This function will tell if terminal support ANSI
     * 
     * @return bool
     */
    final public static function isSupported(): bool
    {
        if (is_null(self::$hasAnsi)) {
            if (stripos(PHP_OS, 'WIN') === 0) {
                $osVersion = php_uname('v');
                if (preg_match('/build (\d+)/i', $osVersion, $matches)) {
                    $buildNumber = (int)$matches[1];
                    self::$hasAnsi = ($buildNumber >= 10586);
                }
            } else {
                self::$hasAnsi = (getenv('TERM') && str_contains(getenv('TERM'), 'xterm'));
            }
        }
        return self::$hasAnsi;
    }
}
