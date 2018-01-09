<?php
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class SendOrder extends DBSmartyMailAction
{
	var $className;
	
	function SendOrder()
	{
		parent::DBSmartyMailAction();

		$this->className = get_class($this);

		$BeanEcmOrdini = new ecm_ordini($this->conn, $_REQUEST['id']);
		$BeanEcmOrdiniMagazzino = new ecm_ordini_magazzino();
		$products = $BeanEcmOrdiniMagazzino->dbGetAllByIdOrdine($this->conn, $_REQUEST['id'], new magazzino(), new content());
		
		$BeanUser = new users($this->conn, $BeanEcmOrdini->getId_user());
		$BeanUserAnag = new users_anag($this->conn, $BeanUser->getId_anag());
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$BeanEcmOrdini->setNote($_REQUEST['note']);
			$BeanEcmOrdini->setTraking($_REQUEST['traking']);
			$BeanEcmOrdini->setCorriere($_REQUEST['corriere']);
			$BeanEcmOrdini->setFatturato(1);
			$BeanEcmOrdini->setSpedito(1);
			$BeanEcmOrdini->setPagato(1);
			$BeanEcmOrdini->dbStore($this->conn);
			
			$this->SendEmail($BeanEcmOrdini, $BeanUserAnag, $products);
			$this->tEngine->assign('confirm', true);
		}	
		
		$this->tEngine->assign('user', $BeanUser->vars());
		$this->tEngine->assign('user_anag', $BeanUserAnag->vars());
		$this->tEngine->assign('ordine', $BeanEcmOrdini->vars());
		$this->tEngine->assign('products', $products);
		
		$this->tEngine->assign('action_class_name', $this->className);
		if(!empty($_REQUEST['print']))
		{
			$this->tEngine->assign('print', 1);
			$this->tEngine->display($this->className);
		}
		else
		{
			$this->tEngine->assign('tpl_action', $this->className);
			$this->tEngine->display('Index');
		}
	}
	
	function SendEmail($BeanEcmOrdini, $userAnag, $products)
	{
		$BeanEcmOrdini = $BeanEcmOrdini->vars();
		$userAnag	   = $userAnag->vars();
		
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $userAnag['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Conferma spedizione ordine N. ".$BeanEcmOrdini['id']." - ".PREFIX_META_TITLE,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Conferma spedizione ordine N. '.$BeanEcmOrdini['id'].' - '.PREFIX_META_TITLE.'</title>
				</HEAD>
				<body style="background-color:#fff;font-family: Arial, Tahoma, Verdana, FreeSans, sans-serif;">
				<table width="100%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.ECM_WWW_ROOT.'/theme/styles/style2/images/logo.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
						<!--<h3>'.PREFIX_META_TITLE.'</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Gentile '.$userAnag['name'].' '.$userAnag['surname'].' il tuo ordine # '.$BeanEcmOrdini['id'].' è stato spedito.<br>
						Di seguito ti riportiamo i dettagli del tuo ordine. 
					</td>
				</tr>
				<tr>
					<td width="50%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#fff;font-size:16px;font-size:16px;"><b>Dati Fatturazione/Spedizione</b></td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Nome</td>
							<td>'.$userAnag['name'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Cogome</td>
							<td>'.$userAnag['surname'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Indirizzo</td>
							<td>'.$userAnag['address'].' '.$userAnag['cap'].' - '.$userAnag['city'].' ('.$userAnag['province'].') - '.$userAnag['nation'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Indirizzo Secondario</td>
							<td>'.$userAnag['address_secondary'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Email</td>
							<td>'.$userAnag['email'].'</td>
						</tr>
						</table>
					</td>
					<td width="50%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#fff;font-size:16px;font-size:16px;"><b>Modalit&aacute; di pagamento</b></td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>'.$BeanEcmOrdini['tipo_pagamento'].'</td>
						</tr>
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#fff;font-size:16px;font-size:16px;"><b>Corriere</b></td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>'.$BeanEcmOrdini['corriere'].'</td>
						</tr>
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#fff;font-size:16px;font-size:16px;"><b>Traking Number</b></td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>'.$BeanEcmOrdini['traking'].'</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="50%" valign="top" colspan="2">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="6" style="color:#fff;font-size:16px;font-size:16px;"><b>Prodotti acquistati</b></td>
						</tr>
						';
					include_once(APP_ROOT.'/beans/ApplicationSetup.php');
					$BeanApplicationSetup = new ApplicationSetup();
					$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
			
					foreach ($products as $prod)
					{
						$iva = IVA;
						$total = $total + str_replace(',', '.', $prod['importo']);
						$imponibile += round(str_replace(',', '.', $prod['importo']) / FATTURA_TAX_IVA, 2);
						$prezzo_iva += str_replace(',', '.', $prod['importo']) - round(str_replace(',', '.', $prod['importo']) / FATTURA_TAX_IVA, 2);

						if(!empty($speseSpedizione[0]['name']) && $speseSpedizione[0]['name'] != '0,00')
						{
							$total = $total + str_replace(',', '.', $speseSpedizione[0]['name']);
							$imponibile += round(str_replace(',', '.', $speseSpedizione[0]['name']) / FATTURA_TAX_IVA, 2);
							if(empty($prezzo_iva))
								$prezzo_iva = str_replace(',', '.', $speseSpedizione[0]['name']) - $imponibile;
						}
	
						$html .='
							<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
								<td>Codice Prodotto</td>
								<td>Nome</td>
								<td>Descrizione</td>
								<td>Quantit&aacute;</td>
								<td>IVA</td>
								<td>Importo</td>
							</tr>
							<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
								<td>'.$prod['magazzino']['bar_code'].'</td>
								<td>'.$prod['magazzino']['name_it'].'</td>
								<td>'.$prod['magazzino']['description_it'].'</td>
								<td>'.$prod['quantita'].'</td>
								<td>'.$iva.'</td>
								<td>&euro; '.$prod['importo'].'</td>
							</tr>
							';
				}
				if(!empty($imponibile))
				{
					$html .= '<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">';
					$html .= '<td colspan="6" align="right">';
					$html .= '<table cellpadding="6" width="400">';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">';
					if(!empty($speseSpedizione[0]['name']) && $speseSpedizione[0]['name'] != '0,00')
					{
						$html .= '<td align="right">Spese Spedizione</td>';
						$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($speseSpedizione[0]['name']).'</td>';
						$html .= '</tr>';
					}
					$html .= '<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">';
					$html .= '<td align="right">Imponibile</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($imponibile).'</td>';
					$html .= '</tr>';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">';
					$html .= '<td align="right">IVA '.$iva.'%</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($prezzo_iva).'</td>';
					$html .= '</tr>';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">';
					$html .= '<td align="right">Totale</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($total).'</td>';
					$html .= '</tr>';
					$html .= '</table>';
					$html .= '</td>';
					$html .= '</tr>';
				}
				$html .= '</table>
					</td>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
						'.ADMIN_RAGIONE_SOCIALE.' - '.ADMIN_INDIRIZZO.' - '.ADMIN_TELEFONO.' - '.ADMIN_P_IVA.'
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();
		
		$is_send = $this->sendMail(EMAIL_ADMIN_FROM);
		$is_send = $this->sendMail('siso77@gmail.com');
		$is_send = $this->sendMail($userAnag['email']);

		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}

		return $is_send;
	}
}
?>