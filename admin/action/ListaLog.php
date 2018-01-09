<?php
class ListaLog extends SmartyAction
{
	var $className;
	
	function ListaLog()
	{
		parent::SmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['type']))
			$_SESSION['type'] = $_REQUEST['type'];
		else 
			$_SESSION['type'] = 'action';
			
		if(!empty($_REQUEST['num_row']))
			$_SESSION['num_row'] = $_REQUEST['num_row'];
		else 
			$_SESSION['num_row'] = 2;
			
		$log_dir = APP_ROOT.'/logs/'.$_SESSION['type'].'/';
		$d = dir($log_dir);

		while (false !== ($entry = $d->read())) 
		{
			if($entry != '.' && $entry != '..')
			{
				$exp = explode('.', substr($entry, 0, -4));

				$key = $exp[1].$exp[0].$exp[2];
				$files[$key]['file_name'] = $entry;
				$files[$key]['file_content'] = file($log_dir.$entry);
			}
		}
		$d->close();
		krsort($files);
		
		$i = 0;
		foreach ($files as $key => $val)
		{
			$i++;
			if($i <= $_SESSION['num_row'])
				$data[$key] = $val;
			else 
				break;
		}
				
		$this->tEngine->assign('selected_num_row', $_SESSION['num_row']);
		$this->tEngine->assign('selected_type', $_SESSION['type']);
		
		$this->tEngine->assign('files', $data);
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display($this->className);
	}
	
	function exportExcel()
	{	
		foreach($_SESSION[$this->className]['result'][0] as $key => $val)
			$fieldToDisplay[strtoupper($key)] = $key;
		$this->exportExcelData($_SESSION[$this->className]['result'], $fieldToDisplay, $this->className.'_'.date('d_m_Y'));
	}
}
?>