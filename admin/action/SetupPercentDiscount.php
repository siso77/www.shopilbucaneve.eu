<?php
include_once(APP_ROOT.'/beans/percent_discount.php');

class SetupPercentDiscount extends DBSmartyAction
{
	var $className;
	
	function SetupPercentDiscount()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['export']))
			$this->exportExcel();
		if(!empty($_REQUEST['reset']))	
			unset($_SESSION['reset']);
		if(!empty($_REQUEST['delete']))
		{
			$BeanPercentDiscount = new percent_discount();
			if(!empty($_REQUEST['id']))
				$BeanPercentDiscount->dbDelete($this->conn, $_REQUEST['id'], false);
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
			else 
			{
				$_SESSION[$this->className]['order_by'] = 'data';
				$_SESSION[$this->className]['order_type'] = 'ASC';				
			}
			$BeanPercentDiscount = new percent_discount();
			$percent_discount = $BeanPercentDiscount->dbGetAll($this->conn, $_SESSION[$this->className]['order_by'], $_SESSION[$this->className]['order_type']);
			$_SESSION[$this->className]['result'] = $percent_discount;
			$HeaderList[0] = $percent_discount[0];
			$this->tEngine->assign('header_list', $HeaderList);
			
			$p = new MyPager($percent_discount, $this->rowForPage);
			$links = $p->getLinks();
			$this->tEngine->assign("list"	    , $p->getData());
			$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
			$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
			$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
			$this->tEngine->assign('numViewPage', $this->numViewPage);
		}
		
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