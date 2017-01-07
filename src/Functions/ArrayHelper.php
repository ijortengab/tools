<?php

namespace IjorTengab\Tools\Functions;

class ArrayHelper
{
    /**
     * Method untuk melakukan operasi update atau retrieve variable dalam
     * class (atau disebut juga property) bertipe array secara mudah dan
     * mengasyikkan (relative menurut saya sendiri :-). Method ini mengubah CRUD
     * (Create, Retrieve, Update, Delete) value dalam array dari langsung
     * (melalui expresi) menjadi melalui fungsi (method).
     *
     * Mendukung array multidimensi dan closure (anonymous function).
     *
     * Cara penggunaan method ini adalah dengan membuat method baru sebagai
     * pembungkus (wrapper). Seperti contoh dibawah ini:
     *
     *   <?php
     *
     *   class FooBar
     *   {
     *
     *       // Property target.
     *       public $options = array(
     *           'homepage' => 'http://github.com/ijortengab',
     *           'email' => 'm_roji28@yahoo.com',
     *       );
     *
     *       // Method options() dibuat sebagai wrapper.
     *       public function options()
     *       {
     *           return ArrayHelper::propertyEditor($this, 'options', func_get_args());
     *       }
     *   }
     *   ?>
     *
     * Contoh Lengkap Penggunaan (diurut mulai dari sederhana sampai rumit):
     *
     *   <?php
     *
     *   $myClass = new MyClass;
     *
     *   // Retrieve melalui cara langsung akses property.
     *   $value = $myClass->option['foo'];
     *
     *   // Retrieve melalui method yang mengasyikkan ini.
     *   $value = $myClass->option('foo');
     *
     *   // Retrieve melalui cara langsung akses property.
     *   $value = $myClass->option['foo']['bar'];
     *
     *   // Retrieve melalui method yang mengasyikkan ini.
     *   $value = $myClass->option('foo[bar]');
     *
     *   // Create/Update melalui cara langsung akses property.
     *   $myClass->option['foo'] = 'hallo';
     *
     *   // Update melalui method yang mengasyikkan ini.
     *   $myClass->option('foo', 'hallo');
     *
     *   // Create/Update melalui cara langsung akses property.
     *   $myClass->option['foo'] = 'hallo';
     *   $myClass->option['bar'] = 'world';
     *
     *   // Create/Update melalui method yang mengasyikkan ini.
     *   $myClass->option('foo', 'hallo')->option('bar', 'world');
     *
     *   // Create/Update melalui cara langsung akses property.
     *   $myClass->option['foo']['child'] = 'hallo';
     *   $myClass->option['bar']['child'] = 'world';
     *
     *   // Create/Update melalui method yang mengasyikkan ini.
     *   $myClass->option('foo[child]', 'hallo')
     *           ->option('bar[child]', 'world');
     *
     *   // Create/Update kemudian Retrieve melalui cara langsung akses
     *   property.
     *   $myClass->option['foo']['child']['subchild'] = 'hallo';
     *   $myClass->option['bar']['child']['subchild'] = 'world';
     *   $value = $myClass->option['other']['child'];
     *
     *   // Create/Update kemudian Retrieve melalui method yang mengasyikkan
     *   ini.
     *   $value = $myClass->option('foo[child][subchild]', 'hallo')
     *                    ->option('bar[child][subchild]', 'world')
     *                    ->option('other[child]');
     *
     *   // Closure/Lambda function/Anonymous function.
     *
     *   // Create/Update melalui cara langsung akses property.
     *   $say = function() {return 'I Love You';};
     *   $myClass->option['get']['merried'] = $say();
     *
     *   // Create/Update melalui method yang mengasyikkan ini.
     *   $myClass->option('get[merried]', function() {return 'I Love You';});
     *
     *   // Closure/Lambda function/Anonymous function with parameter.
     *   $say = function($lang) {
     *       switch ($lang) {
     *           case 'de' : return 'Ich Liebe Dich';
     *           case 'en' : return 'I Love You';
     *           case 'id' : return 'Aku Cinta Kamu';
     *       }
     *   };
     *
     *   // Create/Update melalui cara langsung akses property.
     *   $myClass->option['get']['merried'] = $say('de');
     *
     *   // Create/Update melalui method yang mengasyikkan ini.
     *   $myClass->option('get[merried]', $say, 'de');
     *
     *   ?>
     *
     * ===============================================
     * WARNING WARNING WARNING WARNING WARNING WARNING
     * ===============================================
     *
     * Mengeset data dengan value null sama dengan menghapusnya (unset).
     * Contoh:
     *   $myClass->option('foo[child]', null);
     *
     * Jika anda butuh mengeset property $option['foo']['child'] dengan
     * value null, maka harus langsung mengedit ke property yang bersangkutan
     * dalam contoh ini adalah property $option.
     *
     * Contoh:
     * $data_expand = ArrayHelper::dimensionalExpand(['foo[child]' => null]);
     * $myClass->option = array_replace_recursive($myClass->option, $data_expand);
     */
    public static function propertyEditor($object, $property, $args = array())
    {
        // Tidak menciptakan property baru.
        // Jika property tidak exists, kembalikan null.
        if (!property_exists($object, $property)) {
            return;
        }
        switch (count($args)) {
            case 0:
                // Retrieve value from $property.
                return $object->{$property};

            case 1:
                $variable = array_shift($args);
                // If NULL, it means reset.
                if (is_null($variable)) {
                    $object->{$property} = array();
                }
                // If Array, it meanse replace all value with that array.
                elseif (is_array($variable)) {
                    $object->{$property} = $variable;
                }
                // Otherwise, it means get one info {$property} by key.
                else {
                    // Terinspirasi dari fungsi di Drupal 7:
                    // drupal_array_get_nested_value().
                    $parents = preg_split('/\]?\[/', rtrim($variable, ']'));

                    $ref = &$object->{$property};
                    foreach ($parents as $parent) {
                        if (is_array($ref) && array_key_exists($parent, $ref)) {
                            $ref = &$ref[$parent];
                        }
                        else {
                            return;
                        }
                    }
                    return $ref;
                }
                break;

            case 2:
                // It means set info option.
                // Terinspirasi dari fungsi di Drupal 7:
                // drupal_array_set_nested_value().
                $key = array_shift($args);
                $value = array_shift($args);
                // $parents = explode('->', $key);
                $parents = preg_split('/\]?\[/', rtrim($key, ']'));

                $ref = &$object->{$property};
                foreach ($parents as $parent) {
                    // Edited, ada kejadian seperti ini.
                    // 'data[foo] = string'
                    // sehingga saat
                    // 'data[foo][bar]' = 'string'
                    // menghasilkan error sbb:
                    // Warning: Illegal string offset 'value' in __FILE__ on
                    // line __LINE__.
                    // Solusi, paksa parent menjadi empty array.
                    if (!is_array($ref)) {
                        $ref = array();
                    }
                    $ref_before = &$ref;
                    $ref = &$ref[$parent];
                }
                if (!is_string($value) && is_callable($value, true)) {
                    $value = call_user_func($value);
                }
                // Set value or unset if value is null.
                if (is_null($value)) {
                    unset($ref_before[$parent]);
                }
                else {
                    $ref = $value;
                }
                break;

            default:
                // Untuk argument lebih dari dua.
                $key = array_shift($args);
                // $parents = explode('->', $key);
                $parents = preg_split('/\]?\[/', rtrim($key, ']'));

                $ref = &$object->{$property};
                foreach ($parents as $parent) {
                    $ref = &$ref[$parent];
                }
                // Argument kedua diasumsikan adalah fungsi, argument ketiga
                // dan seterusnya adalah argument untuk dimasukkan kedalam
                // fungsi.
                $callable = array_shift($args);
                if (is_callable($callable)) {
                    $ref = call_user_func_array($callable, $args);
                }
        }

        // Return object back.
        return $object;
    }

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
    public static function dimensionalSimplify($array)
    {
        $result = [];
        foreach ($array as $key_master => $value) {
            self::_dimensionalSimplify($result, $key_master, $value);
        }
        return $result;
    }

    /**
     * Do recursive.
     */
    private static function _dimensionalSimplify(&$result, &$key, $value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $_key = $key;
                $key .= '[' . $k . ']';
                self::_dimensionalSimplify($result, $key, $v);
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
    public static function dimensionalExpand($array_simple)
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

    /**
     *
     */
    public static function filterKeyInteger(Array $array)
    {
        return array_filter($array, 'is_int', ARRAY_FILTER_USE_KEY);
    }

    /**
     *
     */
    public static function filterKeyPattern(Array $array, $pattern)
    {
        return array_filter($array, function ($key) use ($pattern) {
            return preg_match($pattern, $key);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     *
     */
    public static function filterChild(Array $array, Array $condition)
    {
        return array_filter($array, function ($value) use ($condition) {
            return !empty(array_intersect_assoc((array) $value, $condition));
        });
    }

    /**
     *
     */
    public static function elementEditor(&$array)
    {
        $arguments = func_get_args();
        // For arguments:
        // $array, 'insert', 'before/after', 'key', $sub_array.
        if (
            count($arguments) === 5 &&
            $arguments[1] == 'insert' &&
            in_array($arguments[2], ['before', 'after']) &&
            array_key_exists($arguments[3], $array) &&
            is_array($arguments[4])
            ) {
            reset($array);
            $position = 1;
            while ($each = each($array)) {
                switch ($arguments[3]) {
                    case $each['key']:
                        // Ternyata jika value 0, maka lolos juga.
                        // Perlu validasi ulang.
                        if ($arguments[3] === $each['key']) {
                            break 2;
                        }
                }
                $position++;
            }
            switch ($arguments[2]) {
                case 'before':
                    $position -= 1;
                    break;
            }
            $prepend = array_slice($array, 0, $position, true);
            $append = array_slice($array, $position, null, true);
            $array = $prepend + $arguments[4] + $append;
        }
        // For arguments:
        // $array, 'replace', 'key', $sub_array.
        elseif (
            count($arguments) === 4 &&
            $arguments[1] == 'replace' &&
            array_key_exists($arguments[2], $array) &&
            is_array($arguments[3])
        ) {
            self::elementEditor($array, 'insert', 'before', $arguments[2], $arguments[3]);
            unset($array[$arguments[2]]);
        }
    }

    public static function isIndexedKeySorted($array)
    {
        for ($x = 0; $x < count($array); $x++) {
            $key = each($array)['key'];
            if ($key !== $x) {
                return false;
            }
        }
        return true;
    }

    public static function getHighestIndexedKey(Array $array)
    {
        if (empty($array)) {
            return;
        }
        $array = self::filterKeyInteger($array);
        return max(array_keys($array));
    }
}
