<?php
class ControllerError extends SmartyAction
{
	function ControllerError()
	{
		parent::SmartyAction();
		
		$this->tEngine->assign('error_message', $_SESSION['ControllerError']['message']);
		unset($_SESSION['ControllerError']);
		$this->tEngine->assign('tpl_action', 'ControllerError');
		$this->tEngine->display('Index');
	}
}