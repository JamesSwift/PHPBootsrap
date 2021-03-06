<?php
/**
 * James Swift - PHP Bootstrap
 * 
 * 
 * 
 * You are free to use, share and alter/remix this code, provided you distribute 
 * it under the same or a similar license as this work. Please clearly mark any
 * modifications you make (if extensive, a summary at the begining of the file
 * is sufficient). If you redistribute, please include a copy of the LICENSE, 
 * keep the message below intact:
 * 
 * Copyright 2014 James Swift (Creative Commons: Attribution - Share Alike - 3.0)
 * https://github.com/JamesSwift/PHPBootstrap
 * 
 * @author James Swift <swiftscripts@gmail.com>
 * @version v0.1.4
 * @package JamesSwift/PHPBootstrap
 * @copyright Copyright 2014 James Swift (Creative Commons: Attribution - Share Alike - 3.0)
 */

namespace JamesSwift\PHPBootstrap;

abstract class PHPBootstrap {
	
	abstract public function loadDefaultConfig();
	
	abstract protected function _sanitizeConfig($config);
	
	public function __construct($config=null){
		//Load default config
		$this->loadDefaultConfig();
			
		//Allow passing config straight through constructor
		if ($config!==null){
			if ($this->loadConfig($config)===false){
				throw new \Exception("Unable to load passed config.");
			}
		}
	}
	
	public function sanitizeFilePath($path, $removeLeading=false, $addTrailing=false){

		//Check we're dealing with a path
		if (!isset($path) || !is_string($path) || $path==="") {
			throw new \Exception("Cannot sanitize file-path. It must be a non-empty string.");
		}
		
		//Add trailing slash
		if ($addTrailing===true) {
			$path=$path."/";
		}
		
		//Turn all slashes round the same way and remove doubles
		$path=preg_replace('~[\\\\|\\/]+~', '/', $path);
		
		//Remove redundant references to ./
		$path=substr(str_replace("/./","/",$path."/"), 0, -1);

		//Check path for directory traversing
		if (strpos("/".$path."/", "/../")!==false) {
			throw new \Exception("Cannot sanitize file path: '".$path."'. It appears to contain an attempt at directory traversal which may be a security breach.");
		}
		
		//Remove leading slash
		if ($removeLeading===true && substr($path,0,1)==="/"){
			$path=substr($path,1);
		}
		
		return $path;
	
	}
	
	protected function _getConfigFromFile($file){
		
		//Does the file exist
		if (!is_file($file)){ return false; }

		//Atempt to decode it
		$config = json_decode(file_get_contents($file),true);
	
		//Return false on failure
		return ($config === null) ? false : $config;
	}
	
	protected function _loadSignedConfig($config){
		
		//Check we're dealing with a signed config
		if (!isset($config['signedHash'])){
			return false;
		}
		
		//Recheck hash to see if it is valid
		if ($this->_signConfig($config)!==$config['signedHash']){
			return false;
		}

		unset($config['signedHash']);
		
		//Load the signed (previously checked) variables
		$this->_forceMergeConfig($config);

		return true;
		
	}
	
	protected function _forceMergeConfig($config){
		
		//Check $config is an array
		if (!is_array($config)){
			throw new \Exception("Unable to load config. Parameter 1 must be an array");
		}
		
		//Merge with $this
		$newConfig=array();
		foreach($config as $id=>$value){
			
			//If array, try to merge
			if (is_array($value)){
				$newConfig[$id]=$this->$id=$value+$this->$id;
				
			//If not, just override
			} else {
				$newConfig[$id]=$this->$id=$value;
			}
		
		}
		
		return $newConfig;
	}
	
	public function loadConfig($loadFrom, $clearOld=false, $saveChanges=true){

		//If they called this function with no config, just return null
		if ($loadFrom===null) { return null; }
		
		//If we have been passed an array, load that
		if (is_array($loadFrom)) {
			$config=$loadFrom;
			
		//If not, try to load from JSON file
		} else if (is_string($loadFrom)){
			if (!is_file($loadFrom)) {
				throw new \Exception("Passed config file does not exist: ".$loadFrom);
			}
			$config=$this->_getConfigFromFile($loadFrom);
			if ($config===false){
				throw new \Exception("Unable to parse config file: ".$loadFrom);
			}
		}
		
		//Were we able to load $config from somewhere?
		if (!isset($config)) {
			throw new \Exception("Unable to load configuration. Please pass a config array or a valid absolute path to a config file.");
		}
		
		//Reset the class if requested
		if ($clearOld===true) {
			$this->loadDefaultConfig();
		}
		
		//Has this configuration been signed previously? (if so load it without error checking to save CPU cycles)
		if (isset($config['signedHash']) && $this->_loadSignedConfig($config) ){
			return $config;
		}

		//Sanitize configuration
		$sanitizedConfig = $this->_sanitizeConfig($config);
		
		//Apply the config
		$this->_forceMergeConfig($sanitizedConfig);
		
		//Sign this new config
		$sanitizedConfig['signedHash'] = $this->_signConfig($sanitizedConfig);		
		
		//Check if we should save changes back to the file
		if ($saveChanges===true && is_string($loadFrom)){

			//write it back to disk
			file_put_contents($loadFrom, json_encode($sanitizedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		}
		
		//Return the sanitized and signed config
		return $sanitizedConfig;
	}
	
	public function getConfig(){
		return get_object_vars($this);
	}
	
	public function getSignedConfig(){
		
		//Get config to sign
		$config = $this->getConfig();

		//Sign the config
		$config['signedHash'] = $this->_signConfig($config);
		
		//Sign and return it
		return $config;
	}
	
	protected function _signConfig($config){
		
		//Check the config array actually exists
		if (!(isset($config) && is_array($config))){
			return false;
		}
		
		//Remove any previous signature
		unset($config['signedHash']);
	
		//Stringify it and hash it
		return	hash("crc32",
				var_export($config, true).
				" <- Compatible config file for JamesSwift/PHPBootsrap by James Swift"
			);
	}
	
	public function saveConfig($file, $overwrite=false, $format="json", $varName="PHPBootsrapConfigArray"){
		
		if ($overwrite===false && is_file($file)) {
			throw new \Exception("Unable to save settings. File '".$file."' already exists, and method is in non-overwrite mode.");
		}
		
		if ($format==="json"){
			if (file_put_contents($file, json_encode($this->getSignedConfig(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) )!==false ) {
				return true;
			}
		
		} else if ($format==="php"){
			if (file_put_contents($file, "<"."?php \$".$varName." =\n".var_export($this->getSignedConfig(), true).";\n?".">")!==false ){
				return true;
			}
		}
		
		throw new \Exception("An unknown error occured and the settings could not be saved to file: ".$file);
	}
	
}



