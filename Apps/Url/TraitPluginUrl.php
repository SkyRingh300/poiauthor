<?php
namespace InnStudio\PoiAuthor\Apps\Url;

use InnStudio\PoiAuthor\Main\Core;
use InnStudio\PoiAuthor\Apps\FileTimestamp\Api as FileTimestamp;

trait TraitPluginUrl
{
    public static function getDir()
    {
        return Core::$dir;
    }

    public static function getUrl()
    {
        static $cache = null;

        if ($cache === null) {
            $cache = \plugins_url('', Core::$dir . '/' . Core::ID);
        }

        return $cache;
    }

    public static function getFileUrl($fileBasename, $version = false)
    {
        if ($fileBasename[0] !== '/') {
            $fileBasename = "/${fileBasename}";
        }

        $fileUrl = static::getUrl() . $fileBasename;

        if ($version) {
            $fileUrl .= '?v=' . FileTimestamp::getTimestamp();
        }

        return $fileUrl;
    }

    public static function getAppUrl()
    {
        return static::getUrl() . '/Apps';
    }

    public static function getAppFileUrl($__DIR__, $fileBasename, $version = false)
    {
        if ($fileBasename[0] !== '/') {
            $fileBasename = "/$fileBasename";
        }

        $baseDIR = basename($__DIR__);
        $fileUrl = static::getAppUrl() . '/' . $baseDIR . $fileBasename;

        if ($version) {
            $fileUrl .= '?v=' . FileTimestamp::getTimestamp();
        }

        return $fileUrl;
    }

    public static function getAddonUrl()
    {
        return static::getUrl() . '/Addons';
    }

    public static function getAddonFileUrl($__DIR__, $fileBasename, $version = false)
    {
        if ($fileBasename[0] !== '/') {
            $fileBasename = "/$fileBasename";
        }

        $baseDIR = basename($__DIR__);
        $fileUrl = static::getAddonUrl() . '/' . $baseDIR . $fileBasename;

        if ($version) {
            $fileUrl .= '?v=' . FileTimestamp::getTimestamp();
        }

        return $fileUrl;
    }

    public static function getJsUrl($fileBasename, $version = false)
    {
        if ($fileBasename[0] !== '/') {
            $fileBasename = "/$fileBasename";
        }

        return static::getFileUrl(static::JS_BASEDIR . "${fileBasename}.js", $version);
    }

    public static function getImgUrl($fileBasename, $version = false)
    {
        if ($fileBasename[0] !== '/') {
            $fileBasename = "/$fileBasename";
        }

        return static::getFileUrl(static::IMG_BASEDIR . "${fileBasename}", $version);
    }

    public static function getCssUrl($fileBasename, $version = false)
    {
        if ($fileBasename[0] !== '/') {
            $fileBasename = "/$fileBasename";
        }

        return static::getFileUrl(static::CSS_BASEDIR . "${fileBasename}.css", $version);
    }

    public static function getAppJsUrl($__DIR__, $fileBasename = 'frontend', $version = false)
    {
        return static::getAppFileUrl(
            $__DIR__,
            static::ADDONS_BASEDIR . static::JS_BASEDIR . "/${fileBasename}.js",
            $version
        );
    }

    public static function getAppCssUrl($__DIR__, $fileBasename = 'frontend', $version = false)
    {
        return static::getAppFileUrl(
            $__DIR__,
            static::ADDONS_BASEDIR . static::CSS_BASEDIR . "/${fileBasename}.css",
            $version
        );
    }

    public static function getAppImgUrl($__DIR__, $fileBasename, $version = false)
    {
        return static::getAppFileUrl(
            $__DIR__,
            static::ADDONS_BASEDIR . static::IMG_BASEDIR . "/${fileBasename}",
            $version
        );
    }
}
