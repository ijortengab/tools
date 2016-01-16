<?php

namespace IjorTengab\ObjectHelper;

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
     * Todo.
     */
    public static function expand()
    {

    }
}
