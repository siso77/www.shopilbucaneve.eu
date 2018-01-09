<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT.'/beans/banner.php');

class HomeProductListing extends DBSmartyAction
{
	function HomeProductListing()
	{
		parent::DBSmartyAction();

//unset($_SESSION['HomeProductListing']);

		$_SESSION['HomeProductListing']['id_category'] = '';
		if(!empty($_REQUEST['id_category']))
			$_SESSION['HomeProductListing']['id_category'] = $_REQUEST['id_category'];

		$configCacheKey = 'ecm_content_HomeProductListing'.$_SESSION['HomeProductListing']['id_category'];
		
		$BeanCategory = new category();
		$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		
		if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			$BeanContent = new content();

			if(!empty($_SESSION['HomeProductListing']['id_category']))
			{
				$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $_SESSION['HomeProductListing']['id_category']);
				//Query per la gestione della vetrina
				if(empty($ListCategory) || $ListCategory == array())
					$where = " AND category.id IN(".$_SESSION['HomeProductListing']['id_category'].", ".implode(", ", $ListCategory).") AND is_in_evidence > 0 ORDER BY is_in_evidence ASC";
				else
					$where = " AND category.id = ".$_SESSION['HomeProductListing']['id_category']." AND is_in_evidence > 0 ORDER BY is_in_evidence ASC";
				//Query per la gestione della vetrina
					
				if(empty($ListCategory) || $ListCategory == array())
					$where = " AND category.id = ".$_SESSION['HomeProductListing']['id_category']."";
				else
					$where = " AND category.id IN(".$_SESSION['HomeProductListing']['id_category'].", ".implode(", ", $ListCategory).")";
				
				$category = new category();
				$category->dbGetOne($this->conn, $_SESSION['HomeProductListing']['id_category']);
				
				$List[$category->name] = $BeanContent->dbSearch($this->conn, $where, new magazzino());
			}
			else
			{
				foreach ($category as $cat)
				{		
					$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $cat['id']);
					//Query per la gestione della vetrina
					$where = " AND category.id IN(".$cat['id'].", ".implode(", ", $ListCategory).") AND is_in_evidence > 0 ORDER BY is_in_evidence ASC";
					//Query per la gestione della vetrina
					$where = " AND category.id IN(".$cat['id'].", ".implode(", ", $ListCategory).")";
					$List[$cat['name']] = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				}
			}

			if(!empty($List))
				Base_CacheCore::getInstance()->save($List, $configCacheKey);
		}
		$this->tEngine->assign('products', $List);
		
		
		/**
		 * Offerte
		 */
		$configCacheKey = 'ecm_offer_'.$_SESSION['HomeProductListing']['id_category'];
		
		$BeanCategory = new category();
		$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			$BeanContent = new content();
			if(!empty($_SESSION['HomeProductListing']['id_category']))
			{
				$where = " AND category.id = ".$_SESSION['HomeProductListing']['id_category']."";
				$data = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				
				if(empty($data))
				{
					$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $_SESSION['HomeProductListing']['id_category']);
					$where = " AND category.id IN(".$_SESSION['HomeProductListing']['id_category'].", ".implode(", ", $ListCategory).") AND is_in_offer > 0 ORDER BY is_in_offer ASC";
					
					$category = new category();
					$category->dbGetOne($this->conn, $_SESSION['HomeProductListing']['id_category']);
					
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
					$where = " AND category.id IN(".$cat['id'].", ".implode(", ", $ListCategory).") AND is_in_offer > 0 ORDER BY is_in_offer ASC";
					
					$List[$cat['name']] = $BeanContent->dbSearch($this->conn, $where, new magazzino());
				}
			}
			
			if(!empty($List))
				Base_CacheCore::getInstance()->save($List, $configCacheKey);
		}

		$this->tEngine->assign('offer', $List);		
		/**
		 * Offerte
		 */
		
		
		if($_SESSION['LoggedUser']['username'] == 'siso77@gmail.com' || $_SESSION['LoggedUser']['username'] == 'sandro@pro-bike.it')
		{
			$BeanContent = new content();
			$hiddenProd['Prodotto di test'] = $BeanContent->dbSearch($this->conn, ' AND content.id = 1286 ', new magazzino());
			$this->tEngine->assign('hidden_prod', $hiddenProd);
		}
		
		$this->tEngine->assign('tpl_action', 'HomeProductListing');
		$this->tEngine->display('Index');
	}
}
?>