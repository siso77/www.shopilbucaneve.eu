<?php
include_once(APP_ROOT.'/beans/color.php');
include_once(APP_ROOT.'/beans/images_color.php');

class SetupNewColor extends DBSmartyAction
{
	var $className;
	
	function SetupNewColor()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['id']))
		{
			$BeanImagesColor = new images_color();
			$imagesColor 	 = $BeanImagesColor->dbGetAllByIdColor($this->conn, $_REQUEST['id']);

			if($imagesColor == array())
				unset($_SESSION[$this->className]);
			$this->getElemendByKey($imagesColor, 'images', 'img', $BeanImagesColor);
			
			$BeanColor = new color($this->conn, $_REQUEST['id']);
			$this->tEngine->assign('data', $BeanColor->vars());
		}
		else
		{
			unset($_SESSION[$this->className]);
			$this->getElemendByKey(null, 'images', 'img', null);
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']))
				$BeanColor = new color($this->conn, $_REQUEST['id']);
			else
				$BeanColor = new color();

			$BeanColor->setColor($_REQUEST['color']);
			$id = $BeanColor->dbStore($this->conn);

			if(empty($id))
			 $id = $_REQUEST['id'];

			foreach ($_FILES as $key => $file)			
				$this->uploadFile($key, $file, 'color',$id);
			
			unset($_SESSION[$this->className]);
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
			unlink($BeanApplicationSetup->local_path.'/Medium_'.$BeanApplicationSetup->name);
			unlink($BeanApplicationSetup->local_path.'/Small_'.$BeanApplicationSetup->name);
			
			$BeanApplicationSetup->dbDelete($this->conn, array($_REQUEST['id_img']), false);
			unset($_SESSION[$this->className]);
			$this->_redirect('?act='.$this->className.'&id='.$_REQUEST['id']);
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
		$fName = str_replace(" ", "", $server_file['name']);
		$pathFName = $localPath;

		if(!file_exists($localPath))
			mkdir($localPath, 0777, true);

		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Small_".$fName, 40);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine 1');
		$obj = new SISO_UpladImageResize($index, $localPath.'/', "Medium_".$fName, 100);
		if(!$obj->is_uploaded())
			throw new Exception('Errore di caricamento dell\'immagine 1');
			
		if(!move_uploaded_file($server_file['tmp_name'], $localPath.'/'.$fName))
			throw new Exception();

		$BeanWoraFiles = new images_color();
		$BeanWoraFiles->setName($fName);
		$BeanWoraFiles->setId_color($id);
		$BeanWoraFiles->setLocal_path($localPath);
		$BeanWoraFiles->setWww_path($wwwPath);
		$BeanWoraFiles->dbStore($this->conn);
		
		unset($_SESSION[$this->className]);
	}
}