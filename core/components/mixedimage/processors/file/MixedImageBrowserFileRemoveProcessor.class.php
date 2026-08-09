<?php

/**
 * Remove file from TV media source
 *
 * @package mixedimage
 * @subpackage processors.browser.file
 */

namespace MixedImage\Processors\File;

class BrowserFileRemoveProcessor extends \modProcessor
{
    public function getLanguageTopics()
    {
        return ['file', 'mixedimage'];
    }

    public function process()
    {
        $file = $this->getProperty('file');
        if (empty($file)) {
            return $this->failure($this->modx->lexicon('file_err_ns'));
        }

        $media = new MixedImageTvMediaSource($this);
        $media->loadFormData();

        $tv = $media->loadTemplateVar();
        if (!($tv instanceof \modTemplateVar)) {
            return $this->failure($tv);
        }

        $opts = unserialize($tv->input_properties);
        if (!$media->isYesOption($opts['removeFile'] ?? false)) {
            return $this->failure($this->modx->lexicon('permission_denied'));
        }

        $init = $media->initializeTvMediaSource($tv);
        if ($init !== true) {
            return $this->failure($init);
        }

        $removed = $media->removeFileFromSource($file);
        if ($removed !== true) {
            return $this->failure($removed);
        }

        return $this->success();
    }
}
