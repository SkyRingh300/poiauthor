<?php
namespace InnStudio\PoiAuthor\Addons\Admin;

use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Security\Api as Security;

class Ajax extends Api
{
    public function __construct()
    {
        $this->setAjaxLogged();
    }

    public function ajaxProcessLogged()
    {
        Security::checkNonce();
        Security::checkReferer();

        switch (filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING)) {
            case 'searchUsers':
                $this->ajaxSearchUsers();
                break;

            case 'getUser':
                $this->ajaxGetUser();
                break;
        }


    }

    private function ajaxSearchUsers()
    {
        $userName = filter_input(INPUT_GET, 'userName', FILTER_SANITIZE_STRING);

        if (! $userName) {
            return;
        }

        \add_filter('user_search_columns', function($cols) {
            if (! in_array('display_name', $cols)) {
                $cols[] = 'display_name';
            }

            return $cols;
        }, 10, 3);

        $query = new \WP_User_Query([
            'search' => "*{$userName}*",
            'search_columns' => [
                'display_name',
                'user_login'
            ],
            'fields' => [
                'ID',
            ],
            'orderby' => 'ID'
        ]);

        $users = $query->get_results();

        if (! $users) {
            return;
        }

        Functions::jsonOutput([
            'status' => 'success',
            'users' => array_map(function($user) {
                return [
                    'id' => $user->ID,
                    'name' => User::getTheAuthorMeta('display_name', $user->ID),
                    //'url' => User::getAuthorPostsURL($user->ID),
                ];
            }, $users),
        ], true);
    }

    private function ajaxGetUser()
    {
        $userId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT);
        if (! $userId) {
            return;
        }

        $user = User::getUserBy('id', $userId);

        if (! $user) {
            return;
        }

        Functions::jsonOutput([
            'status' => 'success',
            'user' => [
                'name' => User::getTheAuthorMeta('display_name', $user->ID),
                //'url' => User::getAuthorPostsURL($user->ID),
            ],
        ], true);
    }
}

