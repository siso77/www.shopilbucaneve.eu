<?php
include_once(APP_ROOT.'/beans/sizes.php');
include_once(APP_ROOT.'/beans/size_type.php');

class SetupSize extends DBSmartyAction
{
	var $className;
	
	function SetupSize()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);


		if(!empty($_REQUEST['export']))
			$this->exportExcel();
		if(!empty($_REQUEST['reset']))	
			unset($_SESSION['reset']);
		if(!empty($_REQUEST['delete']))
		{
			$BeanSize = new sizes();
			if(!empty($_REQUEST['id']))
				$BeanSize->dbDelete($this->conn, $_REQUEST['id'], false);
		}
			
		if(!empty($_REQUEST['id_size_type']))
		{
			$id_size_type = $_REQUEST['id_size_type'];
			$this->tEngine->assign('contenuto_precaricato', array('id_size_type' => $_REQUEST['id_size_type']));
		}	
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			unset($_SESSION[$this->className]);
			$this->_redirect('?act='.$this->className);
		}
		else 
		{
			if(isset($_REQUEST['order_by']))
			{
				$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
				$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];				
			}
			
			$BeanSizeType = new size_type();
			$sizes = $BeanSizeType->dbGetAll2($this->conn, $_SESSION[$this->className]['order_by'], $_SESSION[$this->className]['order_type'], new sizes(), $id_size_type);

			$_SESSION[$this->className]['result'] = $sizes;
			
			$p = new MyPager($sizes, $this->rowForPage);
			$links = $p->getLinks();
			$this->tEngine->assign("list"	    , $p->getData());
			$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
			$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
			$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
			$this->tEngine->assign('numViewPage', $this->numViewPage);
		}
		$this->tEngine->assign('cmb_size', $BeanSizeType->dbGetAll($this->conn, 'type', 'ASC', new sizes()));
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		foreach($_SESSION[$this->className]['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION[$this->className]['result'], $fieldToDisplay, $this->className.'_'.date('d_m_Y'));
			}	
}
?>