<?php
namespace InnStudio\PoiAuthor\Apps\User;

use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Url\Api as Url;
use InnStudio\Theme\Apps\User\Api as InnThemeUser;

class Api
{
    const returnCode = [
        'noUserFound' => 50001,
    ];

    public static function getUserMeta(...$args)
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'getUserMeta')) {
            return call_user_func_array([InnThemeUser::class, 'getUserMeta'], $args);
        }

        if (isset($args[3]) && $args[3]) {
            unset($args[3]);

            return Functions::metaToJson(call_user_func_array('\get_user_meta', $args));
        }

        static $cache = [];
        $cacheID = md5(json_encode($args));

        if (! isset($cache[$cacheID])) {
            $cache[$cacheID] = Functions::metaToJson(call_user_func_array('\get_user_meta', $args));
        }

        return $cache[$cacheID];
    }

    public static function updateUserMeta(...$args)
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'updateUserMeta')) {
            return call_user_func_array([InnThemeUser::class, 'updateUserMeta'], $args);
        }

        $args[2] = addslashes(json_encode($args[2]));

        if (isset($args[3])) {
            $args[3] = addslashes(json_encode($args[3]));
        }

        return call_user_func_array('\update_user_meta', $args);
    }

    public static function getCurrentUser()
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'getCurrentUser')) {
            InnThemeUser::getCurrentUser();
        }

        if (! static::isUserLoggedIn()) {
            return false;
        }

        static $cache = null;

        if ($cache === null) {
            $cache = \_wp_get_current_user();
        }

        return $cache;
    }

    public static function getCurrentUserID()
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'getCurrentUserID')) {
            return InnThemeUser::getCurrentUserID();
        }

        if (! static::isUserLoggedIn()) {
            return false;
        }

        static $cache = null;

        if ($cache === null) {
            $cache = \get_current_user_id();
        }

        return $cache;
    }

    public static function getAvatarURL(...$args)
    {
        return call_user_func_array([__CLASS__, 'getAvatarData'], $args)['url'];
    }

    public static function getAvatarData(...$args)
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'getAvatarData')) {
            return call_user_func_array([InnThemeUser::class, 'updateUserMeta'], $args);
        }

        static $cache = [];
        $cacheID = md5(json_encode($args));

        if (! isset($cache[$cacheID])) {
            $cache[$cacheID] = call_user_func_array('\get_avatar_data', $args);
        }

        return $cache[$cacheID];
    }

    public static function getUserByMeta($metaKey, $metaValue)
    {
        $users = \get_users([
            'meta_key' => $metaKey,
            'meta_value' => $metaValue,
            'number' => 1,
        ]);

        if (empty($users)) {
            return false;
        }

        return $users[0]->data;
    }

    public static function getUserBy($field, $value, $focus = false)
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'getUserBy')) {
            InnThemeUser::getUserBy($field, $value, $focus);
        }

        static $cache = [];
        $cacheID = $field . $value . $focus;

        if (! $focus && isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $userdata = \WP_User::get_data_by( $field, $value );

        if ( !$userdata ){
            $cache[$cacheID] = false;
            return false;
        }

        $user = new \WP_User;
        $user->init( $userdata );

        $cache[$cacheID] = $user;

        return $user;
    }

    public static function getTheAuthorMeta(...$args)
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'getTheAuthorMeta')) {
            return call_user_func_array([InnThemeUser::class, 'getTheAuthorMeta'], $args);
        }

        if ($args[0] === 'slug') {
            $args[0] = 'nicename';
        }

        static $cache = [];
        $cacheID = md5(json_encode($args));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\get_the_author_meta', $args);

        return $cache[$cacheID];
    }

    public static function getAuthorPostsURL($userID)
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'getAuthorPostsURL')) {
            InnThemeUser::getAuthorPostsURL($userID);
        }

        static $cache = [];

        if (isset($cache[$userID])) {
            return $cache[$userID];
        }

        $cache[$userID] = Url::esc(\get_author_posts_url($userID));

        return $cache[$userID];
    }

    public static function isUserLoggedIn()
    {
        if (class_exists(InnThemeUser::class)) {
            InnThemeUser::isUserLoggedIn();
        }

        static $cache = null;

        if ($cache === null) {
            $cache = \is_user_logged_in();
        }

        return $cache;
    }

    public static function currentUserCan(...$args)
    {
        if (class_exists(InnThemeUser::class) && method_exists(InnThemeUser::class, 'currentUserCan')) {
            return call_user_func_array([InnThemeUser::class, 'currentUserCan'], $args);
        }

        if(! static::isUserLoggedIn()){
            return false;
        }

        static $cache = [];
        $cacheID = md5(json_encode($args));

        if(! isset($cache[$cacheID])){
            $cache[$cacheID] = call_user_func_array('\current_user_can', $args);
        }

        return $cache[$cacheID];
    }
}
