<?php
include_once(APP_ROOT."/beans/vendite.php");
include_once(APP_ROOT."/beans/vendite_magazzino.php");
include_once(APP_ROOT."/beans/magazzino.php");

class AjaxGetGiacenze extends DBSmartyAction
{
	function AjaxGetGiacenze()
	{
		parent::DBSmartyAction();
		
		if(!empty($_REQUEST['id_content']))
		{
			$where .= " AND magazzino.quantita >= 0 AND content.id = ".$_REQUEST['id_content']."";
//			$where .= ' AND magazzino.quantita > 0 ';
			$BeanMagazzino = new magazzino();
			$List = $BeanMagazzino->dbSearch($this->conn, $where);

			$this->tEngine->assign('id_content', $_REQUEST['id_content']);
			$this->tEngine->assign('data', $List);
		}

		echo $this->tEngine->fetch('shared/DivGetGiacenze');
	}
}
?>