<?php
namespace InnStudio\PoiAuthor\Requires;

use InnStudio\PoiAuthor\Main\Core;

class Addons
{
    const items = [
        'Admin'
    ];

    public function __construct()
    {
        foreach (self::items as $item) {
            $item = "\\InnStudio\\PoiAuthor\\Addons\\{$item}\\{$item}";
            new $item();
        }
    }
}
