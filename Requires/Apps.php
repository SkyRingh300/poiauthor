<?php
namespace InnStudio\PoiAuthor\Requires;

use InnStudio\PoiAuthor\Main\Core;

class Apps
{
    const items = [
        'Language',
        //'Options',
        'Update',
        'AssetEnqueue',
        'LocalizeScript',
        //'Help'
    ];

    public function __construct()
    {
        foreach (self::items as $item) {
            $item = "\\InnStudio\\PoiAuthor\\Apps\\{$item}\\{$item}";
            new $item();
        }
    }
}
