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
        if (!$this->getProperty('tvId')) {
            return $this->failure('нету tvID');
        }
        
        // Grab the TV object
        $TV = $this->modx->getObject('modTemplateVar',$this->getProperty('tvId'));
        if (! $TV instanceof modTemplateVar) {
            return $this->failure($this->modx->lexicon('mixedimage.error_tvid_invalid')."<br />\n[".$this->getProperty('tvId')."]");
        }       

        
        // Initialize and check perms for this mediasource
        $this->source = $TV->getSource('web');
        $this->source->initialize();
        if (!$this->source->checkPolicy('create')) {
            return $this->failure($this->modx->lexicon('permission_denied'));
        }
        
        // Grab the path option & prepare path
        $opts = unserialize($TV->input_properties);
        $path = $this->preparePath($opts['path']); 

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
        
        // Generate the file's url
        $fName = array_shift($files);
        $url = (empty($path)) ? $fName['name'] : $path.'/'.$fName['name'];
        $url = preg_replace('/\/{2,}/','/',$url); // remove double symbol "/"
       
        return $this->success(stripslashes($url));
            /* stripslashes(json_encode( (object)array('success' => true, 'msg' => $url))); */
    }
    
    /**
     * Prepare the save path using the TV's defined pathing string
     */
    private function preparePath($pathStr) {
        
        // If the pathStr starts '@SNIPPET ' then run the snippet to get path
        if (strpos($pathStr,'@SNIPPET ') !== false) {
            $snippet = str_replace('@SNIPPET ','',$pathStr);
            return $this->modx->runSnippet($snippet, $this->getProperties());
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
        return str_replace(array_keys($bits), $bits, $str);
    }
}
return 'mixedimageBrowserFileUploadProcessor';