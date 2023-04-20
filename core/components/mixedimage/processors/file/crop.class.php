<?php

/**
 * Crop image and save
 *
 * @package mixedimage
 */

class mixedimageCropProcessor extends modProcessor
{

    public function initialize()
    {
        $this->properties = $this->getProperties();
        return true;
    }


    public function process()
    {
        $data = $this->properties['file'];
        $suffix = $this->properties['suffix'];
        $old_value = $this->properties['value'];
        $image_array_1 = explode(";", $data);
        $image_array_2 = explode(",", $image_array_1[1]);
        $data = base64_decode($image_array_2[1]);

        $name_array = explode(".", $old_value);
        $fileinfo = pathinfo($old_value);

        if (mb_strlen($suffix) > 0) {
            if ($suffix == 'time()') {
                $suffix = "_" . time();
            } elseif (stripos($name_array[0], $suffix) !== false) {
                $suffix = '';
            }
        }

        $image_new = $name_array[0] . $suffix . '.' . $fileinfo['extension'];
        $image_cropped = MODX_BASE_PATH . $this->properties['ctx_path'] . $image_new;

        file_put_contents($image_cropped, $data);

        $this->modx->invokeEvent('OnMixedImageCrop', [
            'image' => $image_cropped,
            'tvId' => $this->properties['tvId']
        ]);

        return $image_new;
    }
}

return 'mixedimageCropProcessor';
