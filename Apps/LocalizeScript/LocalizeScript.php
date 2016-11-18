<?php
namespace InnStudio\PoiAuthor\Apps\LocalizeScript;

class LocalizeScript
{
    public function __construct()
    {
        new Frontend();
        new Admin();
        //new Backend();
    }
}
