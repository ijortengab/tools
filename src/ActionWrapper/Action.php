<?php

namespace IjorTengab\ActionWrapper;

/**
 * Wrap classes in one place.
 *
 * Menyederhanakan dan menyeragamkan syntax saat mengekseskui class tertentu.
 *
 * Contoh penyederhanaan syntax:
 *
 * ```php
 *
 *     $result = Action::ModuleA('doSomething');
 *
 *     $result = Action::ModuleA('doSomething', $contextual_information);
 *
 *     $result = Action::ModuleB('doSomething');
 *
 *     $result = Action::ModuleB('doSomething', $contextual_information);
 *
 * ```
 *
 * Setiap module adalah class yang wajib mengimplementasi ModuleInterface.
 * Class ditaruh didalam folder modules (di-copy atau dibuat sebagai symbolic
 * link).
 *
 */
class Action
{

    public static $current_module;

    /**
     * Setiap static yang dijalankan maka akan diasumsikan sebagai module.
     *
     * PHP Description: __callStatic() is triggered when invoking inaccessible
     * methods in a static context.
     */
    public static function __callStatic($name, $arguments)
    {
        try {
            if (empty($arguments)) {
                Log::setError('Action has not been defined.');
                throw new \Exception;
            }
            // Ambil Module dan lakukan spl_autoload_register().
            Modules::init();
            if (self::$current_module = Modules::getObject($name)) {
                // Finish verify.
                $action = array_shift($arguments);
                $information = array_shift($arguments);
                self::$current_module->setAction($action);
                self::$current_module->setInformation($information);
                self::$current_module->runAction();
                Log::set(self::$current_module->getLog());
                return self::$current_module->getResult();
            }
        }
        catch (\Exception $e) {
        }
    }
}
