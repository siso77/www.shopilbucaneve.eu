<?php
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/ecm_basket_magazzino.php");
include_once(APP_ROOT."/beans/ecm_basket.php");
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");

class ProcedureCleanBasket extends DBSmartyMailAction
{
	var $className;


	function ProcedureCleanBasket()
	{
		parent::DBSmartyMailAction();

		$this->className = get_class($this);
		
		$BeanEcmBasket = new ecm_basket();
		$data = $BeanEcmBasket->dbSearch($this->conn, ' AND ecm_basket.data_modifica_riga  <= NOW() - INTERVAL 1 DAY ', new ecm_basket_magazzino());

		if($_REQUEST['user'] == 'admin' && $_REQUEST['pwd'] == 'f947414c39d8eb052ab0ed26698d63be' && $_SERVER['HTTP_KRUPY_INTEGRATION'] == '846e4c7990797a7134a405d902012596')
		{
			$text_email .= '<table cellpadding="10">';
			$text_email .= '<tr><td colspan="4" style="border:1px solid #ffffff;">Lista Prodotti ripristinati dall\'e-commerce</td></tr>';
			$i = 1;
			foreach ($data as $value)
			{
				$BeanEcmBasket = new ecm_basket($this->conn, $value['id']);
				$BeanEcmBasket->setIs_active(0);
				$BeanEcmBasket->dbStore($this->conn);
//$text .= print_r($BeanEcmBasket, true);
				
				$BeanUsers = new users($this->conn, $value['id_user']);
				$BeanUsersAnag = new users_anag($this->conn, $BeanUsers->getId_anag());
				$text_email .= '<tr>';	
				$text_email .= '<td colspan="4" style="border:1px solid #ffffff;"><b>Prodotto '.$i.'</b></td>';
				$text_email .= '</tr>';
				$text_email .= '<tr>';	
				$text_email .= '<td colspan="4" style="border:1px solid #ffffff;"><b>Dati Utente</b></td>';
				$text_email .= '</tr>';
				$text_email .= '<tr>';	
				$text_email .= '<td style="border:1px solid #ffffff;">Username:'.$BeanUsers->getUsername().'</td>';
				$text_email .= '<td style="border:1px solid #ffffff;" colspan="2">Nominativo: '.$BeanUsersAnag->getName().' '.$BeanUsersAnag->getSurname().'</td>';
				$text_email .= '<td style="border:1px solid #ffffff;">Email: '.$BeanUsersAnag->getEmail().'</td>';
				$text_email .= '</tr>';
				$text_email .= '<tr>';	
				foreach ($value['basket_magazzino'] as $val)
				{
					$BeanMagazzino = new magazzino($this->conn, $val['id_magazzino']);
					$text_email .= '<tr>';	
					$text_email .= '<td colspan="4" style="border:1px solid #ffffff;"><b>Prodotti</b></td>';
					$text_email .= '</tr>';
					
					$text_email .= '<tr>';
					$text_email .= '<td style="border:1px solid #ffffff;">BarCode:'.$BeanMagazzino->getBar_code().'</td>';
					$text_email .= '<td style="border:1px solid #ffffff;">Quantita in Giacenza: '.$BeanMagazzino->getQuantita().'</td>';
					$text_email .= '<td style="border:1px solid #ffffff;">Quantita Ripristinata: '.$val['quantita'].'</td>';
					
					$BeanMagazzino->setOperatore('Ripristinato da Ecm');
					$BeanMagazzino->setQuantita( $BeanMagazzino->getQuantita() +  $val['quantita']);
					$BeanMagazzino->dbStore($this->conn);
//$text .= print_r($BeanMagazzino, true);					
					$text_email .= '<td style="border:1px solid #ffffff;">Quantita Finale: '.$BeanMagazzino->getQuantita().'</td>';
					$text_email .= '</tr>';
					$text_email .= '<tr>';	
					$text_email .= '<td colspan="4">&nbsp;</td>';
					$text_email .= '</tr>';
				}
				$i++;
			}

			$text_email .= '</table>';
//$text_email .= $text;
			$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
						  "To" 			=> $userAnag['email'],
						  "Cc" 			=> "", 
						  "Bcc" 		=> "", 
						  "Subject" 	=> "Krupy - ".date("d/m/Y H:i:s")."Lista Prodotti ripristinati dall'e-commerce",
						  "Date"		=> date("r")
						  );
			$this->setHeaders($hdrs);
			
			$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<html>
					<HEAD>
						<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
					    <title>Conferma Cancellazione Prodotti nel Carrello</title>
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
						'.$text_email.'
						</td>
					</tr>
					<tr>
						<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
							
						</td>
					</tr>
				</table>
				</body>
				</html>';

			$this->setHtmlText($html);
			$this->mail_factory();

			$is_send = $this->sendMail('siso77@gmail.com');

			if(PEAR::isError($is_send))
			{
				echo "Errore nell'invio della mail!";
				exit;
			}
			/**
			 * GESTIRE LO SVUOTAMENTO DEI CARRELLI LASCIATI IN SOSPESO
			 */
		}
	}
}
?>