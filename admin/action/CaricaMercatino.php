<?php
include_once(APP_ROOT.'/beans/mercatino_content.php');
include_once(APP_ROOT.'/beans/images_mercatino.php');
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT.'/beans/users.php');
include_once(APP_ROOT.'/beans/users_anag.php');

class CaricaMercatino extends DBSmartyAction
{
	var $className;
	
	function CaricaMercatino()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		if(empty($_SESSION['LoggedUser']))
		{
			$_SESSION[session_id()]['return'] = 'CaricaMercatino';
			$this->_redirect('?act=Login');
		}
		
		if(!empty($_REQUEST['delete']))
		{
			$BeanMercatino = new mercatino_content($this->conn, $_REQUEST['id']);
			$BeanMercatino->dbDelete($this->conn, array($_REQUEST['id']), false);
			
			$this->_redirect('?act=ListaMercatino');
		}
		
		$BeanUser = new users($this->conn, 67);
		$BeanUserAnag = new users_anag($this->conn, $BeanUser->id_anag);
		$this->tEngine->assign('user_data', $BeanUserAnag->vars());
		
		$BeanCategory = new category();
		$Categories = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		$this->tEngine->assign('cmb_categories', $Categories);
		
		$BeanMercatino = new mercatino_content();
		if(!empty($_REQUEST['id']))
			$images  = $BeanMercatino->dbGetOneCustom($this->conn, $_REQUEST['id'], new images_mercatino());
		$this->getElemendByKey($images, 'images', 'img', $BeanImagesMercatino);

		$this->tEngine->assign('contenuto_precaricato', $images[0]);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$BeanUser = new users($this->conn, 67);
			$BeanUserAnag = new users_anag($this->conn, $BeanUser->id_anag);
			$BeanUserAnag->setPhone($_REQUEST['phone']);
			$BeanUserAnag->setMobile($_REQUEST['mobile']);
			$BeanUserAnag->dbStore($this->conn);
			
			if(!empty($_REQUEST['id']))
				$BeanMercatino = new mercatino_content($this->conn, $_REQUEST['id']);
			else
				$BeanMercatino = new mercatino_content($this->conn, $_REQUEST);

			if(!empty($_REQUEST['price']))
				$BeanMercatino->setPrice($_REQUEST['price']);
			if(!empty($_REQUEST['name']))
				$BeanMercatino->setName($_REQUEST['name']);
			if(!empty($_REQUEST['description']))
				$BeanMercatino->setDescription($_REQUEST['description']);
			if(!empty($_REQUEST['category']))
				$BeanMercatino->setCategory($_REQUEST['category']);
			if(!empty($_REQUEST['brand']))
				$BeanMercatino->setBrand($_REQUEST['brand']);
			$BeanMercatino->setId_user(67);
			$BeanMercatino->setIs_publish(1);
			$BeanMercatino->setData_inserimento_riga(date('Y-m-d'));
			$BeanMercatino->setData_modifica_riga(date('Y-m-d'));
			$BeanMercatino->setOperatore($_SESSION['LoggedUser']['username']);

			$idContent = $BeanMercatino->dbStore($this->conn);

			foreach ($_FILES as $key => $file)
			{
				if(!empty($file['name']))
					$this->uploadFile($key, $file, 'mercatino',$idContent);
			}

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

		$BeanImagesMercatino = new images_mercatino();
		$BeanImagesMercatino->setName($fName);
		$BeanImagesMercatino->setId_mercatino_content($id);
		$BeanImagesMercatino->setLocal_path($localPath);
		$BeanImagesMercatino->setWww_path($wwwPath);		
		$BeanImagesMercatino->dbStore($this->conn);
	}
}
?>