<?php
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");

class ProductInfoFornitoriDe extends DBSmartyAction
{
	var $className;
	
	function ProductInfoFornitoriDe()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['confirm']))
			$this->tEngine->assign('confirm', true);
		if(!empty($_REQUEST['error_captcha']))
			$this->tEngine->assign('error_captcha', true);
			
		if(!empty($_SESSION['request_info']['customer_data']))
			$this->tEngine->assign('customer_data', $_SESSION['request_info']['customer_data']);
			
		$this->className = get_class($this);
		
		$_SESSION[session_id()]['return_uri'] = $_SERVER['REQUEST_URI'];
		
		if(!empty($_REQUEST['id']))
		{
			$BeanGiacenze = new giacenze_forn_gasa($this->conn, $_REQUEST['id']);
			$giacenza = $BeanGiacenze->vars();
		}
		
		if(!empty($_REQUEST['id']))
			$_SESSION[session_id()]['product_id'] = $_REQUEST['id'];

		// LOGICA PER RECUPERARE I PRODOTTI CORRELATI
// 		if(!empty($_REQUEST['id']))
// 		{
// 			$configCacheKey = 'ecm_product_info_releated_';
// 			if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
// 			{
// 				if(!empty($List))
// 					Base_CacheCore::getInstance()->save($List, $configCacheKey);
// 			}			
// 		}
//		shuffle($List);
		
// 		$this->tEngine->assign('releated', $List);
		$this->tEngine->assign('content', $giacenza);
		$this->tEngine->assign('tpl_action', 'SearchFornitoriDe/ProductInfo');
		$this->tEngine->display('Index');
	}
	
	function getCaptcha()
	{
		require_once APP_ROOT.'/securimage/securimage.php';
		$img = new securimage();
		
		$img->image_width = 260;
		$img->image_height = (int)($img->image_width * 0.35);
		
		$img->image_height = 50;
		$img->image_width = (int)($img->image_height * 2.875);
		
		$img->show();
	}
}
?>