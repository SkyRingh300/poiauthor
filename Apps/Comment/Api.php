<?php
namespace InnStudio\PoiAuthor\Apps\Comment;

use InnStudio\PoiAuthor\Apps\Cache\Other;
use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\Theme\Apps\Comment\Api as InnThemeComment;

class Api
{
    public static function getCommentAuthorURL($commentID)
    {
        if (class_exists(InnThemeComment::class) && method_exists(InnThemeComment::class, 'getCommentAuthorURL')) {
            return InnThemeComment::getCommentAuthorURL($commentID);
        }
        
        static $cache = [];

        if (! isset($cache[$commentID])) {
            $cache[$commentID] = \get_comment_author_url($commentID);
        }

        return $cache[$commentID];
    }

    public static function getComment($commentID)
    {
        if (class_exists(InnThemeComment::class) && method_exists(InnThemeComment::class, 'getComment')) {
            return InnThemeComment::getComment($commentID);
        }
        
        static $cache = [];

        if (! isset($cache[$commentID])) {
            $cache[$commentID] = \get_comment($commentID);
        }

        return $cache[$commentID];
    }

    public static function getCommentAuthor($commentID)
    {
        if (class_exists(InnThemeComment::class) && method_exists(InnThemeComment::class, 'getCommentAuthor')) {
            return InnThemeComment::getCommentAuthor($commentID);
        }
        
        static $cache = [];

        if (! isset($cache[$commentID])) {
            $cache[$commentID] = htmlspecialchars(\get_comment_author($commentID));
        }

        return $cache[$commentID];
    }

    public static function getPagesCount($comments)
    {
        if (class_exists(InnThemeComment::class) && method_exists(InnThemeComment::class, 'getPagesCount')) {
            return InnThemeComment::getPagesCount($comments);
        }
        
        static $count = null;

        if ($count === null) {
            $count = \get_comment_pages_count(
                $comments,
                Other::getOption('comments_per_page'),
                Other::getOption('thread_comments')
            );
        }

        return $count;
    }
}
