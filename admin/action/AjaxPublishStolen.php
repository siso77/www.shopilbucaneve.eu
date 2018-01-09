<?php
include_once(APP_ROOT.'/beans/stolen_content.php');
include_once(APP_ROOT.'/beans/images_stolen.php');
include_once(APP_ROOT.'/beans/users.php');
include_once(APP_ROOT.'/beans/users_anag.php');

class AjaxPublishStolen extends DBSmartyMailAction
{
	var $className;
	
	function AjaxPublishStolen()
	{
		parent::DBSmartyMailAction();

		$this->className = get_class($this);
		
		$BeanMercatino = new stolen_content($this->conn, $_REQUEST['id']);
		if($_REQUEST['action'] == 'reject')
		{
			if($BeanMercatino->getIs_active() == 1)
			{
				$BeanMercatino->setIs_active(0);
//				$this->SendEmail($_SESSION['LoggedUser'], $BeanMercatino, true);
			}
			else
				$BeanMercatino->setIs_active(1);
		}
		else 
		{
			if($BeanMercatino->getIs_publish() == 1)
				$BeanMercatino->setIs_publish(0);
			else
			{
				$BeanMercatino->setIs_publish(1);
				$this->SendEmail($_SESSION['LoggedUser'], $BeanMercatino);
			}
		}
		$BeanMercatino->dbStore($this->conn);
		
		if($_REQUEST['action'] == 'reject')
			echo $BeanMercatino->getIs_active();
	}
	
	function SendEmail($user, $BeanMercatino, $rejected = false)
	{	
		$BeanUserAnag = new users_anag($this->conn, $user['id_anag']);
		$userAnag = $BeanUserAnag->vars();
		$BeanMercatino = $BeanMercatino->vars();
		
		$hdrs = array("From" 		=> "info@pro-bike.it", 
					  "To" 			=> $userAnag['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Conferma pubblicazione annuncio # ".$BeanMercatino['id']." - Pro Bike",
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Conferma pubblicazione annuncio # '.$BeanMercatino['id'].' - Pro-Bike.it</title>
				</HEAD>
				<body style="background-color:#000">
				<table width="100%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="http://www.pro-bike.it/wp-content/themes/stationpro/images/logo_bw.png"></td>
					<td align="left" style="color:#fff;font-size:22px;font-family: Arial,Verdana,Sans-serif;color: #999;font-size: 1.3em;font-weight: bold;"><h3>PRO BIKE S.r.l.</h3></td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">';

				$html.= '
						Gentile '.$userAnag['name'].' '.$userAnag['surname'].' il tuo annuncio è stato pubblicato alla redazione di Pro-Bike.<br>
						Per vedere il tuo annuncio entra nella sezione del <a target="_blank" href="http://www.pro-bike.it/store/Stolen-Bike/annunci%20bici%20rubate/1.html">Stolen Bike</a>.';

				$html.= '
					</td>
				</tr>
				<tr>
					<td width="100%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#000000;font-size:16px;font-size:16px;"><b>Dati Utente</b></td>
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
							<td>Telefono Fisso</td>
							<td>'.$userAnag['phone'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Telefono Mobile</td>
							<td>'.$userAnag['mobile'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Email</td>
							<td>'.$userAnag['email'].'</td>
						</tr>
						</table>
					</td>
				</tr>

				<tr>
					<td width="100%" valign="top">
						<table width="100%" cellpadding="6" style="border:1px solid #8F8F8F;">
						<tr style="background-color:#8F8F8F;">
							<td colspan="2" style="color:#000000;font-size:16px;font-size:16px;"><b>Dati Annuncio</b></td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Nome Prodotto</td>
							<td>'.$BeanMercatino['name'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Descrizione Prodotto</td>
							<td>'.$BeanMercatino['description'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Categoria</td>
							<td>'.$BeanMercatino['category'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Marca</td>
							<td>'.$BeanMercatino['brand'].'</td>
						</tr>
						<tr style="color:#8F8F8F;font-size:16px;font-size:16px;">
							<td>Prezzo</td>
							<td>'.$BeanMercatino['price'].'</td>
						</tr>
						</table>
					</td>
				</tr>
				
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
						PRO BIKE S.r.l. - Via Alfredo Catalani 9 - 00199 - Roma - Tel. +39 06 82 21 32 - P.I. 05178341003
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();
		
		$is_send = $this->sendMail($userAnag['email']);
		$is_send = $this->sendMail('siso77@gmail.com');
//		$is_send = $this->sendMail('probikeweb@gmail.com');
//		$is_send = $this->sendMail('info@pro-bike.it');
		
		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
}
?>