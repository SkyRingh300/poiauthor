<?php
namespace InnStudio\PoiAuthor\Apps\AssetEnqueue;

use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Url\Api as Url;

class Frontend extends Api
{
    public function __construct()
    {
        $this->setFrontend('frontendAssets');
    }

    public function frontendAssets()
    {
        \add_action('wp_enqueue_scripts', [$this, 'frontendEnqueueScripts']);
        \add_action('wp_enqueue_scripts', [$this, 'frontendEnqueueCss']);
    }

    public function frontendEnqueueScripts()
    {
        $js = [
            Functions::buildActionName('frontend') => [
                'deps' => [],
                'url' => Url::getJsUrl('frontend'),
            ],

        ];

        foreach($js as $k => $v){
            \wp_enqueue_script(
                $k,
                $v['url'],
                isset($v['deps']) ? $v['deps'] : [],
                $this->getVersion($v)
            );
        }

    }

    public function frontendEnqueueCss(){
        $css = [
            Functions::buildActionName('awesome') => [
                'deps' => [],
                'url' => $this->getAwesomeURL(),
                'version' => null,
            ],
            Functions::buildActionName('frontend') => [
                'deps' => [Functions::buildActionName('awesome')],
                'url' =>  Url::getCssUrl('frontend'),
            ],
        ];

        foreach ($css as $k => $v) {
            \wp_enqueue_style(
                $k,
                $v['url'],
                isset($v['deps']) ? $v['deps'] : [],
                $this->getVersion($v)
            );
        }
    }
}
