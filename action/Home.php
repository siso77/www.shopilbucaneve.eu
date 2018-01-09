<?php

include_once(APP_ROOT.'/beans/banner.php');

class Home extends DBSmartyMailAction
{
	function Home()
	{
		parent::DBSmartyMailAction();

		if(!empty($_REQUEST['get_product_home']))
		{
			include_once(APP_ROOT."/beans/content.php");
			include_once(APP_ROOT."/beans/giacenze.php");
// 			$BeanContent = new content();
// 			$content = $BeanContent->dbSearchDisponibili($this->conn, " AND giacenze.stato = 'H'");
			$BeanGiacenze = new giacenze();
			$content = $BeanGiacenze->dbSearch($this->conn, " AND giacenze.stato = 'O'");
				
			foreach ($content as $key => $giacenza)
			{
				$image = null;
				if(empty($image))
				{
					$obj_image = $this->tEngine->dbGetImageFromBarCode($giacenza['bar_code']);
					$product_image = $this->tEngine->dbGetImageProductFromBarCode($giacenza['bar_code']);
					if(!empty($obj_image))
					{
						$d = dir(APP_ROOT.'/email_images/');
						while (false !== ($entry = $d->read())) {
							if($entry != '.' && $entry != '..')
								$image = $obj_image[0]['www_path'].$obj_image[0]['name'];
						}
						$d->close();
					}
					elseif(!empty($product_image))
					{
						$image = $product_image;
					}			
					else
						$image = $this->tEngine->dbGetImageProductFromBarCode($giacenza['codice_articolo']);
				}

				$gruppo = $this->tEngine->getGruppoById($giacenza['id_gm']);
				$content[$key]['gruppo'] = $gruppo;
				$content[$key]['image'] = $image;
			}
			echo serialize($content);
			exit();
		}
		$BeanBanners = new banner();
		$img_slider = $BeanBanners->dbGetAllByIdCategory($this->conn, 0);
		$this->tEngine->assign('img_slider', $img_slider);

		$this->tEngine->assign('tpl_action', 'Home');
		$this->tEngine->display('Index');
	}
}
?>