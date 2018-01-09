<?php
include_once(APP_ROOT.'/beans/fornitore.php');

class NuovoFornitore extends DBSmartyAction
{
	var $className;
	
	function NuovoFornitore()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['id']))
		{
			$BeanFornitore = new fornitore($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('data', $BeanFornitore->vars());
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['save']))
				$BeanFornitore = new fornitore($this->conn, $_REQUEST);
			elseif(!empty($_REQUEST['id']))
				$BeanFornitore = new fornitore($this->conn, $_REQUEST['id']);
			else
				$BeanFornitore = new fornitore($this->conn, $_REQUEST);
				
			$BeanFornitore->setOperatore($_SESSION['LoggedUser']['username']);
			$id = $BeanFornitore->dbStore($this->conn);

			$params = '&id='.$id.'&edit=1';
			if(!empty($_REQUEST['id']))
				$params = '&id='.$_REQUEST['id'].'&edit=1';

			$this->_redirect('?act='.$this->className.$params);
		}
		
		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}