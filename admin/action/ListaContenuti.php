<?php
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/casa_editrice.php");
include_once(APP_ROOT."/beans/autori.php");
include_once(APP_ROOT."/beans/content.php");
include_once(APP_ROOT."/beans/distributore.php");
include_once(APP_ROOT."/beans/tipo_presa_carico.php");
include_once(APP_ROOT."/beans/category.php");
include_once(APP_ROOT."/beans/brands.php");
include_once(APP_ROOT."/beans/magazzino.php");

class ListaContenuti extends DBSmartyAction
{
	var $className;

	function setSearchKeys($request)
	{
		unset($request['act']);
		unset($request['search']);
		if(!empty($request))
		{
			if($_SESSION[$this->className]['key_searched']['key_search'] == 'Cerca la parola chiave')
				unset($request['key_search']);

			if(!empty($request['id_category']))
				$_SESSION[$this->className]['key_searched']['id_category'] = $request['id_category'];
			if(!empty($request['id_brand']))
				$_SESSION[$this->className]['key_searched']['id_brand'] = $request['id_brand'];
			if(!empty($request['id_fornitore']))
				$_SESSION[$this->className]['key_searched']['id_fornitore'] = $request['id_fornitore'];
			if(!empty($request['is_in_ecommerce']))
				$_SESSION[$this->className]['key_searched']['is_in_ecommerce'] = $request['is_in_ecommerce'];
			if(!empty($request['is_in_evidence']))
				$_SESSION[$this->className]['key_searched']['is_in_evidence'] = $request['is_in_evidence'];
			if(!empty($request['is_invisible']))
				$_SESSION[$this->className]['key_searched']['is_invisible'] = $request['is_invisible'];
			if(!empty($request['is_in_offer']))
				$_SESSION[$this->className]['key_searched']['is_in_offer'] = $request['is_in_offer'];
			if(!empty($request['data_from']))
				$_SESSION[$this->className]['key_searched']['data_from'] = $request['data_from'];
			if(!empty($request['data_to']))
				$_SESSION[$this->className]['key_searched']['data_to'] = $request['data_to'];
			if(!empty($request['key_search']) && $request['key_search'] != 'Cerca la parola chiave')
				$_SESSION[$this->className]['key_searched']['key_search'] = $request['key_search'];
		}
	}

	function ListaContenuti()
	{
		parent::DBSmartyAction();
		
		$this->className = get_class($this);

		if(!empty($_REQUEST['reset']))
			$_SESSION[$this->className] = null;

		if(!empty($_REQUEST['delete']))
		{
			$BeanContent = new content();
			$BeanContent->dbDelete($this->conn,array($_REQUEST['id']), true);
			Base_CacheCore::getInstance()->clean();
		}

		$this->setSearchKeys($_REQUEST);
		
		$BeanCategory = new category();
		$this->tEngine->assign('cmb_category', $BeanCategory->dbGetCategoryTree($this->conn, 'name', 'ASC'));
		
		if(!empty($_REQUEST['export']))
			$this->exportExcel();

		if(!empty($_REQUEST['export_google_merchant']))
			$this->exportExcelGoogleMerchant();
			
		$BeanCategory = new category();
		$Categories = $BeanCategory->dbGetCategoryTree($this->conn);
		$this->tEngine->assign('categories', $Categories);
		
		$BeanBrand = new brands();
		$Brands = $BeanBrand->dbGetAll($this->conn, 'name', 'ASC');
		$this->tEngine->assign('brands', $Brands);
					
		if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_REQUEST['reset']))
		{
			if(!empty($_REQUEST['search']) && $_REQUEST['key_search'] != 'Cerca la parola chiave')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_REQUEST['key_search'];
				$where = " AND (magazzino.bar_code LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.name_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.description_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.price_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR brands.name LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR category.name LIKE '%".$_REQUEST['key_search']."%')";
				$where = " AND (content.name_it LIKE '%".$_REQUEST['key_search']."%'";
				$where .= " OR content.description_it LIKE '%".$_REQUEST['key_search']."%')";
			}
			else 
			{
				$_SESSION[$this->className]['key_search'] = null;
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['order_by'] = null;
				$_SESSION[$this->className]['order_type'] = null;
			}			
		}
		elseif(!empty($_SESSION[$this->className]['key_searched']))
		{
			if($_SESSION[$this->className]['key_searched']['key_search'] != '')
			{
				$_SESSION[$this->className]['result'] = null;
				$_SESSION[$this->className]['key_search'] = $_SESSION[$this->className]['key_searched']['key_search'];
				$where = " AND (magazzino.bar_code LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR content.name_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR content.description_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR content.price_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR brands.name LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR category.name LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%')";
				$where = " AND (content.name_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%'";
				$where .= " OR content.description_it LIKE '%".$_SESSION[$this->className]['key_searched']['key_search']."%')";
			}
		}
		else
			$where = '';

		if(!empty($_REQUEST['order_by']))
		{
			$_SESSION[$this->className]['order_by'] = $_REQUEST['order_by'];
			$_SESSION[$this->className]['order_type'] = $_REQUEST['order_type'];
			$_SESSION[$this->className]['result'] = null;
		}

		if(!empty($_REQUEST['id_brand']))
			$id_brand = $_REQUEST['id_brand'];
		elseif(!empty($_SESSION[$this->className]['key_searched']['id_brand']))
			$id_brand = $_SESSION[$this->className]['key_searched']['id_brand'];
		
		if(!empty($id_brand))
		{
			$where .= " AND content.id_brand = ".$id_brand."";
			$keysSearchedBrand = array('id_brand'=>$id_brand);
			$this->tEngine->assign('id_brand', $id_brand);
		}
		
		if(!empty($_REQUEST['id_category']))
			$id_category = $_REQUEST['id_category'];
		if(!empty($_SESSION[$this->className]['key_searched']['id_category']))
			$id_category = $_SESSION[$this->className]['key_searched']['id_category'];

		if(!empty($id_category))
		{
			$ListCategory = $BeanCategory->dbGetCategoryByParentId($this->conn, $id_category);
			if(is_array($ListCategory) && $ListCategory != array())
				$where .= " AND category.id IN(".implode(", ", $ListCategory).", ".$id_category.")";
			else
				$where .= " AND category.id = ".$id_category."";

			$keysSearchedCategory = array('id_category'=>$id_category);
			$this->tEngine->assign('id_category', $id_category);
		}
		if(is_array($keysSearchedBrand) && is_array($keysSearchedCategory))
			$keysSearched = array_merge($keysSearchedCategory, $keysSearchedBrand);
		elseif(!empty($keysSearchedBrand))
			$keysSearched = $keysSearchedBrand;
		elseif(!empty($keysSearchedCategory))
			$keysSearched = $keysSearchedCategory;
			
		$this->tEngine->assign("contenuto_precaricato", $keysSearched);

		if(!empty($_REQUEST['id_fornitore']))
			$_SESSION[$this->className]['key_searched']['id_fornitore'] = $_REQUEST['id_fornitore'];
		if(!empty($_SESSION[$this->className]['key_searched']['id_fornitore']))
			$this->tEngine->assign("id_fornitore", $_SESSION[$this->className]['key_searched']['id_fornitore']);

		if($_REQUEST['is_in_ecommerce'] == 'on')
			$_SESSION[$this->className]['key_searched']['is_in_ecommerce'] = $_REQUEST['is_in_ecommerce'];
		if($_SESSION[$this->className]['key_searched']['is_in_ecommerce'] == 'on')
		{
			$where .= " AND content.is_in_ecommerce = 1 ";
			$this->tEngine->assign("is_in_ecommerce", $_SESSION[$this->className]['key_searched']['is_in_ecommerce']);
		}
		if($_REQUEST['is_in_evidence'] == 'on')
			$_SESSION[$this->className]['key_searched']['is_in_evidence'] = $_REQUEST['is_in_evidence'];
		if($_SESSION[$this->className]['key_searched']['is_in_evidence'] == 'on')
		{
			$where .= " AND content.is_in_evidence = 1 ";
			$this->tEngine->assign("is_in_evidence", $_SESSION[$this->className]['key_searched']['is_in_evidence']);
		}
		if($_REQUEST['is_invisible'] == 'on')
			$_SESSION[$this->className]['key_searched']['is_invisible'] = $_REQUEST['is_invisible'];
		if($_SESSION[$this->className]['key_searched']['is_invisible'] == 'on')
		{
			$where .= " AND content.is_invisible = 1 ";
			$this->tEngine->assign("is_invisible", $_SESSION[$this->className]['key_searched']['is_invisible']);
		}
		if($_REQUEST['is_in_offer'] == 'on')
			$_SESSION[$this->className]['key_searched']['is_in_offer'] = $_REQUEST['is_in_offer'];
		if($_SESSION[$this->className]['key_searched']['is_in_offer'] == 'on')
		{
			$where .= " AND content.is_in_offer = 1 ";
			$this->tEngine->assign("is_in_offer", $_SESSION[$this->className]['key_searched']['is_in_offer']);
		}
		
		if(!empty($_SESSION[$this->className]['order_by']))
			$where .= ' ORDER BY '.$_SESSION[$this->className]['order_by'].' '.$_SESSION[$this->className]['order_type'];
		else
			$where .= ' ORDER BY content.data_inserimento_riga DESC';

		$BeanContent = new content();
		$List = $BeanContent->dbSearch($this->conn, $where, new magazzino(), $_SESSION[$this->className]['key_searched']['id_fornitore']);
		$_SESSION[$this->className]['result'] = $List;
		
		$p = new MyPager($_SESSION[$this->className]['result'], $this->rowForPage);
		$links = $p->getLinks();
		$this->tEngine->assign("list"	    , $p->getData());
		$this->tEngine->assign('tot_items'  , $p->pager->_totalItems);
		$this->tEngine->assign('curr_page'  , $p->pager->_currentPage);
		$this->tEngine->assign('last_page'  , $p->pager->_totalPages);
		$this->tEngine->assign('numViewPage', $this->numViewPage);
		$this->tEngine->assign('key_search', $_SESSION[$this->className]['key_search']);
		$this->tEngine->assign('keys_searched', $_SESSION[$this->className]['key_searched']);
		$this->tEngine->assign('action_class_name', $this->className);
		$this->tEngine->assign('tpl_action', $this->className);
		$this->tEngine->display('Index');
	}
	
	function exportExcel()
	{	
		$query = "SELECT
					fornitore.nome as fornitore,
					brands.`name` as brand,
					category.`name` as category,
					category.`id` as id_category,
					magazzino.bar_code,
					content.price_it,
					sizes.size,
					color.color,
					magazzino.quantita_caricata,
					content.name_it,
					content.description_it,
					magazzino.quantita
				FROM
					magazzino
				INNER JOIN content ON magazzino.id_content = content.id
				INNER JOIN fornitore ON magazzino.id_fornitore = fornitore.id
				INNER JOIN brands ON content.id_brand = brands.id
				INNER JOIN category ON content.id_category = category.id
				INNER JOIN sizes ON magazzino.id_size = sizes.id
				INNER JOIN color ON magazzino.id_color = color.id";
		$res = mysql_query($query, $this->conn->connection);
		
		$i=0;
		while ($row = mysql_fetch_assoc($res))
		{
			$data[$i]['fornitore'] = $row['fornitore'];
			$data[$i]['brand'] = $row['brand'];
			if(!empty($row['id_category']))
			{
				$BeanCategory = new category($this->conn, $row['id_category']);
				if($BeanCategory->parent_id != $row['id_category'])
				{
					$data[$i]['categoria_madre'] = $BeanCategory->name;
					$BeanCategory = new category($this->conn, $BeanCategory->parent_id);
					$data[$i]['categoria'] = $BeanCategory->name;
				}
				else
				{
					$data[$i]['categoria_madre'] = $BeanCategory->name;
					$data[$i]['categoria'] = $BeanCategory->name;
				}
			}
			$data[$i]['bar_code'] = $row['bar_code'];
			$data[$i]['prezzo_unitario'] = $row['price_it'];
			$data[$i]['taglia'] = $row['size'];
			$data[$i]['colore'] = $row['color'];
			$data[$i]['quantita'] = $row['quantita_caricata'];
			$data[$i]['nome_prodotto'] = $row['name_it'];
			$data[$i]['descrizione_breve'] = substr($row['description_it'], 0, 20).'...';
			$data[$i]['giacenze'] = $row['quantita'];
			$i++;
		}

		$fieldToDisplay['FORNITORE'] = 'fornitore';
		$fieldToDisplay['BRAND'] = 'brand';
		$fieldToDisplay['CATEGORIA MADRE'] = 'categoria_madre';
		$fieldToDisplay['CATEGORIA'] = 'categoria';
		$fieldToDisplay['BAR CODE'] = 'bar_code';
		$fieldToDisplay['PREZZO UNITARIO'] = 'prezzo_unitario';
		$fieldToDisplay['TAGLIA'] = 'taglia';
		$fieldToDisplay['COLORE'] = 'colore';
		$fieldToDisplay['QUANTITA CARICATA'] = 'quantita';
		$fieldToDisplay['NOME PRODOTTO'] = 'nome_prodotto';
		$fieldToDisplay['DESCRIZIONE BREVE'] = 'descrizione_breve';
//		$fieldToDisplay['LIVELLO MINIMO DI MAGAZZINO'] = 'livello_minimo_magazzino';
		$fieldToDisplay['GIACENZE'] = 'giacenze';
				
		$this->exportExcelData($data, $fieldToDisplay, 'lista_content_'.date('d_m_Y'));
	}

	function exportExcelGoogleMerchant()
	{
		$where .= " AND content.is_in_ecommerce = 1 ";
		$BeanContent = new content();
		$List = $BeanContent->dbSearch($this->conn, $where, new magazzino());
		
		include_once(APP_ROOT.'/beans/category.php');
		$Bean = new category();
		include_once(APP_ROOT.'/beans/images.php');
		$BeanImages = new images();
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<rss version ="2.0" xmlns:g="http://base.google.com/ns/1.0">
		<channel><title>Pro-Bike.it - Online Store</title>
		<link>http://www.pro-bike.it/store</link>
		<description>Vendita bici, abbigliamento bike, ricambi bici e assistenza bici</description>
		';
		foreach($List as $k => $value)
		{
			foreach($value['giacenze'] as $key => $val)
			{
				$Bean->dbGetOne($this->conn, $List[$k]['parent_id']);
				
				$hrefCategory = str_replace(' ','-', str_replace(' /','', ucfirst(strtolower($val['name']))));
				$link = 'http://www.pro-bike.it/store/Detail/'.$hrefCategory.'/'.str_replace(' ','-',str_replace("'","", str_replace('"','', $val['name_it']))).'/'.$val['id_content'].'.html';		
	
				$images = $BeanImages->dbGetAllByIdContent($this->conn, $val['id_content']);
				if(!empty($images[0]['name']) && $images[0]['name'] != 'pro-bike_product_default.jpg')
					$img = $images[0]['www_path'].'/Large_'.$images[0]['name'];				
				
				$xml .= '<item>
				';
				$xml .= '	<g:id>'.$val['id_magazzino'].'</g:id>
				';
				$xml .= '	<title>'.$this->clearData($val['name_it']).'</title>
				';

				if(!empty($val['description_it']))
					$xml .= '	<description><![CDATA['.strtolower($this->clearData($val['description_it'])).']]></description>
					';

				$xml .= '<g:google_product_category>'.str_replace('/', '-', htmlentities($Bean->getName().' > '.$val['name'])).'</g:google_product_category>
				';
				
				$xml .= '	<g:product_type>'.str_replace('/', '-', htmlentities($Bean->getName().' > '.$val['name'])).'</g:product_type>
				';
				$xml .= '	<link>'.$this->clearLink($link).'</link>
				';
				$xml .= '	<g:image_link>'.$img.'</g:image_link>
				';
				$xml .= '	<g:condition>new</g:condition>
				';
				$xml .= '	<g:availability>in stock</g:availability>
				';
				$xml .= '	<g:price>'.str_replace(',', '.', $val['price_it']).' EUR</g:price>
				';
				
				if(!empty($val['price_discounted_it']))
					$xml .= '	<g:sale_price>'.str_replace(',', '.', $val['price_discounted_it']).' EUR</g:sale_price>
					';
				if(!empty($val['name_brand']))
					$xml .= '	<g:brand>'.$val['name_brand'].'</g:brand>
				';

				$xml .= '	<g:color>'.$val['color'].'</g:color>
				';
				$xml .= '	<g:gender>unisex</g:gender>
				';
				$xml .= '	<g:age_group>adult</g:age_group>
				';
				$xml .= '	<g:size>'.$val['size'].'</g:size>
				';
				$xml .= '	<g:item_group_id>'.$val['id_content'].'</g:item_group_id>
				';
				$xml .= '	<g:gtin>'.$val['bar_code'].'</g:gtin>
				';
				$xml .= '</item>
				';
			}
		}
		$xml .= '</channel></rss>';

		echo $xml;
		exit();
	}
	
	function clearLink($data)
	{
		$replacment = 
			array(
				"Á"=>"A'",
				"à"=>"a'",
				"À"=>"A'",
				"é"=>"e'",
				"É"=>"E'",
				"è"=>"e'",
				"È"=>"E'",
				"ò"=>"o'",
				"Ò"=>"O'",
				"ò"=>"o'",
				"Ó"=>"O'",
				"ì"=>"i'",
				"Ì"=>"I'",
				"ù"=>"u'",
				"Ù"=>"u'",
				"’"=>"'",
				"®"=>"",
				"™"=>"",
				"&"=>""
			);
		foreach ($replacment as $search => $replace)
			$data = str_replace($search, $replace, $data);

		return $data;
	}
	
	function clearData($data)
	{
		$replacment = 
			array(
				"•"=>"",
				"á"=>"a'",
				"Á"=>"A'",
				"à"=>"a'",
				"À"=>"A'",
				"é"=>"e'",
				"É"=>"E'",
				"è"=>"e'",
				"È"=>"E'",
				"ò"=>"o'",
				"Ò"=>"O'",
				"ò"=>"o'",
				"Ó"=>"O'",
				"ì"=>"i'",
				"Ì"=>"I'",
				"ù"=>"u'",
				"Ù"=>"u'",
				"’"=>"'",
				"®"=>"",
				"#"=>"a",
				"&"=>"",
				"<"=>"",
				">"=>"",
				"/"=>"-",
				"X"=>"x",
				"×"=>"x",
				"°"=>" gradi ",
				"“"=>'"',
				"”"=>'"',
				"™"=>"",
				"–"=>"-",
				"Ö"=>"O",
				"¾"=>" 3quarti ",
				"½"=>" 1Mezzo",
				"…"=>"...",
				"€"=>" Euro "
			
//				"<"=>"&lt;",
//				">"=>"&gt;",
			);
		foreach ($replacment as $search => $replace)
			$data = str_replace($search, $replace, $data);

		return str_replace(array("\n","\r"), "", substr($data, 0, 200));
	}
}
?>