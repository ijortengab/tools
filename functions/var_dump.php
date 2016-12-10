<?php

namespace IjorTengab\Override\PHP\VarDump;

const FILE    = 0b000001;
const LINE    = 0b000010;
const TIME    = 0b000100;
const SUBJECT = 0b001000;
const ALL     = 0b001111;
const COMMENT = 0b010000;
const CAPTURE = 0b100000;

/**
 * Override PHP's core function var_dump to give more powerfull information.
 *
 * @reference
 *   - http://php.net/manual/en/language.variables.basics.php
 */
function var_dump($variable_value, $flags = 0) {
    static $cache_file = [];
    static $cache_file_line = [];
    // Get result of dump variable.
    ob_start();
    \var_dump($variable_value);
    $output = ob_get_contents();
    ob_end_clean();

    // Modify indent from 2 space to 4 space (like print_r).
    preg_match_all('/\n(?<space>[ ]+)/', $output, $matches, PREG_SET_ORDER);
    while ($info = array_shift($matches)) {
        $space = $info['space'];
        $replace = str_replace('  ', "\t", $space);
        $output = preg_replace('/\n[ ]+/', "\n" . $replace, $output, 1);
    }
    $output = str_replace("\t", '    ', $output);

    if ($flags & ALL) {

        // Modify by flag.
        $string = [
            'file' => '',
            'line' => '',
            'time' => '',
            'subject' => '',
        ];
        // Get variable name.
        $last_run = array_shift(debug_backtrace());
        $file = $last_run['file'];
        $line = $last_run['line'];
        if (!isset($cache_file[$file])) {
            $cache_file[$file] = $lines = file($file);
        }
        else {
            $lines = $cache_file[$file];
        }
        $row = $lines[$line - 1];
        $variable_name = '';
        // Pattern from php dot net.
        $pattern = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
        if (preg_match_all('/var_dump\s*\(\s*(?<name>\$+' . $pattern . ')/', $row, $matches, PREG_SET_ORDER)) {
            if (!isset($cache_file_line[$file . ':' . $line])) {
                $cache_file_line[$file . ':' . $line] = $matches;
            }
            $variable_name = array_shift($cache_file_line[$file . ':' . $line])['name'];
        }
        elseif (preg_match_all('/var_dump\s*\(\s*(?<name>.+)(,\s*[~A-Z]+(\s*[&\|\^]\s*[~A-Z]+)*)\)\s*;/', $row, $matches, PREG_SET_ORDER)) {
            if (!isset($cache_file_line[$file . ':' . $line])) {
                $cache_file_line[$file . ':' . $line] = $matches;
            }
            $variable_name = array_shift($cache_file_line[$file . ':' . $line])['name'];
        }
        if ($flags & FILE) {
        $string['file'] = 'File: ' . $file . PHP_EOL;
        }
        if ($flags & LINE) {
            $string['line'] = 'Line: ' . $line . PHP_EOL;
        }
        if ($flags & TIME) {
            $string['time'] = 'TIME: ' . date('c') . PHP_EOL;
        }
        if ($flags & SUBJECT) {
            $string['subject'] = 'var_dump(' . $variable_name . '):' . PHP_EOL;
        }
        $output = implode('', $string) . $output;
    }

    if ($flags & COMMENT) {
        $output = str_replace("\n","\n * ", $output);
        $output = rtrim($output, ' *');
$output = <<<COMMENT
/**
 * $output */

COMMENT;
    }
    if ($flags & CAPTURE) {
        return $output;
    }
    else {
        echo $output;
    }
}
