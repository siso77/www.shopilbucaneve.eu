<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/users_type.php");
include_once(APP_ROOT."/beans/newsletter_emails.php");

class NewsletterSubscribe extends DBSmartyMailAction
{
	function NewsletterSubscribe()
	{
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;

		parent::DBSmartyMailAction();

		if(!empty($_REQUEST['remove']) && !empty($_REQUEST['email']))
		{
			$BeanNewsletterEmails = new newsletter_emails();
			$BeanNewsletterEmails->dbDelete($this->conn, $_REQUEST['email']);
			
			$text = 'Gentile '.$_REQUEST['email'].', <br>
					ti sei cancellato correttamente dalla nostra newsletter.';
			$this->SendEmail($_REQUEST['email'], $text, "Conferma cancellazione newsletter di Pro Bike", 'Cancellazione');
			$this->_redirect('');
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if($this->checkEmail($_REQUEST['newsletter_email']))
			{			
				$BeanNewsletterEmails = new newsletter_emails();
				$email_exists = $BeanNewsletterEmails->dbSearch($this->conn, $_REQUEST['newsletter_email']);
				if(!$email_exists)
				{
					$BeanNewsletterEmails->setEmail($_REQUEST['newsletter_email']);
					$BeanNewsletterEmails->dbStore($this->conn);
					$text = 'Gentile '.$_REQUEST['newsletter_email'].', <br>
							ti ringraziamo per esserti registrato alla nostra newsletter, mediante la quale riceverai tutte le offerte del nostro store.';
					$this->SendEmail($_REQUEST['newsletter_email'], $text);
				}
				$this->_redirect('?act=Home&newsletter_confirm=1');
			}
			else
				$this->_redirect('?act=Home');
		}
	}
	
	function SendEmail($email, $text, $object = "Conferma iscrizione newsletter di Pro Bike", $prefixTitle = 'Registrazione')
	{
		$hdrs = array("From" 		=> "info@pro-bike.it", 
					  "To" 			=> $_REQUEST['newsletter_email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> $object,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>'.$prefixTitle.' Newsletter - Pro-Bike.it</title>
				</HEAD>
				<body style="background-color:#000">
				<table width="100%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="http://www.pro-bike.it/wp-content/themes/stationpro/images/logo_bw.png"></td>
					<td align="left" style="color:#fff;font-size:22px;font-family: Arial,Verdana,Sans-serif;color: #999;font-size: 1.3em;font-weight: bold;"><h3>PRO BIKE S.r.l.</h3></td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						'.$text.' 
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
						Ricevi questa Newsletter perche\' la Tua email '.$email.' risulta essere registrata alla newsletter di Pro-Bike. 
						Per non ricevere la Newsletter di Pro-Bike, <a href="'.WWW_ROOT.'?act=NewsletterSubscribe&remove=1&email='.$email.'">cliccare qui</a>.
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
		
		$to = $email.", siso77@gmail.com, info@pro-bike.it, probikeweb@gmail.com";
		$is_send = $this->sendMail($to);

		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
	
	function checkEmail($email) 
	{
	   // Create the syntactical validation regular expression
	   $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
	   // Presume that the email is invalid
	   $valid = 0;
	   // Validate the syntax
	   if (eregi($regexp, $email))
	   {
	      list($username,$domaintld) = split("@",$email);
	      // Validate the domain
	      if (getmxrr($domaintld,$mxrecords))
	         $valid = 1;
	   } else {
	      $valid = 0;
	   }
	   return $valid;
	}	
}
?>