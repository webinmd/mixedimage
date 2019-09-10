<?php
$corePath = $modx->getOption('core_path',null,MODX_CORE_PATH).'components/mixedimage/';
$assetsUrl = $modx->getOption('assets_url',null,MODX_ASSETS_URL).'components/mixedimage/';

$modx->lexicon->load('mixedimage:default');

switch ($modx->event->name) {
    case 'OnTVInputRenderList':
        $modx->event->output($corePath.'elements/tv/input/');
        break;
    case 'OnTVOutputRenderList':
        $modx->event->output($corePath.'elements/tv/output/');
        break;
    case 'OnTVInputPropertiesList':
        $modx->event->output($corePath.'elements/tv/input/options/');
        break;
    case 'OnTVOutputRenderPropertiesList':
        $modx->event->output($corePath.'elements/tv/properties/');
        break;
    case 'OnDocFormPrerender':
    case 'OnManagerPageBeforeRender':
        $modx->regClientStartupScript($assetsUrl.'js/mgr/mixedimage.js');
        $modx->regClientCSS($assetsUrl.'css/mgr/mixedimage.css');
        $modx->controller->addLexiconTopic('mixedimage:default');
        break;
    case 'OnMODXInit':
    case 'OnLoadWebDocument':
        $mTypes = $modx->getOption('manipulatable_url_tv_output_types',null,'image,file').',mixedimage';
        $modx->setOption('manipulatable_url_tv_output_types', $mTypes);
        break;
}