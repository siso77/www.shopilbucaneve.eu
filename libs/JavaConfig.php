<?php
/*
$class = new JavaConfig($conf_file);
$conf = $class->getDBData();
$conf = $class->getSmartyData();
$conf = $class->getDefaultLanguageData();
$conf = $class->getVirtualIncludePathData();
$conf = $class->getVirtualIncludesData();
$conf = $class->getVirtualDefinesData();
$conf = $class->getSOSystem();
*/

class JavaConfig
{
	var $Java;

	function JavaConfig()
	{	
		/*		Non Indispensabile non riesce comunque a creare la virtual machine
		 * 
		 * system(PHP_ROOT."\php ".APP_ROOT."/createJavaObj.php > system.txt");
		 * $fp = file(APP_ROOT."/system.txt");
		 * $exp = explode("=", $fp[7]);
		 * session_decode($exp[1]);
		 * $this->Java = $_SESSION['__obj_java'];		
		*/
		if(!$_SESSION['config_application'])
		{
			$XML_CONFIG =  "<root>" .
								"<ClassName>utility.SisoXmlFile</ClassName>" .
								"<fields>" .
									"<param1>" .
										"<type>String</type>" .
										"<value>".APP_ROOT."/application.xml</value>" .
									"</param1>" .
								"</fields>" .
							"</root>";
	
			$java = new Java('Index', $XML_CONFIG);			
			$this->Java = $java->Init();
		}	
	}

	function getJava(){ return $this->Java; }

	/*		Util Functions		*/
	function getDbParams()
	{ 
		$data = $this->clearConfigData($this->Java->getDbParams());
		$data = str_replace("\"", "", $data);
		return $data; 
	}
	
	function getDBAvisItalia()
	{
		
		print_r($this->Java->getSection("database_avisitalia"));
		return $this->clearConfigData($this->Java->getSection("database_avisitalia")); 
	}

	function getSmartyTplParams()
	{ 
		return $this->clearConfigData($this->Java->getSmartyTplParams()); 
	}
	
	function getVirtualIncludePathParams()
	{ 
		return $this->clearConfigData($this->Java->getVirtualIncludePathParams());
	}
	
	function getVirtualIncludesParams()
	{ 		
		return $this->clearConfigData($this->Java->getVirtualIncludesParams()); 
	}
	
	function getVirtualDefinesParams()
	{
		return $this->clearConfigData($this->Java->getVirtualDefinesParams()); 
	}
	
	function getSoParams()
	{ 
		$data = $this->clearConfigData($this->Java->getSoParams());
		$data["system"] = str_replace("\"", "", $data["system"]);
		return $data; 
	}
	
	function clearConfigData($toClear, $section = null)
	{
		$exp = explode("|", $toClear);
		unset($exp[0]);
		foreach($exp as $toExp)
		{
			$fields = explode(" > ", $toExp);
			if(substr($fields[0], 0, 1) != ";")
			{
				if(!isset($section))
					$ret[$fields[0]] = $fields[1];
				else
					$ret[$section][$fields[0]] = $fields[1];
			} 
		}

		return $ret;
	}
	/*		Util Functions		*/
}
?>