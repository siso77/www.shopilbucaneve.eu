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
	var $attachedOrder;

	function CheckoutPayment()
	{
		parent::DBSmartyMailAction();
		
		$tmpSessNl = $_SESSION[session_id()]['basket_fornitori'];
		$tmpSessDe = $_SESSION[session_id()]['basket_fornitori_de'];
		unset($tmpSessNl['n_carrelli']);
		unset($tmpSessNl['perc_occupazione']);
		unset($tmpSessDe['n_carrelli']);
		unset($tmpSessDe['perc_occupazione']);

// 		if(empty($_SESSION['user_choice']['date']))
// 			$this->_redirect('?act=CheckoutShopping&error_partenza=1');
		
// 		if(!empty($tmpSessNl) && $tmpSessNl != array())
// 		{
// 			if(empty($_SESSION[session_id()]['partenza_fornitori']))
// 				$this->_redirect('?act=CheckoutShopping&error_partenza_fornitori_1=1');
// 		}	
// 		if(!empty($tmpSessDe) && $tmpSessDe != array())
// 		{
// 			if(empty($_SESSION[session_id()]['partenza_fornitori_de']))
// 				$this->_redirect('?act=CheckoutShopping&error_partenza_fornitori_2=1');
// 		}
			
		if(empty($_SESSION[session_id()]['basket']) && empty($_SESSION[session_id()]['basket_fornitori']) && empty($_SESSION[session_id()]['basket_fornitori_de']))
			$this->_redirect('');
		
		include_once(APP_ROOT.'/beans/ApplicationSetup.php');
		$BeanApplicationSetup = new ApplicationSetup();
		$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
		
		$this->importo_spese = $speseSpedizione[0]['name'];
		$this->id_negozio = "ID NEGOZIO";
		
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

				$BeanEcmOrdiniMagazzinoFornitoriDe = new ecm_ordini_magazzino_forn_de();
				$products_fornitori_de = $BeanEcmOrdiniMagazzinoFornitoriDe->dbGetAllByIdOrdine($this->conn, $BeanEcmOrdini->id);

				/* GENERO L'ORDINE IN FORATO EXCEL PER IL FORNITORE GASA */
				$this->attachedOrderGasa = $this->generateExcelGasa($BeanEcmOrdini, $products_fornitori_de, $BeanUsers);
				
				/* GENERO L'ORDINE IN FORATO EXCEL PER IL FORNITORE DENDEKKER */
				$this->attachedOrderDenDekker = $this->generateCsvDenDekker($BeanEcmOrdini, $products_fornitori, $BeanUsers);
				
				$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
				if(!empty($_SESSION[session_id()]['basket']))
				{
					$fp = fopen(APP_ROOT.'/FlorSysIntegration/Out/0000088V.'.$this->tEngine->getCutomFormatCode($idOrdine, 3), 'w+');
					fwrite($fp, "*MAG#0\r\n");
					
					foreach ($_SESSION[session_id()]['basket'] as $key => $value)
					{
						$BeanMagazzino = new giacenze();
						$_products[$key]['magazzino'] = $value['giacenza'];
						
						$quantita_totale = ($_products[$key]['magazzino']['quantita'])*$value['basket_qty']['sel_quantita'];
						$str = '';
						$str .= 'PC';
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 7);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder(date('d/m/Y'), 10);
						if(COD_CLI_PADD_IN_ORDER == '-')
							$COD_CLI_PADD_IN_ORDER = ' ';
						else
							$COD_CLI_PADD_IN_ORDER = COD_CLI_PADD_IN_ORDER;
						
						if(is_numeric($BeanCustomer->customer_code))
							$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($this->tEngine->getFormatCodiceCliente($BeanCustomer->customer_code, $COD_CLI_PADD_IN_ORDER), 7);
						else
							$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($this->tEngine->getFormatCodiceCliente($BeanCustomer->customer_code, ' '), 7);

						$str .= '  ';
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($_products[$key]['magazzino']['bar_code'], 20);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder( $quantita_totale, 10);
						
						if($value['contenuto']['id_gm'] == 23 && $value['contenuto']['id_famiglia'] == 6){
							$sconto = $value['giacenza'][$this->key_prezzo]*30/100;
							$value['giacenza'][$this->key_prezzo] = $value['giacenza'][$this->key_prezzo] - $sconto;
						}
						elseif($value['giacenza']['id_gm'] == 23){
							$sconto = $value['giacenza'][$this->key_prezzo]*30/100;
							$value['giacenza'][$this->key_prezzo] = $value['giacenza'][$this->key_prezzo] - $sconto;
						}						

						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($value['giacenza'][$this->key_prezzo], 10);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 10);
						$str .= $this->tEngine->getBlankSpaceForFlorSysOrder('0', 5);
						$str .= "\r\n";
						fwrite($fp, $str);						
					}
					fclose($fp);
				}
				$this->attachedOrder = $this->createPdfOrder($_SESSION[session_id()]['basket'], $this->tEngine, APP_ROOT.'/documenti_ordini/'.$_SESSION[session_id()]['ecm_id_ordine']);
				$this->SendEmail($BeanEcmOrdini, $BeanUsersAnag, $products, $products_fornitori, $products_fornitori_de);
				unlink($this->attachedOrder);
			}

			$this->tEngine->assign('confirm', true);
			$this->tEngine->assign('num_ordine', $_SESSION[session_id()]['ecm_id_ordine']);
			unset($_SESSION[session_id()]);
		}
		else 
		{
			if(empty($_SESSION['LoggedUser']))
			{
				$_SESSION[session_id()]['return'] = 'CheckoutShopping';
				$this->_redirect('?act=Login');
			}
/***************************************************** EVENTUALE LOGICA PER LA VERIFICA DELLA DISPONIBILIA'************************************************************/
// 			foreach ($_SESSION[session_id()]['basket'] as $basket)
// 			{
// 				$quantita_acquistata=0;
// 				$BeanGiacenze = new giacenze($this->conn, $basket['giacenza']['id']);
// 				$quantita_acquistata = $basket['basket_qty']['quantita']*$basket['basket_qty']['sel_quantita'];
// 				if($quantita_acquistata > $BeanGiacenze->disponibilita)
// 				_dump($BeanGiacenze);
// 			}
// 			_dump($_SESSION[session_id()]['basket']);
// 			_dump($_REQUEST);
// 			exit();
/***************************************************** EVENTUALE LOGICA PER LA VERIFICA DELLA DISPONIBILIA'************************************************************/
					
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
			foreach ($_SESSION[session_id()]['basket'] as $val)
			{
				$price_it_qty = $val['price_it_qty'];
				$price_discounted_it_qty = $val['price_discounted_it_qty'];
				
				$Content = $val['contenuto'];
				$BeanGiacenze = $val['giacenza'];
				if($Content['id_gm'] == 23 && $Content['id_famiglia'] == 6){
					$sconto = $BeanGiacenze[$this->key_prezzo]*30/100;
					$price_it_qty = $price_it_qty - $sconto;
					$price_discounted_it_qty = $price_discounted_it_qty - $sconto;
				}
				elseif($BeanGiacenze['id_gm'] == 23){
					$sconto = $BeanGiacenze[$this->key_prezzo]*30/100;
					$price_it_qty = $price_it_qty - $sconto;
					$price_discounted_it_qty = $price_discounted_it_qty - $sconto;
				}
				
				if(!empty($price_discounted_it_qty) && $price_discounted_it_qty > 0)
					$IMPORTO = $IMPORTO + str_replace(',', '.', $price_discounted_it_qty);
				else
					$IMPORTO = $IMPORTO + str_replace(',', '.', $price_it_qty);
				
				$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
				if(
					!empty($_REQUEST['nota_'.$val['giacenza']['id']]) && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Inserisci una nota per il prodotto') && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Enter a note for the product') && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Geben Sie eine Note') && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Entrez une note pour le produit') && 
					!stristr($_REQUEST['nota_'.$val['giacenza']['id']], 'Vvedite zapisku dlya produkta')
				)
					$BeanEcmOrdiniMagazzino->setNota($_REQUEST['nota_'.$val['giacenza']['id']]);
				if(!empty($_REQUEST['indispensabile_'.$val['giacenza']['id']]) && $_REQUEST['indispensabile_'.$val['giacenza']['id']] == 'on')
					$BeanEcmOrdiniMagazzino->setIndispensabile(1);
				
				$BeanEcmOrdiniMagazzino->setName_it($val['contenuto']['nome_it']);
				$BeanEcmOrdiniMagazzino->setId_content($val['contenuto']['id']);
				$BeanEcmOrdiniMagazzino->setId_magazzino($val['giacenza']['id']);
				$BeanEcmOrdiniMagazzino->setId_ordine($_SESSION[session_id()]['ecm_id_ordine']);
				$BeanEcmOrdiniMagazzino->setQuantita($val['basket_qty']['sel_quantita']);
				$BeanEcmOrdiniMagazzino->setImporto($price_it_qty);
				$BeanEcmOrdiniMagazzino->dbStore($this->conn);
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
		}
		if($_REQUEST['payment_type'] == 'PAY PAL')
		{
			$_SESSION[session_id()]['remote_address'] = $_SERVER['REMOTE_ADDR'];
			$PP_IDNEGOZIO = null;
			$PP_NUMORD = $_SESSION[session_id()]['ecm_id_ordine'];
			$PP_IMPORTO = str_replace(',', '.', $IMPORTO + str_replace(',', '.', $this->importo_spese));
			$PP_URLDONE = WWW_ROOT."?act=".$this->className.'&params='.base64_encode('confirm=1&payment_type=paypal&id_ordine='.$_SESSION[session_id()]['ecm_id_ordine'].'&remote_address='.$_SERVER['REMOTE_ADDR']).'&pro-bike='.session_id();
		
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head></head>
			<body style="background-color:#fff">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="pay-pal_form">
				<input type="hidden" name="return" value="'.$PP_URLDONE.'">
				<input type="hidden" name="business" value="info@piccologiardino.it">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="item_name" value="Order Number '.$PP_NUMORD.'">
				<input type="hidden" name="amount" value="'.$PP_IMPORTO.'">
				<input type="hidden" name="currency_code" value="EUR">
				<input type="hidden" name="item_number" value="'.$PP_NUMORD.'">
				</form>
				<script>
						var el = document.getElementById("pay-pal_form");
						el.submit();
				</script>
				<table width="100%" height="100%" style="background-color:#000000"><tr><td>&nbsp;Stai per essere rediretto sulle pagine di PayPal per concludere il pagamento!</td></tr></table>
			</body>
			</html>';
			exit();
		}
				
		if($_REQUEST['payment_type'] == 'CC')
		{
			$_SESSION[session_id()]['remote_address'] = $_SERVER['REMOTE_ADDR'];
			$IDNEGOZIO = $this->id_negozio;
			$NUMORD = $_SESSION[session_id()]['ecm_id_ordine'];
			$IMPORTO = str_replace(',', '.', $this->tEngine->getFormatPrice($IMPORTO + $this->importo_spese));
			$IMPORTO = str_replace('.', '', $IMPORTO);
			$VALUTA="978";
			$TCONTAB="I";
			$TAUTOR="I";
			$URLMS = WWW_ROOT."?act=Ms";
			$URLDONE = WWW_ROOT."?act=".$this->className.'&params='.base64_encode('confirm=1&id_ordine='.$_SESSION[session_id()]['ecm_id_ordine'].'&remote_address='.$_SERVER['REMOTE_ADDR']).'&krupy='.session_id();
			$URLBACK = WWW_ROOT.'?act=CheckoutShopping&params='.base64_encode('back=1&id_ordine='.$_SESSION[session_id()]['ecm_id_ordine']).'&krupy='.session_id();
			$KEY = "FjRqt6nyGU-ULeHpRJjhz-S-V-7mbGb-pMQUwdS7MSZfuJFN-x";
			
			$MACCHIARO = "URLMS=".$URLMS."&URLDONE=".$URLDONE."&NUMORD=".$NUMORD."&IDNEGOZIO=".$IDNEGOZIO."&IMPORTO=".$IMPORTO."&VALUTA=".$VALUTA."&TCONTAB=".$TCONTAB."&TAUTOR=".$TAUTOR."&".$KEY;
			$MACCHIARO2 = "NUMORD=".$NUMORD."&IDNEGOZIO=".$IDNEGOZIO."&IMPORTO=".$IMPORTO."&VALUTA=".$VALUTA."&TCONTAB=".$TCONTAB."&TAUTOR=".$TAUTOR."&".$KEY;
			// MAC = UCASE(MD5(MACCHIARO))
			$MAC = strtoupper(md5($MACCHIARO));
			
			$URLSEND = "https://atpos.ssb.it/atpos/pagamenti/main?PAGE=MASTER&
			IMPORTO=".$IMPORTO."&
			VALUTA=".$VALUTA."&
			NUMORD=".$NUMORD."&
			IDNEGOZIO=".$IDNEGOZIO."&
			URLDONE=".urlencode($URLDONE)."&
			URLBACK=".urlencode($URLBACK)."&
			URLMS=".urlencode($URLMS)."&
			TAUTOR=".$TAUTOR."&
			TCONTAB=".$TCONTAB."&
			MAC=".$MAC;
			$URLSEND = '';
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
			<!--<meta http-equiv="refresh" content="1;url='.$URLSEND.'">-->
			</head>
			<body style="background-color:#000000">
				<table width="100%" height="100%" style="background-color:#000000"><tr><td>&nbsp;</td></tr></table>
			</body>
			</html>';
			exit();
		}
		else if(empty($confirm))
			$this->_redirect("?act=".$this->className.'&params='.base64_encode('confirm=0&id_ordine='.$_SESSION[session_id()]['ecm_id_ordine']).'&stream='.session_id());

		$this->tEngine->assign('content', $content);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function createPdfOrder($data, $tEngine, $pathPdf)
	{
		ini_set('precision', 12);
		
		$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
		$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
		$ordine = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $_SESSION[session_id()]['ecm_id_ordine']);
		
		include_once(APP_ROOT.'/libs/ext/FPDF/fpdf.php');
		include_once(APP_ROOT.'/libs/TemplateClass/Template_Orders_PDF.php');
		include_once(APP_ROOT."/beans/gruppi_merceologici.php");
		
		$pdf=new PDF_MC_Table();
		$pdf->AddPage('L');
		$pdf->SetFont('Arial','',12);
	
		$pdf->PageBreakTrigger = 188;
		
		$imageHeaderX = 10;
		$imageHeaderY = 1;
		$imageHeaderWidth = 40;
		$imageHeaderHeight = 20;		
		$pdf->Image(WWW_ROOT.IMG_DIR.'/web/custom_logo/logo.jpg',$imageHeaderX,$imageHeaderY,$imageHeaderWidth, $imageHeaderHeight);
// 		$pdf->Image(WWW_ROOT.IMG_DIR.'/web/custom_logo/greenitaly.jpg',$imageHeaderX+100,$imageHeaderY,$imageHeaderWidth, $imageHeaderHeight);
		$pdf->setY(31);

		$pdf->SetX(2);
		$pdf->SetWidths(array(196, 97));
		$pdf->Row(
				array(
						$this->tEngine->getTranslation('Cliente').': '.$BeanCustomer->ragione_sociale
				));
		$pdf->SetX(2);
		$pdf->Row(
				array(
						$this->tEngine->getTranslation('Destinazione').': '.$BeanCustomer->indirizzo.' '.$BeanCustomer->citta.' '.$BeanCustomer->provincia.' '.$BeanCustomer->cap,
						$this->tEngine->getTranslation('Data Partenza Merce: ').$_SESSION['user_choice']['date']
				));
				
		$pdf->SetFont('Arial','',10);
		//Table with
		$pdf->SetWidths(array(15,16,14,52,15,
						15,12,18,35,15,10,15,22));
			
		srand(microtime()*1000000);
	
		$pdf->setX(2);
		$pdf->Row(array('Img','Vbn',$tEngine->getTranslation('Gruppo'),$tEngine->getTranslation('Descrizione'),$tEngine->getTranslation('Colore'), 
						$tEngine->getTranslation('Imballi'), 'Q x I', 'Q '.$tEngine->getTranslation('Totale'), $tEngine->getTranslation('Note'), 
						$tEngine->getTranslation('Prezzo'), $tEngine->getTranslation('IVA'), $tEngine->getTranslation('Prezzo Totale'), $tEngine->getTranslation('Urgente')));
		$currency = chr(128);
		
		unset($data['n_carrelli']);
		unset($data['perc_occupazione']);
		$data = array_values($data);
		
		foreach($data as $key => $value)
		{
			$BeanGM = new gruppi_merceologici($this->conn, $value['contenuto']['id_gm']);
				
			$pdf->SetFont('Arial','',8);
			$pdf->setX(2);
			$image = null;
			$image = $tEngine->getImageFromVbn($value['contenuto']['vbn']);
			$product_image = $tEngine->dbGetImageProductFromBarCode($value['giacenza']['bar_code']);

			if(empty($image)){
				$obj_image = $tEngine->dbGetImageFromBarCode($value['giacenza']['bar_code']);
				$product_image = $tEngine->dbGetImageProductFromBarCode($value['giacenza']['bar_code']);
			}
			if(!empty($obj_image)){
					
				$d = dir(APP_ROOT.'/email_images/');
				while (false !== ($entry = $d->read())) {
					if($entry != '.' && $entry != '..')
						$image = $obj_image[0]['www_path'].$obj_image[0]['name'];
				}
				$d->close();
			}
			elseif(!empty($product_image))
				$image = $product_image;
	
			$y_image = $pdf->GetY()+1;
			if($pdf->GetY()+$imageHeight > $pdf->PageBreakTrigger)
			{
				$pdf->AddPage('L');
				$y_image = 11;
				$pdf->SetX(2);
			}
				
			$imageWidth = 10;
			$imageHeight = 8;
			if(!empty($image))
				$im = $pdf->Image($image,$pdf->GetX()+1,$y_image+1,$imageWidth,$imageHeight,'','','C',false,300,'',false,false,0,false,false,false,'');
			else
				$im = $pdf->Image(WWW_ROOT."/img/web/image_large.gif",$pdf->GetX()+1,$pdf->GetY()+1,$imageWidth,$imageHeight,'','','C',false,300,'',false,false,0,false,false,false,'');
	
			$indispensabile = !empty($ordine[$key]['indispensabile']) ? 'Si' : '';
			
			$tot_prod = $value['giacenza']['prezzo_'.$_SESSION['LoggedUser']['listino']]*$value['giacenza']['quantita']*$value['basket_qty']['sel_quantita'];
				
			$pdf->Row(array(
					$im,
					$value['giacenza']['bar_code'],
					$BeanGM->gruppo,
					substr($value['giacenza']['nome_it'], 0, 40),
					$value['giacenza']['C3'],
// 					$value['giacenza']['C4'],
// 					substr($value['giacenza']['dimensione'], 0, 7),
// 					$value['giacenza']['openstage'],
					$value['basket_qty']['sel_quantita'],
					$value['giacenza']['quantita'],
					$value['basket_qty']['sel_quantita']*$value['giacenza']['quantita'],
					$ordine[$key]['nota'],
					$currency.' '.$tEngine->getFormatPrice($value['giacenza']['prezzo_'.$_SESSION['LoggedUser']['listino']]),
					$value['giacenza']['cod_iva'],
					$tEngine->getFormatPrice($tot_prod),
					$indispensabile
			),
					5
			);
			$tot += $tot_prod;
		}
	
		$pdf->SetWidths(array(10,22));
	
		// 		$pdf->SetX(263);
		// 		$pdf->MultiCell(32,120-count($data),'',1);

		$pdf->SetX(248);
		$pdf->SetWidths(array(10,15));
		$pdf->Row(array('Tot.', $currency.' '.$tEngine->getFormatPrice($tot)));
		$pdf->Output($pathPdf.'.pdf', 'f');
		return $pathPdf.'.pdf';
		
	}

	function generateEtiflorFileOrder($products, $fp, $BeanMagazzino)
	{
		$data_doc = date('d-m-Y');
		foreach ($products as $key => $value)
		{
			$BeanMagazzino->dbGetOne($this->conn, $value['id_magazzino']);
			
			$magazzino = $BeanMagazzino->vars();
			$magazzino['prezzo_sc'] = $magazzino['prezzo_sc'] + round($this->tEngine->getRicarico($magazzino['prezzo_sc'], $_SESSION['LoggedUser']['sconto_fornitori_nl']), 2);
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
				$str .= $this->tEngine->getBlankSpaceForFlorSysOrder($magazzino['prezzo_sc'], 10);//PREZZO
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

			$txt .='"'.$BeanGicenzeFornitore->codice.'"'.$separator.'"'.$quantita_acquistata.'"'.$separator.'"'.$exp_sc[1].'"'.$separator.'"'.$BeanGicenzeFornitore->descrizione.'"'.$separator.'""'.$separator.'"'.$BeanGicenzeFornitore->prezzo_sc.'"'.$separator.'"0.00"'.$separator.'"K"';
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
			->setCellValue('F'.$x, $BeanGicenzeGasa->prezzo_sc)
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
		
		$BeanEcmOrdini = $BeanEcmOrdini->vars();
		$userAnag	   = $userAnag->vars();
		$orderId 	   = $BeanEcmOrdini['id'];
		$assignmanet   = $this->tEngine->assignment;
		
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Conferma ordine N. '.$BeanEcmOrdini['id'].' - '.PREFIX_META_TITLE.'</title>
				</HEAD>
				<body style="background-color:#fff;font-family: Arial, Tahoma, Verdana, FreeSans, sans-serif;">
				<table width="80%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'img/web/custom_logo/logo.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
						<!--<h3>'.PREFIX_META_TITLE.'</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#000000;font-size:16px;">
						Gentile '.$userAnag['name'].' '.$userAnag['surname'].',<br> il tuo ordine # '.$BeanEcmOrdini['id'].' è andato a buon fine.<br><br>
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

				/* ORDINE ETIFLOR */
				if(!empty($_SESSION[session_id()]['basket']))
				{
					$html .= '<tr>
						<td width="50%" valign="top" colspan="2">
							<table width="100%" cellpadding="6" style="border:1px solid #000000;">
							<tr style="background-color:#000000;">
								<td colspan="10" style="color:#fff;font-size:16px;"><b>Prodotti acquistati</b></td>
							</tr>';
								
					include_once(APP_ROOT.'/beans/ApplicationSetup.php');
					$BeanApplicationSetup = new ApplicationSetup();
					$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
					
					$peso_spedizione = 0;
					
					$tmpSess = $_SESSION[session_id()]['basket'];
					unset($tmpSess['n_carrelli']);
					unset($tmpSess['perc_occupazione']);
					$tmpSess = array_values($tmpSess);

					foreach ($tmpSess as $key => $product)
					{
						$Content = new content($this->conn, $product['giacenza']['id_content']);
						$Content = $Content->vars();
						$BeanGiacenze = $product['giacenza'];
						//$quantita_totale = ($BeanGiacenze['quantita'])*$product['basket_qty']['sel_quantita'];
						$quantita_totale = $product['basket_qty']['sel_quantita'];
						
						$peso_spedizione += $Content['peso']*$quantita_totale;
						if($Content['id_gm'] == 23 && $Content['id_famiglia'] == 6){
							$sconto = $BeanGiacenze[$this->key_prezzo]*30/100;
							$BeanGiacenze[$this->key_prezzo] = $BeanGiacenze[$this->key_prezzo] - $sconto;
						}
						elseif($BeanGiacenze['id_gm'] == 23){
							$sconto = $BeanGiacenze[$this->key_prezzo]*30/100;
							$BeanGiacenze[$this->key_prezzo] = $BeanGiacenze[$this->key_prezzo] - $sconto;
						}

						if($_SESSION['LoggedUser']['is_foreign'] == 0)
							$iva = $Content['cod_iva'];
						$total = $total + str_replace(',', '.', $BeanGiacenze[$this->key_prezzo]);
						
						$imponibile = str_replace(',', '.', $BeanGiacenze[$this->key_prezzo]*$quantita_totale);
						if($_SESSION['LoggedUser']['is_foreign'] == 0)
							$prezzo_iva = str_replace(',', '.', $BeanGiacenze[$this->key_prezzo]*$quantita_totale) * $Content['cod_iva'] / 100;
						
						$tot_imponibile = $tot_imponibile + ($imponibile);
						$tot_prezzo_iva = $tot_prezzo_iva + $prezzo_iva;

						if($key == (count($products)-1))
						{
							if($speseSpedizione[0]['name'] != '0,00' && $speseSpedizione[0]['name'] > 0)
							{
								$total = $total + str_replace(',', '.', $speseSpedizione[0]['name']);
								if(empty($tot_prezzo_iva))
									$tot_prezzo_iva = $tot_imponibile - str_replace(',', '.', $speseSpedizione[0]['name']);
							}
						}						
					
						if($key == 0)
						{
							$html .='
								<tr style="color:#000000;font-size:16px;">
									<td>Codice Prodotto</td>
									<td>Nome</td>
									<td align="center"nowrap="nowrap">Quantit&aacute; Imballo</td>
									<td align="center"nowrap="nowrap">Imballi Acquistati</td>
									<td align="center">Importo</td>';
							if($_SESSION['LoggedUser']['is_foreign'] == 0)
								$html .= '<td align="center">IVA</td>';

							$html .= '<td align="center"nowrap="nowrap">Importo tot.</td>
									<td style="min-width:200px">Note</td>
								</tr>';
						}
						$html .='
							<tr style="color:#000000;font-size:16px;">
								<td align="center">'.$BeanGiacenze['bar_code'].'</td>
								<td nowrap="nowrap">'.substr($Content['nome_it'],0,20).'</td>
								<td align="center">'.$BeanGiacenze['quantita'].'</td>
								<td align="center">'.$product['basket_qty']['sel_quantita'].'</td>
								<td align="center">'.Currency::FormatEuro($BeanGiacenze[$this->key_prezzo]).'</td>';
						if($_SESSION['LoggedUser']['is_foreign'] == 0)
							$html .= '<td align="center">'.$iva.'%</td>';

						$html .= '<td align="center">'.Currency::FormatEuro($BeanGiacenze[$this->key_prezzo]*$quantita_totale).'</td>
								<td>'.$products[$key]['nota'].'</td>';
						$html .='</tr>
						';
					}
						
					if(!empty($imponibile))
					{
						$html .= '<tr style="color:#000000;font-size:16px;">';
						$html .= '<td colspan="10" align="right">';
						$html .= '<table cellpadding="6" width="220">';
						$html .= '<tr style="color:#000000;font-size:16px;">';
						if($speseSpedizione[0]['name'] != '0,00' && $speseSpedizione[0]['name'] > 0)
						{
							$html .= '<td align="right">Spese Spedizione</td>';
// 							if (($tot_imponibile + $tot_prezzo_iva) < 300)
							$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($speseSpedizione[0]['name']).'</td>';
// 							else 
// 							$html .= '<td>Omaggio</td>';
						}
						else
						{
							$html .= '<td align="right"></td>';
							$html .= '<td></td>';
						}
						
						$html .= '<tr style="color:#000000;font-size:16px;">';
						$html .= '<td align="right">Imponibile</td>';
						$html .= '<td>'.Currency::FormatEuro($tot_imponibile).'</td>';
						$html .= '</tr>';
						if($_SESSION['LoggedUser']['is_foreign'] == 0)
						{
							$html .= '<tr style="color:#000000;font-size:16px;">';
							$html .= '<td align="right">IVA '.$iva.'%</td>';
							$html .= '<td>'.Currency::FormatEuro(round($tot_prezzo_iva, 2)).'</td>';
							$html .= '</tr>';
						}
						if(!empty($peso_spedizione))
						{
							$html .= '</tr>';
							$html .= '<tr style="color:#000000;font-size:16px;">';
							$html .= '<td align="right">Spese Spedizione</td>';
							$html .= '<td>'.Currency::FormatEuro($this->tEngine->getFormatPrice($this->tEngine->getSpeseSpedizione($peso_spedizione))).'</td>';
							$html .= '</tr>';
						}
						
						$html .= '<tr style="color:#000000;font-size:16px;">';
						$html .= '<td align="right">Totale</td>';
// 						if (($tot_imponibile) < 300)
						$html .= '<td>'.Currency::FormatEuro( ($tot_imponibile + str_replace(',', '.', $speseSpedizione[0]['name'])) + +$tot_prezzo_iva).'</td>';
// 						else 
// 						$html .= '<td>'.Currency::FormatEuro( ($tot_imponibile)).'</td>';
						$html .= '</tr>';
						$html .= '</table>';
						$html .= '</td>';
						$html .= '</tr>';
					}
					$totale = $tot_imponibile;
					$html .= '</table>
						</td>
					</tr>';
				}
				/* ORDINE ETIFLOR */
				
			
			if(!empty($imponibile_for) || !empty($imponibile_for_de))
			{
				$html .= '<tr>
						<td width="50%" valign="top" colspan="2">
							<table width="100%" cellpadding="6" style="border:1px solid #000000;">
							<tr style="background-color:#000000;">
								<td colspan="10" style="color:#fff;font-size:16px;"><b>Totale Carrello</b></td>
							</tr>';
				$html .= '<tr style="color:#000000;font-size:16px;">';
				$html .= '<td colspan="10" align="right">';
				$html .= '<table cellpadding="6" width="220">';
				$html .= '<tr style="color:#000000;font-size:16px;">';
				$html .= '<td align="right"></td>';
				$html .= '<td></td>';
				$html .= '</tr>';
				if($tot_imponibile > 0)
				{
					$html .= '<tr style="color:#000000;font-size:16px;">';
					$html .= '<td align="right">Imponibile</td>';
					$html .= '<td>'.Currency::FormatEuro($tot_imponibile).'</td>';
					$html .= '</tr>';
				}
				$html .= '<tr style="color:#000000;font-size:16px;">';
				$html .= '<td align="right">Totale</td>';
				$totale = ($total) + $total_for+$total_for_de+$this->tEngine->getSpeseSpedizione($peso_spedizione);
				$html .= '<td>'.Currency::FormatEuro( ($totale+$tot_prezzo_iva) ).'</td>';
				$html .= '</tr>';
				$html .= '</table>';
				$html .= '</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
			
// 			$html_data_partenza .= '<table width="100%" cellpadding="6" style="border:1px solid #000000;"><tr>';
// 			$html_data_partenza .= '<td>DATA PARTENZA DELLA MERCE</td>';
// 			$html_data_partenza .= '<td>'.$_SESSION['user_choice']['date'].'</td>';
// 			$html_data_partenza .= '</tr>';
// 			$html_data_partenza .= '</table>';
// 			$html .= $html_data_partenza;
			
// $BeanOrdine = new ecm_ordini($this->conn, $BeanEcmOrdini['id']);
// $BeanOrdine->setImporto($totale);
// $BeanOrdine->dbStore($this->conn);

// 			$html_n_carrelli = '<table width="100%" cellpadding="6" style="border:1px solid #000000;">
// 						<tr style="background-color:#000000;">
// 							<td colspan="10" style="color:#fff;font-size:16px;"><b>Carrelli Acquistati</b></td>
// 						</tr>';
			
			if(count($_SESSION[session_id()]['basket_fornitori']) > 2)
			{
				$html_n_carrelli .= '<tr>';
				$html_n_carrelli .= '<td>NUMERO CARRELLI OLANDA</td>';
				$html_n_carrelli .= '<td>'.$_SESSION[session_id()]['basket_fornitori']['n_carrelli'].'</td>';
				$html_n_carrelli .= '</tr>';
			}
			if(count($_SESSION[session_id()]['basket_fornitori_de']) > 2)
			{
				$html_n_carrelli .= '<tr>';
				$html_n_carrelli .= '<td>NUMERO CARRELLI GERMANIA / DANIMARCA</td>';
				$html_n_carrelli .= '<td>'.$_SESSION[session_id()]['basket_fornitori_de']['n_carrelli'].'</td>';
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
			$this->setAttachment($this->attachedOrder);
// if($_SESSION['LoggedUser']['username'] == 'siso')
// {
// 	echo $html;
// 	exit();
// }
$destinatari  = "Silvio Sorrentino <siso77@gmail.com>" . ", " ;
$destinatari .= "Commerciale <".EMAIL_ADMIN_TO.">";
$oggetto = "Conferma ordine N. ".$_SESSION[session_id()]['ecm_id_ordine']." - ".PREFIX_META_TITLE;
$intestazioni  = "MIME-Version: 1.0\r\n";
$intestazioni .= "Content-type: text/html; charset=iso-8859-1\r\n";
//$intestazioni .= "To: Silvio Sorrentino <siso77@gmail.com>, Commerciale <".EMAIL_ADMIN_TO.">\r\n";
$intestazioni .= "From: Web Shop <no-replay@shopilbucaneve.eu>\r\n";

$is_send = mail($destinatari, $oggetto, $html, $intestazioni);
return true;
				
		/*********************** PER L'SMTP DI GOOGLE ***********************/
		$this->params["port"] = 465;
		$this->params["host"] = "ssl://".$this->params["host"];
		/*********************** PER L'SMTP DI GOOGLE ***********************/
//echo$html;
//exit();
				
		/*********************** EMAIL PER L'ESERCENTE ***********************/
		$hdrs = array("From" 	=> EMAIL_ADMIN_FROM,
				"To" 			=> EMAIL_ADMIN_FROM,
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "Conferma ordine N. ".$orderId." - ".PREFIX_META_TITLE,
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);		
		$this->setHtmlText($html.$html_n_carrelli.$html_footer);
		$this->mail_factory();
		$is_send = $this->sendMail('siso77@gmail.com');
		$is_send = $this->sendMail(EMAIL_ADMIN_TO);
		/*********************** EMAIL PER L'ESERCENTE ***********************/
		
		/*********************** EMAIL PER L'UTENTE ***********************/
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM,
				"To" 			=> $userAnag['email'],
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "Conferma ordine N. ".$orderId." - ".PREFIX_META_TITLE,
				"Date"		=> date("r")
		);
		$this->setHeaders($hdrs);
		$this->setHtmlText($html.$html_footer);
		$this->mail_factory();

 		$is_send = $this->sendMail($userAnag['email']);
		$is_send = $this->sendMail('siso77@gmail.com');
		/*********************** EMAIL PER L'UTENTE ***********************/

		if(PEAR::isError($is_send))
		{
			print_r($is_send);
			echo "Errore nell'invio della mail!";
			exit;
		}
		
		return $is_send;
	}
}
?>