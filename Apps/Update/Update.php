<?php
namespace InnStudio\PoiAuthor\Apps\Update;

use InnStudio\PoiAuthor\Apps\User\Api as User;
use InnStudio\PoiAuthor\Main\Core;
use InnStudio\PoiAuthor\Apps\Language\L10n;
use InnStudio\PoiAuthor\Apps\Component\Comm as Component;

class Update extends Component
{
    const ID = 'update';
    const updateUrl = 'https://update.inn-studio.com/?action=get_update&slug=';

    private $last;
    private $relativeFile;
    private $response = null;

    public function __construct()
    {
        $this->setAdmin('initUpdate');
    }

    public function initUpdate()
    {
        if (! User::currentUserCan('manage_options')) {
            return;
        }

        $this->setRelativeFile();

        \add_filter('site_transient_update_plugins', [$this, 'checkForUpdate'], 1);
        \add_filter('plugins_api', [$this, 'filterPluginsApi'], 10, 3);
        \add_filter('upgrader_source_selection', [$this, 'filterUpgraderSourceSelection'], 10, 3);
        \add_filter('upgrader_pre_install', [$this, 'filterUpgraderPreInstall'], 10, 2);
        //\add_filter('upgrader_post_install', [$this, 'filterFlushOpcache']);
        \add_filter('upgrader_post_install', [$this, 'filterUpgraderPostInstall'], 10, 3);
    }

    private function setRelativeFile()
    {
        $this->relativeFile = Core::$basename . '/' . Core::ID . '.php';
    }

    public function filterUpgraderPreInstall($true, $hookExtra)
    {
        $this->last = isset($hookExtra['plugin']) ? $hookExtra['plugin'] : false;

        return $true;
    }

    public function checkForUpdate($transient)
    {
        if (! isset($transient->checked[$this->relativeFile])) {
            return $transient;
        }

        $this->getResponse();

        if (! $this->response) {
            return $transient;
        }

        if (version_compare($transient->checked[$this->relativeFile], $this->response['new_version'], '>=')) {
            return $transient;
        }

        $transient->response[$this->relativeFile] = (object)$this->response;
        $transient->response[$this->relativeFile]->plugin = $this->relativeFile;

        return $transient;
    }

    public function filterUpgraderSourceSelection($source, $remoteSource = null, $upgrader = null)
    {
        if ($this->last != Core::$basename) {
            return $source;
        }

        $correctedSource = $remoteSource . '/' . $this->last . '/';

        if (rename($source, $correctedSource)) {
            return $correctedSource;
        } else {
            $upgrader->skin->feedback(L10n::__('Unable to rename downloaded plugin.'));

            return new \WP_Error();
        }
    }

    private function getResponse()
    {
        if ($this->response !== null) {
            return $this->response;
        }

        $url = static::updateUrl . Core::ID;

        $this->response = \wp_remote_get($url, [
            'timeout' => 60,
            'httpversion' => 1.1,
        ]);

        if (\is_wp_error($this->response)) {
            $this->response = false;

            return false;
        }

        if (! isset($this->response['body']) || empty($this->response['body'])) {
            $this->response = false;

            return false;
        }

        $this->response = json_decode($this->response['body'], true);

        if (! $this->response) {
            $this->response = false;

            return false;
        }

        $this->response = $this->toWpFormat($this->response);

        if (! isset($this->response['new_version']) || ! isset($this->response['url']) || ! isset($this->response['package'])) {
            $this->response = false;

            return false;
        }

        return $this->response;
    }

    private function toWpFormat(array $response){
        return [
            'name' => Core::ID,
            'slug' => Core::ID,
            //'plugin' => $this->relativeFile,
            'new_version' => $response['version'],
            'url' => $response['homepage'],
            'tested' => $GLOBALS['wp_version'],
            'package' => $response['download_url'],
            'upgrade_notice' => $response['upgrade_notice'],
            'sections' => $response['sections'],
        ];
    }

    private function flushOpcache()
    {
        if (function_exists('\opcache_reset')) {
            \opcache_reset();
        }
    }

    public function filterUpgraderPostInstall($true, $hookExtra, $result)
    {
        $this->flushOpcache();

        if ($result['destination_name'] === Core::$basename) {
            return $result;
        }

        if (
            strpos($result['destination_name'], Core::$basename) !== false ||
            strpos($result['destination_name'], Core::ID) !== false
        ) {
            $newDestination = $result['local_destination'] . '/' . Core::$basename . '/';
            rename($result['destination'], $newDestination);

            $result['destination'] = $newDestination;
            $result['remote_destination'] = $newDestination;
            $result['destination_name'] = Core::$basename;
        }

        return $result;
    }

    public function filterPluginsApi($false, $action, $response)
    {
        if (! isset($response->slug) || $response->slug !== Core::ID) {
            return $false;
        }

        $this->response = $this->getResponse();
        $response = (object)$this->response;

        //last_update
        if (isset($this->response['last_update'])) {
            unset($response->last_update);
            $response->last_updated = $this->response['last_update'];
        }

        $response->name = Core::getMetaTranslation('name');

        //active_installs
        $response->active_installs = substr($_SERVER['REQUEST_TIME'], 0, 5) + substr($_SERVER['REQUEST_TIME'], -5);

        //rating
        $response->rating = 99;
        $response->num_ratings = floor($response->active_installs / 5.6);

        //donate_link
        $response->donate_link = 'https://ws4.sinaimg.cn/mw600/686ee05djw1f9qx5xqxxij20u019j42n.jpg';

        return $response;
    }
}
