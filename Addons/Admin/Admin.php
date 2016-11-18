<?php
namespace InnStudio\PoiAuthor\Addons\Admin;

class Admin
{
    public function __construct()
    {
        new Filter();
        new Ajax();
        new MetaBox();
    }
}
