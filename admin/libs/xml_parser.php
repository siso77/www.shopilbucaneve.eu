<?php
/*
 * $xml = new xml_parser($file);
 * $value = $xml->getDbParams();
 * $value = $xml->getSmartyTplParams(); 
 * $value = $xml->getSmartyTplParams();
 * $value = $xml->getDefaultLanguageData(); 
 * $value = $xml->getVirtualIncludePathParams(); 
 * $value = $xml->getVirtualIncludesParams();
 * $value = $xml->getVirtualDefinesParams();
 * $value = $xml->getSoParams();
 * 
 * 
 * xml_parser::parseXMLData($xml);
*/

class xml_parser
{
	var $_xml;
	var $_tags;
	var $xml_file;
	
	function xml_parser($file = null)
	{
		if(!$file)
			$file = APP_ROOT."/application.xml";
			
		$this->xml_file = $file;
		$this->makeXMLTree();
	}
	
	function makeXMLTree() 
	{
		$values = null;
		$tags = null;
		
		$open_file = fopen($this->xml_file, "r"); 
		$data = fread($open_file, filesize($this->xml_file));
		$ret = array();

		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_parse_into_struct($parser,$data,$values,$tags);
		xml_parser_free($parser);
		
		$this->_xml = $values;
		$this->_tags = $tags;
	}
	
	function parseXMLData($xml, $multiTag = "ITEM", $flag = false) 
	{
		$values = null;
		$tags = null;
		
		$ret = array();

		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_parse_into_struct($parser,$xml,$values,$tags);
		xml_parser_free($parser);
		
		$this->_xml = $values;
		$this->_tags = $tags;

		return xml_parser::_getAllCustomTag($multiTag, $flag);
	}

	function _getAllCustomTag($tag, $flag = false)
	{
		
		if(!array_key_exists($tag, $this->_tags))
			return false;

		foreach($this->_tags[$tag] as $key => $val)
		{
			if(count($this->_tags[$tag]) > 1)
			{
				$init = $this->_tags[$tag][$key];
				$end = $this->_tags[$tag][$key+1];
				for($i = $init;$i < $end; $i++)
				{
					if(array_key_exists("value", $this->_xml[$i]))
						$ret[$key][$this->_xml[$i]['tag']] = $this->_xml[$i]['value'];
					if($flag)
						$ret[$key+1][$this->_xml[$i+1]['tag']] = $this->_xml[$i+1]['value'];
				}
			}
			else
				$ret = $this->_xml[$this->_tags[$tag][0]]['attributes'];
			$key++;
		}			
		return $ret;
	}

	function _getCustomTag($tag)
	{
		
		if(!array_key_exists($tag, $this->_tags))
			return false;

		if(count($this->_tags[$tag]) > 1)
		{
			$init = $this->_tags[$tag][0];
			$end = $this->_tags[$tag][1];
			for($i = $init;$i < $end; $i++)
			{
				if(array_key_exists("value", $this->_xml[$i]))
					$ret[$tag][$this->_xml[$i]['tag']] = $this->_xml[$i]['value'];
			}
		}
		else
			$ret = $this->_xml[$this->_tags[$tag][0]]['attributes'];
			
		return $ret;
	}

	function _getProducts($tag)
	{
		
		if(!array_key_exists($tag, $this->_tags))
			return false;
		
		$i = 0;
		foreach($this->_xml as $key => $val)
		{
			if($val['type'] == "complete")
				$ret[$i][$val['tag']] = $val['value'];
			$i++;
		}
		print_r($this->_xml);exit;
		return $ret;
	}
	
	function getDbParams()
	{
		if(empty($_SERVER['APPLICATION_ENV']))
			$_SERVER['APPLICATION_ENV'] = 'pro';
			
		$ret = $this->_getCustomTag($_SERVER['APPLICATION_ENV']);
		
		return $ret[$_SERVER['APPLICATION_ENV']];
	}

	function getSmartyTplParams()
	{
		$ret = $this->_getCustomTag("smarty_tpl"); 
		return $ret['smarty_tpl'];
	}
		
	function getDefaultLanguageData()
	{ 
		$ret = $this->_getCustomTag("language");
		return $ret['language'];
	}
	
	function getVirtualIncludePathParams()
	{ 
		$ret = $this->_getCustomTag("VirtualIncludePath");
		return $ret['VirtualIncludePath'];
	}
	
	function getVirtualIncludesParams()
	{ 
		$ret = $this->_getCustomTag("VirtualIncludes");
		return $ret['VirtualIncludes'];
	}
	
	function getVirtualDefinesParams()
	{ 
		$ret = $this->_getCustomTag("VirtualDefines");
		return $ret['VirtualDefines'];
	}
	
	function getUseZendCache()
	{ 
		$ret = $this->_getCustomTag("VirtualDefines");
		return (bool)$ret['VirtualDefines']['USE_ZEND_CACHE'];
	}

	function getSoParams()
	{ 
		$ret = $this->_getCustomTag("SO");
		return $ret;  
	}
	
	function getDBUsersParams()
	{ 
		$ret = $this->_getCustomTag("database_users");
		return $ret;
	}	
}  
?>