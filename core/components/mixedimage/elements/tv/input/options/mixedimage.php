<?php 
$root = $modx->getOption('core_path').'components/mixedimage/elements/tv/input/';

// Dirty hack 'cos i cant seem to get the lexicon topic loaded to js
$modx->lexicon->load('mixedimage:default');
$L = new stdClass;

$mlang = $modx->getOption('cultureKey');
$LL = $modx->lexicon->getFileTopic($mlang,'mixedimage','default');

$modx->controller->setPlaceholder('tveulex',json_encode($LL));


// Options Description
$options_desc_tpl = $modx->getOption('core_path').'components/mixedimage/lexicon/'.$mlang.'/options.desc.tpl';
if (!file_exists($options_desc_tpl)) {
    $options_desc_tpl = $modx->getOption('core_path').'components/mixedimage/lexicon/en/options.desc.tpl';
}
$modx->smarty->assign("options_desc_tpl", $options_desc_tpl);


return $modx->smarty->fetch($root.'tpl/mixedimage.options.tpl');