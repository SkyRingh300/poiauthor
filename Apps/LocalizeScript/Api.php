<?php
namespace InnStudio\PoiAuthor\Apps\LocalizeScript;

use InnStudio\PoiAuthor\Main\Core;
use InnStudio\PoiAuthor\Apps\Condition\Api as Condition;
use InnStudio\PoiAuthor\Apps\Security\Api as Security;
use InnStudio\PoiAuthor\Apps\Url\Api as Url;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Component\Comm as Component;

class Api extends Component
{
    const ID = 'localizeScript';

    public function getConfId()
    {
        return strtoupper(Core::ID . '_CONF');
    }

    protected function getConf()
    {
        $conf = [
            'ID' => Core::ID,
            'ajaxURL' => Url::getAjax(),
            'lang' => [
                'loading' => L10n::__('Loading, please wait...'),
                'error' => L10n::__('Sorry, server is busy now, can not respond your request, please try again later.'),
                'close' => L10n::__('Close'),
                'ok' => L10n::__('OK'),
            ],
        ];

        if (Condition::isAdmin()) {
            $conf['_nonce'] = Security::createNonce();
        }

        return \apply_filters(Functions::buildActionName('dynamicConf'), $conf);
    }
}
