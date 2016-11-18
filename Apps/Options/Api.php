<?php
namespace InnStudio\PoiAuthor\Apps\Options;

use InnStudio\PoiAuthor\Main\Core;
use InnStudio\PoiAuthor\Apps\Avalon\Api as Avalon;
use InnStudio\PoiAuthor\Apps\Security\Api as Security;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Apps\Condition\Api as Condition;
use InnStudio\PoiAuthor\Apps\Url\Api as Url;
use InnStudio\PoiAuthor\Apps\Cache\Other;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Component\Comm as Component;

class Api extends Component
{
    const ID = 'options';
    const URL_ID = 'opts';

    protected static $opts = null;

    public static function getOpts($addonID = null, $force = false)
    {
        if (static::$opts === null) {
            static::$opts = array_filter((array)Other::getOption(Core::ID));
        }

        if ($addonID) {
            return isset(static::$opts[$addonID]) ? static::$opts[$addonID] : null;
        }

        return static::$opts;
    }

    public static function getURL()
    {
        return Url::getAdmin('admin.php?page=' . Functions::buildActionName(static::URL_ID));
    }

    protected static function _saveOpts()
    {
        if (empty(static::$opts)) {
            static::$opts = static::getOpts();
        }

        if( isset($_POST['restore'])) {
            static::$opts = [];

            \delete_option(Core::ID);
        } else {
            static::$opts = \apply_filters(Functions::buildActionName('addonSaveOpts'), []);
            Other::updateOption(Core::ID, static::$opts);
        }

        return static::$opts;
    }

    public static function setAddonOpts($addonID, array $data)
    {
        if (empty(static::$opts)) {
            static::$opts = static::getOpts();
        }

        static::$opts[$addonID] = $data;
        return static::_saveOpts();
    }

    public static function deleteAddonOpts($addonID)
    {
        if (empty(static::$opts)) {
            static::$opts = static::getOpts();
        }

        if (! isset(static::$opts[$addonID])) {
            return false;
        }

        unset(static::$opts[$addonID]);

        return static::_saveOpts();
    }
}
