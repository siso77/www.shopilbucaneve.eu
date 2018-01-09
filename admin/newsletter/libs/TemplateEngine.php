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
		require_once($this->template_dir.'/'.$value.$this->template_ext);
	}
	
	function fetch($value)
	{
		ob_start();
		$this->display('PageCustomPublic');
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
		$exp = explode(' ', $date);
		$date = explode('-', $exp[0]);
		$time = explode(':', $exp[1]);
		
    	switch($this->lang)
    	{
    		case 'it':
				return date(IT_TIMESTAMP_FORMAT, mktime($time[0], $time[1], $time[2],$date[1], $date[2], $date[0]));
    		break;
    		case 'en':
				return date(EN_TIMESTAMP_FORMAT, mktime($time[0], $time[1], $time[2],$date[1], $date[2], $date[0]));
    		break;
    		case 'es':
				return date(ES_TIMESTAMP_FORMAT, mktime($time[0], $time[1], $time[2],$date[1], $date[2], $date[0]));
    		break;
    		case 'de':
				return date(DE_TIMESTAMP_FORMAT, mktime($time[0], $time[1], $time[2],$date[1], $date[2], $date[0]));
    		break;
    		default:
    		break;
    	}
    }
    
    function getHomeFeeds()
	{
		Feeds::getInstance()->setViewFeedsNum(FEEDS_NUM_ROW);
		return Feeds::getInstance()->getFeeds(HOME_FEED_PATH);
	}
	
	function getComboDays()
	{
		$days = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');
		return $days;
	}
	
	function getComboMonths($format = null)
	{
		setlocale(LC_TIME, 'ita', 'it_IT');
		
		if(empty($format))
			$format = 'F';
			
		$month[1] = 'Gennaio';
		$month[2] = 'Febbraio';
		$month[3] = 'Marzo';
		$month[4] = 'Aprile';
		$month[5] = 'Maggio';
		$month[6] = 'Giugno';
		$month[7] = 'Luglio';
		$month[8] = 'Agosto';
		$month[9] = 'Settembre';
		$month[10] = 'Ottobre';
		$month[11] = 'Novembre';
		$month[12] = 'Dicembre';
//		$month[1] = date($format, mktime(0,0,0,1,date('m'),date('Y')));
//		$month[2] = date($format, mktime(0,0,0,2,date('m'),date('Y')));
//		$month[3] = date($format, mktime(0,0,0,3,date('m'),date('Y')));
//		$month[4] = date($format, mktime(0,0,0,4,date('m'),date('Y')));
//		$month[5] = date($format, mktime(0,0,0,5,date('m'),date('Y')));
//		$month[6] = date($format, mktime(0,0,0,6,date('m'),date('Y')));
//		$month[7] = date($format, mktime(0,0,0,7,date('m'),date('Y')));
//		$month[8] = date($format, mktime(0,0,0,8,date('m'),date('Y')));
//		$month[9] = date($format, mktime(0,0,0,9,date('m'),date('Y')));
//		$month[10] = date($format, mktime(0,0,0,10,date('m'),date('Y')));
//		$month[11] = date($format, mktime(0,0,0,11,date('m'),date('Y')));
//		$month[12] = date($format, mktime(0,0,0,12,date('m'),date('Y')));
		return $month;
	}
	
	function getComboYears()
	{
		$years = array(
						date('Y'),
						date('Y')+1,
						);
						
		return $years;
	}	
}
?>