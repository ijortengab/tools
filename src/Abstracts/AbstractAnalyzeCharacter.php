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
    protected $current_char = 0;
    protected $current_char_string = null;
    protected $next_char_string = null;
    protected $prev_char_string = null;
    protected $is_last = false;
    protected $is_break = false;

    /**
     *
     */
    abstract protected function analyzeCurrentLine();

    /**
     *
     */
    abstract protected function analyzeCurrentChar();

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
    public function analyze()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        if (!is_string($this->raw)) {
            return;
        }
        $this->beforeAnalyze();
        do {
            // $this->debug('------------------------', '', 1);
            // $this->debug($this->current_line, '$this->current_line', 1);
            // $this->debug($this->current_column, '$this->current_column', 1);
            // $this->debug($this->current_char, '$this->current_char', 1);
            if ($this->current_column === 1) {
                $this->populateCurrentLine();
                $this->analyzeCurrentLine();
            }
            $this->populateCurrentChar();
            $this->manipulateCurrentChar();
            $this->assignCurrentChar();
            $this->analyzeCurrentChar();
            $this->prepareNextLoop();
            $this->clearAssignChar();
        } while($this->next_char_string !== false);
        $this->afterAnalyze();
    }

    /**
     *
     */
    protected function beforeAnalyze()
    {
        // $this->debug(__METHOD__, '__METHOD__');
    }

    /**
     *
     */
    protected function afterAnalyze()
    {
        // $this->debug(__METHOD__, '__METHOD__');
    }

    /**
     *
     */
    protected function populateCurrentLine()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        $this->current_line_string = '';
        $leftover = substr($this->raw, $this->current_char);
        if (preg_match('/.*/', $leftover, $match)) {
            // $this->debug($match, '$match', 2);
            $this->current_line_string = $match[0];
        }
        // $this->debug($this->current_line_string, '$this->current_line_string', 1);
    }

    /**
     * Todo.
     */
    protected function populateCurrentChar()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        $x = $this->current_char;
        $ch = isset($this->raw[$x]) ? $this->raw[$x] : false;
        $nch = isset($this->raw[$x+1]) ? $this->raw[$x+1] : false;
        $pch = isset($this->raw[$x-1]) ? $this->raw[$x-1] : false;
        $this->current_char_string = $ch;
        $this->next_char_string = $nch;
        $this->prev_char_string = $pch;
        // Debug.
        // $this->debug($this->current_char_string, '$this->current_char_string', 1);
        // $this->debug($this->next_char_string, '$this->next_char_string', 1);
        // $this->debug($this->prev_char_string, '$this->prev_char_string', 1);
    }

    /**
     * Todo.
     */
    protected function manipulateCurrentChar()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        $x = $this->current_char;
        $ch = $this->current_char_string;
        $nch = $this->next_char_string;
        if ($ch == "\r" && $nch == "\n") {
            $ch = "\r\n";
            $nch = isset($this->raw[$x+1]) ? $this->raw[$x+1] : false;
            $this->current_char_string = $ch;
            $this->next_char_string = $nch;
            $this->current_char++;
        }
        // Debug.
        // $this->debug($this->current_char_string, '$this->current_char_string', 1);
        // $this->debug($this->next_char_string, '$this->next_char_string', 1);
        // $this->debug($this->prev_char_string, '$this->prev_char_string', 1);
    }

    /**
     * Todo.
     */
    protected function assignCurrentChar()
    {
        // $this->debug(__METHOD__, '__METHOD__');
        if ($this->next_char_string === false) {
            $this->is_last = true;
        }
        if (in_array($this->current_char_string, ["\r", "\n", "\r\n"])) {
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

        $this->current_char++;
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
    protected function clearAssignChar()
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
