<?php
include_once(APP_ROOT.'/beans/brands.php');
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT.'/beans/images.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/color.php");
include_once(APP_ROOT."/beans/sizes.php");
include_once(APP_ROOT."/beans/percent_discount.php");

class CaricaMagazzinoImmagini extends DBSmartyAction
{
	var $className;
	
	function CaricaMagazzinoImmagini()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['error']))
			$this->tEngine->assign('error_contenuto_precaricato', 1);
		
		$BeanImages = new images();
		if(!empty($_REQUEST['id_content']))
			$images 	 = $BeanImages->dbGetAllByIdContent($this->conn, $_REQUEST['id_content']);
		$this->getElemendByKey($images, 'images', 'img', $BeanImages);

		// Init Recupero del contenuto dallo step uno
		if(!empty($_REQUEST['id_content']))
			$this->getContenutoPrecaricato($_REQUEST['id_content']);
		// End Recupero del contenuto dallo step uno

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$postData = $this->convertTplData($_REQUEST);
//_dump($_REQUEST);
//_dump($postData);
//exit();
			if(is_array($postData))
			{
				if(!empty($_REQUEST['id_content']))
				{
					$BeanContent = new content();
					$search .= " AND content.id = '".$_REQUEST['id_content']."'";
					$ContentFound = $BeanContent->dbSearch($this->conn, $search);
				}
				
				// Init Contenuto		
				if(empty($ContentFound))
				{
					$BeanContenuti = new content($this->conn,$postData);
					$idContent = $BeanContenuti->dbStore($this->conn);
				}
				else
				{
					$idContent = $ContentFound[0]['id'];
				}
				
				foreach ($_FILES as $key => $file)
				{
					if(!empty($file['name']))
						$this->uploadFile($key, $file, 'product',$idContent);
					else
					{
						$BeanImages = new images();
						if(!$BeanImages->dbGetOneByIdContent($this->conn, $idContent))
						{
							$BeanImages->setName('pro-bike_product_default.jpg');
							$BeanImages->setId_content($idContent);
							$BeanImages->setLocal_path(APP_ROOT.'/img/web/product');
							$BeanImages->setWww_path(WWW_ROOT.'/img/web/product');		
							$BeanImages->dbStore($this->conn);
						}						
					}
				}
				// End Contenuto			

				Base_CacheCore::getInstance()->clean();
				
				$this->_redirect('?act=CaricaMagazzinoImmagini&id_content='.$idContent);				
				unset($_SESSION[$this->className]);
			}	
		}

		$this->tEngine->assign('bar_code_searched', $_SESSION['CaricaMagazzino']['bar_code_searched']);
		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function convertTplData($data)
	{
		$ret['operatore'] = $_SESSION['LoggedUser']['username'];

		return $ret;
	}
	
	function getContenutoPrecaricato($id)
	{
		$BeanMagazzino = new magazzino();
		$List = $BeanMagazzino->dbSearch($this->conn, " AND magazzino.id_content = ".$id." ORDER BY magazzino.id DESC");

		if($List == array())
		{
			$BeanContent = new content();
			$List = $BeanContent->dbSearch($this->conn, " AND content.id = '".$id."'");
		}
		
		$BeanImages = new images();
		$images = $BeanImages->dbGetAllByIdContent($this->conn, $List[0]['id_content']);
		$List[0]['images'] = $images;
		
		if(count($List)>1)
			$this->tEngine->assign('contenuto_precaricato', $List[0]);
		elseif(!empty($List))
			$this->tEngine->assign('contenuto_precaricato', $List[0]);
		else
			$this->tEngine->assign('error_contenuto_precaricato', true);
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

		$BeanImages = new images();
		$BeanImages->setName($fName);
		$BeanImages->setId_content($id);
		$BeanImages->setLocal_path($localPath);
		$BeanImages->setWww_path($wwwPath);		
		$BeanImages->dbStore($this->conn);
	}
}
?>