<?php

namespace IjorTengab\DateTime;

/**
 * Class for handle of timing, countdown or countup.
 */
class CountDown extends Timer
{
    // Waktu dalam seconds
    public $count_down;

    public function __construct($seconds) {
        $seconds = (int) $seconds;
        $this->count_down = $seconds;
        $this->start = microtime(TRUE);
    }    

    /**
     * Mengembalikan sisa waktu dalam detik dengan pecahan dua desimal
     * dibelakang koma.
     */
    public function remaining()
    {
        return round($this->count_down - $this->read(), 2);        
    }
    
    public function isTimeOut() 
    {
        return $this->remaining() <= 0;
    }
}
