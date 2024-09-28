<?php

namespace MaplePHP\Prompts;

use Exception;
use InvalidArgumentException;

/**
 * Class Ansi
 * @package MaplePHP\Prompts
 */
class Ansi
{
    /**
     * @var array<string, string>
     */
    public const NAV = [
        '\033[A' => 'up',
        '\033[B' => 'down',
        '\n' => 'enter'
    ];

    private bool $disableAnsi = false;
    private ?bool $hasAnsi = null;

    /**
     * Set one or more styles
     * 
     * @param string|array|null $styles
     * @param string $message
     * @return string
     */
    public function style(string|array|null $styles, string $message): string
    {
        if(!is_null($styles)) {
            if (is_string($styles)) {
                $styles = [$styles];
            }
            foreach ($styles as $style) {
                if (!is_string($style) || !method_exists($this, $style)) {
                    $style = !is_string($style) ? '' : $style;
                    throw new InvalidArgumentException("The style $style does not exist!", 1);
                }
                $message = $this->{$style}($message);
            }
        }
        return (string)$message;
    }

    /**
     * Disable ANSI
     * @param bool $disableAnsi
     * @return self
     */
    function disableAnsi(bool $disableAnsi): self
    {
        $this->disableAnsi = $disableAnsi;
        return $this;
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
        if ($this->isSupported()) {
            return "\033[{$ansiNum}m$message\033[0m";
        }
        return $message;
    }

    /**
     * Create a line
     *
     * @param int $lineLength
     * @param int $color
     * @return string
     */
    public function line(int $lineLength, int $color = 90): string
    {
        $line = str_repeat('─', $lineLength);
        if ($this->isSupported()) {
            return "\033[1;{$color}m$line\033[0m";
        }
        return $line;
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
     * White input color
     *
     * @param string $message
     * @return string
     */
    public function white(string $message): string
    {
        return $this->ansiStyle(97, $message);
    }

    /**
     * Red input color
     * 
     * @param string $message
     * @return string
     */
    public function red(string $message): string
    {
        return $this->ansiStyle(91, $message);
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
        return $this->ansiStyle(94, $message);
    }

    /**
     * Blue input color
     *
     * @param string $message
     * @return string
     */
    public function grey(string $message): string
    {
        return $this->ansiStyle(90, $message);
    }

    /**
     * Magenta input color
     *
     * @param string $message
     * @return string
     */
    public function magenta(string $message): string
    {
        return $this->ansiStyle(35, $message);
    }

    /**
     * Cyan input color
     *
     * @param string $message
     * @return string
     */
    public function cyan(string $message): string
    {
        return $this->ansiStyle(36, $message);
    }

    /**
     * Red input background color
     *
     * @param string $message
     * @return string
     */
    public function redBg(string $message): string
    {
        if(!$this->isSupported()) {
            return "[$message]";
        }
        return $this->ansiStyle(41, $message);
    }

    /**
     * Yellow input background color
     *
     * @param string $message
     * @return string
     */
    public function yellowBg(string $message): string
    {
        if(!$this->isSupported()) {
            return "[$message]";
        }
        return $this->ansiStyle(43, $message);
    }

    /**
     * Blue input background color
     *
     * @param string $message
     * @return string
     */
    public function blueBg(string $message): string
    {
        if(!$this->isSupported()) {
            return "[$message]";
        }
        return $this->ansiStyle(44, $message);
    }

    /**
     * Green input background color
     *
     * @param string $message
     * @return string
     */
    public function greenBg(string $message): string
    {
        if(!$this->isSupported()) {
            return "[$message]";
        }
        return $this->ansiStyle(42, $message);
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
     * @throws Exception
     */
    public function clearDown(): string
    {
        return $this->clearLine() . $this->cursorDown();
    }

    /**
     * Clear line
     *
     * @return string
     * @throws Exception
     */
    public function clearLine(): string
    {
        if (!$this->isSupported()) {
            throw new Exception("Ansi not supported by OS", 1);
        }
        return "\033[2K";
    }

    /**
     * Move cursor to specified line
     *
     * @param int $line
     * @return string
     * @throws Exception
     */
    public function moveCursorTo(int $line): string
    {
        if (!$this->isSupported()) {
            throw new Exception("Ansi not supported by OS", 1);
        }
        return "\033[{$line}A";
    }

    /**
     * Move cursor down
     *
     * @return string
     * @throws Exception
     */
    public function cursorDown(): string
    {
        if (!$this->isSupported()) {
            throw new Exception("Ansi not supported by OS", 1);
        }
        return "\033[1B";
    }

    /**
     * Arrow up key
     *
     * @return string
     * @throws Exception
     */
    public function keyUp(): string 
    {
        if (!$this->isSupported()) {
            throw new Exception("Ansi not supported by OS", 1);
        }
        return "\033[A";
    }

    /**
     * Arrow down key
     *
     * @return string
     * @throws Exception
     */
    public function keyDown(): string 
    {
        if (!$this->isSupported()) {
            throw new Exception("Ansi not supported by OS", 1);
        }
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
     * @throws Exception
     */
    public function keyEscape(): string 
    {
        if (!$this->isSupported()) {
            throw new Exception("Ansi not supported by OS", 1);
        }
        return "\033";
    }

    /**
     * Symbol: checkbox
     *
     * @return string
     * @throws Exception
     */
    public function checkbox(): string 
    {
        if (!$this->isSupported()) {
            throw new Exception("Ansi not supported by OS", 1);
        }
        return "\xE2\x9C\x94";
    }

    /**
     * Check if terminal is modern (Not foolproof)
     * This function will tell if terminal support ANSI
     * 
     * @return bool
     */
    final public function isSupported(): bool
    {
        if($this->disableAnsi) {
            $this->hasAnsi = false;
        }
        if (is_null($this->hasAnsi)) {
            if (stripos(PHP_OS, 'WIN') === 0) {
                $this->hasAnsi = false;
            } else {
                $term = getenv('TERM') !== false ? getenv('TERM') : "";
                $this->hasAnsi = ($term !== "" && str_contains((string)$term, 'xterm'));
            }
        }
        return $this->hasAnsi;
    }
}
