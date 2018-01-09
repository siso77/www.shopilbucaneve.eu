<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class MyAccount extends DBSmartyAction
{
	var $className;
	
	function MyAccount()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		
		if(empty($_SESSION['LoggedUser']))
		{
			$_SESSION[session_id()]['return'] = 'MyAccount';
			$this->_redirect('?act=Login');
		}
		else
		{
			if($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				$BeanUsers = new users();
				$BeanUsers->dbGetOne($this->conn, $_SESSION['LoggedUser']['id']);
				
				if(empty($_REQUEST['password']))
					unset($_REQUEST['password']);
				
				$BeanUsers->fill($_REQUEST);
				if(!empty($_REQUEST['password']) && ($_REQUEST['password'] == $_REQUEST['confirm_password']))
					$BeanUsers->setPassword(md5($_REQUEST['password']).PASSWORD_SALT);
				$BeanUsers->dbStore($this->conn);
				
				$BeanUsersAnag = new users_anag();
				$BeanUsersAnag->dbGetOne($this->conn, $BeanUsers->id_anag);
				$BeanUsersAnag->fill($_REQUEST);
				$BeanUsersAnag->dbStore($this->conn);
			}

			$BeanUsers = new users();
			$BeanUsers->dbGetOne($this->conn, $_SESSION['LoggedUser']['id']);
			$BeanUsersAnag = new users_anag();
			$BeanUsersAnag->dbGetOne($this->conn, $BeanUsers->id_anag);
			$user_data = array_merge($BeanUsers->vars(), $BeanUsersAnag->vars());
			$this->tEngine->assign('user_data', $user_data);
			$this->tEngine->assign('id_user', $BeanUsers->getId());
		}
		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>