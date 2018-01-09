<?php

class Contatti extends SmartyAction
{
	function Contatti()
	{
		parent::SmartyAction();
		
		$this->tEngine->assign('tpl_action', 'Contatti');
		$this->tEngine->display('Index');
	}
}
?>