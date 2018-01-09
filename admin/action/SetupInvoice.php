<?php
include_once(APP_ROOT.'/beans/index_fattura.php');


class SetupInvoice extends DBSmartyAction
{
	var $className;
	
	function SetupInvoice()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$Bean = new index_fattura();
			$Bean->custom_update($this->conn, $_REQUEST['index_invoice']);
		}
			
		$Bean = new index_fattura();
		$index = $Bean->dbGetAll($this->conn);
		$this->tEngine->assign('index', $index[0]['id']);

		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>