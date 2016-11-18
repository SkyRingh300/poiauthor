<?php
namespace InnStudio\PoiAuthor\Apps\Category;

use InnStudio\PoiAuthor\Apps\Options\Api as Options;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Url\Api as Url;

interface categoryInterface
{
    public static function getTermURL($termID);
    public static function getTheCategory($postID);
    public static function getCategory($category, $output = OBJECT, $filter = 'raw');
    public static function getCategories(array $args = []);

    public static function getCurrentCatID();
    public static function getCurrentCatName();
    public static function getCurrentCatSlug();

    public static function getCatRootID($catID = null);
    public static function getCatRootSlug($catSlug);

    public static function getCatIDBySlug($catSlug);
    public static function getCatSlugByID($catID);

    public static function getCatsByChild($childID, array & $limitCatIDs);
    public static function catCheckboxList($id, $name, array $selected = []);
}

class Api implements categoryInterface
{
    const returnCode = [
        'noCategoryFound' => 40001,
    ];

    public static function getTermURL($termID)
    {
        static $cache = [];

        if (isset($cache[$termID])) {
            return $cache[$termID];
        }

        $cache[$termID] = Url::esc(\get_term_link($termID));

        return $cache[$termID];
    }

    public static function getCategories(array $args = [])
    {
        static $cache = [];
        $cacheID = md5(json_encode($args));

        if (! isset($cache[$cacheID])) {
            $cache[$cacheID] = \get_categories($args);
        }

        return $cache[$cacheID];
    }

    public static function getTheCategory($postID)
    {
        static $cache = [];

        if (isset($cache[$postID])) {
            return $cache[$postID];
        }

        $cache[$postID] = \get_the_category($postID);

        return $cache[$postID];
    }
    public static function getCategory($category, $output = OBJECT, $filter = 'raw')
    {
        static $cache = [];
        $cacheID = md5(json_encode(func_get_args()));

        if (isset($cache[$cacheID])) {
            return $cache[$cacheID];
        }

        $cache[$cacheID] = call_user_func_array('\get_category', func_get_args());

        return $cache[$cacheID];
    }

    public static function getCurrentCatID()
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        global $cat,$post;

        if ($cat) {
            $catObj = static::getCategory($cat);
            $cache = $catObj->term_id;
        } elseif($post) {
            $cache = static::getTheCategory($post->ID)[0]->cat_ID;
        }

        return $cache;
    }

    public static function getCurrentCatName()
    {
        return static::getCategory(static::getCurrentCatID())->name;
    }

    public static function getCurrentCatSlug()
    {
        return static::getCategory(static::getCurrentCatID())->slug;
    }

    public static function getCatRootID($catID = null)
    {
        if (!$catID) {
            $catID = static::getCurrentCatID();
        }

        $catParentObj = static::getCategory($catID);
        $catParentID = $catParentObj->category_parent;

        if ($catParentID == 0) {
            return $catID;
        } else {
            return static::getCatRootID($catParentID);
        }
    }

    public static function getCatRootSlug($catID)
    {
        return static::getCategory(static::getCatRootID)->slug;
    }

    public static function getCatIDBySlug($catSlug)
    {
        static $cache = [];

        if (isset($cache[$catSlug])) {
            return $cache[$catSlug];
        }

        $cache[$catSlug] = \get_category_by_slug($catSlug)->term_id;

        return $cache[$catSlug];
    }

    public static function getCatSlugByID($catID)
    {
        return static::getCategory($catID)->slug;
    }

    public static function getCatsByChild($childID, array & $limitCatIDs)
    {
        $cat = static::getCategory($childID);

        if (!$cat) {
            return false;
        }

        $limitCatIDs[] = $childID;

        if ($cat->parent != 0) {
            return static::getCatsByChild(static::getCategory($cat->parent)->term_id, $limitCatIDs);
        }
    }

    public static function catOptsList($addonID, $catID, $child = false){
        $opts = (array)Options::getOpts($addonID);

        if ($child !== false) {
            $selectedCatID = isset($opts[$catID][$child]) && $opts[$catID][$child] != 0 ? $opts[$catID][$child] : null;
        } else {
            $selectedCatID = isset($opts[$catID]) && $opts[$catID] != 0 ? $opts[$catID] : null;
        }
        $args = [
            'name' => $child !== false ? $addonID . '[' . $catID . '][' . $child . ']' : $addonID . '[' . $catID . ']',
            'name' => $child !== false ? "${addonID}[${catID}][${child}]" : $addonID . '[' . $catID . ']',
            'id' => $child !== false ? $addonID . '-' . $catID . '-' . $child : $addonID . '-' . $catID,
            'show_option_none' => L10n::__('Select category'),
            'hierarchical' => 1,
            'hide_empty' => false,
            'selected' => $selectedCatID,
            'echo' => 1,
        ];
        \wp_dropdown_categories($args);
    }

    public static function catCheckboxList($id, $name, array $selected = [])
    {
        $liID = "$id-category-";
        $inputID = "$id-in-";

        ob_start();
        \wp_category_checklist( 0, 0, $selected, false, null, false );
        $content = ob_get_contents();
        ob_end_clean();

        /** replace <li> id */
        $content = str_replace("li id='category-", "li id='" . $liID, $content);

        /** replace input id */
        $content = str_replace('id="in-', 'id="' . $inputID, $content);

        /** repalce input name */
        $content = str_replace('name="post_category[]', 'name="' . $name, $content);

        echo $content;
    }
}
