<?php
include_once(APP_ROOT.'/beans/brands.php');
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT.'/beans/banner.php');

class Brand extends DBSmartyAction
{
	function Brand()
	{
		parent::DBSmartyAction();
		
		if(empty($_REQUEST['id_brand']) || !is_numeric($_REQUEST['id_brand']))
		{
			$BeanBrands = new brands();
			$brands = $BeanBrands->dbGetAllCountContent($this->conn, ' name ', ' ASC ', new content());
			$this->tEngine->assign('brands', $brands);
			$this->tEngine->assign('tpl_action', 'Brand');
			$this->tEngine->display('Index');
		}
		else 
		{
			$_SESSION['Brand']['id_brand'] = '';
			if(!empty($_REQUEST['id_brand']))
				$_SESSION['Brand']['id_brand'] = $_REQUEST['id_brand'];
	
			$configCacheKey = 'ecm_conten_brands'.$_SESSION['Brand']['id_brand'];

			$BeanCategory = new category();
			$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
			if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
			{
				$BeanContent = new content();
				foreach ($category as $cat)
				{		
					$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $cat['id']);
					$where = " AND content.id_brand = ".$_SESSION['Brand']['id_brand']." AND category.id IN(".$cat['id'].", ".implode(", ", $ListCategory).")";
					$List[$cat['name']] = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				}
				if(!empty($List))
					Base_CacheCore::getInstance()->save($List, $configCacheKey);
			}
			$this->tEngine->assign('products', $List);
			
			
			/**
			 * Offerte
			 */
			$configCacheKey = 'ecm_offer_'.$_SESSION['Brand']['id_brand'];
			
			$BeanCategory = new category();
			$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
			if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
			{
				$BeanContent = new content();

				foreach ($category as $cat)
				{		
					$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $cat['id']);
					$where = " AND content.id_brand = ".$_SESSION['Brand']['id_brand']." AND category.id IN(".$cat['id'].", ".implode(", ", $ListCategory).") AND is_in_offer > 0 ORDER BY is_in_offer ASC";
					
					$List[$cat['name']] = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				}
				
				if(!empty($List))
					Base_CacheCore::getInstance()->save($List, $configCacheKey);
			}
	
			$this->tEngine->assign('offer', $List);		
			/**
			 * Offerte
			 */
			
			$this->tEngine->assign('tpl_action', 'Home');
			$this->tEngine->display('Index');
		}
	}
}
?>