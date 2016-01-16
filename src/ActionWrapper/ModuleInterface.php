<?php

namespace IjorTengab\ActionWrapper;

/**
 * Interface yang digunakan oleh module ActionWrapper.
 */
interface ModuleInterface
{
    /**
     * Todo.
     */
    public function setAction($action);

    /**
     * Todo.
     */
    public function setInformation($key, $value);

    /**
     * Todo.
     */
    public function runAction();

    /**
     * Todo.
     */
    public function getResult();

    /**
     * Todo.
     */
    public function getLog($level = null);

}
