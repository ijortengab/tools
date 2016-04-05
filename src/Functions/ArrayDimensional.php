<?php

namespace IjorTengab\Tools\Functions;

class ArrayDimensional
{
    /**
     * Mengubah array multidimensi menjadi satu dimensi.
     *
     * Contoh:
     *
     * ```php
     * // Drupal 7 fields.
     * $array = [
     *     'field_file_1' => [
     *         'und' => [
     *             0 => [
     *                 '_weight' => 0,
     *                 'fid' => 0,
     *                 'display' => 1,
     *             ],
     *         ],
     *     ],
     *     'field_file_2' => [
     *         'und' => [
     *             0 => [
     *                 '_weight' => 0,
     *                 'fid' => 0,
     *                 'display' => 1,
     *             ],
     *         ],
     *     ],
     * ];
     * // Array diatas akan menjadi seperti ini:
     * $array = [
     *      ["field_file_1[und][0][_weight]"] => 0,
     *      ["field_file_1[und][0][fid]"] => 0,
     *      ["field_file_1[und][0][display]"] => 1,
     *      ["field_file_2[und][0][_weight]"] => 0,
     *      ["field_file_2[und][0][fid]"] => 0,
     *      ["field_file_2[und][0][display]"] => 1,
     *  ];
     * ```
     *
     * Array dalam bentuk flat ini digunakan khususnya untuk http request post.
     * Class IjorTengab\Browser\Engine menggunakan fungsi ini.
     *
     */
    public static function simplify($array)
    {
        $result = [];
        foreach ($array as $key_master => $value) {
            self::_simplify($result, $key_master, $value);
        }
        return $result;
    }

    /**
     * Do recursive.
     */
    private static function _simplify(&$result, &$key, $value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $_key = $key;
                $key .= '[' . $k . ']';
                self::_simplify($result, $key, $v);
                $key = $_key;
            }
        }
        else{
            $result[$key] = $value;
        }
    }

    /**
     * Membuat nested array, code bersumber dari drupal 7 pada fungsi
     * drupal_parse_info_format().
     */
    public static function expand($array_simple)
    {
        $info = [];
        foreach ($array_simple as $key => $value) {
            $keys = preg_split('/\]?\[/', rtrim($key, ']'));
            $last = array_pop($keys);
            $parent = &$info;
            // Create nested arrays.
            foreach ($keys as $key) {
                if ($key == '') {
                    $key = count($parent);
                }
                if (!isset($parent[$key]) || !is_array($parent[$key])) {
                    $parent[$key] = array();
                }
                $parent = &$parent[$key];
            }
            // Insert actual value.
            if ($last == '') {
                $last = count($parent);
            }
            // Update hack start
            $parent[$last] = $value;
            // Update hack finish
        }
        return $info;
    }
}
