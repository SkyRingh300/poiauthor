<?php
namespace InnStudio\PoiAuthor\Main;

use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Cache\Other;

class Core
{
    const ID = 'poiauthor';

    public static $basename;
    public static $dir;

    public function __construct($dir)
    {
        static::$dir = $dir;
        static::$basename = basename($dir);
    }

    public static function getMetaTranslation($key = null)
    {
        $data = [
            'name' => L10n::__('Poi Author'),
            'pluginURL' => L10n::__('https://inn-studio.com/poiauthor'),
            'authorURL' => L10n::__('https://inn-studio.com'),
            'author' => L10n::__('INN STUDIO'),
            'qqGroup' => [
                'number' => '170306005',
                'link' => 'https://jq.qq.com/?_wv=1027&k=41RRWxk',
            ],
            'email' => 'kmvan.com@gmail.com',
            'edition' => L10n::__('Professional edition'),
            'des' => L10n::__('A better performance author meta box instead of the build-in of WordPress.'),
        ];

        if ($key) {
            return isset($data[$key]) ? $data[$key] : false;
        }

        return $data;
    }
}
