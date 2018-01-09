<?php
class Template_Invoice_PDF extends Template_PDF
{
	public static $includeTextIva;
	
	function Header() {
		parent::Header();
		self::$imageHeader = WWW_ROOT.IMG_DIR.'/web/logo_fattura.png';		
		$this->Image(self::$imageHeader,self::$imageHeaderX,self::$imageHeaderY,self::$imageHeaderWidth);

	}
	
	function WriteHeaderRight($data){
		
		$this->SetFont('Arial','',8);
		
		foreach($data as $value){
			$this->SetX(self::$textHeaderRightX);
			$this->Cell(
						self::$textHeaderRightWidth,
						self::$textHeaderRightHeight,
						$value, 
						self::$textHeaderRightBorder, 
						self::$textHeaderRightLn, 
						self::$textHeaderRightAlign, 
						self::$textHeaderRightFill, 
						self::$textHeaderRightLink);
		    $this->Ln();
		}
		
	}	
	
	function WriteCell($data, $width, $height, $x, $y, $fontStyle = null, $fontWeight = null, $fontSize = null, $is_milti_cell = false){
		parent::WriteCell($data, $width, $height, $x, $y, $fontStyle, $fontWeight, $fontSize, $is_milti_cell);

	}

	function WriteHeaderCustomer($customer, $vendita, $free_text){
		
		$this->ln();
		self::$textHeaderRightAlign	 = 'R';
		self::$textHeaderRightBorder = 1;
		$this->WriteCell('', 208, 36, 1, 0);

		self::$textHeaderRightAlign	 = 'L';		
		self::$textHeaderRightBorder = 0;

		$this->WriteCell('Cognome Nome o Rag Sociale  ', 208, 6, 1, null, null, 'B', 10);
		$this->WriteCell($customer['nome'], 208, 6, 80, null, null, '', 10);
		$this->ln();
		$this->WriteCell('Indirizzo Sede. ', 208, 6, 1, null, null, 'B', 10);
		$this->WriteCell($customer['address_company'], 208, 6, 80, null, null, '', 10);
		$this->ln();
		$this->WriteCell('Indirizzo Fatturazione. ', 208, 6, 1, null, null, 'B', 10);
		$this->WriteCell($customer['address_invoice'], 208, 6, 80, null, null, '', 10);
		$this->ln();
		$this->WriteCell('C.A.P. ', 208, 6, 1, null, null, 'B', 10);
		$this->WriteCell($customer['zip_code'], 208, 6, 80, null, null, '', 10);
		$this->ln();
		$this->WriteCell('Città ', 208, 6, 1, null, null, 'B', 10);
		$this->WriteCell($customer['city'], 208, 6, 80, null, null, '', 10);
		$this->ln();
		$this->WriteCell('C.F. o P.IVA ', 208, 6, 1, null, null, 'B', 10);
		$this->WriteCell($customer['cf_piva'], 208, 6, 80, null, null, '', 10);
		$this->ln();
		
		self::$textHeaderRightBorder = 1;
		$this->WriteCell('', 50, 30, 158, 51, null, 'B', 12);
		
		self::$textHeaderRightBorder = 0;
		$this->WriteCell('Data ', 20, 10, 160, 51, null, 'B', 12);
		$this->WriteCell(date('d/m/Y'),  25, 10, 180, 51, null, '', 12);

//		$this->WriteCell('DDV n° ', 20, 10, 160, 57, null, 'B', 12);
//		$this->WriteCell($customer['ddv'],  25, 5, 180, 60, null, '', 12, true);

		self::$textHeaderRightBorder = 1;
		$this->WriteCell($free_text, 208, 10, 1, 84, null, '', 8);
		
		//$this->WriteCell('ID Ordine ', 20, 10, 160, 73, null, 'B', 12);
		//$this->WriteCell($customer['id_ordine'],  25, 10, 180, 73, null, '', 12);
	}
	
	function WriteBody($fattura, $customer, $data, $vendita, $imponibile, $percentSale, $prezzoScontato, $totale)
	{
		$paymentType = $data[0]['tipo_pagamento']['description'];
		$percentuale_sconto = $data[0]['percentuale_sconto'];

		$free_text = $data['free_text'];
		unset($data[0]['tipo_pagamento']);
		unset($data[0]['percentuale_sconto']);
		unset($data['free_text']);
		
		$dataHeadRight[] = 'PRO BIKE S.r.l.';
		$dataHeadRight[] = 'Via Alfredo Catalani 9 00199 - Roma';
		$dataHeadRight[] = '+39 06 822132';
		$dataHeadRight[] = 'Partita I.V.A. n.05178341003';
		$dataHeadRight[] = 'info@pro-bike.it';
		$dataHeadRight[] = '';
		$dataHeadRight[] = '';
		$this->WriteHeaderRight($dataHeadRight);
		
		/** SUB HEADER **/
		$this->SetFont('Arial','',18);
		self::$textHeaderRightAlign	 = 'L';
		$this->WriteCell('Cliente ', 50, 10, null, 0);
		self::$textHeaderRightAlign	 = 'R';
		$this->WriteCell('Fattura N°       '.$fattura, 195, 10, 10, 0);
		/** SUB HEADER **/
		
		/** INTESTAZIONE CLIENTE **/
		$this->WriteHeaderCustomer($customer, $vendita, $free_text);
		/** INTESTAZIONE CLIENTE **/

		/** ELENCO LIBRI ACQUISTATI **/		
		$this->SetFont('Arial','',14);
		$this->ln();
		$this->BasicTable($data, $vendita);
		/** ELENCO LIBRI ACQUISTATI **/		
		
		/** FOOTER CON TOTALI **/
		self::$textHeaderRightBorder = 1;
		self::$textHeaderRightAlign	 = 'R';
		
		$this->WriteCell('', 150, 18, 1, 0);
		$this->WriteCell('', 58, 18, 151, 0);
		
		
		self::$textHeaderRightBorder = 0;
		self::$textHeaderRightAlign = 'L';
//		$this->WriteCell('A Ricevimento Fattura - Bonifico Bancario -', 150, 5, 2, 252, null, null, 7);
//		$this->WriteCell('In seguito alla fusione delle Società del gruppo Unicredit, si fa presente che', 150, 5, 2, 255, null, null, 7);
//		$this->WriteCell('a decorrere dal 1 novembre 2010 per il pagamento delle fatture sarà valido', 150, 5, 2, 258, null, null, 7);
//		$this->WriteCell('il nuovo IBAN intestato a DARIO MUSCATELLO: IT35A0200805170000400223874', 150, 5, 2, 261, null, null, 7);
		$this->WriteCell($paymentType, 150, 5, 2, 264, null, null, 7);

//		if(!empty($percentuale_sconto) && $percentuale_sconto != '-')
//		{
//			$discount = $percentuale_sconto;
//			$calculateTotalDiscount = Currency::getFormatDiscount($imponibile,$discount);
//		}
//		elseif(!empty($prezzoScontato))
//		{
//			$discount = $percentSale;
//			$calculateTotalDiscount['discount'] = $prezzoScontato;
//			$calculateTotalDiscount['total_discounted'] = $totale;
//		}	
		foreach ($vendita as $val)
			$total += $total+str_replace(',', '.', $val['total']);

//		$this->WriteCell('Imponibile', 58, 2, 151, 256, null, null, 10);
//		$this->WriteCell(Currency::FormatEuro($imponibile), 58, 2, 180, 256, null, null, 10);
//		$this->WriteCell('Sconto '.$discount.'%', 58, 2, 151, 261, null, null, 10);
//		$this->WriteCell(Currency::FormatEuro($calculateTotalDiscount['percentuale_sconto']), 58, 2, 180, 261, null, null, 10);
		$this->WriteCell('Totale Fattura ', 58, 2, 151, 266, null, 'B', 10);
		$this->WriteCell(Currency::FormatEuro($total), 58, 2, 180, 266, null, 'B', 10);
		/** FOOTER CON TOTALI **/		
	}
	
	function BasicTable($data, $vendita)
	{
		$this->SetFont('Arial','B',10);
	    $this->SetY(95);
		$this->SetX(1);
//	     Header
		$header = array('BAR CODE', 'Descrizione', 'Prezzo', 'Q.tà', 'Importo');
//		foreach($header as $k => $col)
//	    {
	        	$this->Cell(31,7,'BAR CODE',1);
 	        	$this->Cell(101,7,'Descrizione',1);
	        	$this->Cell(25,7,'Prezzo',1);
	        	$this->Cell(20,7,'Q.tà',1);
	        	$this->Cell(31,7,'Importo',1);
//	    }
	    $this->Ln();
		$this->SetFont('Arial','',10);
	    // Data
		$_y = 102;
	    foreach($data as $key => $row)
	    {
	    	$k = 0;
		    $this->SetY($_y);
			$this->SetX(1);
//	        foreach($row as $col)
//	        {
//		        if($k == 1)
	            	$this->Cell(31,6,$row['bar_code'],'LR');
//				if($k == 2)
//	            {
	            	if(strlen($row['name_it'])>INCOICE_STRLEN_3_FILED)
	            		$txt = substr($row['name_it'], 0, INCOICE_STRLEN_3_FILED).'...';
	            	else
	            		$txt = $row['name_it'];
	            	$this->Cell(101,6,$txt,'LR');
//	            }
//	        	if($k == 3)
//	        	{
	        		$prezzo = $vendita[$key]['personal_price'];
	            	$this->Cell(25,6,Currency::FormatEuro($prezzo),'LR');
//	        	}
//	        	if($k == 4)
//	        	{
	        		$qty = $vendita[$key]['quantita'];
	            	$this->Cell(20,6,$qty,'LR');
	            	$this->Cell(31,6,Currency::FormatEuro(($prezzo*$qty)),'LR');
//	        	}
				
	            $k++;
//	        }
	        $this->Ln();
	        $_y = $_y + 6;
	    }
	    $index = 25 - count($data);
		for($i=0; $i < $index; $i++)
		{
		    $this->SetY( $_y );
			$this->SetX(1);
	    	$this->Cell(31,6,'','LR');
	    	$this->Cell(101,6,'','LR');
	    	$this->Cell(25,6,'','LR');
	    	$this->Cell(20,6,'','LR');
	    	$this->Cell(31,6,'','LR');
	        $this->Ln();
	        $_y = $_y + 6;
		}
		
	    $this->SetY( $_y );
		$this->SetX(1);
    	$this->Cell(31,6,'','T');
    	$this->Cell(101,6,'','T');
    	$this->Cell(25,6,'','T');
    	$this->Cell(20,6,'','T');
    	$this->Cell(31,6,'','T');
	}	
	
	function Footer(){
		parent::Footer();
		
		self::$textHeaderRightAlign = 'C';

//		if(self::$includeTextIva)
//			$textIVA = 'IVA assolta dall\'editore ex. Art. 74';
//		$text = 'Documento emesso in relazione ad operazioni assoggettate a IVA- esente da bollo art.6 tab. B-D.P.R.-n°642/72.Esente da bolla di accompagnamento';
		$this->WriteCell($text, 210, 2, 0, 271, null, '', 8);
		$this->WriteCell($textIVA, 210, 2, 0, 274, null, '', 8);
	}
	
	function AcceptPageBreak(){
		parent::AcceptPageBreak();
	}
}
?>