<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_type.php");

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
			$result = $BeanWoraUsers->login($this->conn, $_REQUEST['username'], md5($_REQUEST['pwd']).PASSWORD_SALT);

			if(is_array($result) && $result != array())
			{
				$BeanWoraUsers->setLast_access();
				$BeanWoraUsers->dbStore($this->conn);

				$_SESSION['LoggedUser'] = $result;
				$BeanWoraUsersType = new users_type();
				$userType = $BeanWoraUsersType->dbGetOne($this->conn, $_SESSION['LoggedUser']['id_type']);

				$_SESSION['LoggedUser']['userType'] = $userType['name'];
				$this->_redirect('?act=Newsletter');
				exit();
			}
			else
			{
				$this->tEngine->assign('message', 'lbl_error_login');
				$this->tEngine->assign('post_data', $_POST);
			}
		}
		
		$this->tEngine->display('Login');
	}
}
?>