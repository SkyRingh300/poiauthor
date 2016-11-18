<?php
namespace InnStudio\PoiAuthor\Main;

class Api
{
    public function globPhpFiles($dir, $callback)
    {
        if (is_dir($dir)) {
            $dirs = glob($dir . '/*', GLOB_ONLYDIR);
            $dirFiles = glob($dir . '/*.php');

            foreach (array_merge($dirs, $dirFiles) as $filepath) {
                if (is_dir($filepath)) {
                    $this->globPhpFiles($filepath, $callback);

                    continue;
                } else {
                    call_user_func_array($callback, [$filepath]);
                }
            }
        } else {
            call_user_func_array($callback, [$dir]);
        }
    }
}
