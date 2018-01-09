<?php
/*
$class_ini = new INI($conf_file);
$ini = $class_ini->getDBData();
$ini = $class_ini->getSmartyData();
$ini = $class_ini->getDefaultLanguageData();
$ini = $class_ini->getVirtualIncludePathData();
$ini = $class_ini->getVirtualIncludesData();
$ini = $class_ini->getVirtualDefinesData();
$ini = $class_ini->getSOSystem();

*/
class INI
{
	var $conf_file;
	var $_INI;

	function INI($conf_file=null)
	{
		if(!$conf_file)
			$conf_file = APP_ROOT."/application.ini";

		$this->setConf_file($conf_file);
		$this->__parseINI();
	}

	function setConf_file($conf_file=null)
	{
		if(isset($conf_file) && is_file($conf_file))
			$this->conf_file = $conf_file;
		else
		return new Error("Non e' stato possibile trovare il file di configurazione", __FILE__, __CLASS__, __FUNCTION__, __LINE__, "Percorso ".$conf_file);
	}

	function getConf_file()
	{
		return $this->conf_file;
	}

	function get($key)
	{
		$INI = new INI();
		
		$values = $INI->getINI();
		
		if(array_key_exists($key, $values))
			return $values[$key];
		else
			return false;
	}
	
	function getValueOf($block, $key)
	{
		$INI = new INI();
		
		$values = $INI->getINI();
	
		$match = $values[$block][$key];

		if(isset($match) && $match != "")
			return $match;
		else
			return false;
	}

	function __parseINI($multiblocks=true)
	{
		$this->_INI = parse_ini_file($this->getConf_file(), $multiblocks);
	}

	function getINI(){ return $this->_INI; }

	/*		Util Functions		*/
	function getDbParams()
	{
		$_SESSION['config_application']['database']= $this->_INI['database'];
		return $this->_INI['database']; 
	}
	
	function getDBUsersParams()
	{ 
		$_SESSION['config_application']['database_users']= $this->_INI['database_users'];
		return $this->_INI['database_users']; 
	}

	function getSmartyTplParams()
	{ 
		$_SESSION['config_application']['smarty_tpl']= $this->_INI['smarty_tpl'];
		return $this->_INI['smarty_tpl']; 
	}
	
	function getDefaultLanguageData()
	{ 
		$_SESSION['config_application']['language']= $this->_INI['language'];
		return $this->_INI['language']; 
	}
	
	function getVirtualIncludePathParams()
	{ 
		$_SESSION['config_application']['VirtualIncludePath']= $this->_INI['VirtualIncludePath'];
		return $this->_INI['VirtualIncludePath']; 
	}
	
	function getVirtualIncludesParams()
	{ 
		$_SESSION['config_application']['VirtualIncludes']= $this->_INI['VirtualIncludes'];
		return $this->_INI['VirtualIncludes']; 
	}
	
	function getVirtualDefinesParams()
	{ 
		$_SESSION['config_application']['VirtualDefines']= $this->_INI['VirtualDefines'];
		return $this->_INI['VirtualDefines']; 
	}
	
	function getSoParams(){ $_SESSION['config_application']['SO']= $this->_INI['SO'];return $this->_INI['SO']; }
	
	/*		Util Functions		*/
}
?>