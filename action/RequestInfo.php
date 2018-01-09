<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");

class RequestInfo extends DBSmartyMailAction
{
	var $className;

	function RequestInfo()
	{
		parent::DBSmartyMailAction();
		
		$this->className = get_class($this);

		$_SESSION['request_info']['customer_data']['nome'] = $_REQUEST['nome'];
		$_SESSION['request_info']['customer_data']['cognome'] = $_REQUEST['cognome'];
		$_SESSION['request_info']['customer_data']['telefono'] = $_REQUEST['telefono'];
		$_SESSION['request_info']['customer_data']['email'] = $_REQUEST['email'];
		$_SESSION['request_info']['customer_data']['localita'] = $_REQUEST['localita'];
		$_SESSION['request_info']['customer_data']['richiesta'] = $_REQUEST['richiesta'];
		
		require_once(APP_ROOT.'/libs/ext/google_recaptcha/recaptchalib.php');
		$privatekey = GOOGLE_RECAPCHA_PRIVATE_KEY;
		$resp = recaptcha_check_answer ($privatekey,
										$_SERVER["REMOTE_ADDR"],
										$_POST["recaptcha_challenge_field"],
										$_POST["recaptcha_response_field"]);

		if (!$resp->is_valid) 
			$this->_redirect('?act=ProductInfo&id_giacenza='.$_REQUEST['id_giacenza'].'&error_captcha=1');
		else 
		{
			$BeanContent = new content();
			$where = " AND id = ".$_REQUEST['id_giacenza'];		
			$content = $BeanContent->dbSearchDisponibili($this->conn, $where);

			include_once(APP_ROOT."/beans/newsletter_emails.php");
			if(empty($_SESSION['LoggedUser']))
			{
				$BeanNewsletterEmails = new newsletter_emails();
				$email_exists = $BeanNewsletterEmails->dbSearch($this->conn, $_REQUEST['email']);
				if(!$email_exists)
				{
					$BeanNewsletterEmails->setEmail($_REQUEST['email']);
					$BeanNewsletterEmails->dbStore($this->conn);
				}			
			}
			$this->SendEmail($content);
			unset($_SESSION['request_info']['customer_data']);
			if(!empty($_REQUEST['is_ajax']))
			{
// 				$this->_redirect('?act=ProductInfo&id_giacenza='.$_REQUEST['id_giacenza'].'&confirm=1&is_ajax=1');
// 				exit();
// 				$_REQUEST['confirm'] = 1;
// 				$this->tEngine->display('ProductInfo');
				echo "
				<script type=\"text/javascript\" src=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/js/jquery-1.7.2.min.js\"></script>
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/styles.css\" media=\"all\" />
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/skin.css\" media=\"all\" />
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/light_box.css\" media=\"all\" />
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/widgets.css\" media=\"all\" />
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/elastislide.css\" media=\"all\" />
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/mix.css\" media=\"all\" />
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/skeleton.css\" media=\"all\" />
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/magicat.css\" media=\"all\" />
				<link rel=\"stylesheet\" type=\"text/css\" href=\"".WWW_ROOT."css/skin/frontend/default/megashop-green/css/print.css\" media=\"print\" />
				<script>
				jQuery(document).ready(function() {
					jQuery('#cnf-send-to-friends').show(500);
				});
				</script>
				<div id=\"cnf-send-to-friends\" style=\"position:absolute;display:none;top:10%;left:25%;padding:25px;background-color:#fff;border:1px solid #0e0ef6\">
				<table style=\"margin-top:-12px;margin-bottom:12px;margin-right: 3px;\"><tr><td style=\"padding:18px;font-size:14px;\">".$this->tEngine->getTranslation('Email inviata correttamente!')."</td></tr><tr><td style=\"font-size:14px;\"><div class=\"buttons-cart\" style=\"margin-left:50px;\"><a href=\"javascript:void(0);\" onclick=\"jQuery('#cnf-send-to-friends').hide(500);\" style=\"color:#fff;\">".$this->tEngine->getTranslation('Chiudi')."</a></strong></div></td></tr></table>
				</div>";
				exit();
			}
				
			$this->_redirect('?act=ProductInfo&id='.$_SESSION[session_id()]['product_id'].'&confirm=1'.$param);
		}		
	}
	
	function SendEmail($product)
	{
		
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
					  "To" 			=> $_REQUEST['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Richiesta informazioni da ".str_replace("\\", "", $_REQUEST['nome']).' '.str_replace("\\", "", $_REQUEST['cognome']),
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$bar_code = $product[0]['vbn'];
		if(empty($bar_code))
			$bar_code = $product[0]['bar_code'];
		$obj_image = $this->tEngine->dbGetImageFromBarCode($bar_code);
		$product_image = $this->tEngine->dbGetImageProductFromBarCode($bar_code);
		if(!empty($obj_image))
			$image = $obj_image[0]['www_path'].$obj_image[0]['name'];
		elseif(!empty($product_image))
			$image = $product_image;
		else
			$image = null;
		if(!empty($image))
			$image = '<img src="'.$image.'" width="227">';
		

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Richiesta informazioni da '.str_replace("\\", "", $_REQUEST['nome']).' '.str_replace("\\", "", $_REQUEST['cognome']).'</title>
				</HEAD>
				<body style="background-color:#fff">
				<table width="100%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'/css/images/logo-green.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
						<!--<h3>'.PREFIX_META_TITLE.'</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Richiesta informazioni da '.str_replace("\\", "", $_REQUEST['nome']).' '.str_replace("\\", "", $_REQUEST['cognome']).' per il prodotto :<br>'.$image.'<br>'.$product[0]['bar_code'].' - '.$product[0]['descrizione'].'.<br>
					</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Richiesta: 
					</td>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">
						'.str_replace("\\", "", $_REQUEST['richiesta']).'
					</td>
				</tr>
				<tr>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;" valign="top">
						Dettaglio richiedente	
					</td>
					<td style="color:#8F8F8F;font-size:16px;font-size:16px;">
						<table>
						<tr>
							<td>Nome</td>
							<td>'.str_replace("\\", "", $_REQUEST['nome']).'</td>
						</tr>
						<tr>
							<td>Cognome</td>
							<td>'.str_replace("\\", "", $_REQUEST['cognome']).'</td>
						</tr>
						<tr>
							<td>Telefono</td>
							<td>'.$_REQUEST['telefono'].'</td>
						</tr>
						<tr>
							<td>Email</td>
							<td>'.$_REQUEST['email'].'</td>
						</tr>
						<tr>
							<td>Localita</td>
							<td>'.str_replace("\\", "", $_REQUEST['localita']).'</td>
						</tr>
						<tr>
							<td>Data Richiesta</td>
							<td>'.date('d/m/Y H:i:s').'</td>
						</tr>
						</table>
					</td>
					</tr>
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

		if(!$this->checkEmail($_REQUEST['email']))
		{
			$hdrs = array("From" 		=> EMAIL_ADMIN_FROM, 
						  "To" 			=> $_REQUEST['email'],
						  "Cc" 			=> "", 
						  "Bcc" 		=> "", 
						  "Subject" 	=> "Debug richiesta info ".$_REQUEST['email']." - Addr. ".$_SERVER["REMOTE_ADDR"],
						  "Date"		=> date("r")
						  );
			$this->setHeaders($hdrs);
			$is_send = $this->sendMail('siso77@gmail.com');
			return 1;
		}
//		$is_send = $this->sendMail($_REQUEST['email']);
		$is_send = $this->sendMail('siso77@gmail.com');
		$is_send = $this->sendMail(EMAIL_ADMIN_TO);

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