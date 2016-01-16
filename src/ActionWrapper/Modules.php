<?php

namespace IjorTengab\ActionWrapper;

class Modules
{
    /**
     * Direktori tempat pencarian module.
     */
    const MODULE_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR . 'modules';

    /**
     * Maksimal kedalaman direktori yang akan di scan.
     * Jika bernilai 1, maka itu artinya hanya file yang berada di dalam
     * self::MODULE_DIRECTORY yang akan discan.
     * Jika bernilai 2, maka file yang berada di dalam direktori di dalam
     * direktori self::MODULE_DIRECTORY juga discan.
     * Begitu seterusnya fungsi dari value ini untuk mencegah adanya
     * looping unlimited disebabkan directory yang merupakan link ke parent.
     */
    const SCANDIR_DEEP = 5;

    public static $scan_module_directory = true;

    public static $modules = [];

    protected static $autoload;

    protected static $class_name;

    /**
     * Daftar interface yang dibutuhkan oleh class ini.
     */
    protected static $interface_require = [
        'IjorTengab\ActionWrapper\ModuleInterface',
    ];

    public static function add(array $list)
    {
        self::$modules = array_merge(self::$modules, $list);
    }

    public static function autoload($class)
    {
        $autoload = self::$autoload;
        if (isset($autoload[$class])) {
            include $autoload[$class];
        }
    }

    public static function init()
    {
        if (self::$scan_module_directory) {
            ob_start();
            self::scanModules(self::MODULE_DIRECTORY, self::SCANDIR_DEEP, self::$modules, self::$autoload);
            ob_end_clean();
            spl_autoload_register(array('self', 'autoload'));
        }
    }

    public static function getObject($name)
    {
        try {
            $modules = self::$modules;
            if (!array_key_exists($name, $modules)) {
                Log::setError('Callback for "{name}" has not registered as a module.', ['name' => $name]);
                throw new \Exception;
            }
            $class = $modules[$name];
            if (!class_exists($class)) {
                Log::setError('Class {class} has not been defined.', ['class' => $class]);
                throw new \Exception;
            }
            $interface_require = self::$interface_require;
            $diff = array_diff($interface_require, class_implements($class));
            if (!empty($diff)) {
                Log::setError('Class {class} has not implements required interface: {diff}.', ['class' => $class, 'diff' => implode(', ', $diff)]);
                throw new \Exception;
            }
            return new $class;
        }
        catch (\Exception $e) {
        }
    }

    public static function listModules()
    {
        return self::$modules;
    }

    protected function scanModules($dir, $deep, &$storage, &$autoload)
    {
        if (--$deep < 0) {
            return;
        }
        $list = scandir($dir);
        $list = array_diff($list, array('.', '..'));

        while ($each = array_shift($list)) {
            $test = $dir . DIRECTORY_SEPARATOR . $each;
            $path_parts = pathinfo($test);
            if (!empty($path_parts['filename']) && !empty($path_parts['extension']) && $path_parts['extension'] == 'module') {
                $name_space = require($test);
                if (is_string($name_space)) {
                    $name_space = trim($name_space);
                    if (!empty($name_space)) {
                        $filename = $path_parts['filename'];
                        $class = $name_space . '\\' . $filename;
                        $storage[$filename] = $class;
                        $test_file = substr($test, 0, -7) . '.php';
                        if (file_exists($test_file)) {
                            $autoload[$class] = $test_file;
                        }
                    }
                }

            }
            if (is_dir($test)) {
                self::scanModules($test, $deep, $storage, $autoload);
            }
        }

    }

    /**
     * Mendaftarkan module external untuk bisa terintegrasi dengan framework
     * IBank ini.
     */
    public static function register($name, ModuleInterface $callback)
    {
        self::$registered_modules[$name] = $callback;
    }

}


