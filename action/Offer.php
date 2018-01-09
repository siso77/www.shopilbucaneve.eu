<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT.'/beans/banner.php');

class Offer extends DBSmartyAction
{
	function Offer()
	{
		parent::DBSmartyAction();

		$_SESSION['Home']['id_category'] = '';
		if(!empty($_REQUEST['id_category']))
			$_SESSION['Home']['id_category'] = $_REQUEST['id_category'];

		$configCacheKey = 'ecm_offer_'.$_SESSION['Home']['id_category'];
		
		$BeanCategory = new category();
		$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			$BeanContent = new content();
			if(!empty($_SESSION['Home']['id_category']))
			{
				$where = " AND category.id = ".$_SESSION['Home']['id_category']." LIMIT 0, 100";
				$data = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				
				if(empty($data))
				{
					$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $_SESSION['Home']['id_category']);
					$where = " AND category.id IN(".$_SESSION['Home']['id_category'].", ".implode(", ", $ListCategory).") AND is_in_offer > 0 ORDER BY is_in_offer ASC LIMIT 0, 100";
					
					$category = new category();
					$category->dbGetOne($this->conn, $_SESSION['Home']['id_category']);
					
					$List[$category->name] = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				}
				else
					$List[$data[0]['name']] = $data;
			}
			else
			{
				foreach ($category as $cat)
				{		
					$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $cat['id']);
					$where = " AND category.id IN(".$cat['id'].", ".implode(", ", $ListCategory).") AND is_in_offer > 0 ORDER BY is_in_offer ASC LIMIT 0, 100";
					
					$List[$cat['name']] = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				}
			}
			
			if(!empty($List))
				Base_CacheCore::getInstance()->save($List, $configCacheKey);
		}

		$this->tEngine->assign('products', $List);
		
		$this->tEngine->assign('tpl_action', 'Offer');
		$this->tEngine->display('Index');
	}
}
?>