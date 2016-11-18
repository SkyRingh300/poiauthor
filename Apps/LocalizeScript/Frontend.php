<?php
namespace InnStudio\PoiAuthor\Apps\LocalizeScript;

use InnStudio\PoiAuthor\Apps\Snippets\Functions;

class Frontend extends Api
{
    public function __construct()
    {
        \add_action('wp_enqueue_scripts', [$this, 'confFrontend']);
    }

    public function confFrontend()
    {
        \wp_localize_script(Functions::buildActionName('frontend'), $this->getConfId(), $this->getConf());
    }
}
