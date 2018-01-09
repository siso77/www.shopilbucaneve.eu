<?php
class SeoEngine
{
	private static $_instance = null;
	private $currentAction = null;
	private $ini = null;
	
	private function __construct() 
	{
		$this->ini = parse_ini_file(APP_ROOT.DIRECTORY_SEPARATOR.SEO_CONFIG_FILENAME, true);
	}
	
	static function getInstance() 
	{
		if (null === self::$_instance)
		{
			self::$_instance = new SeoEngine();
		}
		return self::$_instance;
	}
	
	public function setCurrentAction($currentAction)
	{
		if(isset($currentAction))
			$this->currentAction = $currentAction;
	}
	
	public function getIniSection()
	{
		return $this->ini[$this->currentAction];
	}
}
?>