<?php

class StartUp
{
    public function __construct($config, $PM_BASECONFIG_DIR)
    {
        $this->config = $config;
        $this->PM_BASECONFIG_DIR = $PM_BASECONFIG_DIR;
    }
    
    /**
     * Return ini PHP array as merge of config XML files
     */
    public function getIni()
    {
        $iniXmlFile = $this->PM_BASECONFIG_DIR . "/config_{$this->config}.xml";
        if (!file_exists($iniXmlFile)) {
            error_log("P.MAPPER ERROR: cannot find config file $iniXmlFile. Check your settings");
            die();
        }
        
        $iniXml = simplexml_load_file($iniXmlFile, NULL, LIBXML_NOENT);
        $iniConfig = $this->simplexml2ISOarray($iniXml->ini);
        

        // Use a common config dir, if not defined use 'common' as default
        $commonConfig = isset($iniConfig['pm_config_common']) ? $iniConfig['pm_config_common'] : "common";
        if (file_exists($this->PM_BASECONFIG_DIR . "/config_$commonConfig.xml")) {
            $iniCommonXml = simplexml_load_file($this->PM_BASECONFIG_DIR . "/config_$commonConfig.xml", NULL, LIBXML_NOENT);
            $iniCommon = $this->simplexml2ISOarray($iniCommonXml->ini);
            if (isset($iniConfig)) {
                $ini = $this->arrayMerge($iniCommon, $iniConfig);
            } else {
                $ini = $iniCommon;
            }
        } else {
            $ini = $iniConfig;
        }
        
        $iniDefaultsXml = simplexml_load_file($this->PM_BASECONFIG_DIR . "/common/._startup_/.defaults.xml", NULL, LIBXML_NOENT);
        $iniDefaults = $this->simplexml2ISOarray($iniDefaultsXml->ini);
        
        return  $this->arrayMerge($iniDefaults, $ini);
        
    }
    
    /**
     * Deep merge of PHP arrays
     */
    public function arrayMerge ($array1, $array2) 
    {
        if (is_array($array2) && count($array2)) {
            foreach ($array2 as $k => $v) {
                if (is_array($v) && count($v) && array_key_exists($k, $array1) && is_array($array1[$k])) {
                    $array1[$k] = $this->arrayMerge($array1[$k], $v);
                } else {
                    $array1[$k] = $v;
                }
            }
        } else {
            $array1 = $array2;
        }
        return $array1;
    }
    
    
    
    /**
     * Convert SimpleXML object to PHP array structure
     */
    public function simplexml2ISOarray($xml,$attribsAsElements=0) 
    {
        if ($xml instanceof SimpleXMLElement) {
            $attributes = $xml->attributes();
            foreach($attributes as $k=>$v) {
                if ($v) $a[$k] = trim((string)$v);
            }
            $x = $xml;
            $xml = get_object_vars($xml);
        }
        if (is_array($xml)) {
            if (count($xml) == 0) return trim((string)$x); // for CDATA
            foreach($xml as $key=>$value) {
                $r[$key] = $this->simplexml2ISOarray($value,1);
                // Modified by Thomas RAFFIN (SIRAP)
                // maybe strings in XML are not utf8 encoded
                // code from http://php.net/manual/en/function.mb-detect-encoding.php#68607
                if (!is_array($r[$key])) {
    				if (!preg_match('%(?:
                                    [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
                                    |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
                                    |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
                                    |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
                                    |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
                                    |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
                                    |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
                                    )+%xs', $r[$key])) {
                		$r[$key] = utf8_decode(trim($r[$key]));
    				}
                }
                unset($r['comment']);
            }
            if (isset($a)) {
                //if($attribsAsElements) {
                    $r = array_merge($a,$r);
                //} else {
                //    $r['@'] = $a; // Attributes
                //}
            }
            return $r;
        }
        return (string) $xml;
    }

}

?>