<?php
class configureSystem
{
	var $class_ini = false;

	function configureSystem()
	{
		$configCacheKey = str_replace('.', '',APPLICATION_CONFIG_FILENAME);
		if (!$this->class_ini = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			if (stristr(APPLICATION_CONFIG_FILENAME, '.xml'))
				$this->class_ini = new xml_parser(APP_ROOT.'/'.APPLICATION_CONFIG_FILENAME);
			elseif (stristr(APPLICATION_CONFIG_FILENAME, '.ini'))
				$this->class_ini = new INI(APP_ROOT.'/'.APPLICATION_CONFIG_FILENAME);
			else
				exit('The configuration file not valid (type accepted is: .ini|.xml)');

			if($this->class_ini->getUseZendCache())
				Base_CacheCore::getInstance()->save($this->class_ini, $configCacheKey);
		}

		$this->configure_define();
		$this->configure_include_path();
		$this->configure_include();
	}

	function configure_define()
	{
		$ini = $this->class_ini->getVirtualDefinesParams();
			
		foreach($ini as $k => $v){
			if($k == 'LOG_DIR')
				define(strtoupper($k),APP_ROOT.$v);
			else
				define(strtoupper($k), $v);
		}
	}

	function configure_include_path()
	{
		$ini = $this->class_ini->getVirtualIncludePathParams();
		$ini_system = $this->class_ini->getSoParams();

		$inc_tmp = ini_get("include_path");
		$directorySeparator = ($ini_system['SO']['System'] == "UNIX") ? ':' : ';';
		
		$inc_tmp .= $directorySeparator.APP_ROOT;
		foreach($ini as $v){
			$inc_tmp .= $directorySeparator.APP_ROOT."/".$v."/";
		}

		ini_set("include_path", $inc_tmp);
	}
	
	function configure_include()
	{
		$ini = $this->class_ini->getVirtualIncludesParams();
		foreach($ini as $v){
			include_once($v);
		}
	}
}
?>