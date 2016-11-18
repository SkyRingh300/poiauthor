<?php
namespace InnStudio\PoiAuthor\Addons\Admin;

use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Main\Api as MainApi;
use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Apps\Cache\Other;
use InnStudio\PoiAuthor\Main\Core;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Security\Api as Security;


class MetaBox extends Api
{
    public function __construct()
    {
        $this->setAdmin();
        $this->setAdminDynamicConf();
    }

    public function dynamicConfAdmin(array $conf)
    {
        if (Other::getCurrentScreen()->base !== 'post') {
            return $conf;
        }

        global $post;

        $conf[static::getOptID()] = [
            'id' => Functions::buildActionName(static::getOptID()),
            'lang' => [
                'typeAuthorNameForSearch' => L10n::__('Type author name for search'),
                'authorId' => L10n::__('Author ID'),
            ],
            'postId' => $post->ID,
            'authorId' => $post->post_author,
            'authorName' => User::getTheAuthorMeta('display_name', $post->post_author),
        ];

        return $conf;
    }

    public function displayAdmin()
    {
        $this->hookRemoveMetaBox();
        \add_action('add_meta_boxes', [$this, 'actionAddMetaBoxes']);
    }

    public function hookRemoveMetaBox()
    {
        \add_action('admin_menu', [$this, 'actionRemoveMetaBox']);
    }

    public function actionRemoveMetaBox()
    {
        foreach (static::SCREENS as $v) {
            \remove_meta_box('authordiv', $v, 'normal');
        }
    }

    public function actionAddMetaBoxes()
    {
        foreach (static::SCREENS as $screen) {
            \add_meta_box(
                static::getOptID(),
                Core::getMetaTranslation('name') .
                ' <a href="' . Core::getMetaTranslation('pluginURL') . '" target="_blank">' . Core::getMetaTranslation('author') . '</a>',
                [$this, 'displayMetaBox'],
                $screen,
                'side'
            );
        }
    }

    public function displayMetaBox($post)
    {
        ?>
        <div id="<?= Core::ID;?>-metabox-container">
            <i class="fa fa-refresh fa-spin fa-fw"></i>
        </div>
        <?php
    }
}
