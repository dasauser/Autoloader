<?php
namespace ShittyLoader;
/**
 * Class Autoloader
 * @package ShittyLoader
 */
final class Autoloader
{
    /**
     * Var for checking preload
     * @var bool $autoload
     */
    public static $autoload = false;

    public static $root_dir = __DIR__;

    /**
     * Funnction for register new loader
     * @param array $map
     * @param string $root_dir
     * @param bool $need_preload
     */
    final public static function registerLoader(array $map, string $root_dir, bool $need_preload = false) : void
    {
        self::$root_dir = $root_dir;
        if ($need_preload) {
            self::preload($map);
        }
        spl_autoload_register(function ($class_name) use ($map) {
            self::lazyLoad($map, $class_name);
        }, true, true);
    }

    /**
     * Classes loader function
     * @param array $map_file
     * @param string $class_name
     */
    final public static function lazyLoad(array $map_file, string $class_name = '') : void
    {
        if (!class_exists($class_name)) {
            if (isset($map_file['classes'][$class_name])) {
                $class_file = self::$root_dir . "/{$map_file['classes'][$class_name]}.php";
                if (file_exists($class_file)) {
                    if (is_file($class_file) && !is_dir($class_file)) {
                        self::connect($class_file);
                    }
                }
            }
        }
    }

    /**
     * Classes preloader function
     * @param array $map_file
     */
    final public static function preload(array $map_file) : void
    {
        if (self::$autoload === false) {
            self::$autoload = true;
            foreach ($map_file['dirs_recursive'] as $dir) {
                self::requireRecursiveDirs($dir);
            }
        }
    }

    /**
     * Classes including function
     * @param array $classes_array
     */
    final private static function requireClasses(array $classes_array) : void
    {
        foreach ($classes_array as $class_path) {
            self::connect($class_path);
        }
    }

    /**
     * PHP files from directory including function
     * @param string $dirname
     */
    final private static function requireRecursiveDirs(string $dirname) : void
    {
        $dir_array = self::getDirArray($dirname);
        $classes = [];
        if (count($dir_array) !== 0) {
            foreach ($dir_array as $file) {
                $file_path = self::$root_dir . "/$dirname/$file";
                if (is_dir($file_path)) {
                    self::requireRecursiveDirs("$dirname/$file");
                } else if (is_file($file_path)) {
                    if (substr($file_path, strripos($file_path, '.')) === '.php') {
                        $classes[] = $file_path;
                    }
                }
            }
            self::requireClasses($classes);
        }
    }

    /**
     * Getting dir content function
     * @param string $dirname
     * @return array
     */
    final private static function getDirArray(string $dirname) : array
    {
        $dirs_array = scandir(self::$root_dir . '/' . $dirname);
        unset($dirs_array[array_search('.', $dirs_array)]);
        unset($dirs_array[array_search('..', $dirs_array)]);
        return $dirs_array;
    }

    /**
     * Including function
     * @param string $class_file
     */
    final private static function connect(string $class_file) : void
    {
        require_once($class_file);
    }
}