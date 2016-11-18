<?php
namespace InnStudio\PoiAuthor\Apps\Options;

use InnStudio\PoiAuthor\Main\Core;
use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Url\Api as Url;
use InnStudio\PoiAuthor\Apps\Cache\Other;
use InnStudio\PoiAuthor\Apps\Security\Api as Security;

class Admin extends Api
{
    public function __construct()
    {
        $this->addMenu();
    }

    private function addMenu()
    {
        if (User::isUserLoggedIn() && User::currentUserCan('edit_themes')) {
            \add_action('admin_menu', [$this, 'addPage'], 2);
            \add_filter('plugin_action_links', [$this, 'filterActionLink'], 10, 2);

        }
    }

    public function filterActionLink($actions, $pluginFile)
    {
        if (stripos($pluginFile, Core::$basename) !== false) {
            $opts = '<a href="' . $this->getURL() . '">' . L10n::__('Options') . '</a>';

            array_unshift($actions, $opts);
        }

        return $actions;
    }

    public function addPage()
    {
        \add_menu_page(
            sprintf(L10n::__('%s options'), Core::getMetaTranslation('name')),
            sprintf(L10n::__('%s options'), Core::getMetaTranslation('name')),
            'edit_themes',
            Functions::buildActionName(static::URL_ID),
            [$this, 'displayBackend'],
            'dashicons-hammer',
            66
        );
    }

    public function displayBackend()
    {
        ?>
        <div class="wrap <?= Core::ID;?>-wrap">
        <form class="backend-fm <?= Core::ID;?>-backend-fm" method="post" action="<?= Url::getAjax(static::getOptID(), [
            '_nonce' => Security::createNonce(),
        ]);?>">
        <div class="tab-nav-container <?= Core::ID;?>-tab-nav-container"></div>

        <div class="tab-body">
            <?php
            $settings = \apply_filters(Functions::buildActionName('backendSettings'), []);

            ksort($settings);

            foreach ($settings as $legend => $setting) {
                ?>
                <fieldset>
                    <legend class="button button-primary">
                        <i class="fa fa-fw fa-<?= $setting['icon'];?>"></i> <?= $setting['title'];?>
                    </legend>
                    <?php call_user_func($setting['content']);?>
                </fieldset>
            <?php } ?>

        </div>

        <p>
            <input type="hidden" name="_nonce" value="<?= Security::createNonce();?>">

            <button id="submit" type="submit" class="backend-submit button button-primary"><i class="fa fa-check"></i> <span class="tx"><?= L10n::__('Save');?></span></button>

            <label for="options-restore" class="label-options-restore" title="<?= L10n::__('Something error with plugin? Try to restore. Be careful, plugin options will be cleared up!');?>">
                <input id="options-restore" name="restore" type="checkbox" value="1"/>
                <?= L10n::__('Restore to default options');?> <i class="fa fa-question-circle"></i>
            </label>
        </p>
        </form>
        </div>
        <?php
    }
}
