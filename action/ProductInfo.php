<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");

class ProductInfo extends DBSmartyAction
{
	var $className;
	
	function ProductInfo()
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
			$_SESSION[session_id()]['product_id'] = $_REQUEST['id'];
		if(!empty($_REQUEST['id']))
		{
			$BeanContent = new content();
			$where = " AND content.id = ".$_REQUEST['id'];		
			$content = $BeanContent->dbSearchProductInfo($this->conn, $where, new magazzino());
// 			$configCacheKey = 'ecm_product_info_releated_'.$content[0]['id_category'];
// 			if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
// 			{
// 				// RIVEDERE LA LOGICA PER RECUPERARE I PRODOTTI CORRELATI
// 				$BeanCategory = new category();
// 				$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $content[0]['id_category']);
// 				if($ListCategory == array() || empty($ListCategory))
// 					$ListCategory = array($content[0]['id_category']);

// 				$BeanContent = new content();
// 				$where = " AND content.id_category IN(".implode(', ', $ListCategory).") AND content.id != ".$_REQUEST['id']." ORDER BY content.price_discounted_it DESC LIMIT 0, 100";
// 				$List = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				
// 				if(!empty($List))
// 					Base_CacheCore::getInstance()->save($List, $configCacheKey);
// 			}			
		}
		
		if(!empty($_REQUEST['id_giacenza']))
			$_SESSION[session_id()]['id_giacenza'] = $_REQUEST['id_giacenza'];
		if(!empty($_REQUEST['id_giacenza']))
		{
			$BeanGiacenze = new giacenze();
			$where = " AND giacenze.id = ".$_REQUEST['id_giacenza'];
			$content = $BeanGiacenze->dbSearch($this->conn, $where);
			$this->tEngine->assign('giacenza', $content);
		}
		$this->tEngine->assign('releated', $List);
		$this->tEngine->assign('content', $content);
		
		if(!empty($_REQUEST['is_ajax']))
		{
			$this->tEngine->assign('is_ajax', $_REQUEST['is_ajax']);
			echo '<div style="width:750px;">'.$this->tEngine->fetch($this->className).'</div>';
			exit();
		}
		else
		{
			$this->tEngine->assign('tpl_action', $this->className);
			$this->tEngine->display('Index');
		}
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