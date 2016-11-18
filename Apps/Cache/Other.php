<?php
namespace InnStudio\PoiAuthor\Apps\Cache;

use InnStudio\PoiAuthor\Main\Core;
use InnStudio\Theme\Apps\Cache\Other as InnThemeOther;

class Other
{
    public static function getPlugin($key = null)
    {
        static $cache = null;

        if ($cache === null) {
            $cache = \get_plugin_data(Core::$dir . '/' . Core::ID . '.php');
        }

        if ($key) {
            return isset($cache[$key]) ? $cache[$key] : false;
        }

        return $cache;
    }

    public static function getQueryVar(...$args)
    {
        if (class_exists(InnThemeOther::class) && method_exists(InnThemeOther::class, 'getQueryVar')) {
            return call_user_func_array([InnThemeOther::class, 'getQueryVar'], $args);
        }

        static $cache = [];
        $cacheID = md5(json_encode($args));

        if (!isset($cache[$cacheID])) {
            $cache[$cacheID] = call_user_func_array('\get_query_var', $args);
        }

        return $cache[$cacheID];
    }

    public static function getOption($key, $default = false, $focus = false)
    {
        if (class_exists(InnThemeOther::class) && method_exists(InnThemeOther::class, 'getOption')) {
            return call_user_func_array([InnThemeOther::class, 'getOption'], func_get_args());
        }

        static $cache = [];
        $cacheID = md5(json_encode($key . $default));

        if ($focus || !isset($cache[$cacheID])) {
            $cache[$cacheID] = call_user_func_array('\get_option', [$key, $default]);

            if (is_string($cache[$cacheID])) {
                $json = json_decode($cache[$cacheID], true);

                if ($json) {
                    $cache[$cacheID] = $json;
                }
            }
        }

        return $cache[$cacheID];
    }

    public static function updateOption(...$args)
    {
        $args[1] = addslashes(json_encode($args[1]));

        return call_user_func_array('\update_option', $args);
    }

    public static function getCurrentScreen()
    {
        if (class_exists(InnThemeOther::class) && method_exists(InnThemeOther::class, 'getCurrentScreen')) {
            return InnThemeOther::getCurrentScreen();
        }

        static $cache = null;

        if ($cache === null) {
            $cache = \get_current_screen();
        }

        return $cache;
    }

    public static function getBlogInfo($key)
    {
        if (class_exists(InnThemeOther::class) && method_exists(InnThemeOther::class, 'getBlogInfo')) {
            return InnThemeOther::getBlogInfo($key);
        }

        static $cache = [];

        if (!isset($cache[$key])) {
            $cache[$key] = \get_bloginfo($key);
        }

        return $cache[$key];
    }

    public static function getCurrentTime($type, $gmt = false)
    {
        if (class_exists(InnThemeOther::class) && method_exists(InnThemeOther::class, 'getCurrentTime')) {
            return InnThemeOther::getCurrentTime($type, $gmt);
        }

        switch ($type) {
            case 'mysql':
                return $gmt ? gmdate('Y-m-d H:i:s') : gmdate('Y-m-d H:i:s', ($_SERVER['REQUEST_TIME'] + (static::getOption('gmt_offset') * 3600 )));
            case 'timestamp':
                return $gmt ? $_SERVER['REQUEST_TIME'] : $_SERVER['REQUEST_TIME'] + ( static::getOption('gmt_offset') * 3600);
            default:
                return $gmt ? date($type) : date($type, $_SERVER['REQUEST_TIME'] + (static::getOption('gmt_offset') * 3600));
        }
    }

    public static function getDate($type, $timestamp)
    {
        return date($type, $timestamp + ( static::getOption('gmt_offset') * 3600));
    }
}
