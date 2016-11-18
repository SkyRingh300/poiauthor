<?php
namespace InnStudio\PoiAuthor\Apps\Snippets;

use InnStudio\PoiAuthor\Apps\Cache\Other;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Main\Core;

class Functions
{
    public static function metaToJson($meta)
    {
        if (is_array($meta) || is_object($meta)) {
            return $meta;
        }
        $jsonMeta = json_decode($meta, true);

        return $jsonMeta === null ? $meta : $jsonMeta;
    }

    public static function buildActionName($actionName)
    {
        static $cache = [];

        if (! isset($cache[$actionName])) {
            $cache[$actionName] = md5($actionName . Core::ID . AUTH_KEY);
        }

        return $cache[$actionName];
    }

    public static function getRandFloat($min = 0, $max = 100)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public static function jsonOutput($data, $die = false, $jsonp = false)
    {
        $data = json_encode($data);
        $callback = filter_input(INPUT_GET, 'callback', FILTER_SANITIZE_STRING);

        if ($jsonp && $callback) {
            $data = "${callback}(${data})";
            header('Content-Type: application/javascript');
        } else {
            header('Content-Type: application/json');
        }

        return $die ? die($data) : $data;
    }

    public static function filterWesternEuropeanCharacter($s)
    {
        return preg_replace('/[\s\x{3000}\x{FEFF}\xA0]+/ui', '', $s);
    }

    public static function fliterScript($value) {
        $value = preg_replace('/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i', 'data-filter', $value);
        $value = preg_replace('/(.*?)<\/script>/si', '', $value);
        $value = preg_replace('/(.*?)<\/iframe>/si', '', $value);
        return $value;
    }
    /**
     * Echo color radio input group
     *
     * @param array $args
     *     @type string $name
     *     @type string $id
     *     @type string $currentValue
     *     @type arra $attr
     * @version 1.0.0
     */
    public static function colorRadioGroup(array $args)
    {
        $attrHtml = '';
        if(isset($args['attr'])){
            foreach($args['attr'] as $v){
                $attrHtml .= $v[0] . '="' . $v[1] . '" ';
            }
        }
        $i = 0;
        foreach (Core::colors as $v) {
            if (isset($args['currentValue'])) {
                $checked = $args['currentValue'] == $v ? ' checked ' : null;
            }elseif($i == 0){
                $checked = ' checked ';
            }else{
                $checked = null;
            }
            ++$i;
            ?>
            <label class="color-radio-label">
                <input
                    type="radio"
                    name="<?= $args['name'];?>"
                    <?php if(isset($args['id'])){ ?>
                        id="<?= $args['id'];?>-<?= $v;?>"
                    <?php } ?>
                    class="color-radio"
                    style="background: #<?= $v;?>"
                    value="<?= $v;?>"
                    <?= $checked;?>
                    hidden
                    title="<?= $v;?>"
                >
                <i class="fa fa-font fa-fw" style="background: #<?= $v;?>" ></i>
            </label>
            <?php
        }
    }

    public static function optList($value, $text, $currentValue, array $attr = [])
    {
        $selected = $value == $currentValue ? ' selected ' : null;
        $attrHtml = '';

        if ($attr) {
            foreach ($attr as $v) {
                $attrHtml .= $v[0] . '="' . $v[1] . '" ';
            }
        }
        ?>
        <option value="<?= $value;?>" <?= $selected;?> <?= $attrHtml;?>><?= $text;?></option>
        <?php
    }

    public static function multSearchArray($key,$value,$array)
    {
        $results = [];

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray){
                $results = array_merge($results, static::multSearchArray($key, $value,$subarray));
            }
        }

        return $results;
    }
    /**
     * array_multiwalk
     *
     * @param array
     * @param string public static function name
     * @return array
     * @version 1.0.1
     */
    public static function arrayMultiwalk($a,$fn)
    {
        if (!$a || !$fn) {
            return false;
        }

        foreach($a as $k => $v){
            if (is_array($v)) {
                $a[$k] = static::arrayMultiwalk($v,$fn);
                continue;
            } else {
                $a[$k] = $fn($v);
            }
        }

        return $a;
    }

    public static function isNullArray($arr = null)
    {
        if (is_array($arr)) {
           foreach ($arr as $k=>$v) {

                if ($v && ! is_array($v)) {
                    return false;
                }

                $t = static::isNullArray($v);

                if (! $t) {
                    return false;
                }
            }

            return true;
        } else {
            return ! $arr ? true : false;
        }
    }

    public static function removeDIR($path)
    {
        if (! file_exists($path)) {
            return false;
        }

        if (is_file($path)) {
            unlink($path);
            return;
        }

        if ($handle = opendir($path)) {
            while (false !== ($item = readdir($handle))) {
                if ($item == '.' || $item == '..') {
                    continue;
                }

                if (is_dir($path . '/' . $item)) {
                    static::removeDIR($path . '/' . $item);
                } else {
                    unlink($path . '/' . $item);
                }
            }

            closedir($handle);
            rmdir($path);
        }
    }

    public static function statusTip(...$args)
    {
        $defaults = ['type','size','content','wrapper'];
        $types = ['loading','success','error','question','info','ban','warning'];
        $sizes = ['small','middle','large'];
        $wrappers = ['div','span'];
        $type = null;
        $size = null;
        $wrapper = null;

        switch (count($args)) {
            case 1:
                $content = null;
                break;
            case 2:
                $type = $args[0];
                $content = $args[1];
                break;
            default:
                foreach ($args as $k => $v) {
                    $$defaults[$k] = $v;
                }
        }
        if (!$type) {
            $type = $types[0];
        }

        if (!$size) {
            $size = $sizes[0];
        }
        if (!$wrapper) {
            $wrapper = $wrappers[0];
        }

        switch ($type) {
            case 'success':
                $icon = 'check-circle';
                break;
            case 'error' :
                $icon = 'times-circle';
                break;
            case 'info':
            case 'warning':
                $icon = 'exclamation-circle';
                break;
            case 'question':
            case 'help':
                $icon = 'question-circle';
                break;
            case 'ban':
                $icon = 'minus-circle';
                break;
            case 'loading':
                $icon = 'loading';
                break;
            default:
                $icon = $type;
        }

        return '<' . $wrapper . ' class="tip-status tip-status-' . $size . ' tip-status-' . $type . '"><i class="fa fa-' . $icon . ' fa-fw"></i> ' . $content . '</' . $wrapper . '>';
    }

    public static function htmlMinify($html)
    {
        return preg_replace(
            [
                '/ {2,}/',
                '/\t|(?:\r?\n[ \t]*)+/s'
            ],
            ' ',
            $html
        );
    }

    public static function getClientIP()
    {
        $pattern = '/[^0-9a-fA-F:., ]/';

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = preg_replace($pattern, '', $_SERVER['HTTP_X_FORWARDED_FOR']);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = preg_replace($pattern, '', $_SERVER['REMOTE_ADDR']);
        }

        if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        return $ip;
    }

    public static function getImgURL($str, $first = true)
    {
        $pattern = '/<img[^>]+src\s*=\s*[\"\']\s*([^\"\']+)/i';
        preg_match_all($pattern, $str, $matches);

        if ($first) {
            return isset($matches[1][0]) ? $matches[1][0] : null;
        } else {
            return $matches[1];
        }
    }

    public static function getLocalTimestamp($timestamp)
    {
        return (int)$timestamp + (Other::getOption('gmt_offset') * 3600);
    }

    public static function getDate($format, $timestamp)
    {
        return date($format, $timestamp + (Other::getOption('gmt_offset') * 3600));

    }

    public static function getHumanDate($timestamp)
    {
        $text = '';
        $t = Other::getCurrentTime('timestamp') - $timestamp;

        switch ($t) {
            /**
             * in 1 minu, just now
             */
            case ($t < 60) :
                $text = L10n::__('Just');
                break;
            /**
             * in 1 hours, 60 * 60 = 3600
             */
            case ($t < 3600) :
                $text = sprintf(L10n::__('%dmin ago'), floor($t / 60));
                break;
            /**
             * in 1 day, 60 * 60 * 24 = 86400
             */
            case ($t < 86400) :
                $text = sprintf(L10n::__('%dh ago'), floor($t / 3600));
                break;
            /**
             * in 1 month, 60 * 60 * 24 * 30 = 2592000
             */
            case ($t < 2592000) :
                $text = sprintf(L10n::__('%dd ago'), floor($t / 86400));
                break;
            /**
             * in 1 year, 60 * 60 * 24 * 30 * 12 = 31104000
             */
            case ($t < 31104000) :
                $text = sprintf(L10n::__('%dm ago'), floor($t / 2592000));
                break;
            /**
             * in 100 year 60 * 60 * 24 * 30 * 12 * 100 = 3110400000
             */
            case ($t < 3110400000) :
                $text = sprintf(L10n::__('%dy ago'), floor($t / 31104000));
                break;
            /**
             * dislay date
             */
            default:
                $text = date(L10n::__('M j, Y'), $timestamp);
        }
        return $text;
    }

    public static function subStr($str, $len = 120, $extra = '&hellip;')
    {

        if (mb_strlen(trim($str)) <= $len) {
            return $str;
        }

        return mb_substr($str, 0, $len) . $extra;
    }

    public static function subURL($url, array $args = [])
    {
        $args = array_merge([
            'firstLen' => 20,
            'lastLen' => 10,
            'extraStr' => '&hellip;',
        ], $args);

        $urlLen = mb_strlen($url);

        if ($urlLen <= ($args['firstLen'] + $args['lastLen'])) {
            return $url;
        } else {
            $urlBefore = mb_substr($url, 0, $args['firstLen']);
            $urlAfter = mb_substr($url, 0 - $args['lastLen']);

            return "{$urlBefore} {$args['extraStr']} {$urlAfter}";
        }
    }
}
