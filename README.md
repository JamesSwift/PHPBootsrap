<h1>
PHPBootstrap v0.1.3
<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US" style="float:right;"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a>
</h1>

PHPBootstrap is an abstract php class, intended to be used as a starting point when writing modules. 
For example my <a href="https://github.com/JamesSwift/SWDAPI">SWDAPI</a> and 
<a href="https://github.com/JamesSwift/ImageManager">ImageManager</a> modules both rely on this class 
to handle the following tasks:

- Setting
- Getting
- Loading and parsing config  files
- Saving config files
- "Signing" config files to allow sanitisation checks to be bypassed next time the file is loaded.
- Other simple tasks like sanitising file paths

## Examples

To use PHPBootstrap in your class:

    class MyClass extends \JamesSwift\PHPBootstrap\PHPBootstrap {
    
	//define your object variables for example:
	//public $publicVar;
	//private $_privateVar;
    
    	//Define this method:
    	public function loadDefaultConfig(){
    		//When called this should reset the class to default
    	}
    
    	//You also need to define this method
    	protected function _sanitizeConfig($config){
		//When MyClass::loadConfig() is called, it passes
		//the config array to this method which should check
		//it for errors, sanitize it and return it
		return $config;
    	}
    
    }


Just doing the above would then give you class lots of powerful tools, for example:

    $MyClass = new MyClass("config.json");

    $MyClass = new MyClass(array("publicVar"=>"something"));

    $MyClass->loadConfig("extraConfig.json");

    $MyClass->saveConfig("enewConfig.json");

    $signed = $MyClass->getSignedConfig();


## Get The Code

    git clone git://github.com/JamesSwift/PHPBootstrap.git


## License

<span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">JamesSwift\PHPBootstrap</span> by 
<a xmlns:cc="http://creativecommons.org/ns#" href="https://github.com/JamesSwift/PHPBootstrap" property="cc:attributionName" rel="cc:attributionURL">James Swift</a>
 is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US">Creative Commons Attribution-ShareAlike 3.0 Unported License</a>.
