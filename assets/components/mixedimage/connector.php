<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';
 
$corePath = $modx->getOption('core_path').'components/mixedimage/';
 
$modx->lexicon->load('mixedimage:default');
 
// Load the upload processor class for extension
require_once $modx->getOption('core_path').'model/modx/modprocessor.class.php';
require_once $modx->getOption('core_path').'model/modx/processors/browser/file/upload.class.php';
 
/* handle request */
$path = $corePath.'processors/';
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));