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
     */
    public function propertyEditor($object, $property, $args = array())
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
}
