<?php
namespace InnStudio\PoiAuthor\Apps\Page;

class Filter
{
    public function __construct()
    {
        \add_action('delete_post', [$this, 'deletePagePathCache']);
        \add_action('save_post', [$this, 'deletePagePathCache']);
    }

    public function deletePagePathCache($postID)
    {
        \wp_cache_delete('pagePath');
    }
}
