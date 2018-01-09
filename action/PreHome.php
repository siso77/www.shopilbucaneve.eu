<?php

include_once(APP_ROOT.'/beans/banner.php');

class PreHome extends DBSmartyMailAction
{
	function PreHome()
	{
		parent::DBSmartyMailAction();

		$this->tEngine->assign('tpl_action', 'PreHome');
		$this->tEngine->display('Index');
	}
}
?>