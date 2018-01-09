<?php
class Logout extends SmartyAction
{
	function Logout()
	{
		parent::SmartyAction();
		
		$_SESSION['LoggedUser'] = null;
		session_destroy();
		
		$this->tEngine->display('Login');
	}
}
?>