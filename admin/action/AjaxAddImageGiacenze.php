<?php
include_once(APP_ROOT."/beans/images_giacenze.php");
include_once(APP_ROOT."/beans/magazzino.php");

class AjaxAddImageGiacenze extends DBSmartyAction
{
	var $className;
	
	function AjaxAddImageGiacenze()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		if(!empty($_REQUEST['id_magazzino']))
		{
			$where .= " AND magazzino.id = ".$_REQUEST['id_magazzino']."";
			$BeanMagazzino = new magazzino();
			$List = $BeanMagazzino->dbSearch($this->conn, $where);
			$this->tEngine->assign('data', $List[0]);
			
			$BeanImages = new images_giacenze();
			$image = $BeanImages->dbGetAllByBarCode($this->conn, $List[0]['bar_code']);
			$this->tEngine->assign('image', $image);
		}
		if(!empty($_REQUEST['delete']))
		{
			$BeanImages->dbDelete($this->conn, array($image[0]['id']), false);
			unlink($image[0]['local_path'].'/'.$image[0]['name']);
			unlink($image[0]['local_path'].'/Large_'.$image[0]['name']);
			unlink($image[0]['local_path'].'/Medium_'.$image[0]['name']);
			unlink($image[0]['local_path'].'/Small_'.$image[0]['name']);
			$this->_redirect('?act=CaricaMagazzinoGiacenze&id_content='.$List[0]['id_content']);
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(!empty($_FILES['img']['name']))
				$this->uploadFile('img', $_FILES, 'product',$List[0]['bar_code']);
				
			$this->_redirect('?act=CaricaMagazzinoGiacenze&id_content='.$List[0]['id_content']);
		}
		$this->tEngine->assign('action_class_name', $this->className);
		echo $this->tEngine->fetch('shared/BoxSendImg');
	}
	
	function uploadFile($index, $server_file, $customImgRelativePath, $bar_code)
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
		$ext = substr($server_file[$index]['name'], -3);
		$fName = str_replace(" ", "", $bar_code.'.'.$ext);
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
		if(!move_uploaded_file($server_file[$index]['tmp_name'], $localPath.'/'.$fName))
			throw new Exception();
		$BeanImages = new images_giacenze();
		
		$image = $BeanImages->dbGetAllByBarCode($this->conn, $bar_code);
		if(!empty($image))
			$BeanImages->setId($image[0]['id']);
		$BeanImages->setName($fName);
		$BeanImages->setBar_code($bar_code);
		$BeanImages->setLocal_path($localPath);
		$BeanImages->setWww_path($wwwPath);
		$BeanImages->dbStore($this->conn);

	}	
}
?>