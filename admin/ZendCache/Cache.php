<?php
/**
 * @author Silvio Sorrentino
 * 
 * @example :
 * 
 * $cacheKey = 'You Key';
 * if (!$data = Base_CacheCore::getInstance()->load($cacheKey)) 
 * {
 *     $data = Logica da cachare
 * 	   Base_CacheCore::getInstance()->save($data, $cacheKey);
 * }
 * 
 */

interface Base_Cache
{
	const FILE_CONFIG_CORE   = 'zend_cache_core.ini';
	const FILE_CONFIG_OUTPUT = 'zend_cache_output.ini';
	
	static function getInstance();
		
	public static function clean($mode = null, $tag = array());
	
	public static function remove($key);
}

class Base_CacheCore implements Base_Cache 
{
	private static $_instance = null;

	private function __construct() {}
	
	static function getInstance() 
	{
		if (null === self::$_instance)
		{
			$cacheConfig = parse_ini_file(CACHE_CONFIG_INI_PATH . self::FILE_CONFIG_CORE);

			if(empty($cacheConfig['lifetime']))
				$cacheConfig['lifetime'] = null;

			$frontendOptions = array(
				'lifetime' => (int)$cacheConfig['lifetime'],
				'automatic_serialization' => (bool)$cacheConfig['automatic_serialization']
			);
			$backendOptions = array(
				'cache_dir' => APP_ROOT.'/'.$cacheConfig['cache_dir']
			);
			
			if(!is_dir($backendOptions['cache_dir']))
			{
				umask(0);
				mkdir($backendOptions['cache_dir'], 0777, true);
			}

			self::$_instance = Zend_Cache::factory(
													$cacheConfig['frontend'],
													$cacheConfig['backend'],
													$frontendOptions,
													$backendOptions);
		}
		return self::$_instance;
	}
	
	public static function clean($mode = null, $tag = array()) 
	{
		switch ($mode)
		{
			case 'MATCHING_TAG':
				$cleaningMode = Zend_Cache::CLEANING_MODE_MATCHING_TAG;
			break;
			case 'NOT_MATCHING_TAG':
				$cleaningMode = Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG;
			break;
			case 'MATCHING_ANY_TAG':
				$cleaningMode = Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
			break;
			case 'MODE_OLD':
				$cleaningMode = Zend_Cache::CLEANING_MODE_OLD;
			break;
			default:
				$cleaningMode = Zend_Cache::CLEANING_MODE_ALL;
			break;
		}
		self::$_instance->clean($cleaningMode, $tag);
	}	
	
	public static function remove($key)
	{
		self::$_instance->remove($key);
	}
}

class Base_CacheOutput implements Base_Cache 
{
	private static $_instance = null;

	private function __construct() {}
	
	static function getInstance() 
	{
		if (null === self::$_instance)
		{
			$cacheConfig = parse_ini_file(CACHE_CONFIG_INI_PATH . self::FILE_CONFIG_OUTPUT);

			if(empty($cacheConfig['lifetime']))
				$cacheConfig['lifetime'] = null;
			
			$frontendOptions = array(
				'lifetime' => (int)$cacheConfig['lifetime'],
				'automatic_serialization' => (bool)$cacheConfig['automatic_serialization']
			);
			$backendOptions = array(
				'cache_dir' => APP_ROOT.'/'.$cacheConfig['cache_dir']
			);

			if(!is_dir($backendOptions['cache_dir']))
			{
				umask(0);
				mkdir($backendOptions['cache_dir'], 0777, true);
			}

			self::$_instance = Zend_Cache::factory(
													$cacheConfig['frontend'],
													$cacheConfig['backend'],
													$frontendOptions,
													$backendOptions);
		}
		return self::$_instance;
	}
	
	public static function clean($mode = null, $tag = array()) 
	{
		switch ($mode)
		{
			case 'MATCHING_TAG':
				$cleaningMode = Zend_Cache::CLEANING_MODE_MATCHING_TAG;
			break;
			case 'NOT_MATCHING_TAG':
				$cleaningMode = Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG;
			break;
			case 'MATCHING_ANY_TAG':
				$cleaningMode = Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
			break;
			case 'MODE_OLD':
				$cleaningMode = Zend_Cache::CLEANING_MODE_OLD;
			break;
			default:
				$cleaningMode = Zend_Cache::CLEANING_MODE_ALL;
			break;
		}
		
		self::$_instance->clean($cleaningMode, $tag);
	}	
	
	public static function remove($key)
	{
		self::$_instance->remove($key);
	}
}

/**
 * VISUALIZZAZIONE/CANCELLAZIONE DELLA CACHE DI ZEND
 */
if(isset($_SERVER['HTTP_SHOW_ZEND_CACHE']))
{
	$d = dir('../pro-bike.ecm/cache/');
	//echo "Path: " . $d->path . "<br>";
	while (false !== ($entry = $d->read())) {
		if($entry != '.' && $entry != '..' && !stristr($entry, 'internal-metadatas'))
			echo $entry."<br>";
	}
	$d->close();
}
if(isset($_SERVER['HTTP_DELETE_ZEND_CACHE']))
	Base_CacheCore::getInstance()->clean();
