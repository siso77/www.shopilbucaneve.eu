<?php
include_once(APP_ROOT.'/beans/size_type.php');

class SetupNewSizeType extends DBSmartyAction
{
	var $className;
	
	function SetupNewSizeType()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		if(!empty($_REQUEST['id']))
		{
			$BeanSizeType = new size_type($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('data', $BeanSizeType->vars());
		}

		if(!empty($_REQUEST['delete']))
		{
			$BeanSizeType = new size_type();
			$BeanSizeType->dbDelete($this->conn, $_REQUEST['id'], false);
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
				$BeanSizeType = new size_type($this->conn, $_REQUEST['id']);
			else
				$BeanSizeType = new size_type();

			$BeanSizeType->setType($_REQUEST['type']);
			$id = $BeanSizeType->dbStore($this->conn);

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