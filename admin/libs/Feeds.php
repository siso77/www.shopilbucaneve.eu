<?php
class Feeds
{
	private static $_instance = null;
	private $feeds;
	private $viewFeedsNum;
	
	private function __construct() 
	{
		$items = array();
		
		$this->setFeeds(FEEDS_PATH_1, 'FEEDS_1');
		$this->setFeeds(FEEDS_PATH_2, 'FEEDS_2');
		$this->setFeeds(FEEDS_PATH_3, 'FEEDS_3');
		$this->setFeeds(FEEDS_PATH_4, 'FEEDS_4');
	}
	
	private function setFeeds($FEEDS_PATH, $index)
	{
		$items = array();

		if(!stristr($FEEDS_PATH, 'http://'))
			return false;

		$configCacheKey = md5($FEEDS_PATH);
		if (!$this->feeds[$index] = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			$feedUrl = $FEEDS_PATH;
			try {
				$xml = new SimpleXMLElement($feedUrl, NULL, TRUE);
			}
			catch (Exception $e)
			{
				return false;
			}

			$title = $xml->xpath('//channel/title');
			$description = $xml->xpath('//channel/description');
			$items = $xml->xpath('//channel/item');
			
			foreach($items as $key => $val)
			{
				$this->feeds[$index][$key+1]['title'] = utf8_decode($val->title);
				$this->feeds[$index][$key+1]['description'] = utf8_decode($val->description);
			}
			if(USE_ZEND_CACHE)
				Base_CacheCore::getInstance()->save($this->feeds[$index], $configCacheKey);
		}
	}
	private function getFeedCache()
	{
		
	}
	
	static function getInstance() 
	{
		if (null === self::$_instance)
		{
			self::$_instance = new Feeds();
		}
		return self::$_instance;
	}
	
	public function setViewFeedsNum($num)
	{
		$this->viewFeedsNum = $num;
	}
	
	public function resetViewFeedsNum()
	{
		$this->viewFeedsNum = count($this->feeds)-1;
	}
	
	public function resetViewFeedsNumByIndex($index)
	{
		$this->viewFeedsNum = count($this->feeds[$index])-1;
	}

	public function getAllFeeds()
	{
		return $this->feeds;
	}
	
	public function getFeeds($index)
	{
		for($i=1;$i<=$this->viewFeedsNum;$i++)
			$ret[$i] = $this->feeds[$index][$i];

		return $ret;
	}
}