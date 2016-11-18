<?php
namespace InnStudio\PoiAuthor\Apps\Tag;

interface tagInterface
{
    public static function getTheTags($postID);
    public static function getTagBySlug($slug);
    public static function getCurrentTagID();
    public static function getCurrentTagObj();
}

class Api implements tagInterface
{
    public static function getTheTags($postID)
    {
        static $cache = [];

        if (! isset($cache[$postID])) {
            $cache[$postID] = \get_the_tags($postID);
        }

        return $cache[$postID];
    }
    public static function getTagBySlug($slug)
    {
        static $cache = [];

        if (! isset($cache[$slug])) {
            $cache[$slug] = \get_term_by('slug', $slug, 'post_tag');
        }

        return $cache[$slug];

    }

    public static function getCurrentTagID()
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        global $wp_query;
        $cache = $wp_query->query_vars['tag_id'];

        return $cache;
    }

    public static function getCurrentTagObj()
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = \get_tag(static::getCurrentTagID());

        return $cache;
    }
}
