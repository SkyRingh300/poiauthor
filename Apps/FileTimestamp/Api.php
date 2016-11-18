<?php
namespace InnStudio\PoiAuthor\Apps\FileTimestamp;

use InnStudio\PoiAuthor\Apps\Cache\Other;
use InnStudio\PoiAuthor\Apps\Component\Comm as Component;

class Api extends Component
{
    const ID = 'fileTimestamp';

    public static function getTimestamp()
    {
        static $timestamp = null;

        if ($timestamp === null) {
            $timestamp = Other::getPlugin('Version');
        }

        return $timestamp;
    }

    public static function setTimestamp($value = null)
    {
        if (function_exists('\opcache_reset')) {
            \opcache_reset();
        }

        return $value;
    }
}
