<?php
include_once(APP_ROOT."/beans/vendite.php");
include_once(APP_ROOT."/beans/vendite_magazzino.php");
include_once(APP_ROOT."/beans/magazzino.php");

class AjaxGetDetailVendita extends DBSmartyAction
{
	function AjaxGetDetailVendita()
	{
		parent::DBSmartyAction();

		if(!empty($_REQUEST['id_vendita']))
		{
			$search = " WHERE vendite.id = ".$_REQUEST['id_vendita']." ";

			$Bean = new vendite();
			$result = $Bean->dbSearch($this->conn, $search);
			
			$BeanVM = new vendite_magazzino();
			$result['single_sales'] = $BeanVM->dbGetAllByIdVendita($this->conn, $_REQUEST['id_vendita'], new magazzino());
			$this->tEngine->assign('data', $result);
		}

		echo $this->tEngine->fetch('shared/DivDetailVendite');
	}
}
?>