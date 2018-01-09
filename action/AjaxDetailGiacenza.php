<?php

include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");

class AjaxDetailGiacenza extends DBSmartyAction
{
	function AjaxDetailGiacenza()
	{
		parent::DBSmartyAction();

		$BeanContenuti = new content($this->conn, $_REQUEST['id']);
		
		$BeanGiacenze = new giacenze();
		$data = $BeanGiacenze->dbSearch($this->conn, " AND id_content = ".$_REQUEST['id']);
		
		if(empty($data) && !empty($_REQUEST['touch']))
		{
			$data[0]['bar_code'] = '';
			$data[0]['id_content'] = $_REQUEST['id'];
			$data[0]['id_fornitore'] = 0;
			$data[0]['quantita'] = 0;
			$data[0]['disponibilita'] = 0;
		}
		
		$this->tEngine->assign('data', $data);
		$this->tEngine->assign('contenuto', $BeanContenuti->vars());
		
		if(!empty($_REQUEST['touch']))
		{
			$this->tEngine->assign('tpl_action', 'shared/AjaxDetailGiacenza');
			$this->tEngine->display('Index');
		}
		else
		{
			echo $this->tEngine->fetch('shared/AjaxDetailGiacenza');
			exit();
		}
	}
}
