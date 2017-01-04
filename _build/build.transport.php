<?php
    require_once 'tools/build.tools.php';
    require_once 'build.config.php';

    Tools::startTimer();

    $modx = Tools::loadModxInstance();

    $builder = new modPackageBuilder($modx);
    $builder->createPackage(PKG_NAME_LOWER,PKG_VERSION,PKG_RELEASE);


    // Register namespace for this extra -------------------------------------------------
    $builder->registerNamespace(PKG_NAME_LOWER,false,true,'{core_path}components/'.PKG_NAME_LOWER.'/');



    // Create the plugin object ----------------------------------------------------------
    $plugin= $modx->newObject('modPlugin');
    $plugin->set('id',1);
    $plugin->set('name', PKG_NAME);
    $plugin->set('description', PKG_NAME.' '.PKG_VERSION.'-'.PKG_RELEASE.' plugin for MODx Revolution');
    $plugin->set('plugincode', file_get_contents($sources['source_core'] . '/elements/plugins/plugin.mixedImage.php'));
    $plugin->set('category', 0);


    // Add plugin events -----------------------------------------------------------------
    $events = include $sources['data'].'transport.plugin.events.php';
    if (is_array($events) && !empty($events)) {
        $plugin->addMany($events);
    } else {
        $modx->log(xPDO::LOG_LEVEL_ERROR,'Could not find plugin events!');
    }
    $modx->log(xPDO::LOG_LEVEL_INFO,'Packaged in '.count($events).' Plugin Events.'); flush();
    unset($events);


    // Define vehicle attributes ----------------------------------------------------------
    $attributes= array(
        xPDOTransport::UNIQUE_KEY => 'name',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
            'PluginEvents' => array(
                xPDOTransport::PRESERVE_KEYS => true,
                xPDOTransport::UPDATE_OBJECT => false,
                xPDOTransport::UNIQUE_KEY => array('pluginid','event'),
            ),
        ),
    );

    // Create transport vehicle ------------------------------------------------------------
    $vehicle = $builder->createVehicle($plugin, $attributes);



    // Add File resolvers ------------------------------------------------------------------
    $vehicle->resolve('file',array(
        'source' => $sources['source_assets'],
        'target' => "return MODX_ASSETS_PATH . 'components/';",
    ));
    $vehicle->resolve('file',array(
        'source' => $sources['source_core'],
        'target' => "return MODX_CORE_PATH . 'components/';",
    ));


    // Build transport vehicle -------------------------------------------------------------
    $builder->putVehicle($vehicle);
    
    
    // Settings ----------------------------------------------------------------------------
    $settings = include_once $sources['data'].'transport.settings.php';
    $attributes= array(
        xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    );
    if (!is_array($settings)) { $modx->log(modX::LOG_LEVEL_FATAL,'Adding settings failed.'); }
    foreach ($settings as $setting) {
        $vehicle = $builder->createVehicle($setting,$attributes);
        $builder->putVehicle($vehicle);
    }
    $modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($settings).' system settings.'); flush();
    unset($settings,$setting,$attributes);


    // Adding in docs ----------------------------------------------------------------------
    /* now pack in the license file, readme and setup options */
    $builder->setPackageAttributes(array(
        'license' => file_get_contents($sources['docs'] . 'license.txt'),
        'readme' => Tools::parseReadmeTpl($sources['docs'] . 'readme.tpl'),
        'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
    ));
    $modx->log(xPDO::LOG_LEVEL_INFO,'Set Package Attributes.'); flush();


    // Create transport package ------------------------------------------------------------
    $modx->log(xPDO::LOG_LEVEL_INFO,'Zipping up package...'); flush();
    $builder->pack();

    $totalTime= sprintf("%2.4f s", Tools::stopTimer());

    $modx->log(modX::LOG_LEVEL_INFO,"Package Built in {$totalTime}");

    exit ();
