<?php
class Logout extends Action
{
	function Logout()
	{
		parent::Action();
		
		session_destroy();
		
		header('Location: '.WWW_ROOT);
		exit();
	}
}
?>