<?php
class Logout extends Action
{
	function Logout()
	{
		parent::Action();
		
		session_destroy();
		
		if(!empty($_REQUEST['return']))
			$this->_redirect('?act='.$_REQUEST['return']);
		elseif(!empty($_REQUEST['return_uri']))
		{
			$this->_redirect(substr($_REQUEST['return_uri'], 1, strlen($_REQUEST['return_uri'])));
		}
		else
		header('Location: '.WWW_ROOT);
		exit();
	}
}
?>