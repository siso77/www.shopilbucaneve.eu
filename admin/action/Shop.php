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

		include_once(APP_ROOT."/beans/index_fattura.php");
		$BeanIndexFattura = new index_fattura();
		$index_fattura = $BeanIndexFattura->dbGetAll($this->conn);
		$numero_fattura = $index_fattura[0]['id'];
		$this->tEngine->assign('invoice_num', $numero_fattura);
		
		if(!empty($_REQUEST['confirm_insert']))
			$this->tEngine->assign('confirm_insert', true);
			
		if(!empty($_REQUEST['error']))
			$this->tEngine->assign('error', true);

		if(!empty($_REQUEST['delete']))
		{
			unset($_SESSION[$this->className][$_REQUEST['bar_code']]);
			$this->_redirect('?act='.$this->className.'&show_cart=1');
		}

		if(!empty($_REQUEST['nome_spedizione']) || !empty($_REQUEST['cognome_spedizione']))
			$_REQUEST['text_spedizione'] = $_REQUEST['nome_spedizione'].' '.$_REQUEST['cognome_spedizione'];

		if(!empty($_REQUEST['customer_selected']) || !empty($_REQUEST['create_customer']) || !empty($_REQUEST['edit_customer']))
		{
			include_once(APP_ROOT."/beans/customer.php");
			if(!empty($_REQUEST['create_customer']))
			{
				$BeanCustomer = new customer($this->conn, $_REQUEST);
				$BeanCustomer->setOperatore($_SESSION['LoggedUser']['username']);
				$idCustomer = $BeanCustomer->dbStore($this->conn);
				$_REQUEST['customer_selected'] = $idCustomer;
			}
			$BeanCustomer = new customer();
			$search = "";
			if(!empty($_REQUEST['customer_selected']))
				$search .= " AND id = ".$_REQUEST['customer_selected']."";
			$CustomerFound = $BeanCustomer->dbSearch($this->conn, $search.' ORDER BY nome DESC');
			
			if(empty($_REQUEST['edit_customer']))
			{
				$this->assignSessionData();
				$this->tEngine->assign('id_vendita', $_REQUEST['id_vendita']);			
				$this->tEngine->assign('data_rif_scontrino', $_REQUEST['data_rif_scontrino']);			
				$this->tEngine->assign('rif_scontrino', $_REQUEST['rif_scontrino']);			
				$this->tEngine->assign('mezzo', $_REQUEST['mezzo']);			
				$this->tEngine->assign('invoice_num', $_REQUEST['invoice_num']);			
				$this->tEngine->assign('edit_customer', $CustomerFound[0]);
				$this->tEngine->assign('action_class_name', $this->className);
				$this->tEngine->assign('tpl_action', $this->className);
				$this->tEngine->display('Index');
				exit();
			}
			else 
			{
				if(!empty($_REQUEST['id_customer']))
				{
					$CustomerFound = null;
					$BeanCustomer = new customer($this->conn, $_REQUEST['id_customer']);
					$BeanCustomer->setNome($_REQUEST['edit_customer_nome']);
					$BeanCustomer->setCognome($_REQUEST['edit_customer_cognome']);
					$BeanCustomer->setCodice_fiscale($_REQUEST['edit_customer_codice_fiscale']);
					$BeanCustomer->setRagione_sociale($_REQUEST['edit_customer_ragione_sociale']);
					$BeanCustomer->setP_iva($_REQUEST['edit_customer_codice_fiscale']);
					$BeanCustomer->setIndirizzo($_REQUEST['edit_customer_indirizzo']);
					$BeanCustomer->setProvincia($_REQUEST['edit_customer_provincia']);
					$BeanCustomer->setStato($_REQUEST['edit_customer_stato']);
					$BeanCustomer->setCitta($_REQUEST['edit_customer_citta']);
					$BeanCustomer->setCap($_REQUEST['edit_customer_cap']);
					$BeanCustomer->setCellulare($_REQUEST['edit_customer_cellulare']);
					$BeanCustomer->setFisso($_REQUEST['edit_customer_fisso']);
					$BeanCustomer->setEmail($_REQUEST['edit_customer_email']);
					$BeanCustomer->setText_spedizione($_REQUEST['edit_customer_nome_spedizione'].' '.$_REQUEST['edit_customer_cognome_spedizione']);
					$BeanCustomer->setIndirizzo_spedizione($_REQUEST['edit_customer_indirizzo_spedizione']);
					$BeanCustomer->setCap_spedizione($_REQUEST['edit_customer_cap_spedizione']);
					$BeanCustomer->setCitta_spedizione($_REQUEST['edit_customer_citta_spedizione']);
					$BeanCustomer->setProvincia_spedizione($_REQUEST['edit_customer_provincia_spedizione']);
					$BeanCustomer->dbStore($this->conn);
					
					$CustomerFound[0] = $BeanCustomer->vars();
				}
			}
			$BeanVenditeMagazzino = new vendite_magazzino();
			$idVendita = $_REQUEST['id_vendita'];
			$productSale = $BeanVenditeMagazzino->dbGetAllByIdVendita($this->conn, $idVendita);
			foreach ($productSale as $val)
				$idsMagazzino[] = $val['id_magazzino'];

			$BeanMagazzino = new magazzino();
			$dataFattura = $BeanMagazzino->dbSearch($this->conn, ' AND magazzino.id IN('.implode(', ',$idsMagazzino).')');
			
			$BeanVendite = new vendite($this->conn, $idVendita);
			if(!empty($_REQUEST['invoice_num']) && $_REQUEST['invoice_num'] == $index_fattura[0]['id'])
				$BeanVendite->setFattura($index_fattura[0]['id']);
			else
				$BeanVendite->setFattura($_REQUEST['invoice_num']);
			$BeanVendite->setId_cliente($CustomerFound[0]['id']);
			$BeanVendite->dbStore($this->conn);
			
			if(!empty($_REQUEST['invoice_num']) && $_REQUEST['invoice_num'] == $index_fattura[0]['id'])
				$BeanIndexFattura->fast_edit($this->conn, $index_fattura[0]['id']);
			else
				$numero_fattura = $_REQUEST['invoice_num'];

			$this->createPdf($numero_fattura, $CustomerFound[0], $dataFattura, $productSale, $_REQUEST['rif_scontrino'], $BeanVendite->vars(), $_REQUEST['data_rif_scontrino'], $_REQUEST['mezzo']);
			
			unset($_SESSION[$this->className]);
			$this->_redirect('?act='.$this->className.'&confirm_insert=1');			
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
					$where .= " OR brands.name LIKE '%".$_REQUEST['prod_name']."%'";
					$where .= " OR content.description_it LIKE '%".$_REQUEST['prod_name']."%') AND magazzino.quantita > 0 ORDER BY quantita DESC";					
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

					if(!empty($_REQUEST['tipo_pagamento']))
					{
						if($_REQUEST['tipo_pagamento'] == 'POS VIRTUALE')
						{
							$_SESSION['ShopVirtualPos'] = $_SESSION[$this->className];
							$_SESSION['ShopVirtualPos']['REQUEST_DATA'] = $_REQUEST;
							unset($_SESSION[$this->className]);
							$this->_redirect('?act=ShopVirtualPos');
						}

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
							if($BeanMagazzino->getQuantita() > 0)
								$BeanMagazzino->setQuantita($BeanMagazzino->getQuantita()-$BeanVenditeMagazzino->getQuantita());
							$BeanMagazzino->dbStore($this->conn);
							$TotalPurchase += $value['total'];
						}
						
						$BeanVendite->setTotale($this->FormatEuro($TotalPurchase));
						$idVendita = $BeanVendite->dbStore($this->conn);
						
						Base_CacheCore::getInstance()->clean();
					}
					if(!empty($_REQUEST['generate_invoice']))
					{
						include_once(APP_ROOT."/beans/customer.php");
						$BeanCustomer = new customer();
						$search = "";
						if(!empty($_REQUEST['search_nome']))
							$search .= " AND nome LIKE '%".$_REQUEST['search_nome']."%'";
						if(!empty($_REQUEST['search_cognome']))
							$search .= " AND cognome LIKE '%".$_REQUEST['search_cognome']."%'";
						$CustomerFound = $BeanCustomer->dbSearch($this->conn, $search.' ORDER BY nome DESC');

						if(empty($CustomerFound))
							$this->tEngine->assign('customer_not_found', true);

						$this->assignSessionData();
						$this->tEngine->assign('sales_data', array('tipo_pagamento' => $_REQUEST['tipo_pagamento']));
						$this->tEngine->assign('mezzo', $_REQUEST['mezzo']);
						$this->tEngine->assign('rif_scontrino', $_REQUEST['rif_scontrino']);
						$this->tEngine->assign('data_rif_scontrino', $_REQUEST['data_rif_scontrino']);
						$this->tEngine->assign('id_vendita', $idVendita['id']);
						$this->tEngine->assign('list_customer', $CustomerFound);
						$this->tEngine->assign('action_class_name', $this->className);
						$this->tEngine->assign('tpl_action', $this->className);
						$this->tEngine->display('Index');
						exit();
					}
					else 
						unset($_SESSION[$this->className]);
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
					  
	function createPdf($invoiceNum, $cliente, $assignData, $vendita, $rif_scontrino, $BeanVendite, $data_rif_scontrino, $mezzo)
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
		
//		$param['data'] 			= $assignData;
		
		$param['sale'] 			= $vendita;
		$param['rif_scontrino'] = $rif_scontrino;
		$param['bean_vendite'] 	= $BeanVendite;
		$param['FATTURA_TAX_IVA'] = FATTURA_TAX_IVA;
		$param['IVA'] 			  = IVA;
		$param['IVA_INTEGRATORI'] = IVA_INTEGRATORI;
		$param['WWW_ROOT'] 		  = WWW_ROOT;
		$param['data_rif_scontrino'] 		  = $data_rif_scontrino;

		if($mezzo == 'mezzo_cedente')
			$param['mezzo_cedente'] = $mezzo;
		if($mezzo == 'mezzo_cessionario')
			$param['mezzo_cessionario'] = $mezzo;
		if($mezzo == 'mezzo_vettore')
			$param['mezzo_vettore'] = $mezzo;
		if($mezzo == 'mezzo_altro')
			$param['mezzo_altro'] = $mezzo;
		
//		$_SESSION['invoice_'.$invoiceNum] = $param;
		$_SESSION['curr_invoice_num'] 	  = $invoiceNum;
		
		if(is_file($pathDoc.$invoiceNum.'.doc'))
			unlink($pathDoc.$invoiceNum.'.doc');

		$fName = $invoiceNum.'_';
		$fName .= str_replace('', '', $cliente['nome']).'_';
		$fName .= str_replace('', '', $cliente['cognome'].'_');
		$fName .= date('d-m-Y');

//$assignData = array_merge($assignData, $assignData);
//$param['data'] = $assignData;

		$index = round((count($assignData) / 10));
		if($index == 0)
			$index = 1;
			
		for($a = 0; $a < $index; $a++)
		{
			$param['data'] = null;
			$_SESSION['invoice_'.$invoiceNum] = null;
			for($j=0; $j <= 10;$j++)
			{
				if(!empty($assignData[$j]))
				{				
					$param['data'][] = $assignData[$j];
					unset($assignData[$j]);
				}
			}
			$assignData = array_values($assignData);
			$_SESSION['invoice_'.$invoiceNum] = $param;

			ob_start();
				include(APP_ROOT.'/libs/TemplateClass/Template_Invoice_DOC.php');
				$msWord .= ob_get_contents();
			ob_end_clean();
		}
	
		$fp = fopen($pathDoc.$fName.'.doc', 'w+');
		fwrite($fp, $msWord);
		fclose($fp);
//_dump($pathDoc.$fName.'.doc');
//echo $msWord;
//exit();
		
		unset($_SESSION[$this->className]);
		unset($_SESSION['invoice_'.$invoice_num]);
		echo '<script>
				window.open("'.$wwwPathDoc.$fName.'.doc");
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
		$List = $BeanMagazzino->dbSearch($this->conn, $where.' ORDER BY quantita DESC');

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
			if(is_array($ListAssign[$k-1]) && $ListAssign[$k-1]['bar_code'] == $value['bar_code'] && $value['quantita'] > 0)
				$ListAssign[$k-1]['quantita'] = $ListAssign[$k-1]['quantita']+$value['quantita'];
			else
				$ListAssign[$k] = $value;
		}
		
		return $ListAssign;
	}	
}
?>