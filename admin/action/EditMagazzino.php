<?php
include_once(APP_ROOT.'/beans/brands.php');
include_once(APP_ROOT.'/beans/category.php');
include_once(APP_ROOT.'/beans/images.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/color.php");
include_once(APP_ROOT."/beans/sizes.php");
include_once(APP_ROOT."/beans/percent_discount.php");

class EditMagazzino extends DBSmartyAction
{
	var $className;
	
	function EditMagazzino()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);
		
		if(!empty($_REQUEST['error']))
			$this->tEngine->assign('error_contenuto_precaricato', 1);
		
		$BeanPercentDiscount = new percent_discount();
		$PercentDiscount = $BeanPercentDiscount->dbGetAll($this->conn, 'data', 'ASC');
		$this->tEngine->assign('percent_discount', $PercentDiscount);
			
		$BeanCategory = new category();
		$Categories = $BeanCategory->dbGetCategoryTree($this->conn);
		$this->tEngine->assign('categories', $Categories);
		
		$BeanColor = new color();
		$Colors = $BeanColor->dbGetAllCombo($this->conn, ' color ', ' ASC ');
		$this->tEngine->assign('cmb_dhtmlx_color', $Colors);
		
		$BeanSizes = new sizes();
		$Sizes = $BeanSizes->dbGetAll($this->conn);
		$this->tEngine->assign('cmb_dhtmlx_sizes', $Sizes);
		
		$BeanSizeType = new size_type();
		$SizeTypes = $BeanSizeType->dbGetAll($this->conn, ' type ', ' ASC ');
		$this->tEngine->assign('cmb_dhtmlx_size_type', $SizeTypes);
		
		$BeanBrand = new brands();
		$Brands = $BeanBrand->dbGetAll($this->conn, 'name', 'ASC');
		$this->tEngine->assign('brands', $Brands);
		
		
		$BeanImages = new images();
		if(!empty($_REQUEST['id']))
			$images 	 = $BeanImages->dbGetAllByIdContent($this->conn, $_REQUEST['id']);
		$this->getElemendByKey($images, 'images', 'img', $BeanImages);

		// Init Recupero del contenuto dallo step uno
		if(!empty($_REQUEST['id_content']))
			$this->getContenutoPrecaricato($_REQUEST['id_content']);
		elseif(!empty($_REQUEST['id_magazzino'])) 
		{
			$BeanMagazzino = new magazzino($this->conn, $_REQUEST['id_magazzino']);
			$dataMagazzino = $BeanMagazzino->vars();
			$this->getContenutoPrecaricato($dataMagazzino['id_content']);
		}
		if(!empty($_REQUEST['id_magazzino'])) 
			$this->tEngine->assign('id_magazzino', $_REQUEST['id_magazzino']);
		// End Recupero del contenuto dallo step uno
			
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$postData = $this->convertTplData($_REQUEST);
			if(is_array($postData))
			{
//				$BeanContent = new content();
//				$search .= " AND name_it = '".$postData['name_it']."'";
//				$ContentFound = $BeanContent->dbSearch($this->conn, $search);

				// Init Contenuto		
				if(!empty($dataMagazzino['id_content']))
				{
					$BeanContenuti = new content($this->conn,$dataMagazzino['id_content']);
					$BeanContenuti->fill($postData);
					$idContent = $BeanContenuti->dbStore($this->conn);
				}
				else
				{
					$BeanContenuti = new content($this->conn,$_REQUEST['id_content']);
					$BeanContenuti->fill($postData);
					$idContent = $BeanContenuti->dbStore($this->conn);
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
					
				// Init Fornitore			
				if(!empty($postData['id_fornitore']))
				{
					$BeanFornitore = new fornitore($this->conn,$postData['id_fornitore']);
					$FornitoreFound = $BeanFornitore->vars();
				}
				else
				{
					$BeanFornitore = new fornitore();
					$FornitoreFound = $BeanFornitore->dbGetOneByName($this->conn, $postData['fornitore']);
				}
				
				if(!$FornitoreFound)
				{
					$BeanFornitore->setNome($postData['fornitore']);
					$BeanFornitore->setOperatore($_SESSION['LoggedUser']['username']);
					$idFornitore = $BeanFornitore->dbStore($this->conn);
				}
				else 
					$idFornitore = $FornitoreFound['id'];
				// End Fornitore

				// Init Magazzino			
				if(!empty($postData['id_magazzino']))
				{
					$BeanMagazzino = new magazzino($this->conn, $postData['id_magazzino']);
					$BeanMagazzino->fill($postData);
				}
				else
					$BeanMagazzino = new magazzino($this->conn, $postData);
				
				$BeanMagazzino->setId_content($idContent);
				$BeanMagazzino->setId_fornitore($idFornitore);
				$BeanMagazzino->setIs_in_ecommerce($postData['is_in_ecommerce']);
				$BeanMagazzino->setQuantita_caricata($postData['quantita']);
				$BeanMagazzino->setOperatore($_SESSION['LoggedUser']['username']);
				$idMagazzino = $BeanMagazzino->dbStore($this->conn);
				// Init Magazzino
				
				Base_CacheCore::getInstance()->clean();
				
				if(!empty($idMagazzino))
					$this->_redirect('?act=EditMagazzino&id_magazzino='.$idMagazzino.'&confirm_insert=1');
				
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
		if(key_exists('is_in_ecommerce', $data))
				$ret['is_in_ecommerce'] = 1;
		else
			$ret['is_in_ecommerce'] = 0;
			
		if(key_exists('is_in_offer', $data))
				$ret['is_in_offer'] = 1;
		else
			$ret['is_in_offer'] = 0;
			
		if(key_exists('is_in_evidence', $data))
				$ret['is_in_evidence'] = 1;
		else
			$ret['is_in_evidence'] = 0;

		if(empty($data['ddt']))
			$ret['error'][] = 'ddt';
		else
			$ret['ddt'] = $data['ddt'].'|'.$data['data_ddt'];
			
		if(empty($data['id_magazzino']))
			$ret['error'][] = 'id_magazzino';
		else
			$ret['id_magazzino'] = $data['id_magazzino'];
			
			if(empty($data['fattura_carico']))
			$ret['error'][] = 'fattura_carico';
		else
			$ret['fattura_carico'] = $data['fattura_carico'].'|'.$data['data_fattura_carico'];
			
		if(empty($data['percentuale_sconto']))
			$ret['error'][] = 'percentuale_sconto';
		else
			$ret['percentuale_sconto'] = $data['percentuale_sconto'];
			
		if(empty($data['id_category']))
			$ret['error'][] = 'id_category';
		else
			$ret['id_category'] = $data['id_category'];

		if(empty($data['id_color']))
			$ret['error'][] = 'id_color';
		else
			$ret['id_color'] = $data['id_color'];

		if(empty($data['id_color_2']))
			$ret['error'][] = 'id_color_2';
		else
			$ret['id_color_2'] = $data['id_color_2'];
			
		if(empty($data['id_color_3']))
			$ret['error'][] = 'id_color_3';
		else
			$ret['id_color_3'] = $data['id_color_3'];
			
		if(empty($data['id_size']))
			$ret['error'][] = 'id_size';
		else
			$ret['id_size'] = $data['id_size'];
			
		if(empty($data['id_brand']))
			$ret['error'][] = 'id_brand';
		else
			$ret['id_brand'] = $data['id_brand'];
			
		if(empty($data['bar_code']))
			$ret['error'][] = 'bar_code';
		else
			$ret['bar_code'] = $data['bar_code'];
		
		if(empty($data['name_it']))
			$ret['error'][] = 'name_it';
		else
			$ret['name_it'] = $data['name_it'];
		
		if(empty($data['description_it']))
			$ret['error'][] = 'description_it';
		else
			$ret['description_it'] = $data['description_it'];
		
		if(empty($data['name_en']))
			$ret['name_en'] = '';
		else
			$ret['name_en'] = $data['name_en'];
		
		if(empty($data['description_en']))
			$ret['description_en'] = '';
		else
			$ret['description_en'] = $data['description_en'];

		if(empty($data['price_it']))
			$ret['error'][] = 'price_it';
		else
			$ret['price_it'] = $this->validatePrice($data['price_it']);
		
		if(empty($data['price_discounted_it']))
			$ret['error'][] = 'price_discounted_it';
		else
			$ret['price_discounted_it'] = $this->validatePrice($data['price_discounted_it']);
				
		if(empty($data['prezzo_acquisto']))
			$ret['error'][] = 'fattura_carico';
		else
			$ret['prezzo_acquisto'] = $this->validatePrice($data['prezzo_acquisto']);

		

		if(empty($data['quantita']))
			$ret['error'][] = 'quantita';
		else
			$ret['quantita'] = $data['quantita'];
			
		if($data['fornitore_new_value'] == 'true')
		{
			if(empty($data['fornitore']))
				$ret['error'][] = 'id_fornitore';
			else
				$ret['fornitore'] = $data['fornitore'];
		}
		else
		{
			if(empty($data['fornitore']))
				$ret['error'][] = 'id_fornitore';
			else
				$ret['id_fornitore'] = $data['fornitore'];
		}
			
		$ret['operatore'] = $_SESSION['LoggedUser']['username'];

		return $ret;
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
	function getContenutoPrecaricato($id)
	{
		$BeanMagazzino = new magazzino();
		if(!empty($_REQUEST['id_magazzino']))
			$List = $BeanMagazzino->dbSearch($this->conn, " AND magazzino.id = ".$_REQUEST['id_magazzino']." ORDER BY magazzino.id DESC");
		else
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