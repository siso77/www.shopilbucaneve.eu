<?php
include_once(APP_ROOT."/beans/customer.php");
class CaricaCliente extends DBSmartyAction
{
	function CaricaCliente()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['from']))
			$this->tEngine->assign('from', $_REQUEST['from']);
			
		if(!empty($_REQUEST['id']))
		{
			$BeanCustomer = new customer($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('cliente', $BeanCustomer->vars());
		}
			
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
			{
				$BeanCustomer = new customer($this->conn, $_REQUEST['id']);
				$BeanCustomer->fill($_REQUEST);
			}
			else
			{
				$BeanCustomer = new customer($this->conn, $_REQUEST);
				$BeanCustomer->setOperatore($_SESSION['LoggedUser']['username']);
			}	
			$id_cliente = $BeanCustomer->dbStore($this->conn);

			if(!empty($_REQUEST['from']) && $_REQUEST['from'] == 'Shop')
				$this->_redirect('?act='.$_REQUEST['from'].'&show_cart=1');
			elseif(!empty($_REQUEST['from']))
				$this->_redirect('?act='.$_REQUEST['from']);
			else
				$this->_redirect('?act=ListaClienti');
		}
		$this->tEngine->assign('tpl_action', 'CaricaCliente');
		$this->tEngine->display('Index');
	}
}
?>