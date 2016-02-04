<?php

namespace IjorTengab\DateTime;

class Range
{
    // Penampungan argument saat object dibuat menggunakan static method
    // ::create().
    public $string_created;

    // Object DateTime untuk start date.
    protected $start;

    // Object DateTime untuk end date.
    protected $end;

    public $is_start_valid;

    public $is_end_valid;

    public $is_valid = [];

    /**
     *
     */
    public static function create($string)
    {
        $part = explode('~', $string);
        switch (count($part)) {
            case 1:
                $part += [1 => 'now'];

            case 2:
                break;

            default:
                // Not valid.
                return false;
        }

        $part = array_filter($part);
        if (count($part) == 2) {
            $obj = new self($part[0], $part[1]);
            $obj->string_created = $string;
            return $obj;
        }
    }

    /**
     *
     */
    public function __construct($date_1, $date_2)
    {
        $date_1 = $this->getObjectDateTime($date_1, 1);
        $date_2 = $this->getObjectDateTime($date_2, 2);

        if ($date_1 < $date_2) {
            $this->start = $date_1;
            $this->is_start_valid = $this->is_valid[1];
            $this->end = $date_2;
            $this->is_end_valid = $this->is_valid[2];
        }
        elseif ($date_1 > $date_2) {
            $this->start = $date_2;
            $this->is_start_valid = $this->is_valid[2];
            $this->end = $date_1;
            $this->is_end_valid = $this->is_valid[1];
        }
        else {
            // Tanggal pertama diasumsikan sebagai start_date, dan tanggal kedua
            // diasumsikan sebagai end_date.
            $this->is_start_valid = $this->is_valid[1];
            $this->is_end_valid = $this->is_valid[2];
            $this->start = $this->end = $date_1;
        }
    }

    /**
     *
     */
    protected function getObjectDateTime($string, $num)
    {
        $this->is_valid[$num] = true;
        if ($string instanceOf \DateTime) {
            return $string;
        }

        try {
            $date = new \DateTime($string);
        } catch (\Exception $e) {
            $date = new \DateTime;
            $this->is_valid[$num] = false;
        }
        return $date;
    }

    /**
     *
     */
    public function format($format, $which)
    {
        if (property_exists($this, $which) && $this->{$which} instanceOf \DateTime) {
            return $this->{$which}->format($format);
        }
    }

    /**
     *
     */
    public function comparison(\DateTime $time, $comparison, $which) {
        if (property_exists($this, $which) && $this->{$which} instanceOf \DateTime) {
            switch ($comparison) {
                case '>':
                case 'greater':
                    return $time > $this->{$which};

                case '<':
                case 'less':
                    return $time < $this->{$which};

                case '=':
                case 'equal':
                    return $time == $this->{$which};
            }
        }
    }

    /**
     *
     */
    public function diff()
    {
        return $this->start->diff($this->end);
    }

    /**
     *
     */
    public function splitPerMonth()
    {
        // jika start 2 oktober 2015
        // dan end 9 desember 2015
        // maka split month akan menghasilkan
        // 2 oktober 2015 ~ 31 oktober 2015
        // 1 november 2015 ~ 30 november 2015
        // 1 desember 2015 ~ 9 desember 2015
        $storage = [];
        $month = $this->start->format('Y-m');
        $test_end = new \DateTime('last day of ' . $month);
        $test_start = $this->cloneObject('start');
        if ($this->comparison($test_end, 'less', 'end')) {
            do {
                $storage[$month] = new Range($test_start, $test_end);
                $month = $this->getNextMonth($month);
                $test_end = new \DateTime('last day of ' . $month);
                $test_start = new \DateTime('first day of ' . $month);

            } while($this->comparison($test_end, 'less', 'end'));
        }
        if ($this->comparison($test_start, 'less', 'end')) {
            $storage[$month] = new Range($test_start, $this->cloneObject('end'));
        }
        // Clear temporary variable.
        unset($test_start);
        unset($test_end);

        return $storage;
    }

    /**
     *
     */
    public function isSameMonth(\DateTime $time = null, $which = null)
    {
        // return $this->start->format('Y-m') == $this->end->format('Y-m');
        if (null === $time && null === $which) {
            return $this->start->format('Y-m') == $this->end->format('Y-m');
        }
        if (null !== $time && null !== $which) {
            if (property_exists($this, $which) && $this->{$which} instanceOf \DateTime) {
                return $time->format('Y-m') == $this->{$which}->format('Y-m');
            }
        }
        // Tidak boleh return false atau null, jika argument tidak valid.
        throw new \InvalidArgumentException;
    }

    /**
     * Todo.
     */
    public function isSameDay(\DateTime $time = null, $which = null)
    {
        if (null === $time && null === $which) {
            return $this->start->format('Y-m-d') == $this->end->format('Y-m-d');
        }
        if (null !== $time && null !== $which) {
            if (property_exists($this, $which) && $this->{$which} instanceOf \DateTime) {
                return $time->format('Y-m-d') == $this->{$which}->format('Y-m-d');
            }
        }
        // Tidak boleh return false, jika argument tidak valid.
        throw new \InvalidArgumentException;
    }

    /**
     *
     */
    public function cloneObject($which)
    {
        if (property_exists($this, $which) && $this->{$which} instanceOf \DateTime) {
            return clone $this->{$which};
        }
    }

    /**
     *
     */
    protected function getLastDate($month, $is_leap)
    {
        $month = (int) $month;
        $last = false;
        switch ($month) {
            case 2:
                $last = $is_leap ? 29 : 28;
                break;
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                $last = 31;
                break;
            case 4:
            case 6:
            case 9:
            case 11:
                $last = 30;
                break;
        }
        return $last;
    }

    /**
     * argument harus sbb: 2015-01 atau 2015-1 atau 20151 atau 201501
     * return pasti 2015-01
     */
    public static function getNextMonth($string)
    {
        if (preg_match('/^(\d+)\-(\d{1,2})$/', $string, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            if ($month > 12) {
                return false;
            }
            if (++$month == 13) {
                $month = 1;
                $year++;
            }
            return $year . '-' . str_pad($month, 2, 0, STR_PAD_LEFT);
        }
    }

    /**
     * argument harus sbb: 2015-01 atau 2015-1 atau 20151 atau 201501
     * return pasti 2015-01
     */
    public static function getPrevMonth($string)
    {
        if (preg_match('/^(\d+)\-(\d{1,2})$/', $string, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            if ($month > 12) {
                return false;
            }
            if (--$month == 0) {
                $month = 12;
                $year--;
            }
            return $year . '-' . str_pad($month, 2, 0, STR_PAD_LEFT);
        }
    }
}
