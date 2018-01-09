<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class CheckoutShopping extends DBSmartyAction
{
	var $className;
	
	function CheckoutShopping()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);

		include_once(APP_ROOT.'/beans/ApplicationSetup.php');
		$BeanApplicationSetup = new ApplicationSetup();
		$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
		$this->tEngine->assign('spese_spedizione', $speseSpedizione[0]['name']);

		if(!empty($_REQUEST['params']))
			$this->tEngine->assign('params_banking', $_REQUEST['params']);
		
		if(empty($_SESSION['LoggedUser']))
		{
			$_SESSION[session_id()]['return'] = 'CheckoutShopping';
			$this->_redirect('?act=Login');
		}
		else
		{
			$BeanUsers = new users();
			$BeanUsers->dbGetOne($this->conn, $_SESSION['LoggedUser']['id']);
			$BeanUsersAnag = new users_anag();
			$BeanUsersAnag->dbGetOne($this->conn, $BeanUsers->id_anag);
			$user_data = array_merge($BeanUsers->vars(), $BeanUsersAnag->vars());
			$this->tEngine->assign('user_data', $user_data);
			$this->tEngine->assign('id_user', $BeanUsers->getId());
			
			$this->tEngine->assign('basket', $_SESSION[session_id()]['basket']);
		}
		
//		$beanBasket = new ecm_basket();
//		$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
		

		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>