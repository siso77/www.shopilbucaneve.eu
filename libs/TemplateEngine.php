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

	function display($value, $is_fetch = false)
	{
		if(file_exists($this->config_dir.'/'.$value.$this->intlFileExt))
			require_once($this->config_dir.'/'.$value.$this->intlFileExt);
		$this->intl = $text;
		
		if(file_exists($this->config_dir.'/'.$_SESSION['action'].$this->intlFileExt))
			require_once($this->config_dir.'/'.$_SESSION['action'].$this->intlFileExt);
		$this->intl = array_merge($text, $this->intl);

//  		if(stristr($_SERVER['HTTP_USER_AGENT'],'MSIE 10') || stristr($_SERVER['HTTP_USER_AGENT'],'MSIE 8') || stristr($_SERVER['HTTP_USER_AGENT'],'MSIE 7') && $value == 'Index')
//  		{
//  			if($value == 'Index')
// 				$value .= '_ie';
//  		}
 		
//  		if(!$is_fetch)
//  		{
// 	 		ob_start();
//  			require($this->template_dir.'/'.$value.$this->template_ext);
// 	 		$outPutBuffer = ob_get_contents();
// 	 		ob_end_clean();
// 	 		echo str_replace(array("\r", "\t", "\n"), '', $outPutBuffer);
//  		}
//  		else
 			require($this->template_dir.'/'.$value.$this->template_ext);
	}

	function fetch($value)
	{
		ob_start();
		$this->display($value, true);
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
    
    function getFormatCodiceCliente($code, $element = null)
    {
    	if(!empty($element))
    	{
    		switch (strlen($code))
    		{
    			case 1:
    				$return = $element.$element.$element.$element.$element.$element.$code;
    				break;
    			case 2:
    				$return = $element.$element.$element.$element.$element.$code;
    				break;
    			case 3:
    				$return = $element.$element.$element.$element.$code;
    				break;
    			case 4:
    				$return = $element.$element.$element.$code;
    				break;
    			case 5:
    				$return = $element.$element.$code;
    				break;
    			case 6:
    				$return = $element.$code;
    				break;
    			default:
    				$return = $code;
    				break;
    		}    		
    	}
    	else
    	{
    		switch (strlen($code))
    		{
    			case 1:
    				$return = '000000'.$code;
    				break;
    			case 2:
    				$return = '00000'.$code;
    				break;
    			case 3:
    				$return = '0000'.$code;
    				break;
    			case 4:
    				$return = '000'.$code;
    				break;
    			case 5:
    				$return = '00'.$code;
    				break;
    			case 6:
    				$return = '0'.$code;
    				break;
    			default:
    				$return = $code;
    				break;
    		}    		
    	}

    	return $return;
    }
    
	function getBlankSpaceForFlorSysOrder($str, $leght)
    {
    	$index = $leght - strlen($str);

    	for($i = 0; $i < $index;$i++)
    		$str .= ' ';
    	return $str;
    }
    
	function getCutomFormatCode($str, $leght)
    {
    	$index = $leght - strlen($str);

    	for($i = 0; $i < $index;$i++)
    		$ret .= '0';

    	return $ret.$str;
    }
    
    function getHomeFeeds()
	{
		Feeds::getInstance()->setViewFeedsNum(FEEDS_NUM_ROW);
		return Feeds::getInstance()->getFeeds(HOME_FEED_PATH);
	}
	
	function getDateAddMonth($date, $interval)
	{
		$conn = MyDB::connect();
		$query = "SELECT ('".$date."' + INTERVAL ".$interval." MONTH) as date";
		$result = mysql_query($query, $conn->connection);
		if($row = mysql_fetch_assoc($result)) 
			return $row['date'];
		else
			return $date;
	}
	
	function getImageFromVbn($vbn)
	{
		$file = WWW_VBN_IMAGE_PAHT.'/vbn_images/'.$vbn.'.jpg';
		$file_headers = @get_headers($file);

		if($file_headers[0] == 'HTTP/1.1 404 Not Found')
		{
			$file = WWW_VBN_IMAGE_PAHT.'/vbn_images/640/'.$vbn.'.jpg';
			$file_headers = @get_headers($file);
			if($file_headers[0] == 'HTTP/1.1 404 Not Found')
			{
				$file = WWW_VBN_IMAGE_PAHT.'/vbn_images/50/'.$vbn.'.jpg';
				$file_headers = @get_headers($file);
				if($file_headers[0] == 'HTTP/1.1 404 Not Found')
					return false;
				else
					return WWW_VBN_IMAGE_PAHT.'/vbn_images/50/'.$vbn.'.jpg';
					
			}
			else
				return WWW_VBN_IMAGE_PAHT.'/vbn_images/640/'.$vbn.'.jpg';
		}
		else
		{
// 			$filename = $file;
// 			$handle = fopen($filename, "r");
// 			$contents = fread($handle, filesize($filename));
// 			fclose($handle);
// 			return base64_encode($contents);
			return WWW_VBN_IMAGE_PAHT.'/vbn_images/'.$vbn.'.jpg';
		}
	}

	function dbGetImageFromBarCode($bar_code, $dimension = 'Small_')
	{
		include_once(APP_ROOT.'/beans/images_giacenze.php');
		
		$BeanImages = new images_giacenze();
		$images = $BeanImages->dbGetAllByBarCode(MyDB::connect(), $bar_code);
		
		if(!empty($images[0]['name']) && $images[0]['name'] != 'pro-bike_product_default.jpg')
			return $images;
		else
			return false;
	}

	function dbGetImageProductFromBarCode($bar_code)
	{
		if(is_file(APP_ROOT.'/FlorSysIntegration/img/'.$bar_code.'.jpg'))
			return WWW_ROOT.'FlorSysIntegration/img/'.$bar_code.'.jpg';
		elseif(is_file(APP_ROOT.'/FlorSysIntegration/img/'.$bar_code.'.JPG'))
			return WWW_ROOT.'FlorSysIntegration/img/'.$bar_code.'.JPG';
		else
			return false;
	}
	
	function getColorFromIdContent($id)
	{
		include_once(APP_ROOT.'/beans/color.php');
		$Bean = new color();
		$data = $Bean->dbGetOne(MyDB::connect(), $id);

		if(!empty($data['color']))
			return $data['color'];
	}
	
	function getColorColorImageFromId($id)
	{
		if($id != 0)
		{
			include_once(APP_ROOT.'/beans/images_color.php');
			$Bean = new images_color();
			$data = $Bean->dbGetAllByIdColor(MyDB::connect(), $id);
	
			if(!empty($data))
				return $data;
		}
		else
			return null;
	}
	
	function getBrandById($id)
	{
		if($id != 0)
		{
			include_once(APP_ROOT.'/beans/brands.php');
			$Bean = new brands();
			$Bean->dbGetOne(MyDB::connect(), $id);
			$data = $Bean->vars();
			if(!empty($data))
				return $data;
		}
		else
			return null;
	}

	function getCategoryByName($name)
	{
		include_once(APP_ROOT.'/beans/category.php');
		$Bean = new category();
		return $Bean->dbGetCategoryByName(MyDB::connect(), $name);
	}
	
	function getCategoryById($id)
	{
		include_once(APP_ROOT.'/beans/category.php');
		$Bean = new category();
		$Bean->dbGetOne(MyDB::connect(), $id);
		return $Bean->vars();
	}

	function getContentByName($name)
	{
		include_once(APP_ROOT.'/beans/content.php');
		$Bean = new content();
		return $Bean->dbSearch(MyDB::connect(), " AND content.name = '".$name."'");
	}
	
	function getContentById($id)
	{
		include_once(APP_ROOT.'/beans/content.php');
		$Bean = new content(MyDB::connect(), $id);
		return $Bean->vars();
	}
	
	function getContentByVbn($vbn)
	{
		include_once(APP_ROOT.'/beans/content.php');
		$Bean = new content();
		return $Bean->dbSearch(MyDB::connect(), " AND content.vbn = '".$vbn."'");
	}
	
	function getSizeById($id)
	{
		if($id != 0)
		{
			include_once(APP_ROOT.'/beans/sizes.php');
			$Bean = new sizes();
			$Bean->dbGetOne(MyDB::connect(), $id);
			$data = $Bean->vars();
			if(!empty($data))
				return $data;
		}
		else
			return null;
	}
	
	function getInvoiceFromIdCustomer($id, $id_fatt = null)
	{
		$ret = null;
		$path = APP_ROOT.'/fatture/'.$id.'/';
		if(is_dir($path))
		{
			if(!empty($id_fatt))
				$ret[] = WWW_ROOT.'fatture/'.$id.'/'.$id_fatt.'.pdf';
			else
			{
				$d = dir($path);
				while (false !== ($entry = $d->read())) {
					if($entry != '.' && $entry != '..')
						$ret[] = WWW_ROOT.'fatture/'.$id.'/'.$entry;
				}
				$d->close();
			}
		}

		return $ret;
	}
	
	function getGiacenzaByIdContent($idContent)
	{
		include_once(APP_ROOT."/beans/giacenze.php");
		$BeanGiacenze = new giacenze();
		$data = $BeanGiacenze->dbSearch(MyDB::connect(), " AND id_content = ".$idContent);
		
		return $data[0];
	}

	function getGiacenzaById($id)
	{
		include_once(APP_ROOT."/beans/giacenze.php");
		$BeanGiacenze = new giacenze();
		$data = $BeanGiacenze->dbSearch(MyDB::connect(), " AND id = ".$id);
	
		return $data[0];
	}
	
	function getContentExtra($idContent, $param)
	{
		include_once(APP_ROOT."/beans/content_extra.php");
		$BeanContentExtra = new content_extra();
		$data = $BeanContentExtra->dbGetAllByExtraName(MyDB::connect(), $idContent, $param);

		return $data[0]['extra_value'];
	}

	function getFamigliById($idFamiglia)
	{
		include_once(APP_ROOT."/beans/famiglie.php");
		$Bean = new famiglie(MyDB::connect(), $idFamiglia);

		return $Bean->vars();
	}

	function getGruppoById($id)
	{
		include_once(APP_ROOT."/beans/gruppi_merceologici.php");
		$Bean = new gruppi_merceologici(MyDB::connect(), $id);
	
		return $Bean->vars();
	}
	
	function getRicarico($prezzo, $percent)
	{
		if($_SESSION['LoggedUser']['username'] == 'johan@floricolturagardesana.it' || $_SESSION['LoggedUser']['username'] == 'siso')
			$percent = 0;
		
		return ($prezzo * $percent) / 100;
	}

	function getRicaricoScatola($prezzo, $percent)
	{
		if($_SESSION['LoggedUser']['username'] == 'johan@floricolturagardesana.it' || $_SESSION['LoggedUser']['username'] == 'siso')
			$percent = 0;
		
		return ($prezzo * $percent) / 100;
	}
	
	function getTranslation($words)
	{
		if($_SESSION['lang'] != 'it')
		{
			return $this->Translate($words, 'it_to_'.$_SESSION['lang']);
			//htmlspecialchars($string, ENT_COMPAT,'ISO-8859-1', true);
// 			$words = str_replace(' ', '+', $words);
// 			$details_url = "http://mymemory.translated.net/api/get?q=".$words."&langpair=it|".$_SESSION['lang'];
// 			$ch = curl_init();
// 			curl_setopt($ch, CURLOPT_URL, $details_url);
// 			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 			$response = json_decode(curl_exec($ch), true);
// 			return $response['responseData']['translatedText'];
		}
		else
			return $words;
	}
	
	function Translate($word,$conversion = 'it_to_en')
	{
		$word = urlencode($word);

		if($conversion == 'it_to_en')
			$url = 'http://translate.google.com/translate_a/t?client=t&text='.$word.'&hl=en&sl=it&tl=en&multires=1&otf=2&pc=1&ssel=0&tsel=0&sc=1';
		if($conversion == 'it_to_de')
			$url = 'http://translate.google.com/translate_a/t?client=t&text='.$word.'&hl=de&sl=it&tl=de&multires=1&otf=2&pc=1&ssel=0&tsel=0&sc=1';
		if($conversion == 'it_to_fr')
			$url = 'http://translate.google.com/translate_a/t?client=t&text='.$word.'&hl=fr&sl=it&tl=fr&multires=1&otf=2&pc=1&ssel=0&tsel=0&sc=1';
		if($conversion == 'it_to_ru')
			$url = 'http://translate.google.com/translate_a/t?client=t&text='.$word.'&hl=ru&sl=it&tl=ru&multires=1&otf=2&pc=1&ssel=0&tsel=0&sc=1';
		$name_en = $this->curl($url);
		$name_en = explode('"',$name_en);
		
		if($conversion == 'it_to_ru')
			return  $name_en[5];
		else
			return  $name_en[1];
	}
	function curl($url,$params = array(),$is_coockie_set = false)
	{
	
		if(!$is_coockie_set){
			/* STEP 1. letÕs create a cookie file */
			$ckfile = tempnam ("/tmp", "CURLCOOKIE");
	
			/* STEP 2. visit the homepage to set the cookie properly */
			$ch = curl_init ($url);
			curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec ($ch);
		}
	
		$str = ''; $str_arr= array();
		foreach($params as $key => $value)
		{
			$str_arr[] = urlencode($key)."=".urlencode($value);
		}
		if(!empty($str_arr))
			$str = '?'.implode('&',$str_arr);
	
		/* STEP 3. visit cookiepage.php */
	
		$Url = $url.$str;
	
		$ch = curl_init ($Url);
		curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	
		$output = curl_exec ($ch);
		return $output;
	}

	function getSpeseSpedizione($peso_spedizione)
	{
		include_once(APP_ROOT."/beans/spese_spedizione.php");
		$BeanSP = new spese_spedizione();
		$data = $BeanSP->dbGetAll(MyDB::connect(), 'peso_spese_spedizione_da', 'ASC');
		foreach ($data as $val)
		{
			if($peso_spedizione <= $val['peso_spese_spedizione_a'])
				return $val['spese_spedizione_peso'];
		}
	}
}
?>