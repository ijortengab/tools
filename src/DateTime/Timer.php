<?php

namespace IjorTengab\DateTime;

/**
 * Class for handle of timing, countdown or countup.
 */
class Timer
{

    // TimeStamp Unix.
    public $start;
    
    public function __construct() 
    {
        $this->start = microtime(TRUE);
    }
    
    /**
     * Mengembalikan waktu yang telah berjalan dari
     * sejak object ini dibuat sampai saat ini.
     * Waktu dalam seconds.
     */
    public function read()
    {
        return round(microtime(TRUE) - $this->start, 2);        
    }
}
