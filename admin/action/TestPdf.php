<?php

class TestPdf extends Action 
{
	function TestPdf()
	{
		parent::Action();
		
		$fattura		 = '1258';
		$percentSale	 = '15%';
		
		$imponibile 	 = '€ 100,00';		
		$prezzoScontato  = '€ 85,00';
		$totale 		 = '€ 85,00';


		$data 	 		 = array(
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
								array('Bancaria', 'Patria', 'La nuova disciplina del credito fondiario', '40,00', '1', '40,00'),
							);

		$customer['nome'] = 'Silvio Sorrentino';
		$customer['address_company'] = 'Via Roma';
		$customer['address_invoice'] = 'Via Monza';
		$customer['zip_code'] = '00100';
		$customer['city'] = 'Roma';
		$customer['cf_piva'] = 'SRRSLV23R5R424TR09';
		
		$pathPdf = APP_ROOT.'/fatture/'.$_SESSION['book_in_basket_fattura']['id_cliente'].'/'.$fattura.'.pdf';
		
		$this->createPdfInvoice($pathPdf, true, $fattura, $customer, $data, $imponibile, $percentSale, $prezzoScontato, $totale);
		exit();

//		include_once(APP_ROOT.'/libs/ext/FPDF/fpdf.php');
//		include_once(APP_ROOT.'/libs/TemplateClass/Template_PDF.php');
//		include_once(APP_ROOT.'/libs/TemplateClass/Template_Invoice_PDF.php');
//		
//		header('Content-type: application/pdf');
//		Template_Invoice_PDF::$includeTextIva 		 = true;
//		Template_Invoice_PDF::$imageHeaderX 		 = 10;
//		Template_Invoice_PDF::$imageHeaderY 		 = 6;
//		Template_Invoice_PDF::$imageHeaderWidth 	 = 80;
//		Template_Invoice_PDF::$textHeaderRightHeight = 4;
//		Template_Invoice_PDF::$textHeaderRightWidth	 = 50;
//		Template_Invoice_PDF::$textHeaderRightX 	 = 155;
//		Template_Invoice_PDF::$textHeaderRightBorder = 0;
//		Template_Invoice_PDF::$textHeaderRightLn 	 = null;
//		Template_Invoice_PDF::$textHeaderRightAlign	 = 'R';
//		Template_Invoice_PDF::$textHeaderRightFill 	 = null;
//		Template_Invoice_PDF::$textHeaderRightLink 	 = null;
//		$pdf = new Template_Invoice_PDF();
//		$pdf->SetAuthor('Mediaedit');
//		$pdf->AddPage();
//		$pdf->WriteBody($dataHeadRight, $fattura, $customer, $header, $data, $imponibile, $percentSale, $prezzoScontato, $totale);
//		echo $pdf->Output('fattura_'.$fattura.'.pdf', 'S');	
//		exit();	
	}
}
?>