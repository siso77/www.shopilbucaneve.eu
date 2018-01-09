<?php
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT.'/beans/banner.php');

class Banners extends DBSmartyAction
{
	var $className;
	
	function Banners()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);

		if(!empty($_REQUEST['delete']))
		{
			$BeanBanners = new banner();
			$BeanBanners->dbDelete($this->conn, array($_REQUEST['id']), false);
		}

		if(empty($_REQUEST['id_category']))
			$_REQUEST['id_category'] = 0;

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_REQUEST['id']) && empty($_FILES['image_name']['name']))
			{
				$BeanBanners = new banner($this->conn, $_REQUEST['id']);
				$BeanBanners->setLink($_REQUEST['link']);
				$BeanBanners->dbStore($this->conn);
			}
			else
			{
				$key = 1;
				if(!empty($_FILES['image_name']['name']))
					$this->uploadBanner('image_name', $_FILES['image_name'], 'banners',$_REQUEST['id_category']);
			}
		}
		
		$BeanBanners = new banner();
		$data = $BeanBanners->dbGetAllByIdCategory($this->conn, $_REQUEST['id_category']);
		$this->tEngine->assign('data', $data);

		$BeanCategory = new category();
		$Categories = $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC');
		
		if(!empty($_REQUEST['id_category']))
		{
			$this->tEngine->assign('id_category', $_REQUEST['id_category']);
		}

		$this->tEngine->assign('categories', $Categories);
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function uploadBanner($index, $server_file, $customImgRelativePath, $id)
	{
		if($this->IsMobileDevice)
			$localPath = APP_ROOT.'/../'.IMG_DIR.'/wap/'.$customImgRelativePath;
		else
			$localPath = APP_ROOT.'/../'.IMG_DIR.'/web/'.$customImgRelativePath;

		if($this->IsMobileDevice)
			$wwwPath = STORE_WWW_ROOT_PATH.''.IMG_DIR.'/wap/'.$customImgRelativePath;
		else	
			$wwwPath = STORE_WWW_ROOT_PATH.''.IMG_DIR.'/web/'.$customImgRelativePath;

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

		$BeanBanners = new banner();
		$BeanBanners->setImage_name($fName);
		$BeanBanners->setLink($_REQUEST['link']);
		$BeanBanners->setId_category($id);
		$BeanBanners->setLocal_path($localPath);
		$BeanBanners->setWww_path($wwwPath);	
		$BeanBanners->dbStore($this->conn);
	}	
}
?>