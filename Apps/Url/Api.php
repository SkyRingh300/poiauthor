<?php
namespace InnStudio\PoiAuthor\Apps\Url;

use InnStudio\PoiAuthor\Apps\Options\Api as Options;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Post\Api as Post;
use InnStudio\PoiAuthor\Apps\Cache\Other;
use InnStudio\Theme\Apps\Url\Api as InnThemeUrl;

class Api
{
    use TraitPluginUrl;

    const JS_BASEDIR = '/assets/js';
    const CSS_BASEDIR = '/assets/css';
    const IMG_BASEDIR = '/assets/images';
    const APP_BASEDIR = '/App';
    const ADDON_BASEDIR = '/Addons';

    public static function addScheme($url, $htmlspecialchars = true)
    {
        $url = static::esc($url, $htmlspecialchars);

        if (\is_ssl()) {
            return "https:{$url}";
        }

        return "http:{$url}";
    }

    public static function esc($url, $htmlspecialchars = true)
    {
        $url = str_replace([
            'http://',
            'https://'
        ], [
            '//',
            '//'
        ], $url);

        return $htmlspecialchars ? htmlspecialchars($url) : $url;
    }

    public static function getHome($path = null)
    {
        if (class_exists(InnThemeUrl::class) && method_exists(InnThemeUrl::class, 'getHome')) {
            return InnThemeUrl::getHome($path);
        }

        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (!isset($cache[$cacheID])) {
            $cache[$cacheID] = \home_url($path);
        }

        return $cache[$cacheID];
    }

    public static function getAdmin($path = null, $scheme = 'admin')
    {
        if (class_exists(InnThemeUrl::class) && method_exists(InnThemeUrl::class, 'getAdmin')) {
            return InnThemeUrl::getAdmin($path, $scheme);
        }

        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (!isset($cache[$cacheID])) {
            $cache[$cacheID] = \get_admin_url(null, $path, $scheme);
        }

        return $cache[$cacheID];
    }

    public static function getOptsURL()
    {
        return Options::getURL();
    }

    public static function getOpts()
    {
        return Options::getURL();
    }

    public static function getAjax($action = null, array $args = [])
    {
        $url = static::getAdmin('admin-ajax.php');

        if (! $action){
            return $url;
        }

        $action = Functions::buildActionName($action);

        if (empty($args)) {
            return "${url}?action=${action}";
        }

        return "${url}?action=${action}&" . http_build_query($args);
    }

    public static function getCurrent()
    {
        return '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}
