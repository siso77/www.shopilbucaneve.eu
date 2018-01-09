<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/vendite.php");
include_once(APP_ROOT.'/beans/users.php');
include_once(APP_ROOT.'/libs/OpenChartImplement.php');
include_once(APP_ROOT.'/libs/OpenChartPieImplement.php');

class StatsVendite extends DBSmartyAction
{
	var $className;
	
	function StatsVendite()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		$this->getDataDailySale();
		$this->getDataYearlySale();
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', 'StatsVendite');
		$this->tEngine->display('Index');
	}
	
	function getDataDailySale()
	{
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			$_SESSION['StatsVendite']['key_search'] = null;
			$_SESSION['StatsVendite']['result'] = null;
			$_SESSION['StatsVendite']['order_by'] = ' vendite.data_vendita ';
			$_SESSION['StatsVendite']['order_type'] = ' DESC ';
		}
		else
			$where = '';

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION['StatsVendite']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['StatsVendite']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['StatsVendite']['result'] = null;
		}
		
		$_SESSION['StatsVendite']['operatore'] = $_REQUEST['operatore'];
		if(!empty($_SESSION['StatsVendite']['operatore']))
			$where .= " AND vendite.operatore = '".$_SESSION['StatsVendite']['operatore']."'";

		if(!empty($_SESSION['StatsVendite']['order_by']))
			$where .= ' ORDER BY '.$_SESSION['StatsVendite']['order_by'].' '.$_SESSION['StatsVendite']['order_type'];
		else
			$where .= ' ORDER BY vendite.data_vendita DESC';

		$BeanVendite = new vendite();
		for($i=0; $i <= 31;$i++)
		{
			$strData = '';
			if(!empty($_REQUEST['month']))
				$month = $_REQUEST['month'];
			else 
				$month = date('m');
			if(!empty($_REQUEST['year']))
				$year = $_REQUEST['year'];
			else 
				$year = date('Y');
				
			$dateFrom = $year.'-'.$month.'-'.$i;
			$dateTo = $year.'-'.$month.'-'.$i;
			$amount = 0.0;

			foreach ($BeanVendite->dbSearch($this->conn, " WHERE vendite.is_active = 1 AND vendite.data_vendita BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'".$where) as $value) 
			{
				$amount = $amount + str_replace(',', '.', $value['total']);
			}
			$List[$i] = $amount;
		}
		
		if(empty($_REQUEST['month']))
			$_REQUEST['month'] = (int) date('m');
		if(empty($_REQUEST['year']))
			$_REQUEST['year'] = date('Y');

		$mapMonth = array(
							1 => 'Gennaio',
							2 => 'Febbraio',
							3 => 'Marzo',
							4 => 'Aprile',
							5 => 'Maggio',
							6 => 'Giugno',
							7 => 'Luglio',
							8 => 'Agosto',
							9 => 'Settembre',
							10 => 'Ottobre',
							11 => 'Novembre',
							12 => 'Dicembre',
							);
		$OpenChartImplement = new OpenChartImplement("Vendite di Negozio del Mese di ".$mapMonth[$_REQUEST['month']], 'Vendita giornaliera (EURO)');
		$OpenChartImplement->setValues($List);
		$this->tEngine->assign('chart', $OpenChartImplement->getChart());
		
		
		$BeanUsers = new users();
		$users = $BeanUsers->dbGetAllCustom($this->conn, " AND operatore != 'ecommerce'");
		$this->tEngine->assign('users', $users);
		
		if(!empty($_SESSION['StatsVendite']['operatore']))
			$this->tEngine->assign('operatore', $_SESSION['StatsVendite']['operatore']);
		
		$this->tEngine->assign('month', $_REQUEST['month']);
		$this->tEngine->assign('year', $_REQUEST['year']);
	}

	function getDataYearlySale()
	{
		$configCacheKey = 'stats_sale_yearly';
		if (!$List = Base_CacheCore::getInstance()->load($configCacheKey)) 
		{
			if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
			{
				$_SESSION['StatsVenditeAnno']['key_search'] = null;
				$_SESSION['StatsVenditeAnno']['result'] = null;
				$_SESSION['StatsVenditeAnno']['order_by'] = ' vendite.data_vendita ';
				$_SESSION['StatsVenditeAnno']['order_type'] = ' DESC ';
			}
			else
				$where = '';
	
			if(!empty($_REQUEST['order_by']))
			{
				$_SESSION['StatsVenditeAnno']['order_by'] = $_REQUEST['order_by'];
				$_SESSION['StatsVenditeAnno']['order_type'] = $_REQUEST['order_type'];
				$_SESSION['StatsVenditeAnno']['result'] = null;
			}
			
			$_SESSION['StatsVenditeAnno']['operatore'] = $_REQUEST['operatore'];
			if(!empty($_SESSION['StatsVenditeAnno']['operatore']))
				$where .= " AND vendite.operatore = '".$_SESSION['StatsVenditeAnno']['operatore']."'";
	
			if(!empty($_SESSION['StatsVenditeAnno']['order_by']))
				$where .= ' ORDER BY '.$_SESSION['StatsVenditeAnno']['order_by'].' '.$_SESSION['StatsVenditeAnno']['order_type'];
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
	
				foreach ($BeanVendite->dbSearch($this->conn, " WHERE vendite.is_active = 1 AND vendite.data_vendita BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'".$where) as $value) 
				{
					$amount = $amount + str_replace(',', '.', $value['total']);
				}
				$List[$i] = $amount;
			}
			if(!empty($List))
				Base_CacheCore::getInstance()->save($List, $configCacheKey);
		}
		
		if(empty($_REQUEST['month']))
			$_REQUEST['month'] = (int) date('m');
		if(empty($_REQUEST['year']))
			$_REQUEST['year'] = date('Y');

		$OpenChartPieImplement = new OpenChartPieImplement("Vendite di Negozio dell'anno ".$_REQUEST['year'], 'Vendita mensile (EURO)', $List);

		$this->tEngine->assign('chart2', $OpenChartPieImplement->getChart());
	}
}
?>