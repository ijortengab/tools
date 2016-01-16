<?php

namespace IjorTengab\Timer;

/**
 * Class for handle of timing, countdown or countup.
 */
class Timer
{
    public $count_down;

    public $start;

    public $time;

    public function __construct($count_down = NULL) {
        if (is_int($count_down)) {
            $this->count_down = $count_down * 1000;
        }
        $this->start = microtime(TRUE);
    }

    /**
     * Read waktu yang telah berjalan.
     */
    public function read()
    {
        $stop = microtime(TRUE);
        $diff = round(($stop - $this->start) * 1000, 2);
        if (isset($this->time)) {
            $diff += $this->time;
        }
        return $diff;
    }

    /**
     * Check count down.
     * @return sisa waktu
     */
    public function remaining()
    {
        if (null !== $this->count_down) {
            return round($this->count_down - ($this->read()));
        }
    }
}
