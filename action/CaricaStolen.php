<?php
include_once(APP_ROOT.'/beans/stolen_content.php');
include_once(APP_ROOT.'/beans/images_stolen.php');
include_once(APP_ROOT.'/beans/users.php');
include_once(APP_ROOT.'/beans/users_anag.php');

class CaricaStolen extends DBSmartyMailAction
{
	var $className;
	
	function CaricaStolen()
	{
		parent::DBSmartyMailAction();

		$this->className = get_class($this);
		
		if(empty($_SESSION['LoggedUser']))
		{
			$_SESSION[session_id()]['return'] = 'CaricaStolen';
			$this->_redirect('?act=Login');
		}
		
		$BeanMercatino = new stolen_content();
		if(!empty($_REQUEST['id_content']))
			$images  = $BeanMercatino->dbGetAllByIdContent($this->conn, $_REQUEST['id_content']);
		$this->getElemendByKey($images, 'images', 'img', $BeanImagesMercatino);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$BeanMercatino = new stolen_content($this->conn, $_REQUEST);
			$BeanMercatino->setId_user($_SESSION['LoggedUser']['id']);
			$BeanMercatino->setIs_publish(0);
			$BeanMercatino->setData_inserimento_riga(date('Y-m-d'));
			$BeanMercatino->setData_modifica_riga(date('Y-m-d'));
			$idContent = $BeanMercatino->dbStore($this->conn);

			foreach ($_FILES as $key => $file)
			{
				if(!empty($file['name']))
					$this->uploadFile($key, $file, 'stolen',$idContent);
				else
				{
					$BeanImagesMercatino = new images_stolen();
					if(!$BeanImagesMercatino->dbGetOneByIdContent($this->conn, $idContent))
					{
						$BeanImagesMercatino->setName('pro-bike_product_default.jpg');
						$BeanImagesMercatino->setId_content($idContent);
						$BeanImagesMercatino->setLocal_path(APP_ROOT.'/img/web/product');
						$BeanImagesMercatino->setWww_path(WWW_ROOT.'/img/web/product');		
						$BeanImagesMercatino->dbStore($this->conn);
					}						
				}
			}
			$this->SendEmail($_SESSION['LoggedUser'], $BeanMercatino);
			$this->_redirect('?act=ListaMercatino');
		}
		
		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function validatePrice($data)
	{
		$ret = str_replace('.', ',', $data);
		
		$exp = explode(',', $ret);
		if(strlen($exp[1]) == 0)
			$ret .= ',00';
		elseif(strlen($exp[1]) == 1)
			$ret .= ','.$exp[1].'0';

		return $ret;
	}

	function getElemendByKey($value, $element, $suffixRequestKey, $BeanApplicationSetup)
	{		
		if(empty($_SESSION[$this->className][$element]))
			$_SESSION[$this->className][$element][0]['name'] = '';
		if(!empty($_REQUEST['add_'.$suffixRequestKey]))
			$_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) ]['name'] = '';
		if(!empty($_REQUEST['rem_'.$suffixRequestKey]))
		{
			$elToRemove = $_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) - 1 ];
			if(key_exists('id', $elToRemove))
			{
				$BeanApplicationSetup->dbGetOne($this->conn, $elToRemove['id_img']);
				if($BeanApplicationSetup->name != 'pro-bike_product_default.jpg')
				{
					unlink($BeanApplicationSetup->local_path.'/'.$BeanApplicationSetup->name);
					unlink($BeanApplicationSetup->local_path.'/Medium_'.$BeanApplicationSetup->name);
					unlink($BeanApplicationSetup->local_path.'/Small_'.$BeanApplicationSetup->name);
				}
				$BeanApplicationSetup->dbDelete($this->conn, array($elToRemove['id']), false);
			}
			unset($_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) - 1 ]);
		}
		if(!empty($_REQUEST['delete_'.$suffixRequestKey]))
		{
			$BeanApplicationSetup->dbGetOne($this->conn, $_REQUEST['id_img']);
			unlink($BeanApplicationSetup->local_path.'/'.$BeanApplicationSetup->name);
			unlink($BeanApplicationSetup->local_path.'/Medium_'.$BeanApplicationSetup->name);
			unlink($BeanApplicationSetup->local_path.'/Small_'.$BeanApplicationSetup->name);
			
			$BeanApplicationSetup->dbDelete($this->conn, array($_REQUEST['id_img']), false);
			unset($_SESSION[$this->className]);
			$params = '';
			if(!empty($_REQUEST['id_content']))
				$params = '&id_content='.$_REQUEST['id_content'];
			$this->_redirect('?act='.$this->className.'&id='.$_REQUEST['id'].$params);
		}

		$this->tEngine->assign($element, $_SESSION[$this->className][$element]);		
	}	
	
	function uploadFile($index, $server_file, $customImgRelativePath, $id)
	{

		if($this->IsMobileDevice)
			$localPath = APP_ROOT.'/'.IMG_DIR.'/wap/'.$customImgRelativePath;
		else
			$localPath = APP_ROOT.'/'.IMG_DIR.'/web/'.$customImgRelativePath;

		if($this->IsMobileDevice)
			$wwwPath = WWW_ROOT.IMG_DIR.'/wap/'.$customImgRelativePath;
		else	
			$wwwPath = WWW_ROOT.IMG_DIR.'/web/'.$customImgRelativePath;

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);
			
		//$fName = str_replace(" ", "", date('d_m_Y_H_i_s_').$_FILES['attach']['name']);
		$fName = str_replace(" ", "", date('d_m_Y_H_i_s_').$server_file['name']);
		$pathFName = $localPath;

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);

		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Small_".$fName, 40);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine');
		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Medium_".$fName, 100);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine');
		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Large_".$fName, 500);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine');
			
		if(!move_uploaded_file($server_file['tmp_name'], $localPath.'/'.$fName))
			throw new Exception();

		$BeanImagesMercatino = new images_stolen();
		$BeanImagesMercatino->setName($fName);
		$BeanImagesMercatino->setId_stolen_content($id);
		$BeanImagesMercatino->setLocal_path($localPath);
		$BeanImagesMercatino->setWww_path($wwwPath);		
		$BeanImagesMercatino->dbStore($this->conn);
	}
	
	function SendEmail($user, $BeanMercatino)
	{	
		$BeanUserAnag = new users_anag($this->conn, $user['id_anag']);
		$userAnag = $BeanUserAnag->vars();
		$BeanMercatino = $BeanMercatino->vars();
		
		$hdrs = array("From" 		=> "info@pro-bike.it", 
					  "To" 			=> $userAnag['email'],
					  "Cc" 			=> "", 
					  "Bcc" 		=> "", 
					  "Subject" 	=> "Conferma creazione annuncio stolen # ".$BeanMercatino['id']." - Pro Bike",
					  "Date"		=> date("r")
					  );
		$this->setHeaders($hdrs);

		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<html>
				<HEAD>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
				    <title>Conferma creazione annuncio # '.$BeanMercatino['id'].' - Pro-Bike.it</title>
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
						Gentile '.$userAnag['name'].' '.$userAnag['surname'].' il tuo annuncio è stato inviato alla redazione di Pro-Bike.<br>
						Riceverai una email di conferma non appena verrà pubblicato il tuo annuncio.';

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
		$is_send = $this->sendMail('probikeweb@gmail.com');
		$is_send = $this->sendMail('info@pro-bike.it');
		
		if(PEAR::isError($is_send))
		{
			echo "Errore nell'invio della mail!";
			exit;
		}
		return $is_send;
	}
}
?>