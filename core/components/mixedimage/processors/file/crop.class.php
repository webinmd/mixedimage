<?php

/**
 * Crop image and save
 *
 * @package mixedimage
 */

class mixedimageCropProcessor extends modProcessor
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function initialize()
    {
        $this->properties = $this->getProperties();
        return true;
    }

    public function getLanguageTopics()
    {
        return ['mixedimage:default'];
    }

    public function process()
    {
        $oldValue = (string)$this->getProperty('value', '');
        if ($oldValue === '') {
            return $this->failure($this->modx->lexicon('mixedimage.err_crop_value_ns'));
        }

        $decoded = $this->decodeDataUri((string)$this->getProperty('file', ''));
        if ($decoded === null) {
            return $this->failure($this->modx->lexicon('mixedimage.err_crop_invalid_data'));
        }

        $fileInfo = pathinfo($oldValue);
        $extension = $this->resolveExtension($fileInfo, $decoded['mime']);
        $suffix = $this->resolveSuffix(
            (string)$this->getProperty('suffix', ''),
            $fileInfo['filename'] ?? pathinfo($oldValue, PATHINFO_FILENAME)
        );

        $relativePath = $this->buildRelativePath($fileInfo, $suffix, $extension);
        $absolutePath = MODX_BASE_PATH . $this->getProperty('ctx_path', '') . $relativePath;

        if (@file_put_contents($absolutePath, $decoded['data']) === false) {
            return $this->failure($this->modx->lexicon('mixedimage.err_crop_write_failed'));
        }

        $this->modx->invokeEvent('OnMixedImageCrop', [
            'image' => $absolutePath,
            'tvId' => $this->getProperty('tvId'),
        ]);

        return $this->success($relativePath);
    }

    private function decodeDataUri($dataUri)
    {
        if (!preg_match('#^data:([^;]+);base64,(.+)$#s', $dataUri, $matches)) {
            return null;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            return null;
        }

        return [
            'mime' => strtolower(trim($matches[1])),
            'data' => $binary,
        ];
    }

    private function resolveExtension(array $fileInfo, $mime)
    {
        if (!empty($fileInfo['extension'])) {
            return strtolower($fileInfo['extension']);
        }

        return self::MIME_EXTENSIONS[$mime] ?? 'png';
    }

    private function resolveSuffix($suffix, $basename)
    {
        if (mb_strlen($suffix) === 0) {
            return '';
        }

        if ($suffix === 'time()') {
            return '_' . time();
        }

        if (stripos($basename, $suffix) !== false) {
            return '';
        }

        return $suffix;
    }

    private function buildRelativePath(array $fileInfo, $suffix, $extension)
    {
        $dirname = (!empty($fileInfo['dirname']) && $fileInfo['dirname'] !== '.')
            ? $fileInfo['dirname'] . '/'
            : '';
        $filename = $fileInfo['filename'] ?? '';

        return $dirname . $filename . $suffix . '.' . $extension;
    }
}

return 'mixedimageCropProcessor';
