<?php
class RicercaGenerale extends DBSmartyAction
{
	function RicercaGenerale()
	{
		parent::DBSmartyAction();

		$this->tEngine->assign('tpl_action', 'RicercaGenerale');
		$this->tEngine->display('Index');
	}
}
?>