<?php
include_once(APP_ROOT.'/beans/percent_discount.php');

class SetupNewPercentDiscount extends DBSmartyAction
{
	var $className;
	
	function SetupNewPercentDiscount()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['id']))
		{
			$BeanPercentDiscount = new percent_discount($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('data', $BeanPercentDiscount->vars());
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
				$BeanPercentDiscount = new percent_discount($this->conn, $_REQUEST['id']);
			else
				$BeanPercentDiscount = new percent_discount();

			$BeanPercentDiscount->setData($_REQUEST['dercent_discount']);
			$id = $BeanPercentDiscount->dbStore($this->conn);

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