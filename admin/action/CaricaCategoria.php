<?php
include_once(APP_ROOT.'/beans/category.php');

class CaricaCategoria extends DBSmartyAction
{
	var $className;
	
	function CaricaCategoria()
	{
	parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['id']))
		{
			$BeanCategory = new category($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('data', $BeanCategory->vars());
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
			{
				$BeanCategory = new category($this->conn, $_REQUEST['id']);
				$BeanCategory->fill($_REQUEST);
			}
			else
				$BeanCategory = new category($this->conn, $_REQUEST);
				
			$id = $BeanCategory->dbStore($this->conn);
				
			if($_REQUEST['parent_id'] == "0")
				$BeanCategory->setUrl("parentId=".$BeanCategory->getId());
			else
				$BeanCategory->setUrl("idCategory=".$BeanCategory->getId());
				
			$BeanCategory->dbStore($this->conn);

			$params = '&id='.$id.'&edit=1';
			if(!empty($_REQUEST['id']))
				$params = '&id='.$_REQUEST['id'].'&edit=1';

			$this->_redirect('?act='.$this->className.$params);
		}
		$BeanCategory = new category();
		
		$this->tEngine->assign('root_category', $BeanCategory->dbGetRootCategory($this->conn));
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
}
?>