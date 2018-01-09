<?php
include_once(APP_ROOT."/beans/users.php");
include_once(APP_ROOT."/beans/users_anag.php");
include_once(APP_ROOT."/beans/users_type.php");

class ForgotPassword extends DBSmartyMailAction
{
	function ForgotPassword()
	{
		$this->params["host"]  = EMAIL_ADMIN_HOST;
		$this->params["auth"]  = true;
		$this->params["username"]  = EMAIL_ADMIN_USERNAME;
		$this->params["password"]  = EMAIL_ADMIN_PASSWORD;

		parent::DBSmartyMailAction();

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if($_REQUEST['send'])
			{
				$BeanUsers = new users();
				$BeanUsers->dbGetOneByUsername($this->conn, $_REQUEST['email']);
				if($_REQUEST['secret_response'] == $BeanUsers->secret_response)
				{
					$this->SendEmail($BeanUsers);
					$this->tEngine->assign('confirm_reset_password', true);
				}
				else
				{
					$this->tEngine->assign('step', 2);
					$this->tEngine->assign('error_secret_response', true);
					$this->tEngine->assign('secret_question', $BeanUsers->secret_question);
					$this->tEngine->assign('email', $_REQUEST['email']);
				}
			}
			else 
			{
				$BeanUsers = new users();
				$BeanUsers->dbGetOneByUsername($this->conn, $_REQUEST['email']);
				if($BeanUsers->username)
				{
					$this->tEngine->assign('step', 2);
					$this->tEngine->assign('secret_question', $BeanUsers->secret_question);
					$this->tEngine->assign('email', $_REQUEST['email']);
				}
			}
		}
		else
			$this->tEngine->assign('step', 1);
		
		$this->tEngine->assign('tpl_action', 'ForgotPassword');
		$this->tEngine->display('Index');
	}
	
	function getNewPassword($user)
	{
		$arr = str_split('ABCDEFGHIJKLMNOP');
		shuffle($arr);
		$arr = array_slice($arr, 0, 6);
		$password = implode('', $arr);
		$user_exists = $user->dbGetAllByPassword($this->conn, md5($password).PASSWORD_SALT);
		if($user_exists)
			return $this->getNewPassword($user);

		return $password;
	}
	
	function SendEmail($user)
	{
		$password = $this->getNewPassword($user);
		$user->setPassword(md5($password).PASSWORD_SALT);
		$user->dbStore($this->conn);
			
		$hdrs = array("From" 		=> EMAIL_ADMIN_FROM,
				"To" 			=> $_REQUEST['email'],
				"Cc" 			=> "",
				"Bcc" 		=> "",
				"Subject" 	=> "La tua password è stata resettata",
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
					<td width="50" style="color:#000;font-size:22px;"><img src="'.WWW_ROOT.'/themes/uploads/2013/03/logo_email.png"></td>
					<td align="left" style="color:#fff;font-size:22px;color: #999;font-weight: bold;">
						<!--<h3>'.PREFIX_META_TITLE.'</h3>-->
					</td>
				</tr>
				<tr>
					<td colspan="2" style="color:#8F8F8F;font-size:16px;">
					Gentile cliente la tua nuova password è: '.$password.'
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

		$to = $user->username.", siso77@gmail.com";
		$is_send = $this->sendMail($to);

		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}

		return $is_send;
	}
}
?>