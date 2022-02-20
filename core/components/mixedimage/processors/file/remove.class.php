<?php

/**
 * Remove file from directory
 *
 * @param string $path The target directory
 *
 * @package mixedimage
 * @subpackage processors.browser.file
 */

if (!class_exists('\MODX\Revolution\modX')) {
    require_once MODX_CORE_PATH.'model/modx/modprocessor.class.php';
    require_once MODX_CORE_PATH.'model/modx/processors/browser/file/remove.class.php';
} else {
    class_alias(\MODX\Revolution\Processors\Browser\File\Remove::class, \modBrowserFileRemoveProcessor::class);
} 

class mixedimageBrowserFileRemoveProcessor extends modBrowserFileRemoveProcessor
{

}
return 'mixedimageBrowserFileRemoveProcessor';