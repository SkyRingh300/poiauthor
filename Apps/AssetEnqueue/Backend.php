<?php
namespace InnStudio\PoiAuthor\Apps\AssetEnqueue;

use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Url\Api as Url;
use InnStudio\PoiAuthor\Main\Core;

class Backend extends Api
{
    public function __construct()
    {
        $this->setAdmin('backendAssets');
    }

    public function backendAssets()
    {
        \add_action('admin_enqueue_scripts', [$this, 'backendEnqueueScripts']);
        //\add_action('admin_enqueue_scripts', [$this, 'backendEnqueueCss']);
    }

    public function backendEnqueueCss()
    {
        $css = [
            Functions::buildActionName('awesome') => [
                'deps' => [],
                'url' => $this->getAwesomeURL(),
                'version' => null,
            ],
            Functions::buildActionName('backend') => [
                'deps' => [Functions::buildActionName('awesome')],
                'url' =>  Url::getCssUrl('backend'),
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

    public function backendEnqueueScripts()
    {
        $js = [
            Functions::buildActionName('backend') => [
                'url' => Url::getJsUrl('backend'),
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
}
