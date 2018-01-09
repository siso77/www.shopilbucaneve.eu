<?php
include_once(APP_ROOT.'/beans/brands.php');
include_once(APP_ROOT.'/beans/images_brands.php');

class NuovoBrand extends DBSmartyAction
{
	var $className;
	
	function NuovoBrand()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['id']))
		{
			$BeanImagesBrand = new images_brands();
			$imagesBrand 	 = $BeanImagesBrand->dbGetAllByIdBrand($this->conn, $_REQUEST['id']);
			$this->getElemendByKey($imagesBrand, 'images', 'img', $BeanImagesBrand);
			
			$BeanBrand = new brands($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('data', $BeanBrand->vars());
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			foreach ($_FILES as $key => $file)			
				$this->uploadFile($key, $file, 'brands',$_REQUEST['id']);

			if(!empty($_REQUEST['id']))
				$BeanBrand = new brands($this->conn, $_REQUEST['id']);
			else
				$BeanBrand = new brands();

			$BeanBrand->fill($_REQUEST);
			$id = $BeanBrand->dbStore($this->conn);

			$params = '&id='.$id.'&edit=1';
			if(!empty($_REQUEST['id']))
				$params = '&id='.$_REQUEST['id'].'&edit=1';

			$this->_redirect('?act='.$this->className.$params);
		}
		
		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function getElemendByKey($value, $element, $suffixRequestKey, $BeanApplicationSetup)
	{		
		if(empty($_SESSION[$this->className][$element]))
			$_SESSION[$this->className][$element] = $value;
		if(!empty($_REQUEST['add_'.$suffixRequestKey]))
			$_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) ]['name'] = '';
		if(!empty($_REQUEST['rem_'.$suffixRequestKey]))
		{
			$elToRemove = $_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) - 1 ];
			if(key_exists('id', $elToRemove))
			{
				$BeanApplicationSetup->dbGetOne($this->conn, $elToRemove['id_img']);
				unlink($BeanApplicationSetup->local_path.'/'.$BeanApplicationSetup->name);
				unlink($BeanApplicationSetup->local_path.'/Large_'.$BeanApplicationSetup->name);
				unlink($BeanApplicationSetup->local_path.'/Medium_'.$BeanApplicationSetup->name);
				unlink($BeanApplicationSetup->local_path.'/Small_'.$BeanApplicationSetup->name);
				
				$BeanApplicationSetup->dbDelete($this->conn, array($elToRemove['id']), false);
			}
			unset($_SESSION[$this->className][$element][ count($_SESSION[$this->className][$element]) - 1 ]);
		}
		if(!empty($_REQUEST['delete_'.$suffixRequestKey]))
		{
			$BeanApplicationSetup->dbGetOne($this->conn, $_REQUEST['id_img']);
			unlink($BeanApplicationSetup->local_path.'/'.$BeanApplicationSetup->name);
			unlink($BeanApplicationSetup->local_path.'/Large_'.$BeanApplicationSetup->name);
			unlink($BeanApplicationSetup->local_path.'/Medium_'.$BeanApplicationSetup->name);
			unlink($BeanApplicationSetup->local_path.'/Small_'.$BeanApplicationSetup->name);
			
			$BeanApplicationSetup->dbDelete($this->conn, array($_REQUEST['id_img']), false);
			unset($_SESSION[$this->className]);
			$this->_redirect('?act='.$this->className.'&id='.$_REQUEST['id']);
		}

		$this->tEngine->assign($element, $_SESSION[$this->className][$element]);		
	}	
	
	function uploadFile($index, $server_file, $customImgRelativePath, $idProduct)
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
		$fName = str_replace(" ", "", $server_file['name']);
		$pathFName = $localPath;

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);

		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Small_".$fName, 80);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine 1');
		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Medium_".$fName, 250);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine 1');
		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Large_".$fName, 400);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine 1');
			
		if(!move_uploaded_file($server_file['tmp_name'], $localPath.'/'.$fName))
			throw new Exception();

		$BeanWoraFiles = new images_brands();
		$BeanWoraFiles->setName($fName);
		$BeanWoraFiles->setId_brand($idProduct);
		$BeanWoraFiles->setLocal_path($localPath);
		$BeanWoraFiles->setWww_path($wwwPath);
		$BeanWoraFiles->dbStore($this->conn);
		
		unset($_SESSION[$this->className]);
	}	
}