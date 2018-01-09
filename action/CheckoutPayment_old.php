<?php
include_once APP_ROOT.'/libs/ext/PHPExcel/Classes/PHPExcel.php';

include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/customer.php");

include_once(APP_ROOT."/beans/ecm_basket_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_fornitori.php");
include_once(APP_ROOT."/beans/giacenze_fornitori.php");

include_once(APP_ROOT."/beans/ecm_basket_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino_forn_de.php");
include_once(APP_ROOT."/beans/giacenze_forn_gasa.php");

include_once(APP_ROOT."/beans/index_ord_forn.php");

class CheckoutPayment extends DBSmartyMailAction
{
	var $className;
	var $importo_spese;
	var $id_negozio;
	var $attachedOrderGasa;
	var $attachedOrderDenDekker;

	function CheckoutPayment()
	{
		parent::DBSmartyMailAction();
		
		$tmpSessNl = $_SESSION[session_id()]['basket_fornitori'];
		$tmpSessDe = $_SESSION[session_id()]['basket_fornitori_de'];
		unset($tmpSessNl['n_carrelli']);
		unset($tmpSessNl['perc_occupazione']);
		unset($tmpSessDe['n_carrelli']);
		unset($tmpSessDe['perc_occupazione']);

		$tmp = $_SESSION[session_id()]['basket'];
		unset($tmp['n_carrelli']);
		unset($tmp['perc_occupazione']);
		foreach ($tmp as $k => $val)
		{
			if(!empty($_REQUEST['nota_'.$val['giacenza']['id']]) && !stristr($_REQUEST['nota_'.$val['giacenza']['id']],'Inserisci una nota per il prodotto:'))
				$_SESSION[session_id()]['basket'][$k]['nota'] = $_REQUEST['nota_'.$val['giacenza']['id']];
		}

		if(array_key_exists('confirm_getpay', $_REQUEST))
		{
// 			if($_REQUEST['confirm_getpay'] == 1)
// 				$confirm = true;
			if($_REQUEST['confirm_getpay'] == 0)
				$this->_redirect('?act=ShoppingCart&order_ko=1');
		}
		
		if(empty($_SESSION[session_id()]['basket']) && empty($_SESSION[session_id()]['basket_fornitori']) && empty($_SESSION[session_id()]['basket_fornitori_de']))
			$this->_redirect('');
		
		include_once(APP_ROOT.'/beans/ApplicationSetup.php');
		$BeanApplicationSetup = new ApplicationSetup();
		$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
		
		$this->importo_spese = $speseSpedizione[0]['name'];
		//$this->id_negozio = "ID NEGOZIO";
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['params']))
		{
			$params = base64_decode($_REQUEST['params']);
			$exp = explode('&', $params);
			foreach ($exp as $val)
			{
				$ex = explode('=', $val);
				if($ex[0] == 'back')
					$back = 1;
				if($ex[0] == 'confirm')
					$confirm = 1;
				if($ex[0] == 'id_ordine')
					$idOrdine = $ex[1];
				if($ex[0] == 'remote_address')
					$remote_address = $ex[1];
			}
			if($_SESSION[session_id()]['remote_address'] != $remote_address)
			{
				$this->_redirect('?CheckoutShopping=err_remote_addr');
				mail('siso77@gmail.com', 'STREAM - Truffa', 'Remote address: '.$remote_address.'<br>User ID: '.$_SESSION['LoggedUser']['id'].'<br>User Name: '.$_SESSION['LoggedUser']['username']);
			}
		}

		if(!empty($_REQUEST['payment_type']))
			$_SESSION[session_id()]['payment_type'] = $_REQUEST['payment_type'];

		if(!empty($confirm))
		{
			if(!empty($_SESSION[session_id()]['ecm_id_ordine']))
			{
				$beanBasketMagazzinoFornitori = new ecm_basket_magazzino_fornitori();
				$data = $beanBasketMagazzinoFornitori->dbGetAllByIdBasket($this->conn, $_SESSION[session_id()]['ecm_basket']);
				foreach ($data as $bkm)
					$beanBasketMagazzinoFornitori->dbDelete($this->conn, array($bkm['id']));

				$beanBasketMagazzinoFornitoriDe = new ecm_basket_magazzino_forn_de();
				$data = $beanBasketMagazzinoFornitoriDe->dbGetAllByIdBasket($this->conn, $_SESSION[session_id()]['ecm_basket']);
				foreach ($data as $bkm)
					$beanBasketMagazzinoFornitoriDe->dbDelete($this->conn, array($bkm['id']));
				
				$beanBasketMagazzino = new ecm_basket_magazzino();
				$data = $beanBasketMagazzino->dbGetAllByIdBasket($this->conn, $_SESSION[session_id()]['ecm_basket']);
				foreach ($data as $bkm)
					$beanBasketMagazzino->dbDelete($this->conn, array($bkm['id']));

				$beanBasket = new ecm_basket($this->conn);
				$activeBasket = $beanBasket->dbGetOneByIdUser($this->conn, $_SESSION['LoggedUser']['id']);
				$beanBasket->dbDelete($this->conn, array($activeBasket['id']), false);
				
				$BeanEcmOrdini = new ecm_ordini($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
				$BeanEcmOrdini->setOperatore(ECM_OPERATORE);
				$BeanEcmOrdini->setPagato(1);
				if(!empty($_SESSION[session_id()]['partenza_fornitori']))
					$BeanEcmOrdini->setData_partenza_fornitore_1($_SESSION[session_id()]['partenza_fornitori']);
				if(!empty($_SESSION[session_id()]['partenza_fornitori_de']))
					$BeanEcmOrdini->setData_partenza_fornitore_2($_SESSION[session_id()]['partenza_fornitori_de']);
				$idOrdine = $BeanEcmOrdini->dbStore($this->conn);

				$BeanUsers = new users($this->conn, $BeanEcmOrdini->id_user);
				$BeanUsersAnag = new users_anag($this->conn, $BeanUsers->id_anag);
				
				$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
				$products = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $BeanEcmOrdini->id);

				$BeanEcmOrdiniMagazzinoFornitori = new ecm_ordini_magazzino_fornitori();
				$products_fornitori = $BeanEcmOrdiniMagazzinoFornitori->dbGetAllByIdOrdine($this->conn, $BeanEcmOrdini->id);

				//$BeanEcmOrdiniMagazzinoFornitoriDe = new ecm_ordini_magazzino_forn_de();
				//$products_fornitori_de = $BeanEcmOrdiniMagazzinoFornitoriDe->dbGetAllByIdOrdine($this->conn, $BeanEcmOrdini->id);

				/* GENERO L'ORDINE IN FORATO EXCEL PER IL FORNITORE GASA */
				//$this->attachedOrderGasa = $this->generateExcelGasa($BeanEcmOrdini, $products_fornitori_de, $BeanUsers);

				/* GENERO L'ORDINE IN FORATO EXCEL PER IL FORNITORE DENDEKKER */
				$this->attachedOrderDenDekker = $this->generateCsvDenDekker($BeanEcmOrdini, $products_fornitori, $BeanUsers);
				$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
				if(!empty($_SESSION[session_id()]['basket']))
				{
					$tmp = $_SESSION[session_id()]['basket'];
					unset($tmp['n_carrelli']);
					unset($tmp['perc_occupazione']);
					if(empty($tmp))
						$this->_redirect('');
					if(!empty($_SESSION['LoggedUser']['customer_data']['id_agent']))
					{
						$agent = $_SESSION['LoggedUser']['customer_data']['id_agent'];
						if(strlen($agent) == 1)
							$agent = '0'.$agent;
					}
					else
						$agent = '88';
						
					$fp = fopen(APP_ROOT.'/FlorSysIntegration/Out/00000'.$agent.'V.'.$this->tEngine->getCutomFormatCode($idOrdine, 3), 'w+');
					fwrite($fp, "*MAG#0\r\n");
					foreach ($tmp as $key => $value)
					{
						$Bean = new content();
						$content = $Bean->dbFree(MyDB::connect(), "SELECT * FROM content WHERE vbn = '".$value['giacenza']['bar_code']."'");
						
						$str = '';
						$str .= 'PC';
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 7);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(date('d/m/Y'), 10);
						if(COD_CLI_PADD_IN_ORDER == '-')
							$COD_CLI_PADD_IN_ORDER = ' ';
						else
							$COD_CLI_PADD_IN_ORDER = COD_CLI_PADD_IN_ORDER;
						
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($this->tEngine->getFormatCodiceCliente($BeanCustomer->customer_code, $COD_CLI_PADD_IN_ORDER), 7);
						$str .= '  ';
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($value['giacenza']['bar_code'], 20);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(($value['basket_qty']['sel_quantita']*$value['giacenza']['quantita']), 10);
						
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder( ($value['giacenza'][$this->key_prezzo]*$value['giacenza']['quantita'])*$value['basket_qty']['sel_quantita'] , 10);
						
// 						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($value['giacenza'][Currency::getPriceByQty($value['giacenza'], $value['basket_qty']['sel_quantita'])], 10);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 10);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 5);
						
						/* AGGIUNTO COLORE */
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 275);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($value['nota'], 80);
						/* AGGIUNTO COLORE */
						$str .= "\r\n";
						fwrite($fp, $str);
					}
					fclose($fp);
				}

				$products = $_SESSION[session_id()]['basket'];
				unset($products['n_carrelli']);
				unset($products['perc_occupazione']);
				
				$this->SendEmail($BeanEcmOrdini, $BeanUsersAnag, $products, $products_fornitori, $products_fornitori_de);
			}

			$this->tEngine->assign('confirm', true);
			$this->tEngine->assign('num_ordine', $_SESSION[session_id()]['ecm_id_ordine']);
			unset($_SESSION[session_id()]);
		}
		elseif($_REQUEST['payment_type'] == 'CARTA DI CREDITO')
		{
			ini_set('display_errors', true);
			//Codice esercente 9090221
			//Tid (Terminal id) 00628565
			//04251710754

			if(empty($confirm))
			{
				$IMPORTO = 0.00;
				$tmp = $_SESSION[session_id()]['basket'];
				unset($tmp['n_carrelli']);
				unset($tmp['perc_occupazione']);
				foreach ($tmp as $val)
				{
					$price_it_qty = $val['price_it_qty'];
					$price_discounted_it_qty = $val['price_discounted_it_qty'];
					if(!empty($price_discounted_it_qty) && $price_discounted_it_qty > 0)
						$IMPORTO = $IMPORTO + str_replace(',', '.', $price_discounted_it_qty);
					else
						$IMPORTO = $IMPORTO + str_replace(',', '.', $price_it_qty);
					$IMPORTO = $IMPORTO + $prezzoIva;
				}
				include_once(APP_ROOT.'/beans/index_orders.php');
				$BeanIndexOrders = new index_orders();
				$index_orders = $BeanIndexOrders->dbGetAll($this->conn);
				$orderId 	   = $index_orders[0]['id'];
				$BeanIndexOrders->fast_edit($this->conn, $orderId);
				$_SESSION[session_id()]['cc_paymnent'] = $orderId;
				$this->_redirect("?act=PaySella&st=1&amount=".$IMPORTO."&transactionId=".$orderId);
				exit();
			}
		}
		else 
		{
			if(empty($_SESSION['LoggedUser']))
			{
				$_SESSION[session_id()]['return'] = 'CheckoutShopping';
				$this->_redirect('?act=Login');
			}
			if(empty($_SESSION[session_id()]['ecm_id_ordine']))
			{
				$BeanEcmOrdini = new ecm_ordini();
				$BeanEcmOrdini->setTipo_pagamento($_SESSION[session_id()]['payment_type']);
				$BeanEcmOrdini->setId_user($_SESSION['LoggedUser']['id']);
				$BeanEcmOrdini->setOperatore(ECM_OPERATORE);
				$BeanEcmOrdini->setPagato(0);
				$BeanEcmOrdini->setFatturato(0);
				$BeanEcmOrdini->setSpedito(0);
				$idOrdine = $BeanEcmOrdini->dbStore($this->conn);
				$_SESSION[session_id()]['ecm_id_ordine'] = $idOrdine;
			}
			else
			{
				$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
				$values = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
				foreach ($values as $val)
					$BeanEcmOrdiniMagazzino->dbDelete($this->conn, $val['id']);
				
				$BeanEcmOrdiniMagazzinoFornitori = new ecm_ordini_magazzino_fornitori();
				$values = $BeanEcmOrdiniMagazzinoFornitori->dbGetAllByIdOrdine($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
				foreach ($values as $val)
					$BeanEcmOrdiniMagazzinoFornitori->dbDelete($this->conn, $val['id']);

				$BeanEcmOrdiniMagazzinoFornitoriDe = new ecm_ordini_magazzino_forn_de();
				$values = $BeanEcmOrdiniMagazzinoFornitoriDe->dbGetAllByIdOrdine($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
				foreach ($values as $val)
					$BeanEcmOrdiniMagazzinoFornitoriDe->dbDelete($this->conn, $val['id']);
			}
				
			$IMPORTO = 0.00;

			$tmp = $_SESSION[session_id()]['basket'];
			unset($tmp['n_carrelli']);
			unset($tmp['perc_occupazione']);
				
			foreach ($tmp as $val)
			{
				$price_it_qty = $val['price_it_qty'];
				$price_discounted_it_qty = $val['price_discounted_it_qty'];
				if(!empty($price_discounted_it_qty) && $price_discounted_it_qty > 0)
					$IMPORTO = $IMPORTO + str_replace(',', '.', $price_discounted_it_qty);
				else
					$IMPORTO = $IMPORTO + str_replace(',', '.', $price_it_qty);
				
// 				$prezzoIva = (str_replace(',', '.', $price_it_qty) * $val['giacenza']['cod_iva']) / 100;
// 				$prezzoIva = round($prezzoIva, 2);
				$IMPORTO = $IMPORTO + $prezzoIva;

				$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
				if(!empty($_REQUEST['nota_'.$val['giacenza']['id']]) && !stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Inserisci una nota per il prodotto'))
					$BeanEcmOrdiniMagazzino->setNota($_REQUEST['nota_'.$val['giacenza']['id']]);
				if(!empty($_REQUEST['indispensabile_'.$val['giacenza']['id']]) && $_REQUEST['indispensabile_'.$val['giacenza']['id']] == 'on')
					$BeanEcmOrdiniMagazzino->setIndispensabile(1);
				
				$BeanEcmOrdiniMagazzino->setImporto_prodotto($val['giacenza'][$this->key_prezzo]);
				$BeanEcmOrdiniMagazzino->setName_it($val['giacenza']['descrizione']);
// 				$BeanEcmOrdiniMagazzino->setId_content($val['giacenza']['id']);
				$BeanEcmOrdiniMagazzino->setId_magazzino($val['giacenza']['id']);
				$BeanEcmOrdiniMagazzino->setId_ordine($_SESSION[session_id()]['ecm_id_ordine']);
				$BeanEcmOrdiniMagazzino->setQuantita($val['basket_qty']['sel_quantita']);
				if(!empty($val['selected_color']))
					$BeanEcmOrdiniMagazzino->setColore($val['selected_color']);
				$BeanEcmOrdiniMagazzino->setImporto($price_it_qty);
				$BeanEcmOrdiniMagazzino->dbStore($this->conn);
			}
				
			unset($_SESSION[session_id()]['basket_fornitori']['n_carrelli']);
			unset($_SESSION[session_id()]['basket_fornitori']['perc_occupazione']);
			$IMPORTO_FOR = 0.00;
			foreach ($_SESSION[session_id()]['basket_fornitori'] as $val)
			{
				$qty_sc = $val['giacenza']['qta_scatola'];
				$qty_pi = $val['giacenza']['qta_pianale'];
				
				$ricarico = 0;
				$ricarico_commissione_imballi_dendekker = 0;
				$ricarico_commissione_fissa_dendekker = 0;
				if(!empty($val['basket_qty']['sel_quantita_pianale']))
				{
					$prezzo_pi = $val['giacenza']['prezzo_pi'];
// 					if(!empty($assignmanet['commissione_fissa_dendekker']))
// 						$prezzo_pi += round($this->tEngine->getRicarico($prezzo_pi, $assignmanet['commissione_fissa_dendekker']), 2);
					
// 					if(!empty($assignmanet['commissione_imballi_dendekker']))
// 						$prezzo_pi += round($this->tEngine->getRicarico($prezzo_pi, $assignmanet['commissione_imballi_dendekker']), 2);
						
// 					if(!empty($_SESSION['LoggedUser']['sconto_fornitori_nl']))
// 						$prezzo_pi += round($this->tEngine->getRicarico($prezzo_pi, $_SESSION['LoggedUser']['sconto_fornitori_nl']), 2);
						
					$importo_prodotto = ( $prezzo_pi * $qty_pi ) * $val['basket_qty']['sel_quantita_pianale'];
				}
				else
				{
					$prezzo_sc = $val['giacenza']['prezzo_acquisto'];
// 					if(!empty($assignmanet['commissione_fissa_dendekker']))
// 						$prezzo_acquisto += round($this->tEngine->getRicarico($prezzo_acquisto, $assignmanet['commissione_fissa_dendekker']), 2);
					 
// 					if(!empty($assignmanet['commissione_imballi_dendekker']))
// 						$prezzo_acquisto += round($this->tEngine->getRicarico($prezzo_acquisto, $assignmanet['commissione_imballi_dendekker']), 2);
					
// 					if(!empty($_SESSION['LoggedUser']['sconto_fornitori_nl']))
// 						$prezzo_acquisto += round($this->tEngine->getRicarico($prezzo_acquisto, $_SESSION['LoggedUser']['sconto_fornitori_nl']), 2);
					
// 					$this->tEngine->getRicarico($prezzo_acquisto, $this->commissioneScatolaDenDekker[0]['name']);
						
					$importo_prodotto = ( $prezzo_sc * $qty_sc ) * $val['basket_qty']['sel_quantita'];				
				}
				// $importo_prodotto = ( (str_replace(',', '.', $val['giacenza']['prezzo_sc'] + $ricarico) *$qty_sc)*$val['basket_qty']['sel_quantita']);
				$IMPORTO_FOR = $IMPORTO_FOR + $importo_prodotto;
				
				$BeanEcmOrdiniMagazzinoFornitori = new ecm_ordini_magazzino_fornitori();
				if(!empty($_REQUEST['nota_for_nl_'.$val['giacenza']['id']]) && !stristr($_REQUEST['nota_for_nl_'.$val['giacenza']['id']], 'Inserisci una nota per il prodotto'))
					$BeanEcmOrdiniMagazzinoFornitori->setNota($_REQUEST['nota_for_nl_'.$val['giacenza']['id']]);
				if(!empty($_REQUEST['indispensabile_for_nl_'.$val['giacenza']['id']]) && $_REQUEST['indispensabile_for_nl_'.$val['giacenza']['id']] == 'on')
					$BeanEcmOrdiniMagazzinoFornitori->setIndispensabile(1);

				$BeanEcmOrdiniMagazzinoFornitori->setName_it($val['giacenza']['descrizione']);

				$BeanEcmOrdiniMagazzinoFornitori->setId_content(0);
				$BeanEcmOrdiniMagazzinoFornitori->setId_magazzino($val['giacenza']['id']);
				$BeanEcmOrdiniMagazzinoFornitori->setId_ordine($_SESSION[session_id()]['ecm_id_ordine']);
				if(!empty($val['basket_qty']['sel_quantita_pianale']))
					$BeanEcmOrdiniMagazzinoFornitori->setQuantita_pianale($val['basket_qty']['sel_quantita_pianale']);
				else
					$BeanEcmOrdiniMagazzinoFornitori->setQuantita($val['basket_qty']['sel_quantita']);
				$BeanEcmOrdiniMagazzinoFornitori->setImporto($importo_prodotto);
				$BeanEcmOrdiniMagazzinoFornitori->dbStore($this->conn);
			}
				
			unset($_SESSION[session_id()]['basket_fornitori_de']['n_carrelli']);
			unset($_SESSION[session_id()]['basket_fornitori_de']['perc_occupazione']);
			$IMPORTO_FOR_DE = 0.00;
			foreach ($_SESSION[session_id()]['basket_fornitori_de'] as $val)
			{
				$qty_sc = $val['giacenza']['qta_scatola'];
			
				$ricarico = '0.'.$_SESSION['LoggedUser']['sconto_fornitori_de'];
				$ricarico = round($val['giacenza']['prezzo_acquisto'] * $ricarico, 2);
			
				$importo_prodotto_de = ( (str_replace(',', '.', $val['giacenza']['prezzo_acquisto'] + $ricarico) *$qty_sc)*$val['basket_qty']['sel_quantita']);
				$IMPORTO_FOR_DE = $IMPORTO_FOR_DE + $importo_prodotto_de;
				$BeanEcmOrdiniMagazzinoFornitoriDe = new ecm_ordini_magazzino_forn_de();
				if(!empty($_REQUEST['nota_for_de_'.$val['giacenza']['id']]) && !stristr($_REQUEST['nota_for_de_'.$val['giacenza']['id']], 'Inserisci una nota per il prodotto'))
					$BeanEcmOrdiniMagazzinoFornitoriDe->setNota($_REQUEST['nota_for_de_'.$val['giacenza']['id']]);
				if(!empty($_REQUEST['indispensabile_for_de_'.$val['giacenza']['id']]) && $_REQUEST['indispensabile_for_de_'.$val['giacenza']['id']] == 'on')
					$BeanEcmOrdiniMagazzinoFornitoriDe->setIndispensabile(1);

				$BeanEcmOrdiniMagazzinoFornitoriDe->setName_it($val['giacenza']['descrizione']);				
				
				$BeanEcmOrdiniMagazzinoFornitoriDe->setId_content(0);
				$BeanEcmOrdiniMagazzinoFornitoriDe->setId_magazzino($val['giacenza']['id']);
				$BeanEcmOrdiniMagazzinoFornitoriDe->setId_ordine($_SESSION[session_id()]['ecm_id_ordine']);
				$BeanEcmOrdiniMagazzinoFornitoriDe->setQuantita($val['basket_qty']['sel_quantita']);
				$BeanEcmOrdiniMagazzinoFornitoriDe->setImporto($importo_prodotto_de);
				$BeanEcmOrdiniMagazzinoFornitoriDe->dbStore($this->conn);
			}
				
			$BeanEcmOrdini = new ecm_ordini($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
			$BeanEcmOrdini->setImporto($this->tEngine->getFormatPrice($IMPORTO+$IMPORTO_FOR+$IMPORTO_FOR_DE));
			$BeanEcmOrdini->dbStore($this->conn);

			if(!empty($_REQUEST['id_user']))
			{
				$BeanUsers = new users($this->conn, $_REQUEST['id_user']);
				$BeanUsersAnag = new users_anag($this->conn, $BeanUsers->getId_anag());
				$BeanUsersAnag->fill($_REQUEST);
				$BeanUsersAnag->dbStore($this->conn);
			}
			$this->_redirect("?act=".$this->className.'&params='.base64_encode('confirm=0&id_ordine='.$_SESSION[session_id()]['ecm_id_ordine']).'&stream='.session_id());
		}
		
		$this->tEngine->assign('content', $content);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function generateEtiflorFileOrder($products, $fp, $BeanMagazzino)
	{
		$data_doc = date('d-m-Y');
		foreach ($products as $key => $value)
		{
			$BeanMagazzino->dbGetOne($this->conn, $value['id_magazzino']);
			
			$magazzino = $BeanMagazzino->vars();
			$magazzino['prezzo_acquisto'] = $magazzino['prezzo_acquisto'] + round($this->tEngine->getRicarico($magazzino['prezzo_acquisto'], $_SESSION['LoggedUser']['sconto_fornitori_nl']), 2);
			$magazzino['prezzo_pi'] = $magazzino['prezzo_pi'] + round($this->tEngine->getRicarico($magazzino['prezzo_pi'], $_SESSION['LoggedUser']['sconto_fornitori_nl']), 2);
				
			$str = 'FI';// TIPO DOC
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 7);// NUM DOC
			$str .= $data_doc;//DATA DOC
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($this->tEngine->getFormatCodiceCliente($_SESSION['LoggedUser']['id_customer'], $COD_CLI_PADD_IN_ORDER), 7);// COD CLIENTE
			$str .= '  ';//FILLER
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('10', 20);//COD ART/PART
			if(!empty($value['quantita']))
			{
				$exp = explode(' x ', $magazzino['qta_scatola']);
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(($value['quantita']*$exp[0]), 10);//QUANTITA
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($magazzino['prezzo_acquisto'], 10);//PREZZO
			}
			elseif(!empty($value['quantita_pianale']))
			{
				$exp = explode(' x ', $magazzino['qta_pianale']);
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(($value['quantita_pianale']*($exp[0]*$exp[1])), 10);//QUANTITA
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($magazzino['prezzo_pi'], 10);//PREZZO
			}

			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 10);//PAGATO
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO RIGA
			$str .= '       ';//FILLER
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO DOC
			$str .= '*  ';//FISSO
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 7);//COD DEST
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO RIGA 2
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO RIGA 3
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 5);//SCONTO DOC 2
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('', 21);//FILLER
			$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(substr($magazzino['descrizione'], 0, 80), 80);//DESCRIZIONE
			$str .= "\r\n";
			fwrite($fp, $str);
		}
	}
	
	function generateCsvDenDekker($BeanEcmOrdini, $products_fornitori, $BeanUsers)
	{
		$separator = ';';
		include_once(APP_ROOT.'/beans/dendekker_codes.php');
		$BeanDenDekkerCode = new dendekker_codes();
		
		include_once(APP_ROOT.'/beans/customer.php');
		$BeanCustomer = new customer($this->conn, $BeanUsers->id_customer);
		
		$BeanDenDekkerCode->dbGetOneByCustomerCode($this->conn, 'GA'.substr($BeanCustomer->customer_code, 1, strlen($BeanCustomer->customer_code)));

		$exp = explode('-', $BeanEcmOrdini->data_partenza_fornitore_1);
		$data_partenza = $exp[2].'-'.$exp[1].'-'.$exp[0];
		
		$txt = '"'.$BeanDenDekkerCode->code.'"'.$separator.'"'.$data_partenza.'"'.$separator.'""';
		$txt .= "\r\n";
		
		foreach($products_fornitori as $chiave => $valore)
		{
			$BeanGicenzeFornitore = new giacenze_fornitori($this->conn, $valore['id_magazzino']);
			$exp_pi = explode(' x ', $BeanGicenzeFornitore->qta_pianale);
			$exp_sc = explode(' x ', $BeanGicenzeFornitore->qta_scatola);
			if(!empty($valore['quantita_pianale']))
				$quantita_acquistata = $valore['quantita_pianale']*($exp_pi[0]);
			elseif(!empty($valore['quantita']))
				$quantita_acquistata = $valore['quantita'];

			$txt .='"'.$BeanGicenzeFornitore->codice.'"'.$separator.'"'.$quantita_acquistata.'"'.$separator.'"'.$exp_sc[1].'"'.$separator.'"'.$BeanGicenzeFornitore->descrizione.'"'.$separator.'""'.$separator.'"'.$BeanGicenzeFornitore->prezzo_acquisto.'"'.$separator.'"0.00"'.$separator.'"K"';
			$txt .= "\r\n";
		}		

		$fileCsv = APP_ROOT.'/email_excel_fornitori/Order_DenDekker_N_'.$BeanEcmOrdini->id.'_CLI_GA'.$BeanCustomer->customer_code.'.csv';

		$fp = fopen($fileCsv, 'w');
		fwrite($fp, $txt);
		fclose($fp);
		
		return $fileCsv;
	}
	
	function generateExcelGasa($BeanEcmOrdini, $products_fornitori, $BeanUsers)
	{
		include_once(APP_ROOT.'/beans/customer.php');
		$BeanCustomer = new customer($this->conn, $BeanUsers->id_customer);
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Integra srl")->setLastModifiedBy("Integra srl")->setTitle("Order n. ".$BeanEcmOrdini->id)
		->setSubject("Order n. ".$BeanEcmOrdini->id)->setDescription("Order n. ".$BeanEcmOrdini->id)->setKeywords("Order n. ".$BeanEcmOrdini->id)->setCategory("Order n. ".$BeanEcmOrdini->id);
		
		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1', 'Articolo')
		->setCellValue('B1', 'Vaso')
		->setCellValue('C1', 'Altezza')
		->setCellValue('D1', 'Casse x Pianale')
		->setCellValue('E1', 'Pezzi x Cassa')
		//->setCellValue('F1', 'Pianali x CC')
		->setCellValue('F1', 'Prezzo x CC')
		->setCellValue('G1', 'Codice Bara')
		->setCellValue('H1', 'Quantita scatola/e ordinati')
		->setCellValue('I1', 'Cod. Art.')
		->setCellValue('J1', 'Note')
		->setCellValue('K1', 'Indispensabile x Spedizione')
		->setCellValue('L1', 'Partenza')
		->setCellValue('M1', 'Provincia');

		$x = 2;
		foreach($products_fornitori as $chiave => $valore)
		{
			$BeanGicenzeGasa = new giacenze_forn_gasa($this->conn, $valore['id_magazzino']);
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$x, $BeanGicenzeGasa->descrizione)
			->setCellValue('B'.$x, $BeanGicenzeGasa->diametro_vaso)
			->setCellValue('C'.$x, $BeanGicenzeGasa->altezza_pianta)
			->setCellValue('D'.$x, $BeanGicenzeGasa->qta_pianale)
			->setCellValue('E'.$x, $BeanGicenzeGasa->prezzo_pi)
			//->setCellValue('F'.$x, 'Pianali x CC')
			->setCellValue('F'.$x, $BeanGicenzeGasa->prezzo_acquisto)
			->setCellValue('G'.$x, $BeanGicenzeGasa->bar_code)
			->setCellValue('H'.$x, $valore['quantita'])
			->setCellValue('I'.$x, $BeanGicenzeGasa->codice)
			->setCellValue('J'.$x, $valore['nota'])
			->setCellValue('K'.$x, $valore['indispensabile'])
			->setCellValue('L'.$x, $this->tEngine->getFormatDate($BeanEcmOrdini->data_partenza_fornitore_2))
			->setCellValue('M'.$x, $BeanCustomer->provincia);
			$x++;
		}
		$objPHPExcel->setActiveSheetIndex(0);
	
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$fileExcel = APP_ROOT.'/email_excel_fornitori/Order_Gasa_N_'.$BeanEcmOrdini->id.'_CLI_GA'.$BeanCustomer->customer_code.'.xlsx';
		$objWriter->save($fileExcel);
		
		return $fileExcel;
	}

	function SendEmail($BeanEcmOrdini, $userAnag, $products, $products_fornitori, $products_fornitori_de)
	{
		ini_set('precision', 12);
		
		include_once(APP_ROOT.'/beans/index_orders.php');
		$BeanIndexOrders = new index_orders();
		$index_orders = $BeanIndexOrders->dbGetAll($this->conn);
		
		$BeanEcmOrdini = $BeanEcmOrdini->vars();
		$userAnag	   = $userAnag->vars();
		if(empty($_SESSION[session_id()]['cc_paymnent']))
			$orderId = $index_orders[0]['id'];
		else
			$orderId = $_SESSION[session_id()]['cc_paymnent'];

		$assignmanet   = $this->tEngine->assignment;

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
				    <title>Conferma ordine N. '.$index_orders.' - '.PREFIX_META_TITLE.'</title>
				</HEAD>
				<body style="background-color:#fff;font-family: Arial, Tahoma, Verdana, FreeSans, sans-serif;">
				<table width="80%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'/img/web/custom_logo/logo.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
						<!--<h3>'.PREFIX_META_TITLE.'</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#000000;font-size:16px;">
						Gentile '.$userAnag['name'].' '.$userAnag['surname'].',<br> il tuo ordine # '.$index_orders[0]['id'].' &eacute; andato a buon fine.<br><br>
						Di seguito ti riportiamo i dettagli del tuo ordine. 
					</td>
				</tr>
				<tr>
					<td width="50%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #000000;">
						<tr style="background-color:#000000;">
							<td colspan="2" style="color:#fff;font-size:16px;"><b>Dati Fatturazione</b></td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Nome</td>
							<td>'.$userAnag['name'].'</td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Cogome</td>
							<td>'.$userAnag['surname'].'</td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Indirizzo</td>
							<td>'.$userAnag['address'].' '.$userAnag['cap'].' - '.$userAnag['city'].' ('.$userAnag['province'].') - '.$userAnag['nation'].'</td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Indirizzo Secondario</td>
							<td>'.$userAnag['address_secondary'].'</td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Telefono Fisso</td>
							<td>'.$userAnag['phone'].'</td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Telefono Mobile</td>
							<td>'.$userAnag['mobile'].'</td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Email</td>
							<td>'.$userAnag['email'].'</td>
						</tr>
						</table>
					</td>
					<td width="50%" valign="top">';
					if(!empty($userAnag['address_spedizione']) || !empty($userAnag['address_secondary_spedizione']))
					{
						$html .= '<table width="100%" cellpadding="6" style="border:1px solid #000000;">
						<tr style="background-color:#000000;">
							<td colspan="2" style="color:#fff;font-size:16px;"><b>Dati Spedizione</b></td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Nome</td>
							<td>'.$userAnag['name_spedizione'].'</td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Cogome</td>
							<td>'.$userAnag['surname_spedizione'].'</td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>Indirizzo</td>
							<td>';
						if(!empty($userAnag['address_spedizione']))
							$html.= $userAnag['address_spedizione'];
						if(!empty($userAnag['cap_spedizione']))
							$html.= $userAnag['cap_spedizione'];
						if(!empty($userAnag['address_secondary_spedizione']))
							$html.= $userAnag['address_secondary_spedizione'];
						if(!empty($userAnag['province_spedizione']))
							$html.= ' ('.$userAnag['province_spedizione'].')';
						if(!empty($userAnag['nation_spedizione']))
							$html.= ' - '.$userAnag['nation_spedizione'];
						$html.= '</td>
						</tr>
						<tr style="color:#000000;faddress_secondaryont-size:16px;font-size:16px;">
							<td>Email</td>
							<td>'.$userAnag['email'].'</td>
						</tr>';
					$html .= '</table>';
					}
					$html .= '</td>
				</tr>
				<tr>
					<td width="50%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #000000;">
						<tr style="background-color:#000000;">
							<td colspan="2" style="color:#fff;font-size:16px;"><b>Modalit&aacute; di pagamento</b></td>
						</tr>
						<tr style="color:#000000;font-size:16px;">
							<td>';
							$html .= $BeanEcmOrdini['tipo_pagamento'];
							$html .= '</td>
						</tr>
						</table>					
					</td>
				</tr>';

				/* ORDINE MAGAZZINO */
				if(!empty($products))
				{
					$html .= '<tr>
						<td width="50%" valign="top" colspan="2">
							<table width="100%" cellpadding="6" style="border:1px solid #000000;">
							<tr style="background-color:#000000;">
								<td colspan="10" style="color:#fff;font-size:16px;"><b>Prodotti acquistati dal Magazzino</b></td>
							</tr>';
								
					include_once(APP_ROOT.'/beans/ApplicationSetup.php');
					$BeanApplicationSetup = new ApplicationSetup();
					$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
					foreach ($products as $key => $product)
					{
						$BeanGiacenze = new giacenze($this->conn, $product['giacenza']['id_magazzino']);
						$BeanGiacenze = $BeanGiacenze->vars();
						$Bean = new content();
						$content = $Bean->dbFree(MyDB::connect(), "SELECT * FROM content WHERE vbn = '".$BeanGiacenze['bar_code']."'");

						$image = null;
						$image = $this->tEngine->getImageFromVbn($product['giacenza']['vbn']);
						$product_image = $this->tEngine->dbGetImageProductFromBarCode($product['giacenza']['bar_code']);
						
						if(!empty($product_image)){
							$obj_image = $this->tEngine->dbGetImageFromBarCode($product['giacenza']['bar_code']);
							$product_image = $this->tEngine->dbGetImageProductFromBarCode($product['giacenza']['bar_code']);
						
							if(!empty($obj_image)){

								$d = dir($_tplvar['APP_ROOT'].'/email_images/');
								while (false !== ($entry = $d->read())) {
									if($entry != '.' && $entry != '..')
										$image = $obj_image[0]['www_path'].$obj_image[0]['name'];
								}
								$d->close();	
							}
							elseif(!empty($product_image))
								$image = $product_image;
							else
								$image = null;
						}

						if(!empty($image))
									$image = '<img src="'.$image.'" width="90">';
						else
							$image = '<img src="'.WWW_ROOT.IMG_DIR.'/web/image_large.gif" width="90">';

						$html .='
							<tr style="color:#000000;font-size:16px;">
								<td align="center">'.$image.'</td>
								<td align="center">'.$product['giacenza']['bar_code'].'</td>
								<td nowrap="nowrap">'.$product['giacenza']['descrizione'].'</td>
								<td align="center">'.$product['basket_qty']['sel_quantita'].'</td>';
								$html .='<td align="center">'.$product['nota'].'</td>';
								$html .='
								<td>'.$product['giacenza']['nota'].'</td>';
						if(!empty($product['giacenza']['indispensabile']))									
							$html .='<!--<td align="center">Si</td>-->';
						else
							$html .='<!--<td align="center">No</td>-->';
						$html .='</tr>
						';
					}

					$html .= '</table>
						</td>
					</tr>';
				}
				/* ORDINE MAGAZZINO */
				
						
				

				
// 				$html .= '<tr>
// 					<td width="50%" valign="top" colspan="2">
// 						<table width="100%" cellpadding="6" style="border:1px solid #000000;">
// 						<tr style="background-color:#000000;">
// 							<td colspan="10" style="color:#fff;font-size:16px;"><b>Totale Carrello</b></td>
// 						</tr>';
				if(!empty($imponibile_for) || !empty($imponibile_for_de))
				{
					$html .= '<tr style="color:#000000;font-size:16px;">';
					$html .= '<td colspan="10" align="right">';
					$html .= '<table cellpadding="6" width="220">';
					$html .= '<tr style="color:#000000;font-size:16px;">';
					$html .= '<td align="right"></td>';
					$html .= '<td></td>';
					$html .= '</tr>';
// 					if($tot_imponibile > 0)
// 					{
// 						$html .= '<tr style="color:#000000;font-size:16px;">';
// 						$html .= '<td align="right">Imponibile</td>';
// 						$html .= '<td>'.Currency::FormatEuro($tot_imponibile).'</td>';
// 						$html .= '</tr>';
// 					}
					$html .= '<tr style="color:#000000;font-size:16px;">';
					$html .= '<td align="right">Totale</td>';
					$totale += $total_for+$total_for_de+$total+$tot_prezzo_iva;
					$html .= '<td>'.Currency::FormatEuro( $totale ).'</td>';
					$html .= '</tr>';
					$html .= '</table>';
					$html .= '</td>';
					$html .= '</tr>';
				}
				$html .= '</table>';
				
				
// 				$html_n_carrelli = '<table width="100%" cellpadding="6" style="border:1px solid #000000;">
// 						<tr style="background-color:#000000;">
// 							<td colspan="10" style="color:#fff;font-size:16px;"><b>Carrelli Acquistati</b></td>
// 						</tr>';
				if(count($_SESSION[session_id()]['basket_fornitori']) > 2)
				{
					$html_n_carrelli .= '<tr>';
					$html_n_carrelli .= '<td>NUMERO CARRELLI FORNITORE 1</td>';
					$html_n_carrelli .= '<td align="right">'.$_SESSION[session_id()]['basket_fornitori']['n_carrelli'].' Carrelli al '.$_SESSION[session_id()]['basket_fornitori']['perc_occupazione'].'%</td>';
					$html_n_carrelli .= '<td align="right">&nbsp;</td>';
					$html_n_carrelli .= '</tr>';
				}
				if(count($_SESSION[session_id()]['basket_fornitori_de']) > 2)
				{
					$html_n_carrelli .= '<tr>';
					$html_n_carrelli .= '<td>NUMERO CARRELLI FORNITORE 2</td>';
					$html_n_carrelli .= '<td>'.$_SESSION[session_id()]['basket_fornitori_de']['n_carrelli'].' Carrelli al '.$_SESSION[session_id()]['basket_fornitori_de']['perc_occupazione'].'%</td>';
					$html_n_carrelli .= '<td align="right">&nbsp;</td>';
					$html_n_carrelli .= '</tr>';
				}
				$html_n_carrelli .= '</table>';
			$html_footer .= '
			<table>
			<tr>
				<td colspan="2" style="color:#000000;font-size:10px;">
					'.ADMIN_RAGIONE_SOCIALE.' - '.ADMIN_INDIRIZZO.' - '.ADMIN_TELEFONO.' - '.ADMIN_P_IVA.'
				</td>
			</tr>
			</table>';
				
			$html.= '</body>
			</html>';
// _dump(EMAIL_ADMIN_TO);
// echo $html;
// exit();
		$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
			
		/* EMAIL PER L'ESERCENTE */
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM,
				"To" 			=> EMAIL_ADMIN_FROM,
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "EMAIL ESERCENTE - Richiesta ordine N. ".$orderId." - ".PREFIX_META_TITLE,
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);
		//$this->setAttachment($this->attachedOrderGasa);
		$this->setHtmlText($html.$html_n_carrelli.$html_footer);
		$this->mail_factory();
		$is_send = $this->sendMail('amollica@integra-services.it');
 		$is_send = $this->sendMail(EMAIL_ADMIN_TO);
		/* EMAIL PER L'ESERCENTE */

		/* EMAIL PER L'UTENTE */
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM,
				"To" 			=> $userAnag['email'],
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "La sua Richiesta d'ordine N. ".$orderId." - ".PREFIX_META_TITLE,
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);
		$this->setAttachment('');		
		$this->setHtmlText($html.$html_n_carrelli.$html_footer);
		$this->mail_factory();

// 		$is_send = $this->sendMail($userAnag['email']);
		$is_send = $this->sendMail($BeanCustomer->email);
//		$is_send = $this->sendMail('alebobotti@gmail.com');
		$is_send = $this->sendMail('amollica@integra-services.it');
		//$is_send = $this->sendMail(EMAIL_ADMIN_TO);
		/* EMAIL PER L'UTENTE */

		if(empty($_SESSION[session_id()]['cc_paymnent']))
			$BeanIndexOrders->fast_edit($this->conn, $orderId);
		
		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
}
?>