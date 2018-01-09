<?php
include_once(APP_ROOT.'/beans/sizes.php');

class SetupNewSize extends DBSmartyAction
{
	var $className;
	
	function SetupNewSize()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['id']))
		{
			$BeanSize = new sizes($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('data', $BeanSize->vars());
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
				$BeanSize = new sizes($this->conn, $_REQUEST['id']);
			else
				$BeanSize = new sizes();

			$BeanSize->setSize($_REQUEST['size']);
			$BeanSize->setId_type($_REQUEST['id_size_type']);
			$id = $BeanSize->dbStore($this->conn);

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