<?php

namespace IjorTengab\Mission;

/**
 * Trait yang menyajikan cara cepat menggunakan Web Crawler berdasarkan
 * argument informasi awal dan target.
 *
 * Contoh cara penggunaan:
 *
 * ```php
 *
 *     class BNI extends AbstractWebCrawler
 *     {
 *         public function defaultConfiguration() {}
 *
 *
 *         public function defaultCwd() {}
 *     }
 *
 *     $instance = BNI::getInstance();
 *     $result = $instance->executeTarget('get_balance');
 *     $log = $instance->log;
 * ```
 */
trait MissionTrait
{
    /**
     * Create a new instance of object.
     *
     * @param $information mixed
     *   Jika string, maka diasumsikan sebagai json, nantinya akan didecode
     *   sehingga menjadi array.
     */
    public static function getInstance($information = null)
    {
        $instance = new self;
        if (is_string($information)) {
            $information = trim($information);
            $information = json_decode($information, true);
        }
        $information = (array) $information;

        foreach ($information as $key => $value) {
            $instance->set($key, $value);
        }
        return $instance;
    }

    public function executeTarget($target)
    {
        $this->target = $target;
        $this->execute();
        return $this->result;
    }
}
