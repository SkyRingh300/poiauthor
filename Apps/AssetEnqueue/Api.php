<?php
namespace InnStudio\PoiAuthor\Apps\AssetEnqueue;

use InnStudio\PoiAuthor\Apps\FileTimestamp\Api as FileTimestamp;
use InnStudio\PoiAuthor\Apps\Component\Comm as Component;

class Api extends Component
{
    const ID = 'assetEnqueue';
    const awesomeURL = 'https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css';

    protected function getAwesomeURL()
    {
        return static::awesomeURL;
    }

    protected function getVersion($v)
    {
        return array_key_exists('version', $v) ? $v['version'] : FileTimestamp::getTimestamp();
    }
}
