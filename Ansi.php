<?php

namespace MaplePHP\Prompts;

use InvalidArgumentException;

class Ansi
{
    private static $hasAnsi;

    /**
     * Set a custom ansi style
     * @param  int     $ansiNum
     * @param  string  $message
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
     * Set one or more styles
     * @param  string|array  $styles
     * @param  string        $message
     * @return string
     */
    public function style(string|array $styles, string $message): string
    {
        if (is_string($styles)) {
            $styles = array($styles);
        }
        foreach ($styles as $style) {
            if (!method_exists($this, $style)) {
                throw new InvalidArgumentException("The style {$style} does not exit!", 1);
            }
            $message = $this->{$style}($message);
        }
        return $message;
    }

    /**
     * Bold input
     * @param  string $message
     * @return string
     */
    public function bold(string $message): string
    {
        return $this->ansiStyle(1, $message);
    }

    /**
     * Italic input
     * @param  string $message
     * @return string
     */
    public function italic(string $message): string
    {
        return $this->ansiStyle(3, $message);
    }

    /**
     * Read input
     * @param  string $message
     * @return string
     */
    public function red(string $message): string
    {
        return $this->ansiStyle(31, $message);
    }

    /**
     * Green input color
     * @param  string $message
     * @return string
     */
    public function green(string $message): string
    {
        return $this->ansiStyle(32, $message);
    }

    /**
     * Yellow input color
     * @param  string $message
     * @return string
     */
    public function yellow(string $message): string
    {
        return $this->ansiStyle(33, $message);
    }

    /**
     * Blue input color
     * @param  string $message
     * @return string
     */
    public function blue(string $message): string
    {
        return $this->ansiStyle(34, $message);
    }

    /**
     * Check if terminal is modern (Not foolproof)
     * This function will tell if terminal support ANSI
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
                self::$hasAnsi = (getenv('TERM') && strpos(getenv('TERM'), 'xterm') !== false);
            }
        }
        return self::$hasAnsi;
    }
}
