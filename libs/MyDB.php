<?php

class MyDB
{
	function &connect($dsn=null)
	{
		$configCacheKey = str_replace('.', '',APPLICATION_CONFIG_FILENAME);
		if (!$obj = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			if (stristr(APPLICATION_CONFIG_FILENAME, '.xml'))
				$obj = new xml_parser(APP_ROOT.'/'.APPLICATION_CONFIG_FILENAME);
			elseif (stristr(APPLICATION_CONFIG_FILENAME, '.ini'))
				$obj = new INI(APP_ROOT.'/'.APPLICATION_CONFIG_FILENAME);
			else
				exit('The configuration file not valid (type accepted is: .ini|.xml)');

			if($obj->getUseZendCache())
				Base_CacheCore::getInstance()->save($obj, $configCacheKey);
		}

		$db = $obj->getDbParams();

		if(isset($dsn) && (is_array($dsn) || is_string($dsn)))
		{
			$db =& DB::connect($dsn);
			if(DB::isError($db))
				die($db->getMessage());
			else
				return $db;
		}
		else
		{
			$dsn =
			array
			(
				"phptype"  => $db['Db_type'],
				"hostspec" => $db['Server'],
				"database" => $db['Database'],
				"username" => $db['User'],
				"password" => key_exists('Password', $db) ? $db['Password'] : ''
			);
			$db =& DB::connect($dsn);
 	
			if(DB::isError($db))
				die($db->getMessage());
			else
				return $db;
		}
    }
	function disconnect($conn)
	{
		$conn->disconnect();
	}
}
?>