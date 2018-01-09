<?php
include_once(APP_ROOT.'/beans/vendite.php');
include_once(APP_ROOT.'/beans/content.php');
include_once(APP_ROOT.'/beans/images.php');
include_once(APP_ROOT.'/beans/magazzino.php');
include_once(APP_ROOT.'/beans/sizes.php');
include_once(APP_ROOT.'/beans/vendite_magazzino.php');

class Shop extends DBSmartyAction
{
	var $className;
		
	function assignSessionData()
	{
		if(!empty($_SESSION[$this->className]))
		{
			foreach ($_SESSION[$this->className] as $value)
			{
				$BeanContent = new magazzino();
				$List = $BeanContent->dbSearch($this->conn, " AND magazzino.bar_code = '".$value['bar_code']."'");
				$List[0]['quantita'] = $value['quantita'];
				$List[0]['total'] = $value['total'];
				$data[] = $List[0];
			}
			$this->tEngine->assign('data_in_basket', $data);
		}
	}
	
	function Shop()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		$BaseBarCodeGenerator = new BaseBarCodeGenerator();
		$this->tEngine->assign('BaseBarCodeGenerator', $BaseBarCodeGenerator);

		if(!empty($_REQUEST['confirm_insert']))
			$this->tEngine->assign('confirm_insert', true);
			
		if(!empty($_REQUEST['error']))
			$this->tEngine->assign('error', true);
			

		if(!empty($_REQUEST['delete']))
		{
			unset($_SESSION[$this->className][$_REQUEST['bar_code']]);
			$this->_redirect('?act='.$this->className.'&show_cart=1');
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['search']))
		{
			if( ( !empty($_REQUEST['search']) || !empty($_REQUEST['bar_code']) ) && empty($_REQUEST['add_to_basket']) )
			{
				if(!empty($_REQUEST['bar_code']))
					$bar_code = $_REQUEST['bar_code'];
				elseif(!empty($_REQUEST['prod_name']))
				{
					$where = " AND (content.name_it LIKE '%".$_REQUEST['prod_name']."%'";
					$where .= " OR content.description_it LIKE '%".$_REQUEST['prod_name']."%')";
					$BeanContent = new content();
					$Contens = $BeanContent->dbSearch($this->conn, $where);
					
					$where = " AND (magazzino.bar_code LIKE '%".$_REQUEST['prod_name']."%'";
					$where .= " OR content.name_it LIKE '%".$_REQUEST['prod_name']."%'";
					$where .= " OR content.description_it LIKE '%".$_REQUEST['prod_name']."%') AND magazzino.quantita > 0 ORDER BY bar_code DESC";
					$BeanMagazzino = new magazzino();
					$Contens = $BeanMagazzino->dbSearch($this->conn, $history.$where);
					$i = 0;
					$lastBarcode = '';
					foreach ($Contens as $k => $value)
					{
						if($value['bar_code'] != $lastBarcode)
						{
							$i++;
							$lastBarcode = $value['bar_code'];
							$ListAssign[$i] = $value;
						}
						else 
							$ListAssign[$i]['quantita'] = $ListAssign[$i]['quantita']+$value['quantita'];
					}					
					
					$this->tEngine->assign('contents', $ListAssign);
				}					
				elseif(!empty($_REQUEST['search']))
					$bar_code = $_REQUEST['search'];
				
				$List = $this->getSalesData($bar_code);

				if(!empty($List))
					$this->tEngine->assign('data_magazzino', $List);
				else
					$this->tEngine->assign('search_empty', true);

				if($bar_code != 'CERCA')
					$this->tEngine->assign('bar_code_searched', $bar_code);
			}
			else 
			{
				if(!empty($_REQUEST['add_to_basket']))
				{
					unset($_POST['add_to_basket']);
					$_SESSION[$this->className][$_REQUEST['bar_code']] = $_POST;
					$this->assignSessionData();
				}
				else 
				{
					if(empty($_SESSION[$this->className]))
						$this->_redirect('?act='.$this->className.'&error=1');
						
					$BeanVendite = new vendite($this->conn, $_REQUEST);
					$BeanVendite->setId_cliente(1);
					$BeanVendite->setChannel('NEGOZIO');
					$BeanVendite->setTipo_pagamento($_REQUEST['tipo_pagamento']);
					$BeanVendite->setData_vendita(date('Y-m-d H:i:s'));
					$BeanVendite->setIs_invoiced(0);
					$BeanVendite->setOperatore($_SESSION['LoggedUser']['username']);
					$idVendita = $BeanVendite->dbStore($this->conn);

					$TotalPurchase = 0;
					foreach ($_SESSION[$this->className] as $value)
					{
						$BeanMagazzino = new magazzino($this->conn, $value['id_magazzino']);
						
						$BeanVenditeMagazzino = new vendite_magazzino($this->conn,$value);
						$BeanVenditeMagazzino->setId_vendita($idVendita);
						$idVenditeMagazzino = $BeanVenditeMagazzino->dbStore($this->conn);
						$BeanMagazzino->setQuantita($BeanMagazzino->getQuantita()-$BeanVenditeMagazzino->getQuantita());
						$BeanMagazzino->dbStore($this->conn);
						$TotalPurchase += $value['total'];
					}
					
					$BeanVendite->setTotale($this->FormatEuro($TotalPurchase));
					$idVendita = $BeanVendite->dbStore($this->conn);
					
					if(!empty($_REQUEST['generate_invoice']))
					{
						$this->tEngine->assign('rif_scontrino', $_REQUEST['rif_scontrino']);
						/**
						 * Controllare se il cliente esiste o meno
						 */
						include_once(APP_ROOT."/beans/customer.php");
						$BeanCustomer = new customer();
						$search = "";
						if(!empty($_REQUEST['nome']))
							$search .= " AND nome = '".$_REQUEST['nome']."'";
						if(!empty($_REQUEST['cognome']))
							$search .= " AND cognome = '".$_REQUEST['cognome']."'";
						$CustomerFound = $BeanCustomer->dbSearch($this->conn, $search);
						if(
							empty($CustomerFound) && 
							!empty($_REQUEST['indirizzo']) && 
							!empty($_REQUEST['cap']) && 
							!empty($_REQUEST['citta']) &&
							!empty($_REQUEST['provincia'])
						)
						{
							$BeanCustomer = new customer($this->conn, $_REQUEST);
							$BeanCustomer->setIndirizzo_spedizione($_REQUEST['indirizzo']);
							$BeanCustomer->setCap_spedizione($_REQUEST['cap']);
							$BeanCustomer->setCitta_spedizione($_REQUEST['citta']);
							$BeanCustomer->setProvincia_spedizione($_REQUEST['provincia']);
							$BeanCustomer->setStato_spedizione($_REQUEST['stato']);
							$BeanCustomer->setOperatore($_SESSION['LoggedUser']['username']);
							$BeanCustomer->dbStore($this->conn);
							
							$CustomerFound = $BeanCustomer->dbSearch($this->conn, " AND cognome = '".$BeanCustomer->getCognome()."' AND nome = '".$BeanCustomer->getNome()."'");
						}
						elseif(empty($CustomerFound))
						{
							$List = $this->getSalesData($_REQUEST['bar_code']);
							if(!empty($List))
								$this->tEngine->assign('data_magazzino', $List);
							
							$this->tEngine->assign('sales_data', $_REQUEST);
							$this->tEngine->assign('customer_data_searched', array('nome' => $_REQUEST['nome'],'cognome' => $_REQUEST['cognome']));
							$this->tEngine->assign('customer_not_found', true);
							$this->tEngine->assign('action_class_name', $this->className);
							$this->tEngine->assign('tpl_action', $this->className);
							$this->tEngine->display('Index');
							exit();
						}
							$this->tEngine->assign('bar_code_searched', $_REQUEST['bar_code']);
						
						include_once(APP_ROOT."/beans/index_fattura.php");
						$BeanIndexFattura = new index_fattura();
						$index_fattura = $BeanIndexFattura->dbGetAll($this->conn);
						$numero_fattura = $index_fattura[0]['id'];
						$BeanVenditeMagazzino = new vendite_magazzino();
						if(is_array($idVendita))
							$productSale = $BeanVenditeMagazzino->dbGetAllByIdVendita($this->conn, $idVendita['id']);
						else
							$productSale = $BeanVenditeMagazzino->dbGetAllByIdVendita($this->conn, $idVendita);
						foreach ($productSale as $val)
							$idsMagazzino[] = $val['id_magazzino'];

						$BeanMagazzino = new magazzino();
						$dataFattura = $BeanMagazzino->dbSearch($this->conn, ' AND magazzino.id IN('.implode(', ',$idsMagazzino).')');
						
						$BeanVendite = new vendite($this->conn, $idVendita);
						$BeanVendite->setFattura($index_fattura[0]['id']);
						$BeanVendite->setId_cliente($CustomerFound[0]['id']);
						$BeanVendite->dbStore($this->conn);
						
						$BeanIndexFattura->fast_edit($this->conn, $index_fattura[0]['id']);
						$this->createPdf($numero_fattura, $CustomerFound[0], $dataFattura, $productSale, $_REQUEST['rif_scontrino'], $BeanVendite->vars(), $_REQUEST['data_rif_scontrino']);
					}
					unset($_SESSION[$this->className]);
					$this->_redirect('?act='.$this->className.'&confirm_insert=1');
				}
			}
		}
//		elseif(!empty($_REQUEST['bar_code']))
//		{
//			$bar_code = $_REQUEST['bar_code'];
//			$List = $this->getSalesData($bar_code);
//		}
		
		if(!empty($_REQUEST['show_cart']))
			$this->assignSessionData();

		
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
					  
	function createPdf($invoiceNum, $cliente, $assignData, $vendita, $rif_scontrino, $BeanVendite, $data_rif_scontrino)
	{
		$pathDoc = APP_ROOT.'/fatture/'.$cliente['id'].'/';
		$wwwPathDoc = WWW_ROOT.'fatture/'.$cliente['id'].'/';
		if(!is_dir(APP_ROOT.'/fatture/'.$cliente['id']))
			mkdir(APP_ROOT.'/fatture/'.$cliente['id'], 0755, true);

//		$htmltodoc = new HTML_TO_DOC('FATTURA_IMMEDIATA_'.$invoiceNum);
//		$htmltodoc->createDoc(APP_ROOT.'/libs/TemplateClass/Template_Invoice_DOC.php',"Documento",false);
//		$htmltodoc->createDocFromURL( WWW_ROOT.'libs/TemplateClass/Template_Invoice_DOC.php', $pathDoc.'FATTURA_IMMEDIATA_'.$invoiceNum.'.doc', true, 'invoice_num='.$invoiceNum.'&session_name='.session_name() );		$param['invoice_num'] 	= $invoiceNum;
		$param['invoiceNum'] 	= $invoiceNum;
		$param['customer'] 		= $cliente;
		$param['data'] 			= $assignData;
		$param['sale'] 			= $vendita;
		$param['rif_scontrino'] = $rif_scontrino;
		$param['bean_vendite'] 	= $BeanVendite;
		$param['FATTURA_TAX_IVA'] = FATTURA_TAX_IVA;
		$param['IVA'] 			  = IVA;
		$param['WWW_ROOT'] 		  = WWW_ROOT;
		$param['data_rif_scontrino'] 		  = $data_rif_scontrino;
		$_SESSION['invoice_'.$invoiceNum] = $param;
		$_SESSION['curr_invoice_num'] 	  = $invoiceNum;
		
		ob_start();
			include(APP_ROOT.'/libs/TemplateClass/Template_Invoice_DOC.php');
			$msWord = ob_get_contents();
		ob_end_clean();
		$fp = fopen($pathDoc.$invoiceNum.'.doc', 'w+');
		fwrite($fp, $msWord);
		fclose($fp);

		unset($_SESSION[$this->className]);
		unset($_SESSION['invoice_'.$invoice_num]);
		echo '<script>
				window.open("'.$wwwPathDoc.$invoiceNum.'.doc");
				window.location.href = "'.WWW_ROOT.'?act=Shop&confirm_insert=1";
			</script>';
		exit();

//		$pathPdf = APP_ROOT.'/fatture/'.$cliente['id'].'/'.$invoiceNum.'.pdf';
//		if(!is_dir(APP_ROOT.'/fatture/'.$cliente['id']))
//			mkdir(APP_ROOT.'/fatture/'.$cliente['id'], 0755, true);
//		$customer['nome'] 			 = $cliente['nome'].' '.$cliente['cognome'];
//		$customer['address_company'] = $cliente['indirizzo'];
//		$customer['address_invoice'] = $cliente['indirizzo_spedizione'];
//		$customer['zip_code'] 		 = $cliente['cap'];
//		$customer['city'] 			 = $cliente['citta'];
//		$customer['fisso'] 			 = $cliente['fisso'];
//		$customer['cellulare'] 		 = $cliente['cellulare'];
//		$customer['data_fattura'] 	 = $this->invoice_date;
//		$customer['ddv']		 	 = $assignData['ddv'];
//		
//		if(!empty($cliente['p_iva']))
//			$customer['cf_piva'] = $cliente['p_iva'];
//		elseif(!empty($cliente['codice_fiscale']))
//			$customer['cf_piva'] = $cliente['codice_fiscale'];
//			
//		$data = $assignData;
//		$includeTextIva = $data['includeTextIva'];
//		unset($data['cliente']);
//		unset($data['invoice_data']);
//		unset($data['data_fatturazione']);
//		unset($data['ddt']);
//		unset($data['includeTextIva']);
//
//		$this->createPdfInvoice($pathPdf, $includeTextIva, $invoiceNum, $customer, $data, $vendita, $rif_scontrino);
//		
//		echo '<script>
//					window.open("'.WWW_ROOT.'/fatture/'.$cliente['id'].'/'.$invoiceNum.'.pdf");
//					window.location.href = "'.WWW_ROOT.'?act=Shop&confirm_insert=1";
//				</script>';
//		unset($_SESSION[$this->className]);
//		exit();
	}
	
	function getSalesData($bar_code)
	{
		$where = " AND magazzino.bar_code = '".$bar_code."'";
		$BeanMagazzino = new magazzino();
		$List = $BeanMagazzino->dbSearch($this->conn, $where.' ORDER BY data_modifica_riga, quantita DESC');

		$BeanImages = new images();
		$images = $BeanImages->dbGetAllByIdContent($this->conn, $List[0]['id_content']);
		if(!empty($images))
			$List[0]['images'] = $images;

		if(!empty($List))
			$this->tEngine->assign('data', $List);
		
		if($List == array())
			$this->tEngine->assign('magazzino_empty', true);

		if(count($List) == 1)
		{
			$BeanSizes = new sizes();
			$Sizes = $BeanSizes->dbSearch($this->conn, ' WHERE id = '.$List[0]['id_size']);
			$List[0]['sizes'] = $Sizes;
		}
	
		foreach ($List as $k => $value)
		{
			if(is_array($ListAssign[$k-1]) && $ListAssign[$k-1]['bar_code'] == $value['bar_code'])
				$ListAssign[$k-1]['quantita'] = $ListAssign[$k-1]['quantita']+$value['quantita'];
			else
				$ListAssign[$k] = $value;
		}
		return $ListAssign;
	}	
}
?>