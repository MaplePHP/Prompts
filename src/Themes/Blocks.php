<?php

namespace MaplePHP\Prompts\Themes;

use MaplePHP\Prompts\Command;

class Blocks
{
    private Command $command;
    private string $space;
    private array $options = [];
    private array $examples = [];
    private int $optionsLength = 0;
    private array $list = [];
    private int $listLength = 0;
    private array $table = [];
    private int $tableLength = 0;


    /**
     * Initialize a new Blocks instance for command-line output formatting
     *
     * @param Command $command The Command instance to handle output
     * @return void
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
        $this->space = str_repeat(" ", 2);
    }

    /**
     * Add a formatted headline with bold blue styling
     *
     * @param string $title The title text to display as a headline
     * @param string $color
     * @return void
     */
    public function addHeadline(string $title, string $color = "blue"): void
    {
        $this->command->message($this->command->getAnsi()->style(['bold', $color], "{$title}"));
    }

    /**
     * Add a formatted headline with bold blue styling
     *
     * @param string $title The title text to display as a headline
     * @param string|null $color
     * @return void
     */
    public function addText(string $title, ?string $color = null): void
    {
        if($color === null) {
            $this->command->message($title);
        } else {
            $this->command->message($this->command->getAnsi()->style([$color], "{$title}"));
        }
    }

    /**
     * Add padding around
     *
     * @param callable $content
     * @return void
     */
    public function addPadding(callable $content): void
    {
        $this->command->message("");
        $content($this);
        $this->command->message("");
    }

    /**
     * Add a new section with a title and description
     *
     * @param string|null $title The title of the section
     * @param string|callable $description The description text or a callback function that returns an instance of this class
     * @return void
     */
    public function addSection(?string $title, string|callable $description): void
    {

        if($title !== null) {
            $this->command->message("");
            $this->command->message($this->command->getAnsi()->bold("{$title}:"));
        }
        if (is_callable($description)) {
            $inst = $description($this);
            if ($inst instanceof self) {
                $inst->writeOptionLines();
                $inst->writeListLines();
                $inst->writeExampleLines();
            }
        } else {
            $this->command->message("{$this->space}{$description}");
        }
    }

    /**
     * Get severity in with a severity color
     *
     * @param string $severity
     * @param string|null $title
     * @return string
     */
    public function severityLevel(string $severity, ?string $title = null): string
    {
        switch (strtolower($severity)) {
            case 'low':
                return $this->command->getAnsi()->brightBlue($title ?? 'Low');
            case 'medium':
                return $this->command->getAnsi()->yellow($title ?? 'Medium');
        }

        return $this->command->getAnsi()->brightRed($title ?? 'High');
    }

    /**
     * Create a row for cell
     * @param callable $description
     * @return void
     */
    public function addRow(callable $description): void
    {
        $inst = clone $this;
        $inst = $description($inst);
        $inst->writeTableLines();
    }

    /**
     * Adds a code block
     *
     * @param string $code The description text or a callback function that returns an instance of this class
     * @return void
     */
    public function addCode(string $code): void
    {
        $this->command->message("");
        $this->command->message($this->addCodeStyle($code, $this->command->getAnsi()));
        $this->command->message("");
    }

    /**
     * Apply syntax highlighting styling to PHP code using ANSI color codes
     *
     * @param string $code The PHP code to style
     * @param Ansi $ansi The Ansi instance for color formatting
     * @return string The styled code with ANSI color codes
     */
    public function addCodeStyle(string $code, Ansi $ansi): string
    {

        // Keywords like "use", "function", "new"
        $code = preg_replace_callback('/\b(use|new)\b/', function ($m) use ($ansi) {
            return $ansi->blue($m[1]);
        }, $code);

        // Variables like $unit, $case, $valid
        $code = preg_replace_callback('/(\$[a-zA-Z_]\w*)/', function ($m) use ($ansi) {
            return $ansi->brightMagenta($m[1]);
        }, $code);

        // Strings like "Lorem ipsum"
        $code = preg_replace_callback('/(["\'])(.*?)(\1)/', function ($m) use ($ansi) {
            return $ansi->cyan($m[1] . $m[2] . $m[3]);
        }, $code);

        // Data types
        $code = preg_replace_callback('/\b(callable|Closure|null|string|bool|float|int)\b/', function ($m) use ($ansi) {
            return $ansi->brightCyan($m[1]);
        }, $code);

        $code = preg_replace_callback('/\b(Unit|TestCase|TestConfig|Expect)\b/', function ($m) use ($ansi) {
            return $ansi->brightCyan($m[1]);
        }, $code);

        // Functions
        $code = preg_replace_callback('/(\w+)\s*\(/', function ($m) use ($ansi) {
            return $ansi->brightBlue($m[1]) . '(';
        }, $code);


        return $code;
    }

    /**
     * Write formatted option lines with cyan styling and proper spacing
     * Used internally to output the stored options with aligned formatting
     *
     * @return void
     */
    private function writeOptionLines(): void
    {
        foreach ($this->options as $key => $value) {
            $space2 = str_repeat(" ", ($this->optionsLength - strlen($key) + 5));
            $this->command->message(
                $this->command->getAnsi()->cyan("{$this->space}{$key}{$space2}{$value}")
            );
        }
    }

    /**
     * Write formatted option lines with cyan styling and proper spacing
     * Used internally to output the stored options with aligned formatting
     *
     * @return void
     */
    private function writeListLines(): void
    {
        foreach ($this->list as $key => $value) {
            $space2 = str_repeat(" ", ($this->listLength - strlen($key) + 5));
            $this->command->message(
                $this->command->getAnsi()->yellow("{$key}{$space2}{$value}")
            );
        }
    }

    /**
     * Write formatted option lines with cyan styling and proper spacing
     * Used internally to output the stored options with aligned formatting
     *
     * @return void
     */
    private function writeTableLines(): void
    {
        $lineLength = 0;
        $row = $this->buildRowsAndCells($this->table, $this->tableLength, $lineLength);
        foreach ($row as $value) {
            $this->command->message($value);

        }
        $this->command->message(" ");
    }

    private function buildRowsAndCells(array $arr, int $length, int &$lineLength): array
    {
        $row = [];
        $lineLength = 0;
        foreach ($arr as $value) {
            $col1 = $value[0];
            $col2 = $value[1];
            $space2 = str_repeat(" ", ($length - strlen($col1) + 5));

            $title = "{$col1}{$space2}";
            $title = $this->command->getAnsi()->style(["greyBg", "bold"], $title);

            $out = "{$title} {$col2}";
            $row[] = $out;
            if(strlen($out) > $lineLength) {
                $lineLength = strlen($out);
            }
        }
        return $row;
    }


    /**
     * Write formatted example lines with yellow styling for keys and grey italic for values
     * Used internally to output the stored examples with proper formatting and indentation
     *
     * @return void
     */
    private function writeExampleLines(): void
    {
        foreach ($this->examples as $key => $value) {
            $this->command->message(
                $this->command->getAnsi()->style(['yellow'], "{$this->space}{$key}")
            );
            if ($value) {
                $this->command->message(
                    $this->command->getAnsi()->style(['grey', 'italic'], "{$this->space}{$this->space}{$value}")
                );
            }
        }
    }

    /**
     * Add an option with its description to the command help output
     * Returns a new instance of this class with the added option
     *
     * @param string $option The option name/flag to add
     * @param string $description The description text for this option
     * @return self New instance with the option added
     */
    public function addOption(string $option, string $description): self
    {
        $inst = clone $this;
        $length = strlen($option);
        $inst->options[$option] = $description;
        if ($length > $inst->optionsLength) {
            $inst->optionsLength = $length;
        }
        return $inst;
    }

    /**
     * Add an option with its description to the command help output
     * Returns a new instance of this class with the added option
     *
     * @param string $option The option name/flag to add
     * @param string $description The description text for this option
     * @return self New instance with the option added
     */
    public function addList(string $option, string $description): self
    {
        $inst = clone $this;
        $length = strlen($option);
        $inst->list[$option] = $description;
        if ($length > $inst->listLength) {
            $inst->listLength = $length;
        }
        return $inst;
    }

    /**
     * Add an option with its description to the command help output
     * Returns a new instance of this class with the added option
     *
     * @param string $option The option name/flag to add
     * @param string $description The description text for this option
     * @return self New instance with the option added
     */
    public function addCell(string $option, string $description): self
    {
        $length = strlen($option);
        $this->table[] = [$option, $description];
        if ($length > $this->tableLength) {
            $this->tableLength = $length;
        }
        return $this;
    }

    /**
     * Add an example with optional description to the command help output
     * Returns a new instance of this class with the added example
     *
     * @param string $example The example text/command to add
     * @param string|null $description Optional description text for this example
     * @return self New instance with the example added
     */
    public function addExamples(string $example, ?string $description = null): self
    {
        $inst = clone $this;
        $inst->examples[$example] = $description;
        return $inst;
    }
}
