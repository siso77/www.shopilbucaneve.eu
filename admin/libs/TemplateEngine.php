<?php
class BaseTemplateEngine
{
	var $template_dir;
	var $config_dir;
	var $cache_dir;
	var $lang;
	var $template_ext;
	var $intlFileExt = '.conf';
	var $assignment;
	
	var $debugging;
	var $cache_lifetime;
	
	function BaseTemplateEngine($config)
	{
		extract($config);

		if(!$_SESSION['lang'] || $_SESSION['lang'] == "")
			$_SESSION['lang'] = DEFAULT_LANG;
			
		if(!empty($intlFileExt))
			$this->intlFileExt = $intlFileExt;

		$this->setLang($_SESSION['lang']);
		$this->setTemplate_dir(APP_ROOT.$tpl_dir);
		$this->setConfig_dir(APP_ROOT.$conf_dir."/".$_SESSION['lang']);		
		$this->setCache_dir(APP_ROOT.$cache_dir);
		$this->setTemplate_ext($template_ext);
//		$this->setDebugging($debug);
//		$this->setCaching($caching);
//		$this->cache_lifetime = $cache_lifetime;
	}
	
	function setTemplate_ext($ext)
	{
		$this->template_ext = $ext;
	}
	
	function setTemplate_dir($path=null)
	{
		$this->template_dir = $path;
	}
	
	function setConfig_dir($path=null)
	{
		$this->config_dir = $path;
	}

	function setCompile_dir($path=null)
	{
		$this->compile_dir = $path;
	}

	function setCache_dir($path=null)
	{
		$this->cache_dir = $path;
	}

	function setDebugging($debug=null)
	{
		$this->debugging = $debug;
	}

	function setLang($lang)
	{
		$this->lang = $lang;
	}
	
	function getLang()
	{
		return $this->lang;
	}	
}

class TemplateEngine extends BaseTemplateEngine
{
	function TemplateEngine($config)
	{
		parent::BaseTemplateEngine($config);
	}
	
	function assign($var, $value)
	{
		$this->assignment[$var] = $value;
	}

	function display($value)
	{
		if(file_exists($this->config_dir.'/'.$value.$this->intlFileExt))
			require_once($this->config_dir.'/'.$value.$this->intlFileExt);
		$this->intl = $text;
		
		if(file_exists($this->config_dir.'/'.$_SESSION['action'].$this->intlFileExt))
			require_once($this->config_dir.'/'.$_SESSION['action'].$this->intlFileExt);
		$this->intl = array_merge($text, $this->intl);

		require($this->template_dir.'/'.$value.$this->template_ext);
	}

	function fetch($value)
	{
		ob_start();
		$this->display($value);
		$outPutBuffer = ob_get_contents();
		ob_end_clean();
		return $outPutBuffer;				
	}
		
	function getPartial($value, $data)
	{
		$assign = $data;
		require($this->template_dir.'/'.$value.$this->template_ext);
	}
	
	function getIntlPartial($value)
	{
		if(!empty($value))
		{
			require($this->config_dir.'/'.$value.$this->intlFileExt);
			$this->intl = is_array($this->intl) ? array_merge($this->intl, $text) : $text;
		}
	}

	function getText($var)
	{
		return $this->intl[$var];
	}
	
	function getVars()
	{
		return $this->assignment;
	}
	
	function getMenu() {}

	function getSeoTopMenu() 
	{
		if(file_exists($this->config_dir.'/'.'Menu'.$this->intlFileExt))
			require($this->config_dir.'/'.'Menu'.$this->intlFileExt);
		
		SeoEngine::getInstance()->setCurrentAction('Menu');
		
		$iniSection  = SeoEngine::getInstance()->getIniSection();
		$i = 0;
		$j = 0;
		foreach ($iniSection as $key => $val)
		{
			if(stristr($key, 'menu.top.text.'.$this->getLang()))
				$menu[$i]['text'] = $val;
			if(stristr($key, 'menu.top.href.'.$this->getLang()))
			{
				$i++;
				if($val == '#')
					$menu[$i]['href'] = 'javascript:void(0);';
				else
					$menu[$i]['href'] = WWW_ROOT.$val;
				$j = 0;
			}
//			elseif(stristr($key, 'menu.top.lang.key'))
//				$menu[$i]['text'] = $text[$val];
			elseif(stristr($key, 'submenu'))
			{
				if(stristr($key, 'submenu.href.'.$this->getLang()))
				{
					if($val == '#')
						$menu[$i]['submenu'][$j]['href'] = 'javascript:void(0);';
					else
						$menu[$i]['submenu'][$j]['href'] = WWW_ROOT.$val;
				}
				if(stristr($key, 'submenu.lang.key'))
				{
					$menu[$i]['submenu'][$j]['text'] = $text[$val];
					
					$j++;
				}
			}
		}
		
		return $menu;
	}
	
	function getSeoFeeds() 
	{
		SeoEngine::getInstance()->setCurrentAction('Feeds');
		
		$iniSection  = SeoEngine::getInstance()->getIniSection();
		foreach ($iniSection as $key => $val)
		{
			if(stristr($key, 'href.'.$this->getLang()))
				$feedsHref['href'] = WWW_ROOT.$val;
		}

		return $feedsHref;
	}
	
	function getSeoRoute($route) 
	{
		SeoEngine::getInstance()->setCurrentAction('Routes');
		
		$iniSection = SeoEngine::getInstance()->getIniSection();
		foreach ($iniSection as $key => $val)
		{
			if(stristr($key, 'prefix.'.$route.'.href.'.$this->getLang()))
				$routes = WWW_ROOT.$val;
		}

		return $routes;
	}
	
	function getSeoMetatag()
	{
		if(file_exists($this->config_dir.'/'.Session::get('action').$this->intlFileExt))
			require($this->config_dir.'/'.Session::get('action').$this->intlFileExt);
		
		SeoEngine::getInstance()->setCurrentAction(Session::get('action'));
		
		$iniSection = SeoEngine::getInstance()->getIniSection();
		if(is_array($iniSection))
		{
			$tplVariable = $iniSection['tpl.variable'];
			unset($iniSection['tpl.variable']);
		}
		else
			$iniSection = array();

		if(defined($PREFIX_META_TITLE))
			$PREFIX_META_TITLE = PREFIX_META_TITLE;
			
		foreach ($iniSection as $key)
		{
			$exp = explode('.', $key);
			if($exp[1] == 'title')
			{
				if(!empty($PREFIX_META_TITLE))
				{
					if(!empty($text[$exp[1]]))
						$PREFIX_META_TITLE .= PREFIX_META_TITLE_SEPARATOR;

					echo '<title>'.$PREFIX_META_TITLE.$text[$exp[1]].'</title>';
				}
				else
					echo '<title>'.$text[$exp[1]].'</title>';
			}
			else
			{
				if(!empty($this->assignment[$tplVariable])) // DA VEDERE IN BASE AI CONTENUTI NEL DATABASE
					echo '<meta name="'.$exp[1].'" content="'.$text[$exp[1]].$this->assignment[$tplVariable]['contentName'].'" />';
				else
				{
					if(!empty($text[$exp[1]]))
						echo '<meta name="'.$exp[1].'" content="'.$text[$exp[1]].'" />';
				}
			}
		}
	}

	function getMetatag() {}
	
	function getEncoding()
	{
		//return '<meta http-equiv="Content-Type" content="text/html;charset='.$this->assignment['CHARSET'].'" >';
		return '<meta content="text/html; charset='.$this->assignment['CHARSET'].'" http-equiv="Content-Type">';
	}
	
	function isValidForm($requesVars)
	{
		return true;
		if($requesVars['token'] == $_SESSION['SECURE_AUTH']['TPL_HASH'])
		{
			$diff = $this->getTimestampDifferece($_SESSION['SECURE_AUTH']['REQUEST_TIMESTAMP']);
			if($diff['minutes']*60 > FORM_REQUEST_TIMEOUT)
				return false;
			else
				return true;
		}
		else
			return false;
	}
	
	private function getTimestampDifferece($timestamp)
    {
        $now = time();
        $dateDiff    = $now-$timestamp;
        $fullDays    = floor($dateDiff/(60*60*24));
        $fullHours   = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
        $fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
        
        return array('minutes' => $fullMinutes);
        //return array('fullDays' => ceil($dateDiff/(60*60*24)),'fullHours' => $fullHours,'fullMinutes' => $fullMinutes);
    }
    
    function convertTextToUrl($value)
    {
    	$mapCharToExclude = array(':', '/', '"', '#', '?');

    	foreach ($mapCharToExclude as $chrToReplace)
    		$value = str_replace($chrToReplace, '', $value);
		return htmlentities($value);
    }
    
    function getFormatDate($date)
    {
    	$time = null;
		$exp = explode(' ', $date);
		$date = explode('-', $exp[0]);
		if(!empty($exp[1]))
			$time = explode(':', $exp[1]);
			
    	switch($this->lang)
    	{
    		case 'it':
    			$TIMESTAMP_FORMAT = IT_TIMESTAMP_FORMAT;
    		break;
    		case 'en':
    			$TIMESTAMP_FORMAT = EN_TIMESTAMP_FORMAT;
    		break;
    		case 'es':
    			$TIMESTAMP_FORMAT = ES_TIMESTAMP_FORMAT;
    		break;
    		case 'de':
    			$TIMESTAMP_FORMAT = DE_TIMESTAMP_FORMAT;
    		break;
    		default:
    		break;
    	}
    	
		if(!empty($time))
			return date($TIMESTAMP_FORMAT, mktime($time[0], $time[1], $time[2],$date[1], $date[2], $date[0]));
		else
			return date($TIMESTAMP_FORMAT, mktime(0, 0, 0,$date[1], $date[2], $date[0]));
    }
    
    function getFormatPrice($price)
    {
    	$price = str_replace('.', ',', $price);
    	$exp = explode(',', $price);
    	if(is_numeric($exp[0]))
    	{
	    	if(empty($exp[1]))
	    		$return = $exp[0].',00';
	    	else 
	    	{
	    		if(strlen($exp[1]) == 1)
	    			$return = $exp[0].','.$exp[1].'0';
	    		elseif(strlen($exp[1]) > 1)
	    			$return = $exp[0].','.$exp[1];
	    	}
    	}
    	else 
    		$return = $price;

    	return $return;
    }
	
	function truncate($data, $init, $end)
    {
    	return substr($data, $init, $end);
    }
    
    function getFormatCodiceCliente($code)
    {
    	switch (strlen($code))
    	{
    		case 1:
    			$return = '000'.$code;
    		break;
    		case 2:
    			$return = '00'.$code;
    		break;
    		case 3:
    			$return = '0'.$code;
    		break;
    		default:
    			$return = $code;
    		break;
    	}
    	return $return;
    }
    function getHomeFeeds()
	{
		Feeds::getInstance()->setViewFeedsNum(FEEDS_NUM_ROW);
		return Feeds::getInstance()->getFeeds(HOME_FEED_PATH);
	}
	
	function getImageFromIdContent($id, $dimension = 'Small_')
	{
		include_once(APP_ROOT.'/beans/images.php');
		$BeanImages = new images();
		$images = $BeanImages->dbGetAllByIdContent(MyDB::connect(), $id);
		if($images != array() && $images[0]['name'] != 'pro-bike_product_default.jpg')
			return '<img src="'.$images[0]['www_path'].'/'.$dimension.$images[0]['name'].'" alt="'.$images[0]['name'].'">';
	}

	function getImagePathFromIdContent($id, $dimension = 'Small_')
	{
		include_once(APP_ROOT.'/beans/images.php');
		$BeanImages = new images();
		$images = $BeanImages->dbGetAllByIdContent(MyDB::connect(), $id);
		if($images != array() && $images[0]['name'] != 'pro-bike_product_default.jpg')
			return $images[0]['www_path'].'/'.$dimension.$images[0]['name'];
	}
	
	function getOriginalImagePathFromIdContent($id)
	{
		include_once(APP_ROOT.'/beans/images.php');
		$BeanImages = new images();
		$images = $BeanImages->dbGetAllByIdContent(MyDB::connect(), $id);
		if($images != array() && $images[0]['name'] != 'pro-bike_product_default.jpg')
			return $images[0]['www_path'].'/'.$images[0]['name'];
	}
	
	function getColorFromIdContent($id)
	{
		include_once(APP_ROOT.'/beans/color.php');
		$Bean = new color();
		$data = $Bean->dbGetOne(MyDB::connect(), $id);

		if(!empty($data['color']))
			return $data['color'];
	}
	
	function getImageColorFromId($id)
	{
		include_once(APP_ROOT.'/beans/images_color.php');
		$Bean = new images_color();
		$data = $Bean->dbGetAllByIdColor(MyDB::connect(), $id);

		if(!empty($data['color']))
			return $data['color'];
	}
	
	function getInvoiceFromIdCustomer($data, $id_fatt = null)
	{
		$ret = null;
//		$path = APP_ROOT.'/fatture/'.$id.'/';
		$ex = explode(' ', $data['data_vendita']);
		$exp = explode('-', $ex[0]);
		$date = $exp[2].'-'.$exp[1].'-'.$exp[0];
		
		$path = APP_ROOT.'/fatture/'.$data['id_customer'].'/';
		
		if(is_dir($path))
		{
			if(!empty($id_fatt))
			{
				if(file_exists(APP_ROOT.'/fatture/'.$data['id_customer'].'/'.$data['fattura'].'_'.$data['nome'].'_'.$data['cognome'].'_'.$date.'.doc'))
					$ret[] = WWW_ROOT.'fatture/'.$data['id_customer'].'/'.$data['fattura'].'_'.$data['nome'].'_'.$data['cognome'].'_'.$date.'.doc';
			}	
//			else
//			{
//				$d = dir($path);
//				while (false !== ($entry = $d->read())) {
//					if($entry != '.' && $entry != '..')
//						$ret[] = WWW_ROOT.'fatture/'.$id.'/'.$entry;
//				}
//				$d->close();
//			}
		}

		return $ret;
	}
	
	function getLocalPathInvoiceFromIdCustomer($id, $id_fatt = null)
	{
		$ret = null;
		$path = APP_ROOT.'/fatture/'.$id.'/';
		if(is_dir($path))
		{
			if(!empty($id_fatt))
				$ret = APP_ROOT.'/fatture/'.$id.'/'.$id_fatt.'.doc';
		}
		
		return $ret;
	}
}
?>