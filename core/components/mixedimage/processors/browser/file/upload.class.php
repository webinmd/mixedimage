<?php
/**
 * Upload files to a directory
 *
 * @param string $path The target directory
 *
 * @package mixedimage
 * @subpackage processors.browser.file
 */
class mixedimageBrowserFileUploadProcessor extends modBrowserFileUploadProcessor {

    public function initialize() {
        $this->setDefaultProperties(array(
            'source' => 1,
            'path' => false,
        ));
        $this->properties = $this->getProperties();
        if(isset($this->properties['formdata']))$this->formdata=$this->modx->fromJSON($this->properties['formdata']);
        return true;
    }

    public function getLanguageTopics() {
        $langs = parent::getLanguageTopics();
        $langs[] = 'mixedimage';
        return $langs;
    }

    public function process() {
        if (!$this->getSource()) {
            return $this->failure($this->modx->lexicon('permission_denied'));
        }

        // Check a file has been uploaded
        if (count($_FILES) < 1) {
            return $this->failure($this->modx->lexicon('mixedimage.err_file_ns'));
        }

        // Ensure we have been passed the TV's id
        if (!$this->getProperty('tv_id')) {
            return $this->failure($this->modx->lexicon('mixedimage.error_tvid_ns'));
        }

        // Grab the TV object
        $TV = $this->modx->getObject('modTemplateVar',$this->getProperty('tv_id'));
 
        if (! $TV instanceof modTemplateVar) { 
            return $this->failure($this->modx->lexicon('mixedimage.error_tvid_invalid')."<br />\n[".$this->getProperty('tv_id')."]");
        }
        
        $context_key=$this->formdata['context_key'];
        $RES = $this->modx->getObject('modResource',$this->getProperty('res_id'));

        // Initialize and check perms for this mediasource
        $this->source = $TV->getSource($context_key); //
        $this->source->initialize();
        if (!$this->source->checkPolicy('create')) {
            return $this->failure($this->modx->lexicon('permission_denied'));
        }  

        // Grab the path option & prepare path
        $opts = unserialize($TV->input_properties);
        $path = $this->preparePath($opts['path']);

        // Check the mine types
        if(!empty($opts['MIME'])){

            $checkmime_files = $_FILES;
            foreach ($checkmime_files as &$file) {

                $mime_arr = array();
                $mime_arr = explode(",",$opts['MIME']);

                $file_mime = $file['type'];

                if (!in_array($file_mime, $mime_arr)) {
                    return $this->failure($this->modx->lexicon('mixedimage.err_file_mime'));
                }

            }
        }

        // Ensure save path exists (and create it if not)
        $this->source->createContainer($path,'');

        // Prepare file names (prevent duplicate overwrites)
        $prefix = (empty($opts['prefix'])) ? '' : $opts['prefix'];
        $files = $this->prepareFiles($prefix);

        // Do the upload
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
                    $this->modx->error->addField($k,$msg);
                }
                return $this->failure($msg);
            }
        }

        $source_path = ($this->getProperty('ctx_path')) ? $this->getProperty('ctx_path') : '';

        // Generate the file's url;
        $fName = array_shift($files);
        $url = (empty($path)) ? $fName['name'] : $path.'/'.$fName['name'];
        $url = preg_replace('/\/{2,}/','/',$url); // remove double symbol "/"


        if($opts['resize']){

            $params = explode("&", $opts['resize']);

            if(count($params)<1){
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Bad options for resize  '.$opts['resize']);
                return;
            }

            // use modxPhpThumb
            $phpThumb = $this->modx->getService('modphpthumb','modPhpThumb', MODX_CORE_PATH . 'model/phpthumb/', array());

            // source image
            $phpThumb->setSourceFilename(MODX_BASE_PATH.$source_path.$url);

            // set prametrs
            foreach ($params as $v) {
                $arr = explode("=", $v);
                $phpThumb->setParameter($arr['0'], $arr['1']);
            }

            if ($phpThumb->GenerateThumbnail()) {
                if (!$phpThumb->renderToFile(MODX_BASE_PATH.$source_path.$url)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not save rendered image to  '.$url);
                }
            }
            else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($phpThumb->debugmessages, 1));
            }

        }

        return $this->success(stripslashes($url));
    }

    /**
     * Prepare the save path using the TV's defined pathing string
     */
    private function preparePath($pathStr) {
        
        if(!empty($_REQUEST['custompath'])){
            return $_REQUEST['custompath'];
        }

        // If the pathStr starts '@SNIPPET ' then run the snippet to get path
        if (strpos($pathStr,'@SNIPPET ') !== false) {
            $snippet = str_replace('@SNIPPET ','',$pathStr);
            return $this->modx->runSnippet($snippet,array('data'=>$this->formdata,'tv'=>$this->getProperty('tv_id'),'source'=>$this->source->id));
        }

        // Parse path string and return it
        $path = $this->parsePlaceholders($pathStr);
        return $path;
    }

    /**
     * Prepare file name (prevent accidental overwrites)
     */
    private function prepareFiles($prefix) {
        $files = $_FILES;

        $fastuploadtv_translit = (bool)$this->modx->getOption('mixedimage.translit', null, false);

        $fat = $this->modx->getOption('friendly_alias_translit');
        $friendly_alias_translit = (empty($fat) || $fat == 'none') ? false : true;

        // add fix for russian filename
        setlocale(LC_ALL, 'ru_RU.utf8');

        foreach ($files as &$file) {
            $pathInfo = pathinfo($file['name']);
            $ext = $pathInfo['extension'];

            $filename = ($this->getProperty('prefixFilename') == 'true') ? $prefix : $prefix.$pathInfo['filename'];
            $filename = $this->parsePlaceholders($filename);

            if ($fastuploadtv_translit) {
                $filename = modResource::filterPathSegment($this->modx, $filename); // cleanAlias (translate)
                if ($friendly_alias_translit) {
                    $filename = preg_replace('/[^A-Za-z0-9_-]/', '', $filename); // restrict segment to alphanumeric characters only
                }
                $filename = preg_replace('/-{2,}/','-',$filename); // remove double symbol "-"
                $filename = trim($filename, '-'); // remove first symbol "-"

                $ext = strtolower($ext);
            }

            $file['name'] = $filename . '.' . $ext;
        }
        return $files;
    }

    /**
     * Parse placeholders in input fields
     */
    private function parsePlaceholders($str) {
        $random_lenght = (int)$this->modx->getOption('mixedimage.random_lenght', null, 6, true);
        $bits = array(
            '{id}'      => $this->getProperty('res_id'),    // Resource ID
            '{pid}'     => $this->getProperty('p_id'),      // Resource Parent ID
            '{alias}'   => $this->getProperty('res_alias'), // Resource Alias
            '{palias}'  => $this->getProperty('p_alias'),   // Resource Parent Alias
            '{tid}'     => $this->getProperty('tv_id'),     // TV ID
            '{uid}'     => $this->modx->user->get('id'),    // User ID
            '{rand}'    => substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil($random_lenght/strlen($x)) )),1,$random_lenght), // Random string
            '{t}' => time(),    // Timestamp
            '{y}' => date('Y'), // Year
            '{m}' => date('m'), // Month
            '{d}' => date('d'), // Day
            '{h}' => date('H'), // Hour
            '{i}' => date('i'), // Minute
            '{s}' => date('s'), // Second
        );
        
        $tags=explode('$|$','[[+'.implode(']]$|$[[+',array_keys($this->formdata)).']]');
        $str = str_replace($tags,array_values($this->formdata),$str);
        
        return str_replace(array_keys($bits), $bits, $str);
    }
}
return 'mixedimageBrowserFileUploadProcessor';