<?php
namespace InnStudio\PoiAuthor\Apps\Condition;

use InnStudio\PoiAuthor\Apps\Options\Api as Options;
use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\Theme\Apps\Condition\Api as InnThemeCondition;

interface conditionInterface
{
    public static function isOptsPage();
    public static function isArchive();
    public static function isPostTypeArchive($post_types = null);
    public static function isAttachment($attachment = null);
    public static function isAdmin();
    public static function isFrontPage();
    public static function isAuthor($author = null);
    public static function is404();
    public static function isSearch();
    public static function isTag($tag = null);
    public static function isCategory($category = null);
    public static function isDate();
    public static function isDay();
    public static function isMonth();
    public static function isYear();
    public static function isHome();
    public static function isSingular($post_types = null);
    public static function isPage($page = null);
    public static function isUserLoggedIn();
    public static function isAjax();
}

class Api implements conditionInterface
{
    public static function isOptsPage()
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);

        return $page === Functions::buildActionName(Options::URL_ID) && User::currentUserCan('manage_options') && static::isAdmin();
    }

    public static function isArchive()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_archive();
        }

        return $cache;
    }

    public static function isPostTypeArchive($post_types = null)
    {
        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\is_post_type_archive', func_get_args());

        return $cache[$cacheID];
    }

    public static function isAttachment($attachment = null)
    {
        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\is_attachment', func_get_args());

        return $cache[$cacheID];
    }

    public static function isAdmin()
    {
        if (class_exists(InnThemeCondition::class) && method_exists(InnThemeCondition::class, 'isAdmin')) {
            return InnThemeCondition::isAdmin();
        }

        static $cache = null;

        if ($cache === null){
            $cache = \is_admin();
        }

        return $cache;
    }

    public static function isFrontPage()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_front_page();
        }

        return $cache;
    }

    public static function is404()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_404();
        }

        return $cache;
    }

    public static function isSearch()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_search();
        }

        return $cache;
    }

    public static function isTag($tag = null)
    {
        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\is_tag', func_get_args());

        return $cache[$cacheID];
    }

    public static function isCategory($category = null)
    {
        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\is_category', func_get_args());

        return $cache[$cacheID];
    }

    public static function isDate()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_date();
        }

        return $cache;
    }

    public static function isday()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_day();
        }

        return $cache;
    }

    public static function isMonth()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_month();
        }

        return $cache;
    }

    public static function isYear()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_year();
        }

        return $cache;
    }

    public static function isSingular($post_types = null)
    {
        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\is_singular', func_get_args());

        return $cache[$cacheID];
    }

    public static function isPage($page = null)
    {
        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\is_page', func_get_args());

        return $cache[$cacheID];
    }

    public static function isHome()
    {
        static $cache = null;

        if ($cache === null){
            $cache = \is_home();
        }

        return $cache;
    }

    public static function isUserLoggedIn()
    {
        return User::isUserLoggedIn();
    }

    public static function isAuthor($author = null)
    {
        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\is_author', func_get_args());

        return $cache[$cacheID];
    }

    public static function isAjax()
    {
       return defined('DOING_AJAX') && DOING_AJAX;
    }
}
