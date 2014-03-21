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
 * https://github.com/James-Swift/PHPBootstrap
 * 
 * @author James Swift <swiftscripts@gmail.com>
 * @package James-Swift/PHPBootstrap
 * @copyright Copyright 2013 James Swift (Creative Commons: Attribution - Share Alike - 3.0)
 */

namespace JamesSwift\ImageManager;

abstract class PHPBootstrap {

	protected $_config;
	
	
	abstract public function loadDefaultConfig($config);
	
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
		if (!isset($path) || !is_string($path) || $path==="")
			throw new \Exception("Cannot sanitize file-path. It must be a non-empty string.");
		
		//Add trailing slash
		if ($addTrailing===true) $path=$path."/";
		
		//Turn all slashes round the same way
		$path=str_replace(Array("\\","/",'\\',"//"),"/",$path);
		
		//Remove redundant references to ./
		$path=substr(str_replace("/./","/",$path."/"), 0, -1);

		//Check path for directory traversing
		if (strpos("/".$path."/", "/../")!==false)
			throw new \Exception("Cannot sanitize file path: '".$path."'. It appears to contain an attempt at directory traversal which may be a security breach.");

		//Remove leading slash
		if ($removeLeading===true && substr($path,0,1)==="/")
			$path=substr($path,1);
		
		return $path;
	
	}
}



