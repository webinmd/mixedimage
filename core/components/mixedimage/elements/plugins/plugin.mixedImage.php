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
        $modx->regClientStartupScript($assetsUrl.'js/mgr/mixedimage.js');
        $modx->regClientCSS($assetsUrl.'css/mgr/mixedimage.css');
        break;
    case 'OnMODXInit':
        $mTypes = $modx->getOption('manipulatable_url_tv_output_types',null,'image,file').',mixedimage';
        $modx->setOption('manipulatable_url_tv_output_types', $mTypes);
        break;
    case 'OnFileManagerUpload':
        if ((bool)$modx->getOption('mixedimage.translit', null, false))
        {
            $fat = $modx->getOption('friendly_alias_translit');
            $friendly_alias_translit = (empty($fat) || $fat == 'none') ? false : true;

            foreach($files as $file)
            {
                if($file['error'] == 0)
                {
                    $pathInfo = pathinfo($file['name']);
                    $oldPath = $directory.$file['name'];

                    $filename = modResource::filterPathSegment($modx, $pathInfo['filename']); // cleanAlias (translate)
                    if ($friendly_alias_translit)
                    {
                        $filename = preg_replace('/[^A-Za-z0-9_-]/', '', $filename); // restrict segment to alphanumeric characters only
                    }
                    $filename = preg_replace('/-{2,}/','-',$filename); // remove double symbol "-"
                    $filename = trim($filename, '-'); // remove first symbol "-"

                    $newPath = $filename . '.' . strtolower($pathInfo['extension']);

                    $source->renameObject($oldPath, $newPath);
                }
            }
        }
        break;
}