<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT.'/beans/banner.php');

class HomeFiltred extends DBSmartyAction
{
	function HomeFiltred()
	{
		parent::DBSmartyAction();

		$_SESSION['HomeFiltred']['id_category'] = null;
		$_SESSION['HomeFiltred']['id_brand'] = null;
		
		if(!empty($_REQUEST['id_category']))
			$_SESSION['HomeFiltred']['id_category'] = $_REQUEST['id_category'];
		else
			$_SESSION['HomeFiltred']['id_category'] = '';

		if(!empty($_REQUEST['id_brand']))
		{
			$_SESSION['HomeFiltred']['id_brand'] = $_REQUEST['id_brand'];
			$query .= ' content.id_brand = '.$_SESSION['HomeFiltred']['id_brand'];
		}
		else
			$_SESSION['HomeFiltred']['id_brand'] = null;
			
		if(!empty($_REQUEST['price_from']) && !empty($_REQUEST['price_to']))
		{
			$_SESSION['HomeFiltred']['price_from'] = $_REQUEST['price_from'];
			$_SESSION['HomeFiltred']['price_to'] = $_REQUEST['price_to'];
			
			$query .= " ( (content.price_it BETWEEN '".$_SESSION['HomeFiltred']['price_from']."' AND '".$_SESSION['HomeFiltred']['price_to']."')";
			$query .= " OR (content.price_discounted_it BETWEEN '".$_SESSION['HomeFiltred']['price_from']."' AND '".$_SESSION['HomeFiltred']['price_to']."') )";
		}
		else
		{
			$_SESSION['HomeFiltred']['price_from'] = null;
			$_SESSION['HomeFiltred']['price_to'] = null;
		}

		$configCacheKey = 'ecm_content_home_filtred_'.$_SESSION['HomeFiltred']['id_brand'].$_SESSION['HomeFiltred']['id_category'].str_replace(',','',$_SESSION['HomeFiltred']['price_to']).str_replace(',','',$_SESSION['HomeFiltred']['price_from']);
		
		$BeanCategory = new category();
		$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			$BeanContent = new content();
			if(!empty($_SESSION['HomeFiltred']['id_category']))
			{
				$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $_SESSION['Home']['id_category']);
				if(empty($ListCategory) || $ListCategory == array())
					$where = " AND category.id = ".$_SESSION['Home']['id_category']."";
				else
					$where = " AND category.id IN(".$_SESSION['Home']['id_category'].", ".implode(", ", $ListCategory).")";

				$category = new category();
				$category->dbGetOne($this->conn, $_SESSION['HomeFiltred']['id_category']);

				$List[$category->name] = $BeanContent->dbSearch($this->conn, $where.' AND '.$query, new magazzino());
			}
			
			if(!empty($List))
				Base_CacheCore::getInstance()->save($List, $configCacheKey);
		}
		$this->tEngine->assign('products', $List);
		
		
		/**
		 * Offerte
		 */
		$configCacheKey = 'ecm_offer_filtred_'.$_SESSION['HomeFiltred']['id_category'];
		
		$BeanCategory = new category();
		$category = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			$BeanContent = new content();
			if(!empty($_SESSION['HomeFiltred']['id_category']))
			{
				$where = " AND category.id = ".$_SESSION['HomeFiltred']['id_category']."";
				$data = $BeanContent->dbSearch($this->conn, $where.' AND '.$query, new magazzino());
				
				if(empty($data))
				{
					if(!empty($_SESSION['HomeFiltred']['id_category']))
						$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $_SESSION['HomeFiltred']['id_category']);
					
					$where = " AND category.id IN(".$_SESSION['HomeFiltred']['id_category'].", ".implode(", ", $ListCategory).") AND is_in_offer > 0 ORDER BY is_in_offer ASC";
					
					$category = new category();
					$category->dbGetOne($this->conn, $_SESSION['HomeFiltred']['id_category']);
					
					$List[$category->name] = $BeanContent->dbSearch($this->conn, $where.' AND '.$query, new magazzino());
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
?>