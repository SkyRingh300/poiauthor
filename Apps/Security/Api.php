<?php
namespace InnStudio\PoiAuthor\Apps\Security;

use InnStudio\PoiAuthor\Apps\Url\Api as Url;
use InnStudio\PoiAuthor\Apps\Component\Comm as Component;

class Api extends Component
{
    const ID = 'security';

    public static function checkReferer($limitURL = null)
    {
        if (! $limitURL) {
            $limitURL = Url::getHome();
        }

        if (! isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], $limitURL) !== 0) {
            die(header($_SERVER['SERVER_PROTOCOL'] . ' 403', true));
        }
    }

    public static function createNonce($action = 'ajax', $focus = false)
    {
        if ($focus) {
            return \wp_create_nonce($action);
        }

        static $cache = [];

        if (! isset($cache[$action])) {
            $cache[$action] = \wp_create_nonce($action);
        }

        return $cache[$action];
    }

    public static function checkNonce(array $args = [])
    {
        $args = array_merge([
            'action' => 'ajax',
            'query' => '_nonce',
            'die' => true,
        ], $args);

        call_user_func_array('\check_ajax_referer', $args);
    }
}
