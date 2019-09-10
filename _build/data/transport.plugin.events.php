<?php
/**
 * @package mixedimage
 * @subpackage build
 */
$events = array();

$evs = array(
    'OnDocFormPrerender',
    'OnTVInputPropertiesList',
    'OnTVInputRenderList',
    'OnTVOutputRenderList',
    'OnTVOutputRenderPropertiesList',
    'OnMODXInit',
    'OnLoadWebDocument',
    'OnManagerPageBeforeRender'
);

foreach ($evs as $ev) {
    $events[(string)$ev] = $modx->newObject('modPluginEvent');
    $events[(string)$ev]->fromArray(array(
        'event' => (string) $ev,
        'priority' => 0,
        'propertyset' => 0,
    ),'',true,true);
}

return $events;

