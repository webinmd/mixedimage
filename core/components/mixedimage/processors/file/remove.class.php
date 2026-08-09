<?php

/**
 * Remove file from TV media source
 *
 * @package mixedimage
 * @subpackage processors.browser.file
 */

require_once dirname(__FILE__) . '/tvmediasource.class.php';
require_once dirname(__FILE__) . '/MixedImageBrowserFileRemoveProcessor.class.php';

return \MixedImage\Processors\File\BrowserFileRemoveProcessor::class;
