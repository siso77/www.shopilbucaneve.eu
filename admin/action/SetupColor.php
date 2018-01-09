<?php
include_once(APP_ROOT.'/beans/color.php');
include_once(APP_ROOT.'/beans/images_color.php');


class SetupColor extends DBSmartyAction
{
	var $className;
	
	function SetupColor()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['export']))
			$this->exportExcel();
		if(!empty($_REQUEST['reset']))	
			unset($_SESSION['reset']);
		if(!empty($_REQUEST['delete']))
		{
			$BeanColor = new color();
			if(!empty($_REQUEST['id']))
				$BeanColor->dbDelete($this->conn, $_REQUEST['id'], false);
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
				$_SESSION[$this->className]['order_by'] = 'id';
				$_SESSION[$this->className]['order_type'] = 'ASC';				
			}
			$BeanColor = new color();
			$Colors = $BeanColor->dbGetAll($this->conn, $_SESSION[$this->className]['order_by'], $_SESSION[$this->className]['order_type']);
			$_SESSION[$this->className]['result'] = $Colors;
			
			$HeaderList[0] = $Colors[0];
			$this->tEngine->assign('header_list', $HeaderList);
			
			$p = new MyPager($Colors, $this->rowForPage);
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