<?php
include_once(APP_ROOT."/beans/users.php");

class AjaxSetScontoFornitoriDe extends DBSmartyAction
{
	var $className;
	
	function AjaxSetScontoFornitoriDe()
	{
		parent::DBSmartyAction();
		
		if(!empty($_REQUEST['id_user']))
		{
			$Bean = new users($this->conn, $_REQUEST['id_user']);
			$Bean->setSconto_fornitori_de($_REQUEST['sconto']);
			$Bean->dbStore($this->conn);
			echo '<br><br><span style="font-size:12px">Modifica avvenuta con successo!</span>';
		}
		exit();
	}
}
?>