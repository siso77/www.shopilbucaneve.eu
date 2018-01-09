<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_type.php");
include_once(APP_ROOT."/beans/customer.php");

class ChoiceUser extends DBSmartyAction
{
	function ChoiceUser()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['use_customer']))
		{
			$idAgent = $_SESSION['LoggedUser']['customer_data']['id_agent'];
			
			$BeanCustomer = new customer($this->conn, $_REQUEST['use_customer']);
			$_SESSION['LoggedUser']['customer_data'] = $BeanCustomer->vars();
			$_SESSION['LoggedUser']['customer_data']['id_agent'] = $idAgent;
			$_SESSION['LoggedUser']['listino'] = $BeanCustomer->listino;
			$this->_redirect('?act=Search');
			exit();
		}
		$BeanCustomer = new customer($this->conn);
		$customers = $BeanCustomer->dbSearch($this->conn, " AND codice_agente = ".$_SESSION['LoggedUser']['customer_data']['id_agent']);

		$this->tEngine->assign('customers', $customers);
		$this->tEngine->assign('tpl_action', 'ChoiceUser');
		$this->tEngine->display('Index');
	}
}
?>