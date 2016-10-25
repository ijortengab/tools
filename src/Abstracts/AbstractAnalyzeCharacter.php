<?php

namespace IjorTengab\Tools\Abstracts;

/**
 *
 */
abstract class AbstractAnalyzeCharacter
{
    public $debug = false;
    protected $raw;
    protected $current_column = 1;
    protected $current_line = 1;
    protected $current_line_string = null;
    protected $current_character = 0;
    protected $current_character_string = null;
    protected $next_character_string = null;
    protected $prev_character_string = null;
    protected $is_last = false;
    protected $is_break = false;

    /**
     *
     */
    abstract protected function analyzeCurrentLine();

    /**
     *
     */
    abstract protected function analyzeCurrentCharacter();

    /**
     * Construct.
     */
    public function __construct($raw = null)
    {
        if (is_string($raw)) {
            $this->raw = $raw;
        }
    }

    /**
     * Mulai melakukan analisis dengan cara
     * walk through each character.
     */
    public function looping()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        if (!is_string($this->raw)) {
            return;
        }
        $is_continue = $this->beforeLooping();
        if (false === $is_continue) {
            return;
        }
        do {
            // $this->debug('------------------------', '', 1);
            // $this->debug($this->current_line, '$this->current_line', 1);
            // $this->debug($this->current_column, '$this->current_column', 1);
            // $this->debug($this->current_character, '$this->current_character', 1);
            if ($this->current_column === 1) {
                $this->populateCurrentLine();
                $this->manipulateCurrentLine();
                $this->analyzeCurrentLine();
            }
            $this->populateCurrentCharacter();
            $this->manipulateCurrentCharacter();
            $this->assignCurrentCharacter();
            $this->beforeAnalyze();
            $this->analyzeCurrentCharacter();
            $this->afterAnalyze();
            $this->prepareNextLoop();
            $this->resetAssignCharacter();
        } while($this->next_character_string !== false);
        $this->afterLooping();
    }

    /**
     *
     */
    protected function beforeLooping()
    {
    }

    /**
     *
     */
    protected function afterLooping()
    {
    }

    /**
     *
     */
    protected function beforeAnalyze()
    {
    }

    /**
     *
     */
    protected function afterAnalyze()
    {
    }

    /**
     *
     */
    protected function populateCurrentLine()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        $this->current_line_string = '';
        $leftover = substr($this->raw, $this->current_character);
        if (preg_match('/.*/', $leftover, $match)) {
            // $this->debug($match, '$match', 2);
            // $this->current_line_string berisi string tanpa break.
            $this->current_line_string = rtrim($match[0], "\r\n");
        }
        // $this->debug($this->current_line_string, '$this->current_line_string', 1);
    }

    /**
     *
     */
    protected function manipulateCurrentLine()
    {
        // return $this;
    }


    /**
     * Todo.
     */
    protected function populateCurrentCharacter()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        $x = $this->current_character;
        $ch = isset($this->raw[$x]) ? $this->raw[$x] : false;
        $nch = isset($this->raw[$x+1]) ? $this->raw[$x+1] : false;
        $pch = isset($this->raw[$x-1]) ? $this->raw[$x-1] : false;
        $this->current_character_string = $ch;
        $this->next_character_string = $nch;
        $this->prev_character_string = $pch;
        // Debug.
        // $this->debug($this->current_character_string, '$this->current_character_string', 1);
        // $this->debug($this->next_character_string, '$this->next_character_string', 1);
        // $this->debug($this->prev_character_string, '$this->prev_character_string', 1);
    }

    /**
     * Todo.
     */
    protected function manipulateCurrentCharacter()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        $x = $this->current_character;
        $ch = $this->current_character_string;
        $nch = $this->next_character_string;
        if ($ch == "\r" && $nch == "\n") {
            $ch = "\r\n";
            $nch = isset($this->raw[$x+2]) ? $this->raw[$x+2] : false;
            $this->current_character_string = $ch;
            $this->next_character_string = $nch;
            $this->current_character++;
        }
        // Debug.
        // $this->debug($this->current_character_string, '$this->current_character_string', 1);
        // $this->debug($this->next_character_string, '$this->next_character_string', 1);
        // $this->debug($this->prev_character_string, '$this->prev_character_string', 1);
    }

    /**
     * Todo.
     */
    protected function assignCurrentCharacter()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        if ($this->next_character_string === false) {
            $this->is_last = true;
        }
        if (in_array($this->current_character_string, ["\r", "\n", "\r\n"])) {
            $this->is_break = true;
        }
    }

    /**
     *
     */
    protected function prepareNextLoop()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        if ($this->is_break && !$this->is_last) {
            $this->current_line++;
        }

        $this->current_character++;
        //
        if ($this->is_break) {
            $this->current_column = 1;
        }
        else {
            $this->current_column++;
        }
    }

    /**
     *
     */
    protected function resetAssignCharacter()
    {
        $this->is_break = false;
    }

    /**
     * Debug.
     */
    protected function debug($variable, $name, $indent = 0)
    {
        if (!$this->debug) {
            return;
        }
        ob_start();
        var_dump($variable);
        $debugoutput = ob_get_contents();
        ob_end_clean();
        /* Indent. */
        $indentstring = '';
        while ($indent) {
            $indentstring .= '  ';
            $indent--;
        }
        if ($indentstring !== '') {
            $debugoutput = str_replace("\n","\n" . $indentstring, $debugoutput);
            $debugoutput = rtrim($debugoutput, ' ');
        }
        /* Indent. */
        echo $indentstring . 'var_dump(' . $name . '): ';
        echo $debugoutput;
    }
}
