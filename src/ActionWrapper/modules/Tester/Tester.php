<?php

namespace Vendor\Package;

use IjorTengab\ActionWrapper\ModuleInterface;

class Tester implements ModuleInterface
{
    /**
     * Todo.
     */
    public function getLog($level = null){}

    /**
     * Todo.
     */
    public function setAction($action){}

    /**
     * Todo.
     */
    public function setInformation($key, $value){}

    /**
     * Todo.
     */
    public function runAction()
    {
        $string = 'Class ' . __CLASS__ . ' melakukan eksekusi.';
        print_r($string);
    }

    /**
     * Todo.
     */
    public function getResult()
    {
        $string = 'Class ' . __CLASS__ . ' berhasil dijalankan dengan baik';
        return $string;
    }


}
