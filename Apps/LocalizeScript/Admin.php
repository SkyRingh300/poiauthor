<?php
namespace InnStudio\PoiAuthor\Apps\LocalizeScript;

use InnStudio\PoiAuthor\Apps\Snippets\Functions;

class Admin extends Api
{
    public function __construct()
    {
        \add_action('admin_enqueue_scripts', [$this, 'confBackend']);
    }

    public function confBackend()
    {
        \wp_localize_script(Functions::buildActionName('backend'), $this->getConfId(), $this->getConf());
    }
}
