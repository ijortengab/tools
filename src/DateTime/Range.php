<?php

namespace IjorTengab\DateTime;

// "now"
// "3 October 2005"
// "+5 hours"
// "+1 week"
// "+1 week 3 days 7 hours 5 seconds"
// "next Monday"
// "last Sunday"
class Range
{
    public $string_created;

    public $from;

    public $to;

    public $from_string;

    public $to_string;

    public $is_valid = [];

    public static function create($string)
    {
        $explode = explode('~', $string);
        switch (count($explode)) {
            case 1:
                $explode += [1 => 'now'];

            case 2:
                break;

            default:
                // Not valid.
                return;
        }

        // Santize.
        foreach ($explode as &$each) {
            $each = trim($each);
            $each = strtolower($each);
        }
        $explode = array_filter($explode);
        if (count($explode) == 2) {
            $obj = new self($explode[0], $explode[1]);
            $obj->string_created = $string;
            return $obj;
        }
    }

    protected function convertTime($string, $num)
    {
        $this->is_valid[$num] = true;
        switch ($string) {
            // Yang sering digunakan.
            case 'now':
            case 'today':
            case 'yesterday':
            case 'last day':
            case 'last month':
            case 'last year':
            case 'tomorrow':
                $time = strtotime($string);
                break;

            default:
                $time = strtotime($string);
                if (false === $time) {
                    $this->is_valid[$num] = false;
                    $time = time();
                }
                break;
        }
        return $time;
    }

    public function __construct($date_1, $date_2)
    {
        $date_1 = $this->convertTime($date_1, 1);
        $date_2 = $this->convertTime($date_2, 2);

        if ($date_1 < $date_2) {
            $this->from = $date_1;
            $this->is_valid['from'] = $this->is_valid[1];            
            $this->to = $date_2;
            $this->is_valid['to'] = $this->is_valid[2];
        }
        elseif ($date_1 > $date_2) {
            $this->from = $date_2;
            $this->is_valid['from'] = $this->is_valid[2];
            $this->to = $date_1;
            $this->is_valid['to'] = $this->is_valid[1];
        }
        else {
            // Tanggal pertama diasumsikan sebagai from, dan tanggal kedua
            // diasumsikan sebagai to.
            $this->is_valid['from'] = $this->is_valid[1];
            $this->is_valid['to'] = $this->is_valid[2];
            $this->from = $this->to = $date_1;
        }
        $this->from_string = date('c', $this->from);
        $this->to_string = date('c', $this->to);
        unset($this->is_valid[1]);
        unset($this->is_valid[2]);
    }
}
