<?php
include_once(APP_ROOT."/beans/magazzino.php");		
include_once(APP_ROOT."/beans/content.php");

class CaricaMagazzinoStepUno extends DBSmartyAction
{
	var $className;
	
	function CaricaMagazzinoStepUno()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['confirm_insert']))
			$this->tEngine->assign('confirm_insert', true);
		
		unset($_SESSION['CaricaMagazzino']);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['name_it']))
			{
				$BeanContenuti = new content();
				$Content = $BeanContenuti->dbSearch($this->conn, " AND content.name_it LIKE '%".$_REQUEST['name_it']."%' ");
				$this->tEngine->assign('content_found', $Content);
			}
			else
			{
				$BeanMagazzino = new magazzino($this->conn);
				$ContenutiFound = $BeanMagazzino->dbSearch($this->conn, " AND magazzino.bar_code = '".$_REQUEST['bar_code']."' ORDER BY magazzino.id DESC");
				
				$_SESSION['CaricaMagazzino']['bar_code_searched'] = $_REQUEST['bar_code'];
				if(!empty($ContenutiFound))
					$this->_redirect('?act=CaricaMagazzino&id_content='.$ContenutiFound[0]['id_content'].'&bar_code='.$_REQUEST['bar_code']);
				else
				{
					//$this->_redirect('?act=CaricaMagazzino&error=1');
					$this->tEngine->assign('error_content_not_found', true);
				}
			}
		}

		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>