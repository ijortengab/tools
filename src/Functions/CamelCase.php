<?php

namespace IjorTengab\Tools\Functions;

class CamelCase
{
    /**
     * Mengubah string dari request_home_page_authenticated, menjadi
     * requestHomePageAuthenticated yang menjadi standard penamaan
     * method pada PSR-1.
     */
    public static function convertFromUnderScore($string)
    {
        // Prepare Cache.
        $string = trim($string);
        if ($string === '') {
            return;
        }
        static $cache = [];
        if (isset($cache[$string])) {
            return $cache[$string];
        }

        // Action.
        $result = $string;
        $result = strtolower($result);
        $explode = explode('_', $result);
        do {
            if (!isset($x)) {
                $x = 0;
                $method = $explode[$x];
            }
            else {
                $method .= ucfirst($explode[$x]);
            }
        } while(count($explode) > ++$x);
        $cache[$string] = $method;
        return $method;
    }

    /**
     * Mengubah camelCase menjadi camel_case.
     *
     * @link
     *    http://stackoverflow.com/q/1175208
     *
     * Example:
     *
     * 'CamelCase' => 'camel_case'
     * 'CamelCamelCase' => 'camel_camel_case'
     * 'Camel2Camel2Case' => 'camel2_camel2_case'
     * 'getHTTPResponseCode' => 'get_http_response_code'
     * 'get2HTTPResponseCode' => 'get2_http_response_code'
     * 'HTTPResponseCode' => 'http_response_code'
     * 'HTTPResponseCodeXYZ' => 'http_response_code_xyz'
     */
    public static function convertToUnderScore($string)
    {
        // Prepare Cache.
        $string = trim($string);
        if ($string === '') {
            return;
        }
        static $cache = [];
        if (isset($cache[$string])) {
            return $cache[$string];
        }

        // Action.
        $result = $string;
        $result = preg_replace('/(.)([A-Z][a-z]+)/', '$1_$2', $result);
        $result = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $result);
        $result = strtolower($result);
        $cache[$string] = $result;
        return $result;
    }
}
