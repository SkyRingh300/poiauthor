<?php
namespace InnStudio\PoiAuthor\Apps\Page;

use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Post\Api as Post;
use InnStudio\PoiAuthor\Apps\Cache\Other;

interface pageInterface
{
    public static function getPageByPath($pagePath);
    public static function getCurrentPageID();
    public static function getPageURLBySlug($slug);
    public static function getPageIDBySlug($slug);
    public static function pageOptsList($addonID, $optID);
}

class Api implements pageInterface
{
    public static function createPage(array $pages)
    {
        if (! User::currentUserCan('manage_options')) {
            return false;
        }

        foreach ($pages as $pagePath => $v){
            static::getPageByPath($pagePath) || \wp_insert_post(array_merge([
                'post_content' => '',
                'post_name' => null,
                'post_title' => null,
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed',
            ], $v));
        }
    }

    public static function getCurrentPageID()
    {
        global $page_id;

        if (! $page_id) {
            global $post;
            return $post->ID;
        }

        return $page_id;
    }

    public static function getPageURLBySlug($slug)
    {
        static $cache = [];

        if (isset($cache[$slug])) {
            return $cache[$slug];
        }

        $postID = static::getPageIDBySlug($slug);
        $cache[$slug] = Post::getPermalink($postID);

        return $cache[$slug];
    }

    public static function getPageIDBySlug($slug)
    {
        static $cache = [];

        if (isset($cache[$slug])) {
            return $cache[$slug];
        }

        $page = \get_page_by_path($slug);

        if ($page) {
            $cache[$slug] = $page->ID;
        } else {
            $cache[$slug] = false;
        }

        return $cache[$slug];
    }

    public static function getPageByPath($pagePath)
    {
        $cacheID = 'pagePath';
        $posts = array_filter((array)\wp_cache_get($cacheID));

        if (isset($posts[$pagePath])) {
            return Post::getPost($posts[$pagePath]);
        }

        $post = call_user_func_array('\get_page_by_path', func_get_args());

        if (isset($post->ID)) {
            $posts[$pagePath] = $post->ID;
            \wp_cache_set($cacheID, $posts);
            return $post;
        }

        return false;
    }

    public static function pageOptsList($addonID, $optID)
    {
        static $pages = null;
        if ($pages === null) {
            $pages = \get_pages();
        }

        $opt = Other::getOpts($addonID);
        $selectedPageID = isset($opt[$optID]) ? (int)$opt[$optID] : null;
        ?>
        <select name="<?= $addonID;?>[<?= $optID;?>]" id="<?= $addonID;?>-<?= $optID;?>">
            <option value="-1"><?= L10n::__('Select page');?></option>
            <?php
            foreach($pages as $page){
                if($selectedPageID == $page->ID){
                    $selected = ' selected ';
                }else{
                    $selected = null;
                }
                ?>
                <option value="<?= $page->ID;?>" <?= $selected;?>><?= Post::getTheTitle($page->ID);?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }
}
