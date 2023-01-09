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

        $image_array_1 = explode(";", $data);
        $image_array_2 = explode(",", $image_array_1[1]);
        $data = base64_decode($image_array_2[1]);

        $name_array = explode(".", $this->properties['value']);

        $image_new = $name_array[0] . '_crop' . '.png';
        $image_cropped = MODX_BASE_PATH . $this->properties['ctx_path'] . $image_new;

        file_put_contents($image_cropped, $data);

        return $image_new;
    }

}

return 'mixedimageCropProcessor';
