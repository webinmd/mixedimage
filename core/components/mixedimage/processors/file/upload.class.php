<?php

/**
 * Upload files to a directory
 *
 * @param string $path The target directory
 *
 * @package mixedimage
 * @subpackage processors.browser.file
 */

if (!class_exists('\MODX\Revolution\modX')) {
    require_once MODX_CORE_PATH.'model/modx/modprocessor.class.php';
    require_once MODX_CORE_PATH.'model/modx/processors/browser/file/upload.class.php';
} else {
    class_alias(\MODX\Revolution\Processors\Browser\File\Upload::class, \modBrowserFileUploadProcessor::class);
} 

class mixedimageBrowserFileUploadProcessor extends modBrowserFileUploadProcessor
{

    public function initialize()
    {
        $this->setDefaultProperties(array(
            'source' => 1,
            'path' => false
        ));
        $this->properties = $this->getProperties();
        if (isset($this->properties['formdata'])) {
            $this->formdata = $this->modx->fromJSON($this->properties['formdata']);
        }

        return true;
    }


    public function getLanguageTopics()
    {
        $langs = parent::getLanguageTopics();
        $langs[] = 'mixedimage';
        return $langs;
    }


    public function process()
    {

        if (count($_FILES) < 1 && !$this->getProperty('url')) {
            return $this->failure($this->modx->lexicon('mixedimage.err_file_ns'));
        }

        if (!$this->getProperty('tv_id')) {
            return $this->failure($this->modx->lexicon('mixedimage.error_tvid_ns'));
        }

        $TV = $this->modx->getObject('modTemplateVar', $this->getProperty('tv_id'));

        if (!$TV instanceof modTemplateVar) {
            return $this->failure($this->modx->lexicon('mixedimage.error_tvid_invalid') . "<br />\n[" . $this->getProperty('tv_id') . "]");
        }

        $context_key = $this->formdata['context_key'];

        // Initialize and check perms for this mediasource
        $this->source = $TV->getSource($context_key); //
        $this->source->initialize();
        if (!$this->source->checkPolicy('create')) {
            return $this->failure($this->modx->lexicon('permission_denied'));
        }

        $opts = unserialize($TV->input_properties);
        $path = $this->preparePath($opts['path']);

        $checkedExt = false;

        // check extension for url file
        if ($file_url = $this->getProperty('url')) {
            $checkedExt = $this->checkFileUrlExtension($file_url, $opts);
        } else {
            $checkedExt = $this->checkFilesExtension($opts);
        }

        if (!$checkedExt) {
            return $this->failure($this->modx->lexicon('mixedimage.err_file_mime'));
        }

        // Ensure save path exists (and create it if not)
        $this->source->createContainer($path, '');
        $prefix = (empty($opts['prefix'])) ? '' : $opts['prefix'];


        if ($file_url) {
            
            if(!$files = $this->downloadFromUrl($file_url, $path, $prefix)) {
                return $this->failure($this->modx->lexicon('mixedimage.err_file_url_download'));
            }

        } else {

            $files = $this->prepareFiles($prefix);

            // Do the upload to container
            $success = $this->source->uploadObjectsToContainer($path, $files);

            // Check for upload errors
            if (empty($success)) {
                $msg = '';
                $errors = $this->source->getErrors();

                // Remove 'directory already exists' error
                if (isset($errors['name'])) {
                    unset($errors['name']);
                }

                if (count($errors) > 0) {
                    foreach ($errors as $k => $msg) {
                        $this->modx->error->addField($k, $msg);
                    }
                    return $this->failure($msg);
                }
            }
        }

        $source_path = ($this->getProperty('ctx_path')) ? $this->getProperty('ctx_path') : '';

        // Generate the file's url;
        $fName = array_shift($files);
        $url = (empty($path)) ? $fName['name'] : $path . '/' . $fName['name'];
        $url = preg_replace('/\/{2,}/', '/', $url); // remove double symbol "/"


        if ($opts['resize']) {

            $params = explode("&", $opts['resize']);

            if (count($params) < 1) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Bad options for resize  ' . $opts['resize']);
                return;
            }

            // use modxPhpThumb
            $phpThumb = $this->modx->getService('modphpthumb', 'modPhpThumb', MODX_CORE_PATH . 'model/phpthumb/', array());

            // source image
            $phpThumb->setSourceFilename(MODX_BASE_PATH . $source_path . $url);

            // set prametrs
            foreach ($params as $v) {
                $arr = explode("=", $v);
                $phpThumb->setParameter($arr['0'], $arr['1']);
            }

            if ($phpThumb->GenerateThumbnail()) {
                if (!$phpThumb->renderToFile(MODX_BASE_PATH . $source_path . $url)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not save rendered image to  ' . $url);
                }
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($phpThumb->debugmessages, 1));
            }
        }

        // check if is external source 
        if ($source_url = $this->source->getBaseUrl()) {
            if (!filter_var($source_url, FILTER_VALIDATE_URL) === false) {
                $url = $source_url . $url;
            }
        }

        return $this->success(stripslashes($url));
    }



    /**
     * Check mime type for uploading from url
     */
    private function checkFileUrlExtension($file_url, $opts)
    {
        $file_ext = pathinfo($file_url, PATHINFO_EXTENSION);
        $mime_arr = [];

        // Check the mine types
        if (!empty($opts['MIME'])) {
            $mime_arr = explode(",", $opts['MIME']);
        } else {
            $option_upload_files = $this->modx->getOption('upload_files');
            $mime_arr = explode(",", $option_upload_files);
        }

        if (!in_array($file_ext, $mime_arr)) {
            return false;
        }

        return true;
    }


    /**
     * Check mime type for uploading from form
     * array S_FILES
     */
    private function checkFilesExtension($opts)
    {
        $mime_arr = [];

        if (!empty($opts['MIME'])) {

            $mime_arr = explode(",", $opts['MIME']);
            $checkmime_files = $_FILES;

            foreach ($checkmime_files as &$file) {
                $file_mime = $file['type'];
                if (!in_array($file_mime, $mime_arr)) {
                    return false;
                }
            }
        }

        return true;
    }


    private function downloadFromUrl($file_url, $path, $prefix)
    {

        if (!$files = $this->prepareFiles($prefix, $file_url)) {
            return false;
        }

        $bases = $this->source->getBases($path); 

        //Local path of image - where will we save the image
        $file_output = fopen($bases['pathAbsoluteWithPath'] . $files[0]['name'], 'wb');

        //\\ Download and save image
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file_url);
        curl_setopt($ch, CURLOPT_FILE, $file_output);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resultStatus != 200) {
            return false;
        }
        //\\ end download file 

        return $files;
    }


    /**
     * Prepare the save path using the TV's defined pathing string
     */
    private function preparePath($pathStr)
    {

        if (!empty($_REQUEST['custompath'])) {
            return $_REQUEST['custompath'];
        }

        // If the pathStr starts '@SNIPPET ' then run the snippet to get path
        if (strpos($pathStr, '@SNIPPET ') !== false) {
            $snippet = str_replace('@SNIPPET ', '', $pathStr);
            return $this->modx->runSnippet($snippet, array('data' => $this->formdata, 'tv' => $this->getProperty('tv_id'), 'source' => $this->source->id));
        }

        // Parse path string and return it
        $path = $this->parsePlaceholders($pathStr);

        return $path;
    }

    /**
     * Prepare file name (prevent accidental overwrites)
     */
    private function prepareFiles($prefix, $file_url = false)
    {

        $mixedimage_translit = (bool)$this->modx->getOption('mixedimage.translit', null, false); // modx 2
        $system_translit = (bool)$this->modx->getOption('upload_translit', null, false); // modx 3

        // add fix for russian filename
        setlocale(LC_ALL, 'ru_RU.utf8');

        $files = [];

        if ($file_url) {
            $files[0]['name'] = $file_url;
        } else {
            $files = $_FILES;
        }

        foreach ($files as &$file) {
            $pathInfo = pathinfo($file['name']);
            $ext = $pathInfo['extension'];

            $filename = ($this->getProperty('prefixFilename') == 'true') ? $prefix : $prefix . $pathInfo['filename'];
            $filename = $this->parsePlaceholders($filename);

            if ($mixedimage_translit || $system_translit) {
                $filename = modResource::filterPathSegment($this->modx, $filename);
                $ext = strtolower($ext);
            }

            $file['name'] = $filename . '.' . $ext;
        }

        return $files;
    }



    /**
     * Parse placeholders in input fields
     */
    private function parsePlaceholders($str)
    {
        $random_lenght = (int)$this->modx->getOption('mixedimage.random_lenght', null, 6, true);
        $bits = array(
            '{id}'      => $this->getProperty('res_id'),    // Resource ID
            '{pid}'     => $this->getProperty('p_id'),      // Resource Parent ID
            '{alias}'   => $this->modx->sanitizeString($this->getProperty('res_alias')), // Resource Alias
            '{palias}'  => $this->modx->sanitizeString($this->getProperty('p_alias')),   // Resource Parent Alias
            '{context}'    => $this->formdata['context_key'], // context_key
            '{tid}'     => $this->getProperty('tv_id'),     // TV ID
            '{uid}'     => $this->modx->user->get('id'),    // User ID
            '{rand}'    => substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil($random_lenght / strlen($x)))), 1, $random_lenght), // Random string
            '{t}' => time(),    // Timestamp
            '{y}' => date('Y'), // Year
            '{m}' => date('m'), // Month
            '{d}' => date('d'), // Day
            '{h}' => date('H'), // Hour
            '{i}' => date('i'), // Minute
            '{s}' => date('s'), // Second
        );  

        $tags = explode('$|$', '[[+' . implode(']]$|$[[+', array_keys($this->formdata)) . ']]');
        $str = str_replace($tags, array_values($this->formdata), $str);

        return str_replace(array_keys($bits), $bits, $str);
    }
}
return 'mixedimageBrowserFileUploadProcessor';
