<?php
namespace InnStudio\PoiAuthor\Apps\Language;

use InnStudio\PoiAuthor\Main\Core;

class Language
{
    public function __construct()
    {
        \load_plugin_textdomain(Core::ID, null, Core::$basename . '/Languages');
    }
}
