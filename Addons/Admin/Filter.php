<?php
namespace InnStudio\PoiAuthor\Addons\Admin;

use InnStudio\PoiAuthor\Apps\Cache\Other;

class Filter extends Api
{
    public function __construct()
    {
        $this->setAdmin('hookWpDropdownUsers');
    }

    public function hookWpDropdownUsers()
    {
        \add_filter('wp_dropdown_users', [$this, 'filterWpDropdownUsers']);
    }

    public function filterWpDropdownUsers($html)
    {
        if (Other::getCurrentScreen()->base !== 'edit') {
            return $html;
        }

        ?>
        <select style="display:none;" class="authors poiauthor-author-id" name="post_author"></select>
        <?php
    }
}
