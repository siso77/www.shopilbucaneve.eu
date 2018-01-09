<?php
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");

class ListCompare extends DBSmartyAction
{
	function ListCompare()
	{
		parent::DBSmartyAction();
		
		if(!empty($_REQUEST['delete']))
		{
			$_SESSION[session_id()]['compared'] = null;
			$this->_redirect('');
		}

		$List['Lista Prodotti Confrontati'] = $_SESSION[session_id()]['compared']['contents'];
		
		$this->tEngine->assign('products', $List);
		
		/**
		 * Offerte
		 */
		if(empty($_SESSION['ListCompare']['id_category']))
			$_SESSION['ListCompare']['id_category'] = 1;

		$configCacheKey = 'ecm_offer_compare_'.$_SESSION['ListCompare']['id_category'];
		
		$BeanCategory = new category();
		$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			$BeanContent = new content();
			if(!empty($_SESSION['ListCompare']['id_category']))
			{
				$where = " AND category.id = ".$_SESSION['ListCompare']['id_category']."";
				$data = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				
				if(empty($data))
				{
					if(!empty($_SESSION['ListCompare']['id_category']))
						$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $_SESSION['ListCompare']['id_category']);
					
					$where = " AND category.id IN(".$_SESSION['ListCompare']['id_category'].", ".implode(", ", $ListCategory).") AND is_in_offer > 0 ORDER BY is_in_offer ASC";
					
					$category = new category();
					$category->dbGetOne($this->conn, $_SESSION['ListCompare']['id_category']);
					
					$List[$category->name] = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				}
				else
					$List[$data[0]['name']] = $data;
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