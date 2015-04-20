<?php
class InfluxdbMetrics extends StudipPlugin implements SystemPlugin
{
    const NAVIGATION_ID = 'influxdbdmin';
    const CHILD_PLUGIN  = 'InfluxdbMetricsPlugin';

    public function __construct()
    {
        parent::__construct();
        StudipAutoloader::addAutoloadPath(__DIR__);
        $this->setupNavigation();
    }

    private function setupNavigation()
    {
        if (!$GLOBALS['perm']->have_perm('root')) {
            return;
        }


        if (Navigation::hasItem('/admin/config')) {
            $url = PluginEngine::getURL($this, array(), 'show');
            Navigation::addItem('/admin/config/' . self::NAVIGATION_ID, new Navigation('InfluxDB Metrics', $url));
        }
    }

    #######################################################################

    // ***** EN/DISABLE MAGIC *****

    static function onDisable($id)
    {
        self::deactivateChildPlugin();
    }

    #######################################################################

    // ***** CONTROLLER ACTIONS *****


    // show the admin interface
    function show_action()
    {
        $this->requireRoot();

        Navigation::activateItem('/admin/config/' . self::NAVIGATION_ID);

        $parameters = array(
              'plugin'   => $this
            , 'settings' => $this->getSettings()
            , 'active'   => $this->isChildPluginActivated()
        );

        $factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        echo $factory->render('show', $parameters, $GLOBALS['template_factory']->open('layouts/base'));
    }


    // update settings
    function settings_action()
    {
        $this->requireRoot();

        if (!Request::isPost()) {
            throw new AccessDeniedException();
        }

        # get settings
        $settings = Request::getArray('settings');

        # validate them
        list($valid, $err) = $this->validateSettings($settings);
        if (!$valid) {
            PageLayout::postMessage(MessageBox::error(_('Es sind Fehler aufgetreten:'), $err));
            $this->redirect('show', compact('err'));
            return;
        }

        $this->updateSettings($settings);

        $this->activateChildPlugin();

        PageLayout::postMessage(MessageBox::info(_('Aktivierung abgeschlossen!')));
        $this->redirect('show');
    }

    // deactivate metrics plugin
    function deactivate_action()
    {
        $this->requireRoot();

        if (!Request::isPost()) {
            throw new AccessDeniedException();
        }

        self::deactivateChildPlugin();

        PageLayout::postMessage(MessageBox::info(_('Deaktivierung abgeschlossen!')));
        $this->redirect('show');
    }

    #######################################################################

    // ***** BUSINESS LOGIC & HELPERS *****

    private function getSettings()
    {
        return InfluxdbMetricsSettings::get();
    }

    private function updateSettings($settings)
    {
        InfluxdbMetricsSettings::set($settings);
    }

    private function validateSettings($settings)
    {
        $errors = array();

        # adress of statsd host
        if (!filter_var($settings['host'], FILTER_VALIDATE_IP) && !preg_match('/^(?:[a-z0-9\-]\.?)+$/i', $settings['host'])) {
            $errors[] = _('Host ist ungültig.');
        }

        # port of statsd host
        if (!filter_var($settings['port'], FILTER_VALIDATE_INT)) {
            $errors[] = _('Port ist ungültig.');
        }

        return array(sizeof($errors) === 0, $errors);
    }


    private function isChildPluginActivated()
    {
        $info = PluginManager::getInstance()->getPluginInfo(self::CHILD_PLUGIN);
        return $info && $info['enabled'];
    }

    private function activateChildPlugin()
    {
          $plugin_manager = PluginManager::getInstance();

          # register
          $additional_class = self::CHILD_PLUGIN;
          $pluginpath = 'UOL/' . __CLASS__;
          $pluginid = $this->getPluginId();
          $id = $plugin_manager->registerPlugin($additional_class, $additional_class, $pluginpath, $pluginid);

          # and activate
          $plugin_manager->setPluginEnabled($id, TRUE);
    }


    private static function deactivateChildPlugin()
    {
        $info = PluginManager::getInstance()->getPluginInfo(self::CHILD_PLUGIN);
        PluginManager::getInstance()->unregisterPlugin($info['id']);
    }


    private function requireRoot()
    {
        global $perm;
        if (!$perm->have_perm('root')) {
            throw new AccessDeniedException();
        }
    }


    private function redirect($action)
    {
        header('Location: ' . PluginEngine::getURL($this, array(), $action));
    }
}
