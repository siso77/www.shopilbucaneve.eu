<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/vendite.php");
include_once(APP_ROOT.'/beans/users.php');
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT.'/libs/OpenChartImplement.php');
include_once(APP_ROOT.'/libs/OpenChartPieImplement.php');
include_once(APP_ROOT.'/libs/OpenChartMultiImplement.php');

class StatsVenditeDett extends DBSmartyAction
{
	var $className;
	
	function StatsVenditeDett()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		$BeanCategory = new category();
		$Categories = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		$this->tEngine->assign('categories', $Categories);
		
		include_once(APP_ROOT."/beans/brands.php");
		$BeanBrand = new brands();
		$brands = $BeanBrand->dbGetAll(MyDB::connect(), ' name ', ' ASC ');
		$this->tEngine->assign('cmb_brand', $brands);
		
		$BeanUsers = new users();
		$users = $BeanUsers->dbGetAllCustom($this->conn, " AND operatore != 'ecommerce'");
		$this->tEngine->assign('users', $users);
		
		if(empty($_REQUEST['month']))
			$_REQUEST['month'] = (int) date('m');
		if(empty($_REQUEST['year']))
			$_REQUEST['year'] = date('Y');

		$this->tEngine->assign('month', $_REQUEST['month']);
		$this->tEngine->assign('year', $_REQUEST['year']);
		
		$this->getChartCategorySale($Categories);
		$this->getChartBrandSale($brands);
		$this->getChrtSale();
		$this->getPieSale();
		
		$this->tEngine->assign('id_category', $_SESSION['StatsVenditeDettAnno']['category']);
		$this->tEngine->assign('id_brand', $_SESSION['StatsVenditeDettAnno']['brand']);
		if(!empty($_SESSION['StatsVenditeDett']['operatore']))
			$this->tEngine->assign('operatore', $_SESSION['StatsVenditeDett']['operatore']);
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', 'StatsVenditeDett');
		$this->tEngine->display('Index');
	}

	function getChartBrandSale($brands)
	{
		foreach ($brands as $k => $brand)
		{
			if($k <= 20)
				$List[$brand['name']] = $this->getData($brand['id']);
		}
		$OpenChartMultiImplement = new OpenChartMultiImplement("Vendite X Brand ", 'Vendita (EURO)', $List);
		$this->tEngine->assign('chart_brands', $OpenChartMultiImplement->getChart());
	}

	function getChartCategorySale($Categories)
	{
		foreach ($Categories as $cat)
			$List[$cat['name']] = $this->getData(null, $cat['id']);

		$OpenChartMultiImplement = new OpenChartMultiImplement("Vendite X Categoria", 'Valuta (EURO)', $List);
		$this->tEngine->assign('chart_category', $OpenChartMultiImplement->getChart());
	}

	function getChrtSale()
	{
		$List = $this->getData();

		$OpenChartImplement = new OpenChartImplement("Vendite di Negozio dell' anno ".$_REQUEST['year'], 'Vendita mensile (EURO)');
		$OpenChartImplement->setValues($List);
		$this->tEngine->assign('chart', $OpenChartImplement->getChart());
	}

	function getPieSale()
	{
		$List = $this->getData();
		
		$OpenChartPieImplement = new OpenChartPieImplement("Vendite di Negozio dell'anno ".$_REQUEST['year'], 'Vendita mensile (EURO)', $List);
		$this->tEngine->assign('chart2', $OpenChartPieImplement->getChart());
	}

	function getData($idBrand = null, $idCat = null)
	{
		$prefixCacheKey = 'stats_sale_brand_category_'.$_REQUEST['year'].'_';
		$configCacheKey = $prefixCacheKey.$_REQUEST['id_brand'].'_'.$_REQUEST['id_category'];
		if(!empty($idCat))
			$configCacheKey = $prefixCacheKey.$idCat;
		if(!empty($idBrand))
			$configCacheKey = $prefixCacheKey.$idBrand;
			
		if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			$where = '';
			$_SESSION['StatsVenditeDettAnno']['operatore'] = $_REQUEST['operatore'];
			if(!empty($_SESSION['StatsVenditeDettAnno']['operatore']))
				$where .= " AND vendite.operatore = '".$_SESSION['StatsVenditeDettAnno']['operatore']."'";

			if(!empty($idBrand))
				$where .= " AND brands.id = '".$idBrand."'";
			elseif(empty($idBrand) && empty($idCat))
			{
				$_SESSION['StatsVenditeDettAnno']['brand'] = $_REQUEST['id_brand'];
				if(!empty($_SESSION['StatsVenditeDettAnno']['brand']))
					$where .= " AND brands.id = '".$_SESSION['StatsVenditeDettAnno']['brand']."'";
			}

			if(!empty($idCat))
				$where .= " AND category.id = '".$idCat."'";
			elseif(empty($idCat) && empty($idBrand))
			{
				$_SESSION['StatsVenditeDettAnno']['category'] = $_REQUEST['id_category'];
				if(!empty($_SESSION['StatsVenditeDettAnno']['category']))
					$where .= " AND category.id = '".$_SESSION['StatsVenditeDettAnno']['category']."'";
			}
			if(!empty($_SESSION['StatsVenditeDettAnno']['order_by']))
				$where .= ' ORDER BY '.$_SESSION['StatsVenditeDettAnno']['order_by'].' '.$_SESSION['StatsVenditeDettAnno']['order_type'];
			else
				$where .= ' ORDER BY vendite.data_vendita DESC';

			$BeanVendite = new vendite();
			for($i=0; $i <= 12;$i++)
			{
				$strData = '';
				$month = (strlen($i) == 1) ? '0'.$i : $i;
				
				if(!empty($_REQUEST['year']))
					$year = $_REQUEST['year'];
				else 
					$year = date('Y');
	
				$dateFrom = $year.'-'.$month.'-01';
				$dateTo = $year.'-'.$month.'-31';
				$amount = 0.0;

				$data = $BeanVendite->dbSearchStatsVendite($this->conn, " WHERE vendite.is_active = 1 AND vendite.data_vendita BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'".$where);

				foreach ($data as $value) 
				{
					$amount = $amount + str_replace(',', '.', $value['total']);
				}
				$List[$i] = $amount;
			}
			if(!empty($List))
				Base_CacheCore::getInstance()->save($List, $configCacheKey);
		}
		return $List;	
	}	
}
?>