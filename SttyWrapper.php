<?php

namespace MaplePHP\Prompts;
use InvalidArgumentException;

class SttyWrapper
{
    
    protected $command = array();

    public function __construct()
    {
    }
    
    /**
     * Get command as string
     * @return string
     */
    public function __toString(): string
    {
        return $this->get();
    }

    /**
     * Get command as string
     * @return string
     */
    public function get(): string
    {
        return implode(";", $this->command);
    }

    /**
     * Msking input
     * @return [type] [description]
     */
    public function maskInput(): self
    {
        return $this->toggleEcho(false)->readInput()->toggleEcho(true)->raw('echo $input');
    }

    /**
     * Turn on/off output stream
     * @param  bool   $bool
     * @return static
     */
    public function toggleEcho(bool $bool): self
    {        
        return $this->toggleEnable($bool, "echo");
    }

    /**
     * Toggle character break
     * @param  bool   $bool
     * @return static
     */
    public function toggleCharBreakMode(bool $bool): self
    {        
        return $this->toggleEnable($bool, "cbreak");
    }

    /**
     * Will listen to the input
     * @return static
     */
    public function readInput(): self
    {
        return $this->raw('read input');
    }


    /**
     * Toggle a custom command on/off
     * @param  bool   $bool
     * @param  string $command
     * @return static
     */
    public function toggleEnable(bool $bool, string $command): self
    {
        return $this->raw(('stty '.(!$bool ? '-' : '').$command));
    }

    /**
     * Will listen to the input
     * @return static
     */
    public function raw(string $input): self
    {
        $inst = clone $this;
        $inst->command[] = $input;
        return $inst;
    }

    /**
     * Check if is a Unix supported OS
     * @return bool
     */
    function isUnix(): bool
    {
        $os = php_uname('s');
        $supportedOSes = ['Linux', 'Unix', 'Darwin'];
        foreach ($supportedOSes as $supportedOS) {
            if (stripos($os, $supportedOS) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if stty is supported
     * @return bool
     */
    function hasSttySupport(): bool
    {
        // Make sure it really is installed
        // Can be absent on specialized UNIX environments (e.g. minimalistic or embedded system)
        if (function_exists("exec") && $this->isUnix()) {
            exec('stty -a 2>&1', $output, $returnStatus);
            return ($returnStatus === 0);
        }
        return false;
    }
}
