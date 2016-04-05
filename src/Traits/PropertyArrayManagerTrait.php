<?php

namespace IjorTengab\Tools\Traits;

trait PropertyArrayManagerTrait
{
    /**
     * Method untuk melakukan operasi update atau retrieve variable dalam
     * class (atau disebut juga property) bertipe array secara mudah dan
     * mengasyikkan (relative menurut saya sendiri :-).
     *
     * Mendukung array multidimensi dan closure (anonymous function).
     *
     * Method ini mengubah CRUD array dari langsung (melalui expresi) menjadi
     * melalui fungsi (method).
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
     *   $value = $myClass->option('foo->bar');
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
     * Cara penggunaan method ini adalah dengan membuat method baru sebagai
     * pembungkus (wrapper). Seperti contoh dibawah ini:
     *
     *   <?php
     *
     *   class MyClass
     *   {
     *
     *       use PropertyArrayManagerTrait;
     *
     *       // Property target.
     *       var $options = array(
     *           'homepage' => 'http://github.com/ijortengab',
     *           'email' => 'm_roji28@yahoo.com',
     *       );
     *
     *       // Method options() dibuat sebagai wrapper method propertyArrayManager().
     *       public function options()
     *       {
     *           return $this->propertyArrayManager('options', func_get_args());
     *       }
     *
     *       public function __construct()
     *       {
     *           // Retrieve semua nilai dari property $options.
     *           $array = $this->options();
     *
     *           // Retrieve value yang mempunyai key 'homepage'.
     *           $homepage = $this->options('homepage');
     *
     *           // Update value yang mempunyai key 'email'.
     *           $this->options('email', 'm.roji28@gmail.com');
     *
     *           // Update keseluruhan nilai dari property $options.
     *           $array = array('name' => 'IjorTengab');
     *           $this->options($array);
     *
     *           // Clear property $options (empty array)
     *           $this->options(NULL);
     *       }
     *   }
     *   ?>
     *
     */
    protected function propertyArrayManager($property, $args = array())
    {
        // Tidak menciptakan property baru.
        // Jika property tidak exists, kembalikan null.
        if (!property_exists(__CLASS__, $property)) {
            return;
        }
        switch (count($args)) {
            case 0:
                // Retrieve value from $property.
                return $this->{$property};

            case 1:
                $variable = array_shift($args);
                // If NULL, it means reset.
                if (is_null($variable)) {
                    $this->{$property} = array();
                }
                // If Array, it meanse replace all value with that array.
                elseif (is_array($variable)) {
                    $this->{$property} = $variable;
                }
                // Otherwise, it means get one info {$property} by key.
                else {
                    // Terinspirasi dari fungsi di Drupal 7:
                    // drupal_array_get_nested_value().
                    // $parents = explode('->', $variable);
                    $parents = preg_split('/\]?\[/', rtrim($variable, ']'));

                    $ref = &$this->{$property};
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

                $ref = &$this->{$property};
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

                $ref = &$this->{$property};
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
        return $this;
    }
}