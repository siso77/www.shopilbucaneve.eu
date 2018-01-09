<?php
class Test extends DBSmartyAction
{
	function Test()
	{
		parent::DBSmartyAction();
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			echo '<pre>';
			print_r($_REQUEST);
			exit();
		}
		
		$this->tEngine->assign('tpl_action', 'Test');
		$this->tEngine->display('Index');
	}
}
?>