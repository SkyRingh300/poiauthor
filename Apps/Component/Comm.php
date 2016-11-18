<?php
namespace InnStudio\PoiAuthor\Apps\Component;

use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Snippets\Functions;
use InnStudio\PoiAuthor\Apps\Condition\Api as Condition;
use InnStudio\PoiAuthor\Apps\Options\Api as Options;
use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Main\Core;

abstract class Comm
{
    public static function getOptID()
    {
        return static::ID;
    }

    public static function getOpts($key = null, $force = false)
    {
        if (! $force) {
            static $cache = [];
            $cacheID = static::getOptID();

            if (! isset($cache[$cacheID])) {
                $cache[$cacheID] = array_merge(static::getDefaultOpts(), (array)Options::getOpts(static::getOptID()));
            }

            if ($key) {
                return isset($cache[$cacheID][$key]) ? $cache[$cacheID][$key] : false;
            }

            return $cache[$cacheID];
        }

        $opts = array_merge(static::getDefaultOpts(), (array)Options::getOpts(static::getOptID()));

        if (! $opts) {
            return false;
        }

        if ($key) {
            return isset($opts[$key]) ? $opts[$key] : false;
        }

        return $opts;
    }

    protected static function getDefaultOpts()
    {
        return [];
    }

    protected function saveOpts()
    {
        return isset($_POST[static::getOptID()]) ? $_POST[static::getOptID()] : false;
    }

    protected function setFrontend($method = 'displayFrontend')
    {
        if (! Condition::isAdmin() && ! Condition::isAjax()) {
            call_user_func([$this, $method]);
        }
    }

    protected function setAdmin($method = 'displayAdmin')
    {
        if (Condition::isAdmin() && ! Condition::isAjax()) {
            call_user_func([$this, $method]);
        }
    }

    protected function setBackend(array $args = [])
    {
        if (! Condition::isOptsPage()) {
            return;
        }

        $args = array_merge([
            'icon' => null,
            'content' => [$this, 'displayBackend'],
        ], $args);

        \add_filter(
            Functions::buildActionName('backendSettings'),
            function(array $settings) use ($args)
            {
                $settings[$this->getBackendName()] = [
                    'title' => $this->getBackendName(),
                    'icon' => $args['icon'],
                    'content' => $args['content'],
                ];

                return $settings;
            }
        );
    }

    protected function setNotAjaxLogged($actionName = null, $method = 'notAjaxLogged')
    {
        if (! Condition::isAjax()) {
            $actionName = $actionName ? Functions::buildActionName($actionName) : Functions::buildActionName(get_called_class());
            \add_action("wp_ajax_$actionName", [$this, $method]);
        }
    }

    protected function setNotAjax($method = 'displayNotAjax')
    {
        if (! Condition::isAjax()) {
            call_user_func([$this, $method]);
        }
    }

    protected function setAjaxAll($actionName = null, $method = 'ajaxProcess')
    {
        if (! Condition::isAjax()) {
            return;
        }

        if (! $actionName) {
            $actionName = static::getOptID();
        }

        $actionName = Functions::buildActionName($actionName);
        \add_action("wp_ajax_nopriv_{$actionName}", [$this, $method]);
        \add_action("wp_ajax_{$actionName}", [$this, $method]);
    }

    protected function setAjax($actionName = null, $method = 'ajaxProcess')
    {
        if (! Condition::isAjax() || User::isUserLoggedIn()) {
            return;
        }

        if (! $actionName) {
            $actionName = static::getOptID();
        }

        $actionName = Functions::buildActionName($actionName);
        \add_action("wp_ajax_nopriv_{$actionName}", [$this, $method]);
    }

    protected function setAjaxLogged($actionName = null, $method = 'ajaxProcessLogged')
    {
        if (! Condition::isAjax() || ! User::isUserLoggedIn()) {
            return;
        }

        if (! $actionName) {
            $actionName = static::getOptID();
        }

        $actionName = Functions::buildActionName($actionName);
        \add_action("wp_ajax_{$actionName}", [$this, $method]);
    }

    protected function isDynamicRequest($ID = null)
    {
        if (! $ID) {
             $ID = static::getOptID();
        }

        $key = Functions::buildActionName($ID);

        return isset($_GET[$key]) ? $_GET[$key] : false;
    }

    protected function setDynamicRespond($method = 'dynamicRespond', $pri = 10)
    {
        if (Condition::isAjax()) {
            \add_filter(Functions::buildActionName('dynamicRespond'), [$this, $method], $pri);
        }
    }

    protected function setFrontendDynamicConf($method = 'dynamicConfFrontend')
    {
        if (! Condition::isAdmin() && ! Condition::isAjax()) {
            \add_filter(Functions::buildActionName('dynamicConf'), [$this, $method]);
        }
    }
    protected function setDynamicConf($method = 'dynamicConf')
    {
        if (! Condition::isAjax()) {
            \add_filter(Functions::buildActionName('dynamicConf'), [$this, $method]);
        }
    }

    protected function setBackendDynamicConf($method = 'dynamicConfBackend')
    {
        if (Condition::isOptsPage() && ! Condition::isAjax()) {
            \add_filter(Functions::buildActionName('dynamicConf'), [$this, $method]);
        }
    }

    protected function setAdminDynamicConf($method = 'dynamicConfAdmin')
    {
        if (! Condition::isOptsPage() && Condition::isAdmin()) {
            \add_filter(Functions::buildActionName('dynamicConf'), [$this, $method]);
        }
    }

    protected function setFrontendDynamicRequest($method = 'frontendDynamicRequest')
    {
        if (! Condition::isAdmin() && ! Condition::isAjax()) {
            \add_filter(Functions::buildActionName('dynamicRequest'), [$this, $method]);
        }
    }

    protected function setBackendDynamicRequest($method = 'backendDynamicRequest')
    {
        if (Condition::isOptsPage() && ! Condition::isAjax()) {
            \add_filter(Functions::buildActionName('dynamicRequest'), [$this, $method]);
        }
    }

    protected function setDynamicRequest($method = 'dynamicRequest')
    {
        if (! Condition::isAjax()) {
            \add_filter(Functions::buildActionName('dynamicRequest'), [$this, $method]);
        }
    }

    protected function setOptsSave($method = 'saveOpts')
    {
        \add_filter(
            Functions::buildActionName('addonSaveOpts'),
            function(array $opts = []) use ($method)
            {
                $opts[static::getOptID()] = call_user_func([$this, $method]);
                return $opts;
            }
        );
    }

    public static function isEnabled($key = 'enabled')
    {
        return (int)static::getOpts($key) === 1;
    }

    protected function immediateSaveOpts(array $newAddonData)
    {
        Options::setAddonOpts(static::getOptID(), $newAddonData);
    }

    protected function optInputList(array $args)
    {
        $args = array_merge([
            'th' => null,
            'des' => null,
            'attrs' => [],
        ], $args);

        $args['attrs'] = array_merge([
            'type' => 'text',
            'class' => null,
        ], $args['attrs']);

        //number
        if ($args['attrs']['type'] === 'number' && ! $args['attrs']['class']) {
            $args['attrs']['class'] = 'small-text';
        }

        //widefat
        if (! $args['attrs']['class']) {
            $args['attrs']['class'] = 'widefat';
        }

        //icon type
        $isIconType = isset($args['attrs']['name']) && stripos($args['attrs']['name'], 'icon') !== false;

        $attrs = [];

        foreach ($args['attrs'] as $attrName => $attrValue) {
            if ($attrValue === true) {
                $attrs[] = $attrName;
                continue;
            }

            $attrs[] = "${attrName}=\"${attrValue}\"";
        }

        ?>
        <tr>
            <th>
                <?php
                if (isset($args['attrs']['id'])) {
                    ?>
                    <label for="<?= $args['attrs']['id'];?>">
                        <?= $args['th'];?>
                        <?php if ($isIconType) { ?>
                            -
                            <i class="fa fa-<?= $args['attrs']['value'];?>"></i>
                        <?php } ?>
                    </label>
                <?php } else { ?>
                    <?= $args['th'];?>
                <?php } ?>
            </th>
            <td>
                <input <?= implode(' ', $attrs);?> />

                <?php
                if ($args['des']) {
                    if ($args['attrs']['class'] === 'small-text') {
                        ?>
                        <span class="description"><?= $args['des'];?></span>
                    <?php } else { ?>
                        <p class="description"><?= $args['des'];?></p>
                    <?php
                    }
                }
                ?>
            </td>
        </tr>
        <?php
    }

    protected function optSelectList(array $args = [])
    {
        $args = array_merge([
            'th' => L10n::__('Enable or not?'),
            'value' => (int)static::getOpts('enabled'),
            'attrs' => [],
            'des' => null,
            'opts' => [
                [
                    'value' => 1,
                    'text' => 'y'
                ], [
                    'value' => -1,
                    'text' => 'n'
                ],
            ]
        ], $args);

        $args['attrs'] = array_merge([
            'id' => static::getOptID() . 'enabled',
            'class' => 'widefat',
            'name' => static::getOptID() . '[enabled]',
        ], $args['attrs']);

        foreach ($args['attrs'] as $attrName => $attrValue) {
            if ($attrValue === true) {
                $attrs[] = $attrName;
                continue;
            }

            $attrs[] = "${attrName}=\"${attrValue}\"";
        }
        ?>
        <tr>
            <th>
                <?php if (isset($args['attrs']['id'])) { ?>
                    <label for="<?= $args['attrs']['id'];?>"><?= $args['th'];?></label>
                <?php } else { ?>
                    <?= $args['th'];?>
                <?php } ?>
            </th>
            <td>
                <select <?= implode(' ', $attrs);?> >
                    <?php
                    foreach ($args['opts'] as $v) {
                        //yes or no
                        if (! isset($v['value'])) {
                            if ($v['text'] === 'y') {
                                $v['value'] = 1;
                            } elseif ($v['text'] === 'n') {
                                $v['value'] = -1;
                            }
                        }

                        if ($v['text'] === 'y') {
                            $v['text'] = L10n::_x('Yes', 'Selector');
                        } elseif ($v['text'] === 'n') {
                            $v['text'] = L10n::_x('No', 'Selector');
                        }

                        $selected = $v['value'] == $args['value'] ? 'selected' : null;
                        ?>
                        <option value="<?= $v['value'];?>" <?= $selected;?> ><?= $v['text'];?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php if ($args['des']) { ?>
                    <p class="description"><?= $args['des'];?></p>
                <?php } ?>
            </td>
        </tr>
        <?php
    }

    protected function optTextareaList(array $args)
    {
        $args = array_merge([
            'th' => L10n::__('A.D HTML code'),
            'value' => null,
            'attrs' => [],
            'des' => null,
        ], $args);

        $args['attrs'] = array_merge([
            'class' => 'widefat',
            'rows' => 3,
        ], $args['attrs']);

        //ad
        if (isset($args['attrs']['name']) && stripos($args['attrs']['name'], 'ad') !== false) {
            $args['attrs']['rows'] = 5;

            //placeholder
            if (! isset($args['attrs']['placeholder'])) {
                $args['attrs']['placeholder'] = L10n::__('HTML code');
            }
        }


        $attrs = [];

        foreach ($args['attrs'] as $attrName => $attrValue) {
            if ($attrValue === true) {
                $attrs[] = $attrName;
                continue;
            }

            $attrs[] = "${attrName}=\"${attrValue}\"";
        }
        ?>
        <tr>
            <th>
                <?php if (isset($args['attrs']['id'])) { ?>
                    <label for="<?= $args['attrs']['id'];?>"><?= $args['th'];?></label>
                <?php } else { ?>
                    <?= $args['th'];?>
                <?php } ?>
            </th>
            <td>
                <textarea <?= implode(' ', $attrs);?> ><?= $args['value'];?></textarea>

                <?php if ($args['des']) { ?>
                    <p class="description"><?= $args['des'];?></p>
                <?php } ?>
            </td>
        </tr>
        <?php
    }

    protected function optCheckboxList(array $args)
    {
        $args = array_merge([
            'th' => null,
            'content' => null,
            'inputs' => [],
            'values' => [],
            'attrs' => [],
            'des' => null,
        ], $args);

        ?>
        <tr>
            <th>
                <?php if (isset($args['attrs']['id'])) { ?>
                    <label for="<?= $args['attrs']['id'];?>"><?= $args['th'];?></label>
                <?php } else { ?>
                    <?= $args['th'];?>
                <?php } ?>
            </th>
            <td>
                <div class="categorydiv">
                    <div class="tabs-panel">
                        <ul class="categorychecklist form-no-clear">
                            <?php
                            if (! empty($args['callback'])) {
                                call_user_func_array($args['callback'][0], isset($args['callback'][1]) ? $args['callback'][1] : []);
                            } else {
                                foreach ($args['inputs'] as $k => $input) {
                                    $checked = in_array($input['value'], $args['values']) ? 'checked' : null;
                                    ?>
                                    <li>
                                        <label>
                                            <input type="checkbox" value="<?= $input['value'];?>" name="<?= $args['attrs']['name'];?>" <?= $checked;?> /> <?= $input['text'];?>
                                        </label>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <?php if ($args['des']) { ?>
                    <p class="description"><?= $args['des'];?></p>
                <?php } ?>
            </td>
        </tr>
        <?php
    }

    protected function placeholderInputs(array $inputs = [])
    {
        if (! $inputs) {
            $inputs = $this->getReplaceKeywords();
        }
        ?>
        <p>
            <?php
            foreach ($inputs as $k => $v) {
            ?><input type="text" value="<?= $k;?>" title="<?= $v;?>" class="small-text text-select" readonly /><?php
            }
            ?>
        </p>
        <?php
    }

    protected function tplItemControl($placeholder)
    {
        ?>
        <tr>
            <th><?= L10n::__('Control');?></th>
            <td>
                <a href="javascript:;" class="button move-up" data-target="<?= static::getOptID();?>item<?= $placeholder;?>" title="<?= L10n::__('Move up');?>"><i class="fa fa-arrow-up"></i></a>

                <a href="javascript:;" class="button move-down" data-target="<?= static::getOptID();?>item<?= $placeholder;?>" title="<?= L10n::__('Move down');?>"><i class="fa fa-arrow-down"></i></a>

                <a href="javascript:;" data-target="<?= static::getOptID();?>item<?= $placeholder;?>" class="del button" title="<?= L10n::__('Delete this item');?>" ><i class="fa fa-trash"></i></a>
            </td>
        </tr>
        <?php
    }

    public function getName()
    {
        return static::getOpts('name');
    }

    public function getIcon()
    {
        return static::getOpts('icon');
    }

    protected function getBackendName()
    {
        return static::getOptID();
    }

    protected function getLang($key)
    {
        return stripslashes((string)static::getOpts($key));
    }
}
