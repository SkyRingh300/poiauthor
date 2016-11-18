<?php
namespace InnStudio\PoiAuthor\Apps\Post;

use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Url\Api as Url;
use InnStudio\PoiAuthor\Apps\Cache\Other;
use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Apps\Category\Api as Category;
use InnStudio\PoiAuthor\Apps\Condition\Api as Condition;
use InnStudio\PoiAuthor\Apps\ImgPlaceholder\Api as ImgPlaceholder;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Main\Core;

class Api
{
    const ID = 'post';
    const returnCode = [
        'noPostFound' => 30001,
    ];

    public static function getTranslationStatus($status)
    {
        static $cache = null;

        if ($cache === null) {
            $cache = \get_post_statuses();
        }

        return isset($cache[$status]) ? $cache[$status] : $status;
    }

    public static function getQuery(array $args = [],array $queryArgs = [])
    {
        $args = array_merge([
            'orderby' => 'views',
            'order' => 'desc',
            'posts_per_page' => Other::getOption('posts_per_page'),
            'paged' => 1,
            'category__in' => [],
            'date' => 'all',
        ],$args);

        $queryArgs = array_merge([
            'posts_per_page' => (int)$args['posts_per_page'],
            'paged' => (int)$args['paged'],
            'ignore_sticky_posts' => true,
            'category__in' => (array)$args['category__in'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'has_password' => false,
        ], $queryArgs);

        switch($args['orderby'])
        {
            case 'views':
                if (class_exists('\InnStudio\PoiAuthor\Apps\PostViews\PostViews') && \InnStudio\PoiAuthor\Apps\PostViews\Api::isEnabled()) {
                    $queryArgs['meta_key'] = \InnStudio\PoiAuthor\Apps\PostViews\Api::postMetaKey;
                    $queryArgs['orderby'] = 'meta_value_num';
                }
                break;

            case 'thumb-up':
            case 'thumb':
            case 'aproval':
                if (class_exists('\InnStudio\PoiAuthor\Apps\PostApproval\PostApproval') && \InnStudio\PoiAuthor\Apps\PostApproval\Api::isEnabled()) {
                    $queryArgs['meta_key'] = \InnStudio\PoiAuthor\Apps\PostApproval\Api::postMetaKey['approvalCount'];
                    $queryArgs['orderby'] = 'meta_value_num';
                }
                break;

            case 'rand':
            case 'random':
                $queryArgs['orderby'] = 'rand';
                break;

            case 'comment':
                $queryArgs['orderby'] = 'comment_count';
                break;

            case 'recomm':
            case 'recommended':
                if( class_exists('\InnStudio\PoiAuthor\Apps\RecommPost\RecommPost') && \InnStudio\PoiAuthor\Apps\RecommPost\Api::isEnabled()) {
                    $queryArgs['post__in'] = (array)\InnStudio\PoiAuthor\Apps\RecommPost\Api::getIDs();
                }else{
                    $queryArgs['post__in'] = (array)Other::getOption('sticky_posts');
                    unset($queryArgs['ignore_sticky_posts']);
                }
                unset($queryArgs['post__not_in']);
                break;

            default:
                $queryArgs['orderby'] = 'date';
        }

        if ($args['date'] && $args['date'] !== 'all') {
            switch($args['date']){
                case 'daily' :
                    $after = 'day';
                    break;

                case 'weekly' :
                    $after = 'week';
                    break;

                case 'monthly' :
                    $after = 'month';
                    break;

                default:
                    $after = 'day';
            }

            $queryArgs['date_query'] = [[
                'column' => 'post_date_gmt',
                'after'  => '1 ' . $after . ' ago',
            ]];
        }

        return new \WP_Query($queryArgs);
    }

    public static function getPostMeta(...$args)
    {
        if (isset($args[3]) && $args[3]) {
            unset($args[3]);

            return Functions::metaToJson(call_user_func_array('\get_post_meta', $args));
        }

        static $cache = [];
        $cacheID = md5(json_encode($args));

        if (! isset($cache[$cacheID])) {
            $cache[$cacheID] = Functions::metaToJson(call_user_func_array('\get_post_meta', $args));
        }

        return $cache[$cacheID];
    }

    public static function updatePostMeta(...$args)
    {
        $args[2] = addslashes(json_encode($args[2]));

        if (isset($args[3])) {
            $args[3] = addslashes(json_encode($args[3]));
        }

        return call_user_func_array('\update_post_meta', $args);
    }

    public static function getPost($postID, $force = false)
    {
        if (! $postID) {
            return false;
        }

        if ($force) {
            return \get_post($postID);
        }

        static $cache = [];

        if (! isset($cache[$postID])) {
            $cache[$postID] = \get_post($postID);
        }

        return $cache[$postID];
    }

    public static function getTheTitle($postID, $leavename = false)
    {
        static $cache = [];

        if (isset($cache[$postID])) {
            return $cache[$postID];
        }

        global $post;

        if (!isset($post->ID) || $post->ID != $postID) {
            $p = static::getPost($postID);
        } else {
            $p = $post;
        }

        $title = isset($p->post_title) ? $p->post_title : '';

        if (! Condition::isAdmin()) {
            if(! empty($p->post_password)){
                $title = sprintf(\apply_filters('protected_title_format', __('Protected: %s'), $p), $title);
            } elseif (isset($p->post_status) && 'private' == $p->post_status) {
                $title = sprintf(\apply_filters('private_title_format', __('Private: %s'), $p), $title);
            }
        }
        unset($p);
        $cache[$postID] = htmlspecialchars($title);

        return $cache[$postID];
    }

    public static function getHumanDate($postID)
    {
        return Functions::getHumanDate(strtotime(static::getPost($postID)->post_date));
    }

    public static function getPermalink($postID, $leavename = false)
    {
        static $cache = [];

        if (isset($cache[$postID])) {
            return $cache[$postID];
        }

        global $post;

        if (!isset($post->ID) || $post->ID != $postID) {
            $p = static::getPost($postID);
        } else {
            $p = $post;
        }

        $rewritecode = [
            '%year%',
            '%monthnum%',
            '%day%',
            '%hour%',
            '%minute%',
            '%second%',
            $leavename ? '' : '%postname%',
            '%post_id%',
            '%category%',
            '%author%',
            $leavename ? '' : '%pagename%',
        ];


        if ($p->post_type == 'page') {
            return Url::esc(\get_page_link($p, $leavename));
        } elseif ($p->post_type == 'attachment') {
            return Url::esc(\get_attachment_link($p, $leavename));
        } elseif (in_array($p->post_type, \get_post_types(['_builtin' => false]))) {
            return Url::esc(\get_post_permalink($p, $leavename));
        }

        $permalink = Other::getOption('permalink_structure');
        $permalink = \apply_filters('pre_post_link', $permalink, $p, $leavename);

        if ('' != $permalink && !in_array($p->post_status, ['draft', 'pending', 'auto-draft', 'future'])) {
            $unixtime = strtotime($p->post_date);

            $category = '';
            if (strpos($permalink, '%category%') !== false) {
                $cats = Category::getTheCategory($p->ID);
                if ($cats) {
                    usort($cats, '_usort_terms_by_ID'); // order by ID

                    /**
                     * Filter the category that gets used in the %category% permalink token.
                     *
                     * @since 3.5.0
                     *
                     * @param stdClass $cat  The category to use InnStudio\PoiAuthor\in the permalink.
                     * @param array    $cats Array of all categories associated with the post.
                     * @param WP_Post  $post The post in question.
                     */
                    $category_object = \apply_filters('post_link_category', $cats[0], $cats, $p);

                    $category_object = \get_term($category_object, 'category');
                    $category = $category_object->slug;
                    if ($parent = $category_object->parent) {
                        $category = \get_category_parents($parent, false, '/', true) . $category;
                    }
                }
                // show default category in permalinks, without
                // having to assign it explicitly
                if (empty($category)) {
                    $default_category = \get_term(Other::getOption('default_category'), 'category');
                    $category = \is_wp_error($default_category) ? '' : $default_category->slug;
                }
            }

            $author = '';
            if (strpos($permalink, '%author%') !== false) {
                $authordata = User::getUserdata($p->post_author);
                $author = $authordata->user_nicename;
            }

            $date = explode(" ",date('Y m d H i s', $unixtime));
            $rewritereplace =
            array(
                $date[0],
                $date[1],
                $date[2],
                $date[3],
                $date[4],
                $date[5],
                $p->post_name,
                $p->ID,
                $category,
                $author,
                $p->post_name,
           );
            $permalink = Url::getHome(str_replace($rewritecode, $rewritereplace, $permalink));
            $permalink = \user_trailingslashit($permalink, 'single');
        } else { // if they're not using the fancy permalink option
            $permalink = Url::getHome() . '?p=' . $p->ID;
        }

        /**
         * Filter the permalink for a post.
         *
         * Only applies to posts with post_type of 'post'.
         *
         * @since 1.5.0
         *
         * @param string  $permalink The post's permalink.
         * @param WP_Post $post      The post in question.
         * @param bool    $leavename Whether to keep the post name.
         */
        $cache[$postID] = Url::esc(\apply_filters('post_link', $permalink, $p, $leavename));
        unset($p);
        return $cache[$postID];
    }

    public static function getTheExcerpt($postID, $len = 120, $extra = '...')
    {
        static $cache = [];

        if (isset($cache[$postID])) {
            return $cache[$postID];
        }

        $post = static::getPost($postID);
        $excerpt = \get_the_excerpt($postID);

        if ($excerpt) {
            $cache[$postID] = Functions::subStr($excerpt, $len, $extra);
        } else {
            $cache[$postID] = Functions::subStr(str_replace("\n", '', strip_tags($post->post_content)), $len, $extra);
        }

        return $cache[$postID];
    }

    public static function getThumbnailURL($postID, $size = 'thumbnail', $placeholderURL = null){
        if (! $placeholderURL) {
            $placeholderURL = ImgPlaceholder::getThumbnailPlaceholderURL();
        }

        $imgURL = null;

        /** check sinapicv2 thumbnail */
        if (class_exists('\sinapicv2\sinapicv2')) {
            $imgURL = \sinapicv2\sinapicv2::get_post_thumbnail_url($postID, true);
        } else {
            if (\has_post_thumbnail($postID)) {
                $thumbnailID = \get_post_thumbnail_id($postID);

                $imgURL = \wp_get_attachment_image_src($thumbnailID, $size)[0];

                if ($size !== 'thumbnail') {
                    if (\wp_get_attachment_image_src($thumbnailID, 'full')[0] === $imgURL) {
                        $imgURL = \wp_get_attachment_image_src($thumbnailID, 'thumbnail')[0];
                    }
                }
            }
        }

        if (! $imgURL) {
            $post = static::getPost($postID);
            $imgURL = $post ? Functions::getImgURL($post->post_content) : false;
            unset($post);
        }

        if (! $imgURL) {
            $imgURL = $placeholderURL;
        }

        return $imgURL;
        //if (\is_ssl() && stripos($imgURL, 'https://')) {

        //}

        //return stripos(Other::getBlogInfo('home')) Url::esc($imgURL);
    }

    public static function getPreviousThumbnailURL($placeholderURL = null, $size = 'thumbnail')
    {
        $prevPost = \get_previous_post(true);

        if ($prevPost->ID) {
            $URL = static::getThumbnailURL($prevPost->ID, $size, $placeholderURL);
        } else {
            $URL = null;
        }

        return $URL;
    }

    public static function getNextThumbnailURL($placeholderURL = null, $size = 'thumbnail')
    {
        $prevPost = \get_next_post(true);

        if ($prevPost->ID) {
            $URL = static::getThumbnailURL($prevPost->ID, $size, $placeholderURL);
        } else {
            $URL = null;
        }

        return $URL;
    }
}
