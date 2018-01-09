<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/giacenze.php");
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");

class ShoppingCart extends DBSmartyAction
{	
	function ShoppingCart()
	{
		parent::DBSmartyAction();
// unset($_SESSION[session_id()]['basket']);
// unset($_SESSION[session_id()]['cart']);
// unset($_SESSION[session_id()]);
// _dump($_SESSION[session_id()]['basket']);
// exit();

		if(empty($_SESSION['LoggedUser']))
		{
			if(!empty($_REQUEST['is_ajax']))
			{
				echo "<script>document.location.href = '".WWW_ROOT."?act=Login';</script>";
				exit();
			}
			else
				$this->_redirect('?act=Login');
		}
		
		if(!empty($_REQUEST['print_orders']))
		{
			$this->createPdfOrder($_SESSION[session_id()]['basket'], $this->tEngine);
			exit();
				
		}		
		
		/***
		 * SETTO IL COLORE DEL PRODOTTO
		 */
		if(!empty($_REQUEST['colore']))
		{
			$beanBasket = new ecm_basket();
			$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id']);
			$beanBasketMagazzino = new ecm_basket_magazzino();
			$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $_REQUEST['id_magazzino']);
			$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);

			if(!empty($basketMagazzino))
			{
				$beanBasketMagazzino->setColore($_REQUEST['colore']);
				$beanBasketMagazzino->dbStore($this->conn);
			}
				
			$_SESSION[session_id()]['basket'][$_REQUEST['index']]['selected_color'] = $_REQUEST['colore'];
		}
		/***
		 * SETTO IL COLORE DEL PRODOTTO
		*/
		
		if(!empty($_SERVER['HTTP_DELETE_SESSION']))
			unset($_SESSION[session_id()]);
			
		if(!empty($_REQUEST['params']))
			$this->tEngine->assign('params_banking', $_REQUEST['params']);
			
		if(!empty($_REQUEST['delete']))
		{
			$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_giacenza']);
			$giacenza = $BeanGiacenze->vars();
				
			$beanBasket = new ecm_basket();
			$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id']);
			$i = 1;
			foreach ($_SESSION[session_id()]['basket'] as $key => $value)
			{
				if($key != 'n_carrelli' && $key != 'perc_occupazione')
				{
					if($value['giacenza']['id'] == $_REQUEST['id_giacenza'])
					{
						$beanBasketMagazzino = new ecm_basket_magazzino();
						$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
							
						if(!empty($basketMagazzino))
							$beanBasketMagazzino->dbDelete($this->conn, array($basketMagazzino['id']), false);
							
						unset($_SESSION[session_id()]['basket'][$key]);
					}
					else
					{
						$tmp_session[$i] = $value;
						$i++;
					}
				}
			}
				
			if(count($_SESSION[session_id()]['basket']) == 0)
				unset($_SESSION[session_id()]['ecm_basket']);
				
			unset($_SESSION[session_id()]['basket']);
			$_SESSION[session_id()]['basket'] = $tmp_session;
				
			if(empty($_REQUEST['is_ajax']))
			{
				$this->_redirect('?act=ShoppingCart');
				exit();
			}				
			if(count($_SESSION[session_id()]['basket']) == 0)
				unset($_SESSION[session_id()]['ecm_basket']);

			if(!empty($_REQUEST['is_ajax']))
			{
				$this->tEngine->assign('basket', $_SESSION[session_id()]['basket']);
				$this->calcolaVolumiCarrello();
				
				echo "<script>jQuery('#pencentuale_occupazione_top').html( 'Percentuale Occupazione Carrello ".$_SESSION[session_id()]['basket']['perc_occupazione']." % - N. Carrelli ".$_SESSION[session_id()]['basket']['n_carrelli']."');</script>";
				echo "<script>jQuery('#pencentuale_occupazione_bottom').html( 'Percentuale Occupazione Carrello ".$_SESSION[session_id()]['basket']['perc_occupazione']." % - N. Carrelli ".$_SESSION[session_id()]['basket']['n_carrelli']."');</script>";
				exit();
			}

			if(!empty($_REQUEST['delete_from_box']))
			{
				$this->_redirect('Magazzino-Online/Lista-Prodotti.html');
				exit();
			}
		}
		
		$beanBasket = new ecm_basket();
		$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
		
		if(!empty($_REQUEST['quantita']))
		{
			if(empty($_SESSION[session_id()]['basket']['n_carrelli']))
				$_SESSION[session_id()]['basket']['n_carrelli'][]['volume_occupato'] = 0;

			if(!empty($_REQUEST['is_ajax']))
			{
				$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_giacenza']);
				$giacenza = $BeanGiacenze->vars();
				
				$Bean = new content();
				
				$beanBasket = new ecm_basket();
				$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id'], date('Y-m-d'));
				
				if(empty($basket))
				{
					$beanBasket->setKey_session(session_id());
					$beanBasket->setId_user($_SESSION['LoggedUser']['id']);
					$beanBasket->setOperatore(ECM_OPERATORE);
					$beanBasket->dbStore($this->conn);
					$basket = $beanBasket->vars();
				}
				
				$beanBasketMagazzino = new ecm_basket_magazzino();
				$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
				$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
				if(empty($basketMagazzino))
				{
					$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
					$beanBasketMagazzino->setId_basket($basket['id']);
					$beanBasketMagazzino->setId_magazzino($giacenza['id']);
					$beanBasketMagazzino->setQuantita($_REQUEST['quantita']);
				}
				$beanBasketMagazzino->dbStore($this->conn);
				
				$_SESSION[session_id()]['ecm_basket'] = $basket['id'];
				
				$BeanMagazzino = new giacenze($this->conn, $_REQUEST['id_giacenza']);
				$in_session = false;
				foreach ($_SESSION[session_id()]['basket'] as $k => $value)
				{
					if($value['giacenza']['id'] == $giacenza['id'])
					{
						//$in_session = true;
						$in_session = false;
						$_SESSION[session_id()]['basket'][$k]['giacenza'] = $giacenza;
						$content = $giacenza;
						
						$this->key_prezzo = $this->getPriceByQty($giacenza, $_REQUEST['quantita']);
						
						$_SESSION[session_id()]['basket'][$k]['basket_qty']['quantita'] = $_REQUEST['quantita']*$content['quantita'];
						$_SESSION[session_id()]['basket'][$k]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'];
						$_SESSION[session_id()]['basket'][$k]['volume'] = $giacenza['volume_singolo'];
						$_SESSION[session_id()]['basket'][$k]['price_it_qty'] = (str_replace(',', '.', $giacenza[$this->key_prezzo]) * $_REQUEST['quantita'] )*$content['quantita'];
						
						$this->key_prezzo = null;
					}
				}
				if(!$in_session)
				{
					$tmp_session = $_SESSION[session_id()]['basket'];
					unset($tmp_session['n_carrelli']);
					unset($tmp_session['perc_occupazione']);
					$index = count($tmp_session)+1;
					$_SESSION[session_id()]['basket'][$index]['giacenza'] = $giacenza;
					$content = $giacenza;
						
					$this->key_prezzo = $this->getPriceByQty($giacenza, $_REQUEST['quantita']);
					
					$_SESSION[session_id()]['basket'][$index]['basket_qty']['quantita'] = $_REQUEST['quantita']*$giacenza['quantita'];
					$_SESSION[session_id()]['basket'][$index]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'];
					$_SESSION[session_id()]['basket'][$index]['volume'] = $giacenza['volume_singolo'];
					$_SESSION[session_id()]['basket'][$index]['price_it_qty'] = (str_replace(',', '.', $giacenza[$this->key_prezzo]) * $_REQUEST['quantita'] )*$giacenza['quantita'];
					
					$this->key_prezzo = null;
				}
			}
			else
			{
				/***
				 * Nuova Logica per gestire bene le quantita a carrello
				*/
				
				$bsk_tmp = $_SESSION[session_id()]['basket'];
				unset($bsk_tmp['n_carrelli']);
				unset($bsk_tmp['perc_occupazione']);
				/***
				 * Nuova Logica per gestire bene le quantita a carrello
				*/
				
				if(is_array($_REQUEST['quantita']))
				{
					foreach ($_REQUEST['quantita'] as $key => $quantita)
					{
						$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_giacenza'][$key]);						
						$giacenza = $BeanGiacenze->vars();
						$content = $giacenza;
						
						$this->key_prezzo = $this->getPriceByQty($giacenza, $quantita);
						$BeanMagazzino = new giacenze($this->conn, $_REQUEST['id_giacenza'][$key]);
						
						$_SESSION[session_id()]['basket'][$key+1]['basket_qty']['sel_quantita'] = $quantita;
						$_SESSION[session_id()]['basket'][$key+1]['basket_qty']['quantita'] = $quantita*$content['quantita'];
						$_SESSION[session_id()]['basket'][$key+1]['volume'] = $giacenza['volume_singolo'];
						$_SESSION[session_id()]['basket'][$key+1]['price_it_qty'] = ( str_replace(',', '.', $giacenza[$this->key_prezzo]) * $quantita ) * $content['quantita'];
						
						$beanBasket = new ecm_basket();
						$basket = $beanBasket->dbGetOneByCustomerAndDate($this->conn, $_SESSION['LoggedUser']['id']);
						$beanBasketMagazzino = new ecm_basket_magazzino();
						$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
						$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
						if(!empty($basketMagazzino))
						{
							$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
							$beanBasketMagazzino->setQuantita($quantita);
							$beanBasketMagazzino->dbStore($this->conn);
						}
						
						$this->key_prezzo = null;
					}
				}
				else
				{
					if(!empty($_REQUEST['id_giacenza']))
					{
						$BeanGiacenze = new giacenze($this->conn, $_REQUEST['id_giacenza']);
						$giacenza = $BeanGiacenze->vars();
						
						$BeanMagazzino = new giacenze($this->conn, $_REQUEST['id_giacenza']);
						
						$beanBasketMagazzino = new ecm_basket_magazzino();
						$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
						if(!empty($basketMagazzino))
						{
							$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
							$beanBasketMagazzino->setQuantita($_REQUEST['quantita']);
							$beanBasketMagazzino->dbStore($this->conn);
						}
	
						if(empty($basket))
						{
							$beanBasket->setKey_session(session_id());
							$beanBasket->setId_user($_SESSION['LoggedUser']['id']);
							$beanBasket->setOperatore(ECM_OPERATORE);
							$beanBasket->dbStore($this->conn);
							$basket = $beanBasket->vars();
						}
	
						$beanBasketMagazzino = new ecm_basket_magazzino();
						$basketMagazzino = $beanBasketMagazzino->dbGetOneByBasketAndMagazzino($this->conn, $basket['id'], $giacenza['id']);
						$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
						if(empty($basketMagazzino))
						{
							$beanBasketMagazzino = new ecm_basket_magazzino($this->conn, $basketMagazzino['id']);
							$beanBasketMagazzino->setId_basket($basket['id']);
							$beanBasketMagazzino->setId_magazzino($giacenza['id']);
							$beanBasketMagazzino->setQuantita($_REQUEST['quantita']);
						}
						$beanBasketMagazzino->dbStore($this->conn);
	
						$key = count($_SESSION[session_id()]['basket']);
						
						$this->key_prezzo = $this->getPriceByQty($giacenza, $_REQUEST['quantita']);
						
						$_SESSION[session_id()]['basket'][$key]['giacenza'] = $giacenza;
						$_SESSION[session_id()]['basket'][$key]['basket_qty']['sel_quantita'] = $_REQUEST['quantita'];
						$_SESSION[session_id()]['basket'][$key]['basket_qty']['quantita'] = $_REQUEST['quantita']*$giacenza['quantita'];
						$_SESSION[session_id()]['basket'][$key]['volume'] = $giacenza['volume_singolo'];
						$_SESSION[session_id()]['basket'][$key]['price_it_qty'] = ( str_replace(',', '.', $giacenza[$this->key_prezzo]) * $_REQUEST['quantita'] ) * $giacenza['quantita'];
						
						$this->key_prezzo = null;
					}
				}
			}
		}

		$this->tEngine->assign('basket', $_SESSION[session_id()]['basket']);
// 		$this->calcolaVolumiCarrello();
		
		if(!empty($_REQUEST['is_ajax']))
		{
			echo "<script>jQuery('#pencentuale_occupazione_top').html( 'Percentuale Occupazione Carrello ".$_SESSION[session_id()]['basket']['perc_occupazione']." % - N. Carrelli ".$_SESSION[session_id()]['basket']['n_carrelli']."');</script>";
			echo "<script>jQuery('#pencentuale_occupazione_bottom').html( 'Percentuale Occupazione Carrello ".$_SESSION[session_id()]['basket']['perc_occupazione']." % - N. Carrelli ".$_SESSION[session_id()]['basket']['n_carrelli']."');</script>";
			exit();
// 			echo $this->tEngine->fetch('shared/BoxCart');
// 			echo "<script>jQuery('#amount').html( '".count($_SESSION[session_id()]['basket'])."' );</script>";
// 			exit();
		}
		else
		{
			$this->tEngine->assign('tpl_action', 'ShoppingCart');
			$this->tEngine->display('Index');
		}
	}
	
	function createPdfOrder($data, $tEngine)
	{
		include_once(APP_ROOT.'/beans/customer.php');
	
		$BeanCustomer = new customer($this->conn, $_SESSION['LoggedUser']['id_customer']);
	
		include_once(APP_ROOT.'/libs/ext/FPDF/fpdf.php');
		include_once(APP_ROOT.'/libs/TemplateClass/Template_Orders_PDF.php');
	
		$pdf=new PDF_MC_Table();
		$pdf->AddPage('L');
		$pdf->SetFont('Arial','',10);
	
		$pdf->PageBreakTrigger = 200;
	
		$imageHeaderX = 50;
		$imageHeaderY = 1;
		$imageHeaderWidth = 80;
		$imageHeaderHeight = 20;
		$pdf->Image(WWW_ROOT.IMG_DIR.'/web/custom_logo/logo.jpg',$imageHeaderX,$imageHeaderY,$imageHeaderWidth, $imageHeaderHeight);
		//$pdf->Image(WWW_ROOT.IMG_DIR.'/web/custom_logo/greenitaly.jpg',$imageHeaderX+100,$imageHeaderY,$imageHeaderWidth, $imageHeaderHeight);
		$pdf->setY(31);
	
		$pdf->SetFont('Arial','',12);
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
						//$this->tEngine->getTranslation('Data Partenza Merce: ').$_SESSION['user_choice']['date']
				));
		$pdf->SetFont('Arial','',10);
	
		//Table with
		$pdf->SetWidths(array(16,42,125,15,12,12,12,15,11,16,17));
	
		srand(microtime()*1000000);
	
		$pdf->setX(2);
		$pdf->Row(array('Img',$tEngine->getTranslation('Codice'),$tEngine->getTranslation('Descrizione'), $tEngine->getTranslation('Imballi'), 'Q x I', 'Q '.$tEngine->getTranslation('Totale'), $tEngine->getTranslation('Note'),
				$tEngine->getTranslation('Prezzo'), ($BeanCustomer->is_foreign == 0) ? $tEngine->getTranslation('IVA') : '', $tEngine->getTranslation('Prezzo Totale'), $tEngine->getTranslation('Urgente')));
	
		$currency = chr(128);
		unset($data['n_carrelli']); 
		unset($data['perc_occupazione']); 
		foreach($data as $value)
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
			if($pdf->GetY()+20 > $pdf->PageBreakTrigger)
			{
				$_Y = $pdf->GetY();
				$pdf->SetY(-21);
	
				$pdf->SetFont('Arial', '', 8);
				$pdf->Cell(0,10,$this->tEngine->getTranslation('Pagina').' '.$pdf->PageNo().'', 0, 0, 'C');
				$pdf->SetY($_Y);
			}
			if($pdf->GetY()+20 > $pdf->PageBreakTrigger)
			{
				$pdf->AddPage('L');
				$pdf->SetFont('Arial','',12);
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
								//$this->tEngine->getTranslation('Data Partenza Merce: ').$_SESSION['user_choice']['date']
						));
	
				$pdf->SetFont('Arial','',10);
				//Table with
					
				srand(microtime()*1000000);
					
				$pdf->setX(2);
				$pdf->Row(array('Img',$tEngine->getTranslation('Codice'),$tEngine->getTranslation('Descrizione'), $tEngine->getTranslation('Imballi'), 'Q x I', 'Q '.$tEngine->getTranslation('Totale'), $tEngine->getTranslation('Note'),
						$tEngine->getTranslation('Prezzo'), ($BeanCustomer->is_foreign == 0) ? $tEngine->getTranslation('IVA') : '', $tEngine->getTranslation('Prezzo Totale'), $tEngine->getTranslation('Urgente')));
				$currency = chr(128);
	
				$y_image = 20;
				$pdf->SetX(2);
				$pdf->SetFont('Arial','',8);
			}
				
			$imageWidth = 10;
			$imageHeight = 8;
			if(!empty($image))
				$im = $pdf->Image($image,$pdf->GetX()+1,$y_image,$imageWidth,$imageHeight,'','','C',false,300,'',false,false,0,false,false,false,'');
			else
				$im = $pdf->Image(WWW_ROOT."/img/web/image_large.gif",$pdf->GetX()+1,$pdf->GetY()+1,$imageWidth,$imageHeight,'','','C',false,300,'',false,false,0,false,false,false,'');
	
			$tot_prod = $value['giacenza']['prezzo_'.$_SESSION['LoggedUser']['listino']]*$value['giacenza']['quantita']*$value['basket_qty']['sel_quantita'];
				
			if(strlen($value['contenuto']['C3']) > 14)
				$colore = substr($value['contenuto']['C3'],0,14).'..';
			else
				$colore = $value['contenuto']['C3'];
	
			if($BeanCustomer->is_foreign == 0)
				$iva = $value['contenuto']['cod_iva'];
			else
				$iva = '';
				
			$pdf->Row(array(
					$im,
					$value['giacenza']['bar_code'],
					substr($value['giacenza']['descrizione'], 0, 28),
	
					$value['basket_qty']['sel_quantita'],
					$value['giacenza']['quantita'],
					$value['basket_qty']['sel_quantita']*$value['giacenza']['quantita'],
					$ordine[$key]['nota'],
					$currency.' '.$tEngine->getFormatPrice($value['giacenza']['prezzo_'.$_SESSION['LoggedUser']['listino']]),
					$iva,
					$tEngine->getFormatPrice($tot_prod),
					$indispensabile
			),
					5
			);
			$tot += $tot_prod;
		}
		$_Y = $pdf->GetY();
		$pdf->SetY(-21);
	
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(0,10,$this->tEngine->getTranslation('Pagina').' '.$pdf->PageNo().'', 0, 0, 'C');
		$pdf->SetY($_Y);
	
		$pdf->SetWidths(array(10,22));
	
		$pdf->SetX(255);
		$pdf->SetWidths(array(10,15));
		$pdf->Row(array('Tot.', $currency.' '.$tEngine->getFormatPrice($tot)));
		$pdf->Output();
	}	
}
?>