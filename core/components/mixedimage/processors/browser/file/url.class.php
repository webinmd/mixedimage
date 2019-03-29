<?php
/**
 * Upload files to a directory
 *
 * @param string $path The target directory
 *
 * @package mixedimage
 * @subpackage processors.browser.file
 */
class mixedimageBrowserFileUrlProcessor extends modBrowserFileUploadProcessor 
{

     
    public function initialize() 
    {         
        $this->properties = $this->getProperties();
        if(isset($this->properties['formdata']))$this->formdata=$this->modx->fromJSON($this->properties['formdata']);
        
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

        if (!$this->getSource()) {
            return $this->failure($this->modx->lexicon('permission_denied'));
        }

        $properties = $this->properties;

        // Ensure we have been passed the TV's id
        if (!$properties['tvId']) {
            return $this->failure($this->modx->lexicon('mixedimage.error_tvid_ns'));
        }    

        // Check a file has been uploaded
        if (!$properties['url']) {
            return $this->failure($this->modx->lexicon('mixedimage.url_empty'));
        }  

        $tv_id = $this->getProperty('tvId'); 

        // Grab the TV object
        $TV = $this->modx->getObject('modTemplateVar',$tv_id);
 
        if (! $TV instanceof modTemplateVar) { 
            return $this->failure($this->modx->lexicon('mixedimage.error_tvid_invalid')."<br />\n[".$properties['tvId']."]");
        }        
        
        $context_key = $this->formdata['context_key'];
        $RES = $this->modx->getObject('modResource',$this->formdata['id']);

        // Initialize and check perms for this mediasource
        $this->source = $TV->getSource($context_key); //
        $this->source->initialize();
        if (!$this->source->checkPolicy('create')) {
            return $this->failure($this->modx->lexicon('permission_denied'));
        }  

        // Grab the path option & prepare path
        $opts = unserialize($TV->input_properties); 
        $path = $this->preparePath($opts['path']);

        $file_ext = pathinfo($properties['url'], PATHINFO_EXTENSION);
        $file_name = pathinfo($properties['url'], PATHINFO_FILENAME); 

        // Check the mine types
        if(!empty($opts['MIME'])) {

            $mime_arr = array();
            $mime_arr = explode(",",$opts['MIME']);

            if (!in_array($file_ext, $mime_arr)) {
                return $this->failure($this->modx->lexicon('mixedimage.err_file_mime'));
            } 

        } else {

            $option_upload_files = $this->modx->getOption('upload_files');
            $mime_arr = array();
            $mime_arr = explode(",",$option_upload_files);
            if (!in_array($file_ext, $mime_arr)) {
                return $this->failure($this->modx->lexicon('mixedimage.err_file_mime'));
            } 

        }

        $this->source->createContainer($path,'');

        // Prepare file names (prevent duplicate overwrites)
        $prefix = (empty($opts['prefix'])) ? '' : $opts['prefix']; // prefix from TV settings 
        $file = $this->prepareFile($prefix, $properties['url']); 
        $bases = $this->source->getBases($path); 
 
        //Local path of image - where will we save the image
        $file_output = fopen($bases['pathAbsoluteWithPath'].$file, 'wb');

        //\\ Download and save image
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $properties['url']);
        curl_setopt($ch, CURLOPT_FILE, $file_output);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($resultStatus != 200){
            return $this->failure($this->modx->lexicon('mixedimage.err_file_url_download'));
        }
        //\\ end download file 


        $url = (empty($path)) ? $file : $path.'/'.$file;
        $url = preg_replace('/\/{2,}/','/',$url);

        $source_path = ($this->getProperty('ctx_path')) ? $this->getProperty('ctx_path') : ''; 
 
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
            } else { 
                $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($phpThumb->debugmessages, 1));
            } 
        }   
        
        return $this->success($url); 
    } 

    
    private function preparePath($pathStr) 
    {
        
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
    private function prepareFile($prefix, $url) 
    { 

        $mixedimage_translit = (bool)$this->modx->getOption('mixedimage.translit', null, false);

        $fat = $this->modx->getOption('friendly_alias_translit');
        $friendly_alias_translit = (empty($fat) || $fat == 'none') ? false : true;

        // add fix for russian filename
        setlocale(LC_ALL, 'ru_RU.utf8');

        $file_ext = pathinfo($url, PATHINFO_EXTENSION);
        $file_name =  pathinfo($url, PATHINFO_FILENAME);

        $filename = ($this->getProperty('prefixFilename') == 'true') ? $prefix : $prefix.$file_name;
        $filename = $this->parsePlaceholders($filename);

        if ($mixedimage_translit) {
            $filename = modResource::filterPathSegment($this->modx, $filename); // cleanAlias (translate)
            if ($friendly_alias_translit) {
                $filename = preg_replace('/[^A-Za-z0-9_-]/', '', $filename); // restrict segment to alphanumeric characters only
            }
            $filename = preg_replace('/-{2,}/','-',$filename); // remove double symbol "-"
            $filename = trim($filename, '-'); // remove first symbol "-"

            $file_ext = strtolower($file_ext);
        }

        $output_file = $filename . '.' . $file_ext; 

        return $output_file;
    }

    /**
     * Parse placeholders in input fields
     */
    private function parsePlaceholders($str) 
    {
        $random_lenght = (int)$this->modx->getOption('mixedimage.random_lenght', null, 6, true);
        $fields = $this->formdata;
 

        $bits = array(
            '{id}'      => $this->getProperty('res_id'),    // Resource ID
            '{pid}'     => $this->getProperty('p_id'),      // Resource Parent ID
            '{alias}'   => $this->modx->sanitizeString($this->getProperty('res_alias')), // Resource Alias
            '{palias}'  => $this->modx->sanitizeString($this->getProperty('p_alias')),   // Resource Parent Alias
            '{context}' => $this->formdata['context_key'], // context_key
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
return 'mixedimageBrowserFileUrlProcessor';