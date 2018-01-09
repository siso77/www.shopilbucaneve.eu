<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/ecm_ordini_magazzino.php");
include_once(APP_ROOT."/beans/ecm_ordini.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");
include_once(APP_ROOT."/beans/ecm_basket.php");

class RequestOrder extends DBSmartyMailAction
{
	var $className;
	var $importo_spese;
	var $id_negozio;

	function RequestOrder()
	{
		parent::DBSmartyMailAction();
		
		$this->className = get_class($this);
		
		$mapReferrerContacts[] = 'Piero Di Bartolomei';
		$mapReferrerContacts[] = 'Roberto Grechi';
		$mapReferrerContacts[] = 'Francesco Scruci';
		$mapReferrerContacts[] = 'Mercato di Campoverde (Aprilia)';
		$mapReferrerContacts[] = 'Mercato dei fiori (Trionfale , Roma)';

		$this->tEngine->assign('map_referrer_contacts', $mapReferrerContacts);
//_dump($_REQUEST);
//exit();

		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function SendEmail($BeanEcmOrdini, $userAnag, $products)
	{
		$BeanEcmOrdini = $BeanEcmOrdini->vars();
		$userAnag	   = $userAnag->vars();
		
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $userAnag['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Conferma ordine N. ".$BeanEcmOrdini['id']." - ".PREFIX_META_TITLE,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Conferma ordine N. '.$BeanEcmOrdini['id'].' - '.PREFIX_META_TITLE.'</title>
				</HEAD>
				<body style="background-color:#fff;font-family: Arial, Tahoma, Verdana, FreeSans, sans-serif;">
				<table width="100%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'/theme/styles/style2/images/logo.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
						<!--<h3>'.PREFIX_META_TITLE.'</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						Gentile '.$userAnag['name'].' '.$userAnag['surname'].',<br> il tuo ordine # '.$BeanEcmOrdini['id'].' è andato a buon fine.<br><br>
						Di seguito ti riportiamo i dettagli del tuo ordine. 
					</td>
				</tr>
				<tr>
					<td width="50%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#fff;font-size:16px;"><b>Dati Fatturazione</b></td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Nome</td>
							<td>'.$userAnag['name'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Cogome</td>
							<td>'.$userAnag['surname'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Indirizzo</td>
							<td>'.$userAnag['address'].' '.$userAnag['cap'].' - '.$userAnag['city'].' ('.$userAnag['province'].') - '.$userAnag['nation'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Indirizzo Secondario</td>
							<td>'.$userAnag['address_secondary'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Telefono Fisso</td>
							<td>'.$userAnag['phone'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Telefono Mobile</td>
							<td>'.$userAnag['mobile'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Email</td>
							<td>'.$userAnag['email'].'</td>
						</tr>
						</table>
					</td>
					<td width="50%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#fff;font-size:16px;"><b>Dati Spedizione</b></td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Nome</td>
							<td>'.$userAnag['name_spedizione'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Cogome</td>
							<td>'.$userAnag['surname_spedizione'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Indirizzo</td>
							<td>'.$userAnag['address_spedizione'].' '.$userAnag['cap_spedizione'].' - '.$userAnag['address_secondary_spedizione'].' ('.$userAnag['province_spedizione'].') - '.$userAnag['nation_spedizione'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>Indirizzo Secondario</td>
							<td>'.$userAnag['address_secondary_spedizione'].'</td>
						</tr>
						<tr style="color:#8F8F8F;faddress_secondaryont-size:16px;font-size:16px;">
							<td>Email</td>
							<td>'.$userAnag['email'].'</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="50%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#fff;font-size:16px;"><b>Modalit&aacute; di pagamento</b></td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;">
							<td>';
						if($BeanEcmOrdini['tipo_pagamento'] == 'CC')
							$html .= 'Carta di Credito';
						if($BeanEcmOrdini['tipo_pagamento'] == 'vaglia')
							$html .= 'Vaglia Postale';
						if($BeanEcmOrdini['tipo_pagamento'] == 'bonifico')
							$html .= 'Bonifico';
						if($BeanEcmOrdini['tipo_pagamento'] == 'contrassegno')
							$html .= 'Contrassegno';
							$html .= '</td>
						</tr>
						</table>					
					</td>
				</tr>
				<tr>
					<td width="50%" valign="top" colspan="2">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="8" style="color:#fff;font-size:16px;"><b>Prodotti acquistati</b></td>
						</tr>
						';
				include_once(APP_ROOT.'/beans/ApplicationSetup.php');
				$BeanApplicationSetup = new ApplicationSetup();
				$speseSpedizione = $BeanApplicationSetup->dbGetAllByField(MyDB::connect(), 'spese_spedizione');
		
				foreach ($products as $key => $product)
				{
					foreach ($product['magazzino'] as $prod)
					{
						if($prod['id_category'] == 175)
							$iva = IVA_INTEGRATORI;
						else
							$iva = IVA;
	
						$total = $total + str_replace(',', '.', $product['importo']);
	
						if($prod['id_category'] == 175)
						{
							$imponibile_integratori = round(str_replace(',', '.', $product['importo']) / FATTURA_TAX_IVA_INTEGRATORI, 2);
							$prezzo_iva_integratori = str_replace(',', '.', $product['importo'])-$imponibile_integratori;
							$tot_imponibile_integratori = $tot_imponibile_integratori + $imponibile_integratori;
							$tot_prezzo_iva_integratori = $tot_prezzo_iva_integratori + $prezzo_iva_integratori;
						}
						else
						{
							$imponibile = round(str_replace(',', '.', $product['importo']) / FATTURA_TAX_IVA, 2);
							$prezzo_iva = str_replace(',', '.', $product['importo']) - $imponibile;
							$tot_imponibile = $tot_imponibile + $imponibile;
							$tot_prezzo_iva = $tot_prezzo_iva + $prezzo_iva;
						}
						if($key == (count($products)-1))
						{
							$total = $total + str_replace(',', '.', $speseSpedizione[0]['name']);
							$tot_imponibile += round(str_replace(',', '.', $speseSpedizione[0]['name']) / FATTURA_TAX_IVA, 2);
							if(empty($tot_prezzo_iva))
							{
								$tot_prezzo_iva = $tot_imponibile - str_replace(',', '.', $speseSpedizione[0]['name']);
							}
						}
					
						$html .='
							<tr style="color:#8F8F8F;font-size:16px;">
								<td>Codice Prodotto</td>
								<td>Nome</td>
								<td>Marca</td>
								<td>Colore</td>
								<td>Misura</td>
								<td>Quantit&aacute;</td>
								<td>IVA</td>
								<td>Importo</td>
							</tr>
							<tr style="color:#8F8F8F;font-size:16px;">
								<td>'.$prod['bar_code'].'</td>
								<td>'.$prod['name_it'].'</td>
								<td>'.$prod['name_brand'].'</td>
								<td>'.$prod['color'].'</td>
								<td>'.$prod['size'].'</td>
								<td>'.$product['quantita'].'</td>
								<td>'.$iva.'</td>
								<td>&euro; '.$product['importo'].'</td>
							</tr>
							';
					}
				}
				
				if(!empty($tot_imponibile_integratori))
				{
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td colspan="8" align="right">';
					$html .= '<table cellpadding="6" width="400">';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td align="right">Spese Spedizione</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($speseSpedizione[0]['name']).'</td>';
					$html .= '</tr>';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td align="right">Imponibile</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($tot_imponibile_integratori).'</td>';
					$html .= '</tr>';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td align="right">IVA '.IVA_INTEGRATORI.'%</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($tot_prezzo_iva_integratori).'</td>';
					$html .= '</tr>';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td align="right">Totale</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($total).'</td>';
					$html .= '</tr>';
					$html .= '</table>';
					$html .= '</td>';
					$html .= '</tr>';
				}
				if(!empty($imponibile))
				{
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td colspan="8" align="right">';
					$html .= '<table cellpadding="6" width="400">';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td align="right">Spese Spedizione</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($speseSpedizione[0]['name']).'</td>';
					$html .= '</tr>';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td align="right">Imponibile</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($tot_imponibile).'</td>';
					$html .= '</tr>';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
					$html .= '<td align="right">IVA '.$iva.'%</td>';
					$html .= '<td>&euro; '.$this->tEngine->getFormatPrice($tot_prezzo_iva).'</td>';
					$html .= '</tr>';
					$html .= '<tr style="color:#8F8F8F;font-size:16px;">';
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
					<td colspan="2" style="color:#8F8F8F;font-size:10px;">
						'.ADMIN_RAGIONE_SOCIALE.' - '.ADMIN_INDIRIZZO.' - '.ADMIN_TELEFONO.' - '.ADMIN_P_IVA.'
					</td>
				</tr>
			</table>
			</body>
			</html>';
				
		$this->setHtmlText($html);
		$this->mail_factory();

		$is_send = $this->sendMail($userAnag['email']);
		$is_send = $this->sendMail('siso77@gmail.com');
		$is_send = $this->sendMail(EMAIL_ADMIN_TO);
		
		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
}
?>