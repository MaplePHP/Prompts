<?php

namespace MaplePHP\Prompts\Themes;

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
        if ($styles !== null) {
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
    public function disableAnsi(bool $disableAnsi): self
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
     * Create a dashed line
     *
     * @param int $lineLength
     * @param int $color
     * @return string
     */
    public function dashedLine(int $lineLength, int $color = 90): string
    {
        $thin = $this->supportsUnicode() ? "\u{200A}" : "";
        $line = str_repeat("─$thin", round($lineLength/2));
        if ($this->isSupported()) {
            return "\033[1;{$color}m$line\033[0m";
        }
        return $line;
    }

    /**
     * Get middot ANSI character
     * @return string
     */
    public function middot(): string
    {
        if ($this->isSupported()) {
            return "\u{2022}";
        }
        return ".";
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
     * Black input text color
     *
     * @param string $message
     * @return string
     */
    public function black(string $message): string
    {
        return $this->ansiStyle(30, $message);
    }

    /**
     * Red input text color
     *
     * @param string $message
     * @return string
     */
    public function red(string $message): string
    {
        return $this->ansiStyle(31, $message);
    }

    /**
     * Green input text color
     *
     * @param string $message
     * @return string
     */
    public function green(string $message): string
    {
        return $this->ansiStyle(32, $message);
    }

    /**
     * Yellow input text color
     *
     * @param string $message
     * @return string
     */
    public function yellow(string $message): string
    {
        return $this->ansiStyle(33, $message);
    }

    /**
     * Blue input text color
     *
     * @param string $message
     * @return string
     */
    public function blue(string $message): string
    {
        return $this->ansiStyle(34, $message);
    }

    /**
     * Magenta input text color
     *
     * @param string $message
     * @return string
     */
    public function magenta(string $message): string
    {
        return $this->ansiStyle(35, $message);
    }

    /**
     * Cyan input text color
     *
     * @param string $message
     * @return string
     */
    public function cyan(string $message): string
    {
        return $this->ansiStyle(36, $message);
    }

    /**
     * White input text color
     *
     * @param string $message
     * @return string
     */
    public function white(string $message): string
    {
        return $this->ansiStyle(37, $message);
    }

    /**
     * Bright black (gray) input text color
     *
     * @param string $message
     * @return string
     */
    public function brightBlack(string $message): string
    {
        return $this->ansiStyle(90, $message);
    }

    /**
     * Grey input text color
     *
     * @param string $message
     * @return string
     */
    public function grey(string $message): string
    {
        return $this->brightBlack($message);
    }

    /**
     * Bright red input text color
     *
     * @param string $message
     * @return string
     */
    public function brightRed(string $message): string
    {
        return $this->ansiStyle(91, $message);
    }

    /**
     * Bright green input text color
     *
     * @param string $message
     * @return string
     */
    public function brightGreen(string $message): string
    {
        return $this->ansiStyle(92, $message);
    }

    /**
     * Bright yellow input text color
     *
     * @param string $message
     * @return string
     */
    public function brightYellow(string $message): string
    {
        return $this->ansiStyle(93, $message);
    }

    /**
     * Bright blue input text color
     *
     * @param string $message
     * @return string
     */
    public function brightBlue(string $message): string
    {
        return $this->ansiStyle(94, $message);
    }

    /**
     * Bright magenta input text color
     *
     * @param string $message
     * @return string
     */
    public function brightMagenta(string $message): string
    {
        return $this->ansiStyle(95, $message);
    }

    /**
     * Bright cyan input text color
     *
     * @param string $message
     * @return string
     */
    public function brightCyan(string $message): string
    {
        return $this->ansiStyle(96, $message);
    }

    /**
     * Bright white input text color
     *
     * @param string $message
     * @return string
     */
    public function brightWhite(string $message): string
    {
        return $this->ansiStyle(97, $message);
    }

    /**
     * Set custom background
     * @param int $int
     * @param string $message
     * @return string
     */
    public function bg(int $int, string $message): string
    {
        if (!$this->isSupported()) {
            return "[$message]";
        }
        return $this->ansiStyle($int, $message);
    }

    /**
     * Black input background color
     *
     * @param string $message
     * @return string
     */
    public function blackBg(string $message): string
    {
        return $this->bg(40, $message);
    }

    /**
     * Red input background color
     *
     * @param string $message
     * @return string
     */
    public function redBg(string $message): string
    {
        return $this->bg(41, $message);
    }

    /**
     * Green input background color
     *
     * @param string $message
     * @return string
     */
    public function greenBg(string $message): string
    {
        return $this->bg(42, $message);
    }

    /**
     * Yellow input background color
     *
     * @param string $message
     * @return string
     */
    public function yellowBg(string $message): string
    {
        return $this->bg(43, $message);
    }

    /**
     * Blue input background color
     *
     * @param string $message
     * @return string
     */
    public function blueBg(string $message): string
    {
        return $this->bg(44, $message);
    }

    /**
     * Magenta input background color
     *
     * @param string $message
     * @return string
     */
    public function magentaBg(string $message): string
    {
        return $this->bg(45, $message);
    }

    /**
     * Cyan input background color
     *
     * @param string $message
     * @return string
     */
    public function cyanBg(string $message): string
    {
        return $this->bg(46, $message);
    }

    /**
     * White input background color
     *
     * @param string $message
     * @return string
     */
    public function whiteBg(string $message): string
    {
        return $this->bg(47, $message);
    }

    /**
     * Bright black (gray) input background color
     *
     * @param string $message
     * @return string
     */
    public function brightBlackBg(string $message): string
    {
        return $this->bg(100, $message);
    }

    /**
     * Grey input background color
     *
     * @param string $message
     * @return string
     */
    public function greyBg(string $message): string
    {
        return $this->brightBlackBg($message);
    }

    /**
     * Bright red input background color
     *
     * @param string $message
     * @return string
     */
    public function brightRedBg(string $message): string
    {
        return $this->bg(101, $message);
    }

    /**
     * Bright green input background color
     *
     * @param string $message
     * @return string
     */
    public function brightGreenBg(string $message): string
    {
        return $this->bg(102, $message);
    }

    /**
     * Bright yellow input background color
     *
     * @param string $message
     * @return string
     */
    public function brightYellowBg(string $message): string
    {
        return $this->bg(103, $message);
    }

    /**
     * Bright blue input background color
     *
     * @param string $message
     * @return string
     */
    public function brightBlueBg(string $message): string
    {
        return $this->bg(104, $message);
    }

    /**
     * Bright magenta input background color
     *
     * @param string $message
     * @return string
     */
    public function brightMagentaBg(string $message): string
    {
        return $this->bg(105, $message);
    }

    /**
     * Bright cyan input background color
     *
     * @param string $message
     * @return string
     */
    public function brightCyanBg(string $message): string
    {
        return $this->bg(106, $message);
    }

    /**
     * Bright white input background color
     *
     * @param string $message
     * @return string
     */
    public function brightWhiteBg(string $message): string
    {
        return $this->bg(107, $message);
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
     * Detects if the terminal can reliably handle Unicode output.
     *
     * @return bool
     */
    final public  function supportsUnicode(): bool
    {
        return function_exists('mb_internal_encoding') && mb_internal_encoding() === 'UTF-8';
    }

    /**
     * Check if the terminal is modern (Not foolproof),
     * This function will tell if the terminal supports ANSI
     *
     * @return bool
     */
    final public function isSupported(): bool
    {
        if ($this->disableAnsi) {
            $this->hasAnsi = false;
        }
        if ($this->hasAnsi === null) {
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
