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
     * @return void
     */
    public function addHeadline(string $title): void
    {
        $this->command->message($this->command->getAnsi()->style(['bold', 'blue'],"{$title}"));
    }
    
    /**
     * Add a new section with a title and description
     *
     * @param string $title The title of the section
     * @param string|callable $description The description text or a callback function that returns an instance of this class
     * @return void
     */
    public function addSection(string $title, string|callable $description): void
    {
        $this->command->message("");
        $this->command->message($this->command->getAnsi()->bold("{$title}:"));
        if(is_callable($description)) {
            $inst = $description($this);
            if($inst instanceof self) {
                $inst->writeOptionLines();
                $inst->writeExampleLines();
            }
        } else {
            $this->command->message("{$this->space}{$description}");
        }
    }

    
    /**
     * Write formatted option lines with cyan styling and proper spacing
     * Used internally to output the stored options with aligned formatting
     *
     * @return void
     */
    private function writeOptionLines(): void
    {
        foreach($this->options as $key => $value) {
            $space2 = str_repeat(" ", ($this->optionsLength - strlen($key) + 5));
            $this->command->message(
                $this->command->getAnsi()->cyan("{$this->space}--{$key}{$space2}{$value}")
            );
        }
    }

    
    /**
     * Write formatted example lines with yellow styling for keys and grey italic for values
     * Used internally to output the stored examples with proper formatting and indentation
     *
     * @return void
     */
    private function writeExampleLines(): void
    {
        foreach($this->examples as $key => $value) {
            $this->command->message(
                $this->command->getAnsi()->style(['yellow'], "{$this->space}{$key}")
            );
            if($value) {
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
        if($length > $inst->optionsLength) {
            $inst->optionsLength = $length;
        }
        return $inst;
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