<?php

/**
 * Shared TV and media source helpers for mixedimage file processors.
 *
 * @package mixedimage
 */

namespace MixedImage\Processors\File;

class MixedImageTvMediaSource
{
    /** @var \modProcessor */
    private $processor;

    /** @var array<string, mixed> */
    private $formdata = [];

    /** @var \modMediaSource|null */
    private $source;

    public function __construct(\modProcessor $processor)
    {
        $this->processor = $processor;
    }

    public function setSource(\modMediaSource $source): void
    {
        $this->source = $source;
    }

    public function getSource(): ?\modMediaSource
    {
        return $this->source;
    }

    public function loadFormData(): void
    {
        if (!$this->processor->getProperty('formdata')) {
            return;
        }

        $decoded = $this->processor->modx->fromJSON($this->processor->getProperty('formdata'));
        $this->formdata = is_array($decoded) ? $decoded : [];
    }

    /**
     * @return \modTemplateVar|string Failure message
     */
    public function loadTemplateVar()
    {
        $tvId = $this->processor->getProperty('tv_id') ?: $this->processor->getProperty('tvId');
        if (empty($tvId)) {
            return $this->processor->modx->lexicon('mixedimage.error_tvid_ns');
        }

        $tv = $this->processor->modx->getObject('modTemplateVar', $tvId);
        if (!$tv instanceof \modTemplateVar) {
            return $this->processor->modx->lexicon('mixedimage.error_tvid_invalid')
                . "<br />\n[" . $tvId . "]";
        }

        return $tv;
    }

    /**
     * @param \modTemplateVar $tv
     * @return true|string
     */
    public function initializeTvMediaSource(\modTemplateVar $tv)
    {
        $contextKey = $this->formdata['context_key'] ?? 'web';
        $this->source = $tv->getSource($contextKey);
        if (!$this->source instanceof \modMediaSource) {
            return $this->processor->modx->lexicon('mixedimage.error_remove');
        }
        $this->source->initialize();

        return true;
    }

    public function isYesOption($value): bool
    {
        if ($value === true || $value === 1 || $value === '1') {
            return true;
        }

        $normalized = strtolower((string)$value);

        return in_array($normalized, ['yes', 'true', 'on'], true)
            || $value === $this->processor->modx->lexicon('yes');
    }

    public function normalizeRelativePath(string $file): string
    {
        $file = preg_replace('/[\.]{2,}/', '', $file);
        $file = ltrim($file, '/');

        if ($this->source instanceof \modMediaSource) {
            $baseUrl = $this->source->getBaseUrl();
            if (!empty($baseUrl) && filter_var($baseUrl, FILTER_VALIDATE_URL) && strpos($file, $baseUrl) === 0) {
                $file = substr($file, strlen($baseUrl));
            }
        }

        return ltrim($file, '/');
    }

    /**
     * @return true|string
     */
    public function removeFileFromSource(string $file)
    {
        if (!$this->source instanceof \modMediaSource) {
            return $this->processor->modx->lexicon('mixedimage.error_remove');
        }

        if (!$this->source->checkPolicy('remove')) {
            return $this->processor->modx->lexicon('permission_denied');
        }

        $file = $this->normalizeRelativePath($file);
        if ($file === '') {
            return $this->processor->modx->lexicon('file_err_ns');
        }

        if (!$this->source->removeObject($file)) {
            $errors = $this->source->getErrors();

            return !empty($errors)
                ? implode("\n", $errors)
                : $this->processor->modx->lexicon('mixedimage.error_remove');
        }

        return true;
    }
}
