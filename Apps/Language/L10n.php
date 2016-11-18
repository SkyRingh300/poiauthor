<?php
namespace InnStudio\PoiAuthor\Apps\Language;

use InnStudio\PoiAuthor\Main\Core;

interface l10nInterface
{
    public static function _x($text, $context, $domain = null);
    public static function _nx($single, $plural, $number, $domain = null);
    public static function _n($single, $plural, $number, $domain = null);
    public static function __($text, $domain = null);
}

class L10n implements l10nInterface
{
    public static function _x($text, $context, $domain = null)
    {
        if (!$domain) {
            $domain = Core::ID;
        }

        return \translate_with_gettext_context( $text, $context, $domain );
    }

    public static function _nx($single, $plural, $number, $domain = null)
    {
        if (!$domain) {
            $domain = Core::ID;
        }

        return \_nx($single, $plural, $number, $domain);
    }

    public static function _n($single, $plural, $number, $domain = null)
    {
        if (!$domain) {
            $domain = Core::ID;
        }

        return \_n($single, $plural, $number, $domain);
    }

    public static function __($text, $domain = null)
    {
        if (!$domain) {
            $domain = Core::ID;
        }

        return \translate($text, $domain);
    }
}
