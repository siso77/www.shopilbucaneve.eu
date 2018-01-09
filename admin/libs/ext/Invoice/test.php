<?php

//http://salvatorecapolupo.it/2011/03/fattura-pdf-in-php.html
include 'ezPdf/class.ezpdf.php';
$pdf =& new Cezpdf('a4');
$mainFont='pdf/fonts/Times-Roman.afm';

$codeFont = 'pdf/fonts/Courier.afm';
$pdf->selectFont($mainFont);

//dati ingresso fattura
$datafattura='';
$nomecliente='';
$cognomecliente='';
$indirizzocliente='';
$capcliente='';
$cittacliente='';
$cfcliente='';
$creditiacquistati = '';

//intestazione
$pdf->ezText("Ing. Salvatore Capolupo\n",16,array('justification'=>'left'));
$pdf->ezText("Via",16,array('justification'=>'left'));
$pdf->ezText("P. IVA XXXXXXXXX \n",16,array('justification'=>'left'));

//2 colonne, 1 per me l'altra del cliente
$data = array(
array('name'=>$nomecliente.' '.$cognomecliente,'type'=>""/**'Fattura n. '.$numerofattura**/),
array('name'=>$indirizzocliente.' '.$capcliente,'type'=>'Data: '.$datafattura),
array('name'=>$cittacliente,'type'=>'CF CLIENTE: '.$cfcliente),
);

$pdf->ezTable($data,array('type'=>'','name'=>''),'' ,array('showHeadings'=>0 ,'shaded'=>0 ,'width'=>400 ,'cols'=>array('name'=>array('link'=>'url')) ));

$totale= $creditiacquistati; //semplicemente
$quattropercento = ($creditiacquistati*4/100);
$tot1 = $totale + $quattropercento;
$ritenuta = ($totale*20/100);

$data2 = array(
array('a'=>'','b'=>'Descrizione','d'=>'Compenso','e'=>'Totale'),
array('a'=>'','b'=>'Promozione web '.$creditiacquistati.' crediti prepagati','d'=>"".$creditiacquistati." EUR",'e'=>"".$creditiacquistati." EUR"),

array('a'=>'','b'=>'','c'=>'','d'=>'Imponibile','e'=>"".$totale." EUR"),
array('a'=>'','b'=>'','c'=>'','d'=>'+ 4%','e'=>"".$quattropercento." EUR"),
array('a'=>'','b'=>'','c'=>'','d'=>'Totale','e'=>"".$tot1." EUR"),
array('a'=>'','b'=>'','c'=>'','d'=>'Ritenuta (20%)','e'=>"".$ritenuta." EUR"),
array('a'=>'','b'=>'——','c'=>'','d'=>'——','e'=>"——"),
array('a'=>'','b'=>'','c'=>'','d'=>'Totale','e'=>"".$totale-$ritenuta." EUR")
);

$pdf->ezTable($data2,array('a'=>'','b'=>'', 'c'=>'','d'=>'', 'e'=>'' ),'Riepilogo ordine' ,array('showHeadings'=>0,'shaded'=>0 , 'width'=>500));

//questo va indicato per i cosiddetti "minimi"
$pdf->ezText();

$pdf->ezSetDy(-100);

$pdf->openHere('Fit');
$pdf->ezStream();
?>