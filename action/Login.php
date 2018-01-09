<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_type.php");
include_once(APP_ROOT."/beans/customer.php");

class Login extends DBSmartyAction
{
	function Login()
	{
		parent::DBSmartyAction();
		
		if(!$this->tEngine->isValidForm($_REQUEST))
		{
			$_SESSION['SECURE_AUTH'] = null;
			$this->_redirect('?act=Home');
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$BeanWoraUsers = new users();
			$result = $BeanWoraUsers->login($this->conn, mysql_real_escape_string($_POST['username']), md5(mysql_real_escape_string($_POST['password'])).PASSWORD_SALT);
			if(is_array($result) && $result != array())
			{
				$BeanWoraUsers->setLast_access();
				$BeanWoraUsers->dbStore($this->conn);
				
				$_SESSION['LoggedUser'] = $result;
				$BeanWoraUsersType = new users_type();
				$userType = $BeanWoraUsersType->dbGetOne($this->conn, $_SESSION['LoggedUser']['id_type']);

				$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
				$_SESSION['LoggedUser']['listino'] = $BeanCustomer->listino;
				$_SESSION['LoggedUser']['scorporo_iva'] = $BeanCustomer->scorporo_iva;
				$_SESSION['LoggedUser']['customer_data'] = $BeanCustomer->vars();
				//if($result['is_agent'] > 2)
				if(!empty($result['is_agent']))
					$_SESSION['LoggedUser']['customer_data']['id_agent'] = $result['is_agent'];

				$_SESSION['LoggedUser']['userType'] = $userType['name'];
				
				
				
				if(!empty($_SESSION['LoggedUser']['customer_data']['id_agent']))
					$this->_redirect('?act=ChoiceUser');
				
				if(!empty($_REQUEST['return_uri']))
					$_SESSION[session_id()]['return_uri'] = $_REQUEST['return_uri'];

				if(!empty($_SESSION[session_id()]['return']))
				{
					$this->_redirect('?act='.$_SESSION[session_id()]['return']);
					echo $_SESSION[session_id()]['return'];
				}
				elseif(!empty($_SESSION[session_id()]['return_uri']))
				{
// 					echo $_SESSION[session_id()]['return_uri'];
					$this->_redirect($_SESSION[session_id()]['return_uri']);
				}
				else
					$this->_redirect('Magazzino-Online/Lista-Prodotti.html');
				exit();
			}
			else
			{
//				if(empty($_REQUEST['username']))
//					echo 'error_login_username';
//				elseif(empty($_REQUEST['password']))
//					echo 'error_login_password';
//				else
//					echo 'error_login';
//				exit();
				$this->tEngine->assign('error_message', 'lbl_error_login');
				$this->tEngine->assign('post_data', $_POST);
			}
		}
		
		
		$this->tEngine->assign('tpl_action', 'Login');
		$this->tEngine->display('Index');
	}
}
?>