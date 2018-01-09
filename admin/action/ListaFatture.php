<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/vendite.php");

class ListaFatture extends DBSmartyAction
{
	var $className;
	
	function ListaFatture()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
	
		if(!empty($_REQUEST['export']))
		{
			$this->exportExcel();
		}
		
		if(!empty($_REQUEST['delete']))
		{
			/**
			 * Prevedere lo spostamento delle fattura non la cancellazione
			 */
			unlink(base64_decode($_REQUEST['delete']));
			if(!empty($_REQUEST['id_vendita']))
			{
				$BeanVendite = new vendite();
				$BeanVendite->dbDelete($this->conn,array($_REQUEST['id_vendita']), true);
			}
			$this->_redirect('?act=ListaFatture&reset=1');
		}
				
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION['ListaFatture']['result'] = null;
				$_SESSION['ListaFatture']['key_search'] = $_REQUEST['key_search'];
			}
			else 
			{
				$_SESSION['ListaFatture']['key_search'] = null;
				$_SESSION['ListaFatture']['result'] = null;
				$_SESSION['ListaFatture']['order_by'] = null;
				$_SESSION['ListaFatture']['order_type'] = null;
			}			
		}
		else
			$where = '';

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION['ListaFatture']['order_by'] = $_REQUEST['order_by'];
			$_SESSION['ListaFatture']['order_type'] = $_REQUEST['order_type'];
			$_SESSION['ListaFatture']['result'] = null;
		}			

		if(empty($_SESSION['ListaFatture']['order_by']))
		{
			$_SESSION['ListaFatture']['order_by'] = 'fattura';
			$_SESSION['ListaFatture']['order_type'] = 'ASC';
		}
		$d = dir(APP_ROOT.'/fatture/');
		while (false !== ($entry = $d->read())) 
		{
			if($entry != '.' && $entry != '..')
			{
				if(is_dir(APP_ROOT.'/fatture/'.$entry))
				{
					$dir = dir(APP_ROOT.'/fatture/'.$entry);
					$i = 0;
					while (false !== ($file = $dir->read())) 
					{
						if($file != '.' && $file != '..')
						{
							$exp = explode('_', $file);
							if($_SESSION['ListaFatture']['order_by'] == 'data_vendita')
								$key = substr($exp[3], 0, -4).$i;
							elseif($_SESSION['ListaFatture']['order_by'] == 'fattura')
								$key = $exp[0];
							elseif($_SESSION['ListaFatture']['order_by'] == 'nome')
								$key = $exp[1].$i;
							elseif($_SESSION['ListaFatture']['order_by'] == 'cognome')
								$key = $exp[2].$i;
							else 
								$key = $i;
			
							$add = false;
							if(!empty($_SESSION['ListaFatture']['key_search']))
							{
								if(stristr($exp[0], $_SESSION['ListaFatture']['key_search']) || stristr($exp[1], $_SESSION['ListaFatture']['key_search']) || stristr($exp[2], $_SESSION['ListaFatture']['key_search']))
									$add = true;
								else 
									$add = false;
									
								if($add)
								{
									$FreeInvoices[$key]['nome'] = $exp[1];
									$FreeInvoices[$key]['cognome'] = $exp[2];
									$FreeInvoices[$key]['fattura'] = $exp[0];
									$FreeInvoices[$key]['data_vendita'] = $exp[3];
									$FreeInvoices[$key]['invoice_folder'] = $entry;
								}
							}
							else
							{	
								$FreeInvoices[$key]['nome'] = $exp[1];
								$FreeInvoices[$key]['cognome'] = $exp[2];
								$FreeInvoices[$key]['fattura'] = $exp[0];
								$FreeInvoices[$key]['data_vendita'] = $exp[3];
								$FreeInvoices[$key]['invoice_folder'] = $entry;
							}
							$i++;	
						}
					}					
				}
			}
		}

//_dump($FreeInvoices);
//exit();
		if(!empty($FreeInvoices))
			$List = $FreeInvoices;
		
		if($_SESSION['ListaFatture']['order_type'] == 'ASC')
			krsort($List);
		else
			ksort($List);
//_dump($List);
//exit();
		
		$p = new MyPager($List, $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION['ListaFatture']['key_search']);
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', 'ListaFatture');
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