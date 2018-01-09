<?php
include_once(APP_ROOT.'/beans/banner.php');
include_once(APP_ROOT.'/beans/users.php');
include_once(APP_ROOT.'/beans/users_anag.php');
class Home extends DBSmartyMailAction
{
	function Home()
	{
		parent::DBSmartyMailAction();

		if(!empty($_REQUEST['activate_account']))
		{
			$BeanUsers = new users();
			$BeanUsers->dbGetOneNotActive($this->conn, $_REQUEST['id_user']);
			$BeanUsers->setConfirmation_code(null);
			$BeanUsers->setIs_active(1);
			$BeanUsers->dbStore($this->conn);
			$this->SendEmail($BeanUsers);
		}
		
		$BeanUsers = new users();
		$Users = $BeanUsers->dbGetAllNewUser($this->conn);
		foreach($Users as $key => $val)
		{
			$BeanUserAnag = new users_anag($this->conn, $val['id_anag']);
			$Users[$key]['user_anag'] = $BeanUserAnag->vars();
		}
		$this->tEngine->assign('new_users', $Users);

		$BeanUsers = new users();
		$UsersAccess = $BeanUsers->dbGetAllAccess($this->conn);
		$this->tEngine->assign('users_acces', $UsersAccess);
		
		$BeanUsers = new users();
		$UsersOnline = $BeanUsers->dbGetAllOnLine($this->conn);
		$this->tEngine->assign('users_online', $UsersOnline);
		
		$BeanBanners = new banner();
		$img_slider = $BeanBanners->dbGetAllByIdCategory($this->conn, 0);
		$this->tEngine->assign('img_slider', $img_slider);

		$this->tEngine->assign('tpl_action', 'Home');
		$this->tEngine->display('Index');
	}
	
	function SendEmail($BeanUsers)
	{
		$BeanUsersAnag = new users_anag($this->conn, $BeanUsers->id_anag);
		
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;
		
		
		$hdrs = array("From" 		=> EMAIL_ADMIN_TO, 
					  "To" 			=> $BeanUsersAnag->email,
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Conferma di attivazione del tuo account ".PREFIX_META_TITLE,
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Registrazione utenti - Pro-Bike.it</title>
				</HEAD>
				<body style="background-color:#fff">
				<table width="100%" height="100%" border="0" cellspacing="10">
				<tr>
					<td width="50" style="color:#000;font-size:22px;"><img src="'.ECM_WWW_ROOT.'/css/images/logo-green.png"></td>
					<td align="left" style="color:#fff;font-size:22px;font-family: Arial,Verdana,Sans-serif;color: #999;font-size: 1.3em;font-weight: bold;">
						<!--<h3>Krupy S.r.l.</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
					Gentile '.$BeanUsersAnag->name.', <br>
					il tuo account "'.$BeanUsers->username.'" è stato attivato.<br> 
					Da questo momento potrai utilizzare il tuo account per visionare il nostro <a href="http://briflor.florsistemi.it/">catalogo prodotti</a>.<br>
					
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:16px;">
						Grazie,<br>
						Lo stuff di '.ADMIN_RAGIONE_SOCIALE.'<br><br><br>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;font-size:10px;">
						'.ADMIN_RAGIONE_SOCIALE.' - '.ADMIN_INDIRIZZO.' - '.ADMIN_TELEFONO.'
					</td>
				</tr>
			</table>
			</body>
			</html>';

		$this->setHtmlText($html);
		$this->mail_factory();

		$is_send = $this->sendMail($BeanUsersAnag->username);
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