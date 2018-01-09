<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/casa_editrice.php");
include_once(APP_ROOT."/beans/autori.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/distributore.php");
include_once(APP_ROOT."/beans/tipo_presa_carico.php");
include_once(APP_ROOT.'/beans/category.php');

class ListaMagazzinoNull extends DBSmartyAction
{
	var $className;

	function ListaMagazzinoNull()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['reset']))
			$_SESSION[$this->className]['key_searched'] = null;

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
			$_SESSION[$this->className]['result'] = null;
		}
		if(empty($_SESSION[$this->className]['result']))
		{
			$BeanMagazzino = new magazzino();
			$where .= " AND content.is_active = 1 AND (magazzino.quantita = '0' OR magazzino.prezzo_acquisto LIKE '0,00' OR magazzino.prezzo_acquisto IS NULL)";
			if(!empty($_SESSION[$this->className]['order_by']))
				$where .= ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];
			else
				$where .= ' ORDER BY content.data_inserimento_riga DESC';
			$_SESSION[$this->className]['result'] = $BeanMagazzino->dbSearch2($this->conn, $where);
		}
		$p = new MyPager($_SESSION[$this->className]['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		$fieldToDisplay['FORNITORE'] ='fornitore';
		$fieldToDisplay['BRAND'] ='name_brand';
		$fieldToDisplay['CATEGORIA MADRE'] = 'root_cat';
		$fieldToDisplay['CATEGORIA'] = 'name';
		$fieldToDisplay['CODICE ARTICOLO'] ='bar_code';
		$fieldToDisplay['DESCRIZIONE'] ='name_it';
		$fieldToDisplay['DESCRIZIONE BREVE'] ='description_it';
		$fieldToDisplay['COSTO UNITARIO'] ='prezzo_acquisto';
		$fieldToDisplay['QUANTITA'] ='quantita_inizio_anno';
		$fieldToDisplay['LIVELLO MINIMO MAGAZZINO'] ='';
		$fieldToDisplay['CARICO'] ='quantita_caricata';
		$fieldToDisplay['SCARICO'] ='scarico';
		$fieldToDisplay['GIACENZA'] ='quantita';
		$fieldToDisplay['TOTALE'] ='totale';
		
		$BeanMagazzino = new magazzino();
		
		if(!empty($_REQUEST['btn_data2_to']))
		{
			unset($_SESSION[$this->className]['data_from']);
			unset($_SESSION[$this->className]['data_to']);
			$_SESSION[$this->className]['data2_to'] = $_REQUEST['data2_to'];
		}
		elseif(!empty($_REQUEST['data_from']))
		{
			unset($_SESSION[$this->className]['data2_to']);
			$_SESSION[$this->className]['data_from'] = $_REQUEST['data_from'];
			$_SESSION[$this->className]['data_to'] = $_REQUEST['data_to'];
		}
		
		if(!empty($_SESSION[$this->className]['data2_to']))
			$where .= " AND magazzino.data_inserimento_riga < '".$_SESSION[$this->className]['data2_to']."'";
		elseif(!empty($_SESSION[$this->className]['data_from']))
			$where .= " AND magazzino.data_inserimento_riga BETWEEN '".$_SESSION[$this->className]['data_from']."' AND '".$_SESSION[$this->className]['data_to']."'";

		$where .= ' AND content.is_active = 1 ORDER BY content.name_it ASC';
		$List = $BeanMagazzino->dbSearch2($this->conn, $where);
		$i = 0;
		$lastBarcode = '';
		
		$BeanCategory = new category();
		foreach ($List as $k => $value)
		{
			$BeanCategory->dbGetOne($this->conn, $value['parent_id']);
			$rootCat = $BeanCategory->vars();
			$value['root_cat'] = $rootCat['name'];

			if($value['name_it'] == $lastBarcode) 
			{
				$ListAssign[$i]['quantita_inizio_anno'] = $ListAssign[$i]['quantita_inizio_anno']+$value['quantita_caricata'];
				if($value['quantita'] > 0)
				{
					$ListAssign[$i]['quantita'] = $ListAssign[$i]['quantita']+$value['quantita'];
					$ListAssign[$i]['quantita_caricata'] = $ListAssign[$i]['quantita']+$value['quantita_caricata'];
				}
				$ListAssign[$i]['scarico'] = $ListAssign[$i]['quantita_caricata'] - $ListAssign[$i]['quantita'];
				$ListAssign[$i]['quantita_inizio_anno'] = $ListAssign[$i]['quantita_inizio_anno'] + $value['quantita_caricata'];
				$ListAssign[$i]['price_it'] = $this->FormatEuro(str_replace(',', '.', $ListAssign[$i]['price_it'])+str_replace(',', '.', $value['price_it']));
				$ListAssign[$i]['price_discounted_it'] = $this->FormatEuro(str_replace(',', '.', $ListAssign[$i]['price_discounted_it'])+str_replace(',', '.', $value['price_discounted_it']));
			}
			else 
			{
				$i++;
				$lastBarcode = $value['name_it'];
				
				$value['quantita_inizio_anno'] = $value['quantita_inizio_anno']+$value['quantita_caricata'];
				$value['scarico'] = $value['quantita_caricata'] - $value['quantita'];
				$value['price_it'] = $this->FormatEuro(str_replace(',', '.', $value['price_it']));
				$value['price_discounted_it'] = $this->FormatEuro(str_replace(',', '.', $value['price_discounted_it']));
				$ListAssign[$i] = $value;
			}
			$ListAssign[$i]['totale'] = $this->FormatEuro(str_replace(',', '.', $value['prezzo_acquisto'])*$ListAssign[$i]['quantita']);
		}
		
		$this->exportExcelData($ListAssign, $fieldToDisplay, 'lista_magazzino_'.date('d_m_Y'));
	}
}
?>