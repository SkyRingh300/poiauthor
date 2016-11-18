<?php
/*
 * Plugin Name:     Poi Author
 * Plugin URI:      https://inn-studio.com/poiauthor
 * Description:     A better performance author meta box instead of the build-in of WordPress.
 * Author:          INN STUDIO
 * Author URI:      https://inn-studio.com
 * Version:         2.0.0
 * Text Domain:     poiauthor
 * Domain Path:     /Languages
 *
 */

namespace InnStudio\PoiAuthor;

require __DIR__ . '/vendor/autoload.php';

use InnStudio\PoiAuthor\Requires\Apps;
use InnStudio\PoiAuthor\Requires\Addons;
use InnStudio\PoiAuthor\Main\Core;

\add_action('plugins_loaded', function() {
    new Core(__DIR__);
    new Apps();
    new Addons();
});
