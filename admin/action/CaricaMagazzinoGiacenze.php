<?php
//include_once(APP_ROOT.'/beans/brands.php');
//include_once(APP_ROOT.'/beans/category.php');
//include_once(APP_ROOT.'/beans/images.php');
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/magazzino.php");
include_once(APP_ROOT."/beans/color.php");
include_once(APP_ROOT."/beans/sizes.php");
include_once(APP_ROOT."/beans/percent_discount.php");

class CaricaMagazzinoGiacenze extends DBSmartyAction
{
	var $className;
	
	function CaricaMagazzinoGiacenze()
	{
		parent::DBSmartyAction();

		$this->className = get_class($this);
		
		if(!empty($_REQUEST['error']))
			$this->tEngine->assign('error_contenuto_precaricato', 1);
		
		$BeanPercentDiscount = new percent_discount();
		$PercentDiscount = $BeanPercentDiscount->dbGetAll($this->conn, 'data', 'ASC');
		$this->tEngine->assign('percent_discount', $PercentDiscount);
			
		$BeanColor = new color();
		$Colors = $BeanColor->dbGetAllCombo($this->conn, ' color ', ' ASC ');
		$this->tEngine->assign('cmb_dhtmlx_color', $Colors);
		
		$BeanSizes = new sizes();
		$Sizes = $BeanSizes->dbGetAll($this->conn);
		$this->tEngine->assign('cmb_dhtmlx_sizes', $Sizes);
		
		$BeanSizeType = new size_type();
		$SizeTypes = $BeanSizeType->dbGetAll($this->conn, ' type ', ' ASC ');
		$this->tEngine->assign('cmb_dhtmlx_size_type', $SizeTypes);

		if(!empty($_REQUEST['id_magazzino']) && !empty($_REQUEST['delete']))
		{
			$BeanMagazzino = new magazzino();
			$BeanMagazzino->dbDelete($this->conn, array($_REQUEST['id_magazzino']));
		}

		if(!empty($_REQUEST['id_magazzino']) && !empty($_REQUEST['edit']) && !empty($_REQUEST['invia']))
		{
			$postData = $this->convertTplData($_REQUEST);
			$BeanMagazzino = new magazzino($this->conn, $_REQUEST['id_magazzino']);
			$BeanMagazzino->fill($postData);
			$BeanMagazzino->setDdt($postData['ddt']);
			$BeanMagazzino->setFattura_carico($postData['fattura_carico']);
			$BeanMagazzino->setQuantita_caricata($postData['quantita']);
			$BeanMagazzino->setQuantita($postData['quantita']);
			$BeanMagazzino->setOperatore($_SESSION['LoggedUser']['username']);
			$BeanMagazzino->dbStore($this->conn);
			
			
			$this->_redirect('?act=CaricaMagazzinoGiacenze&id_content='.$BeanMagazzino->getId_content().'&id_magazzino='.$_REQUEST['id_magazzino']);
			exit();
		}
		
		// Init Recupero del contenuto dallo step uno
		if(!empty($_REQUEST['id_content']))
			$this->getContenutoPrecaricato($_REQUEST['id_content']);
		// End Recupero del contenuto dallo step uno

		if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_REQUEST['edit']))
		{
			$postData = $this->convertTplData($_REQUEST);
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
					$BeanContenuti = new content($this->conn,$ContentFound[0]['id']);
					$BeanContenuti->fill($postData);
					$BeanContenuti->dbStore($this->conn);
					$idContent = $ContentFound[0]['id'];
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
				$BeanMagazzino = new magazzino($this->conn, $postData);
				$BeanMagazzino->setId_content($idContent);
				$BeanMagazzino->setId_fornitore($idFornitore);
				$BeanMagazzino->setQuantita_caricata($postData['quantita']);
				$BeanMagazzino->setQuantita($postData['quantita']);
				$BeanMagazzino->setOperatore($_SESSION['LoggedUser']['username']);
				$idMagazzino = $BeanMagazzino->dbStore($this->conn);
				// Init Magazzino
				
				Base_CacheCore::getInstance()->clean();
				
				$this->_redirect('?act=CaricaMagazzinoGiacenze&id_content='.$idContent);				
				unset($_SESSION[$this->className]);
			}	
		}

		$this->tEngine->assign('bar_code_searched', $_SESSION[$this->className]['bar_code_searched']);
		$this->tEngine->assign('action_class_name', $this->className);		
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function convertTplData($data)
	{
		if(is_numeric($_REQUEST['bar_code']))
		{
			if($_REQUEST['bar_code'] > 0)
				$ret['bar_code'] = $_REQUEST['bar_code'];
			else
				$ret['bar_code'] = '';
		}
		else
		{
			if(key_exists('bar_code', $data))
				$ret['bar_code'] = $_REQUEST['bar_code'];
			else
				$ret['bar_code'] = '';
		}
		
		if(empty($data['ddt']))
			$ret['error'][] = 'ddt';
		else
			$ret['ddt'] = $data['ddt'].'|'.$data['data_ddt'];
			
		if(empty($data['fattura_carico']))
			$ret['error'][] = 'fattura_carico';
		else
			$ret['fattura_carico'] = $data['fattura_carico'].'|'.$data['data_fattura_carico'];

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
			
		if(empty($data['prezzo_acquisto']))
			$ret['error'][] = 'fattura_carico';
		else
			$ret['prezzo_acquisto'] = $this->FormatEuro($data['prezzo_acquisto']);

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
		if(!empty($_REQUEST['id_magazzino']) && !empty($_REQUEST['edit']))
			$param = ' AND magazzino.id = '.$_REQUEST['id_magazzino'];
		$List = $BeanMagazzino->dbSearch($this->conn, $param." AND magazzino.quantita >= 0 AND magazzino.id_content = ".$id." ORDER BY magazzino.data_inserimento_riga DESC");

		if($List == array())
		{
			$BeanContent = new content();
			$List = $BeanContent->dbSearch($this->conn, " AND content.id = '".$id."'");
		}
//		$i = -1;
//		$lastBarcode = '';
//		foreach ($List as $k => $value)
//		{
//			if($value['bar_code'] != $lastBarcode && !is_null($value['bar_code']))
//			{
//				$i++;
//				$lastBarcode = $value['bar_code'];
//				$ListAssign[$i] = $value;
//			}
//			else if(!is_null($value['bar_code']))
//			{
//				$ListAssign[$i]['quantita'] = $ListAssign[$i]['quantita']+$value['quantita'];
//			}
//		}		
		
		if(count($List)>1)
			$this->tEngine->assign('contenuto_precaricato', $List);
		elseif(!empty($List))
			$this->tEngine->assign('contenuto_precaricato', $List);
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