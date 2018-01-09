<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/users_type.php");
include_once(APP_ROOT."/beans/newsletter_emails.php");
include_once(APP_ROOT."/beans/customer.php");

class CreateAccount extends DBSmartyMailAction
{
	function CreateAccount()
	{
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;

		parent::DBSmartyMailAction();

		if(!empty($_REQUEST['req']))
		{
			$exp = explode('|', base64_decode($_REQUEST['req']));
			$BeanUsers = new users();
			$BeanUsers->dbGetOneByUsername($this->conn, $exp[1]);
			if($BeanUsers->confirmation_code == $_REQUEST['req'])
			{
				$BeanUsers->setConfirmation_code('');
				$BeanUsers->setIs_active(1);
				$BeanUsers->dbStore($this->conn);
				$result = $BeanUsers->login($this->conn, $BeanUsers->username, $BeanUsers->password);
				$_SESSION['LoggedUser'] = $result;
				$BeanUsers->setLast_access();
				$BeanUsers->dbStore($this->conn);
				
				$BeanWoraUsersType = new users_type();
				$userType = $BeanWoraUsersType->dbGetOne($this->conn, $_SESSION['LoggedUser']['id_type']);
				$_SESSION['LoggedUser']['userType'] = $userType['name'];
				
				$BeanUsersAnag = new users_anag();
				$BeanUsersAnag->dbGetOne($this->conn, $BeanUsers->getId_anag());
				$this->tEngine->assign('user_data', array_merge($BeanUsers->vars(), $BeanUsersAnag->vars()));
				$this->tEngine->assign('user_activated', true);
			}
			else
				$this->tEngine->assign('user_not_active', true);
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if($_REQUEST['password'] == $_REQUEST['confirm_password'])
			{
				$BeanUsers = new users();
				$BeanUsers->dbGetOneByUsername($this->conn, $_REQUEST['username']);
				if(empty($BeanUsers->id))
				{
					$_REQUEST['email'] = $_REQUEST['username'];

					$BeanCustomer = new customer();
					$is_customer_exists = $BeanCustomer->dbSearch($this->conn, " AND ragione_sociale LIKE '%".$_REQUEST['name']."%'");

					if($is_customer_exists)
						$idCustomer = $is_customer_exists[0]['customer_code'];
					else
						$idCustomer = null;

					$BeanNewsletterEmails = new newsletter_emails();
					$BeanNewsletterEmails->setEmail($_REQUEST['email']);
					$is_exists = $BeanNewsletterEmails->dbSearch($this->conn, $_REQUEST['email']);
					if(!$is_exists)
						$BeanNewsletterEmails->dbStore($this->conn);					

					$_REQUEST['address'] = $_REQUEST['address'].','.$_REQUEST['civico'];
					$_REQUEST['address_spedizione'] = $_REQUEST['address_spedizione'].','.$_REQUEST['civico_spedizione'];
					
					$BeanUsersAnag = new users_anag($this->conn, $_REQUEST);
					$idUserAnag = $BeanUsersAnag->dbStore($this->conn);
	
					$request_code = base64_encode($id.'|'.$_REQUEST['username'].'|'.date('Y-m-d'));
					$BeanUsers = new users($this->conn, $_REQUEST);
					$BeanUsers->setConfirmation_code($request_code);
					$BeanUsers->setId_type(3);
					$BeanUsers->setId_customer($idCustomer);
					$BeanUsers->setPassword(md5($_POST['password']).PASSWORD_SALT);
					$BeanUsers->setOperatore('ecommerce');
					$BeanUsers->setId_anag($idUserAnag);
					if(!empty($_REQUEST['is_t_c_accepted']))
						$BeanUsers->setIs_t_c_accepted(1);
					else 
						$this->_redirect('?act=CreateAccount&error_tc=1');
					
					if(!empty($_REQUEST['is_newsletter_subscribed']))
						$BeanUsers->setIs_newsletter_subscribed(1);
					else
						$BeanUsers->setIs_newsletter_subscribed(0);
						
					$id = $BeanUsers->dbStore($this->conn);

					if($id > 0)
						$this->tEngine->assign('confirm_account', true);
					
					$this->SendEmail($request_code);
				}
				else 
					$this->tEngine->assign('user_exist', true);
			}
			$this->tEngine->assign('user_data', $_REQUEST);
		}
		$this->tEngine->assign('tpl_action', 'CreateAccount');
		$this->tEngine->display('Index');
	}
	
	function SendEmail($request_code)
	{
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $_REQUEST['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Conferma di registrazione del tuo account ".PREFIX_META_TITLE,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Registrazione utenti - '.PREFIX_META_TITLE.'</title>
				</HEAD>
				<body style="background-color:#fff;">
				<table width="100%" height="100%" border="0" cellspacing="10" style="">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'/img/web/custom_logo/logo.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
						<!--<h3>'.PREFIX_META_TITLE.'</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
					Gentile cliente il tuo account è stato creato con successo.<br> 
					Il tuo account ora dovrà essere attivato dalla nostra redazione.<br>
					Non appena sarà attivato riceverai una mail di notifica di attivazione.
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
					Di seguio ti riportiamo i dati di registrazione.
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
						Nome: '.$_REQUEST['name'].'<br>
						Cognome:
						'.$_REQUEST['surname'].'<br>
						Email:
						'.$_REQUEST['email'].'<br><br>
						Grazie,<br>
						Lo staff di '.ADMIN_RAGIONE_SOCIALE.'<br><br><br>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
						'.ADMIN_RAGIONE_SOCIALE.' - '.ADMIN_INDIRIZZO.' - '.ADMIN_TELEFONO.' '.ADMIN_P_IVA.'
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();

		$to = $_REQUEST['email'].", siso77@gmail.com, ".EMAIL_ADMIN_TO;
		$is_send = $this->sendMail($to);

		/* Email per il richiedente*/
//		$hdrs = array("From" 		=> "info@pro-bike.it", 
//					  "To" 			=> $_REQUEST['email'],
//					  "Cc" 			=> "", 
//					  "Bcc" 		=> "", 
//					  "Subject" 	=> "",
//					  "Date"		=> date("r")
//					  );
//		$this->setHeaders($hdrs);
//		$this->setHtmlText($html);
//		$this->mail_factory();
//		$is_send = $this->sendMail($_REQUEST['email']);
//		/* Email per il richiedente*/
//
		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
}
?>