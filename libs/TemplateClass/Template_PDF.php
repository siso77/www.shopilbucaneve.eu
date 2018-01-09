<?php
class Template_PDF extends FPDF
{
	public $defaultFontStyle 	= 'Arial';
	public $defaultFontWeight 	= '';
	public $defaultFontSize = 14;
	
	public static $imageHeader;
	public static $imageHeaderX;
	public static $imageHeaderY;
	public static $imageHeaderWidth;
	
	public static $textHeaderRightWidth;
	public static $textHeaderRightHeight;
	public static $textHeaderRightX;
	public static $textHeaderRightBorder;
	public static $textHeaderRightLn;
	public static $textHeaderRightAlign;
	public static $textHeaderRightFill;
	public static $textHeaderRightLink;
	
	function Header(){}
	
	function Footer()
	{
	    // Page footer
	    $this->SetY(-15);
	    $this->SetFont('Arial','I',8);
	    $this->SetTextColor(128);
	    $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
	}
	
	function WriteCell($data, $width, $height, $x, $y, $fontStyle = null, $fontWeight = null, $fontSize = null, $is_milti_cell = false){

		if(empty($fontStyle))
			$fontStyle = $this->defaultFontStyle;
		if(empty($fontSize))
			$fontSize = $this->defaultFontSize;
		if(empty($fontWeight))
			$fontWeight = $this->defaultFontWeight;

		$this->SetFont($fontStyle,$fontWeight,$fontSize);

		if(!empty($y))
			$this->SetY($y);
		$this->SetX($x);
		if($is_milti_cell)
		{
			$this->MultiCell(
							$width,
							$height,
							$data, 
							self::$textHeaderRightBorder, 
							self::$textHeaderRightAlign, 
							self::$textHeaderRightFill);		
		}
		else
		{
			$this->Cell(
						$width,
						$height,
						$data, 
						self::$textHeaderRightBorder, 
						self::$textHeaderRightLn, 
						self::$textHeaderRightAlign, 
						self::$textHeaderRightFill, 
						self::$textHeaderRightLink);
		}
	}	
	
	function SetTitle($title)
	{
		$this->title = $title;
	}
	
	function AcceptPageBreak()
	{
	    // Method accepting or not automatic page break
	    if($this->col<2)
	    {
	        // Go to next column
	        $this->SetCol($this->col+1);
	        // Set ordinate to top
	        $this->SetY($this->y0);
	        // Keep on page
	        return false;
	    }
	    else
	    {
	        // Go back to first column
	        $this->SetCol(0);
	        // Page break
	        return true;
	    }
	}
	
	// Better table
	function ImprovedTable($header, $data)
	{
	    // Column widths
	    $w = array(40, 35, 40, 45);
	    // Header
	    for($i=0;$i<count($header);$i++)
	        $this->Cell($w[$i],7,$header[$i],1,0,'C');
	    $this->Ln();
	    // Data
	    foreach($data as $row)
	    {
	        $this->Cell($w[0],6,$row[0],'LR');
	        $this->Cell($w[1],6,$row[1],'LR');
	        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
	        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
	        $this->Ln();
	    }
	    // Closing line
	    $this->Cell(array_sum($w),0,'','T');
	}
	
	// Colored table
	function FancyTable($header, $data)
	{
	    // Colors, line width and bold font
	    $this->SetFillColor(255,0,0);
	    $this->SetTextColor(255);
	    $this->SetDrawColor(128,0,0);
	    $this->SetLineWidth(.3);
	    $this->SetFont('','B');
	    // Header
	    $w = array(40, 35, 40, 45);
	    for($i=0;$i<count($header);$i++)
	        $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
	    $this->Ln();
	    // Color and font restoration
	    $this->SetFillColor(224,235,255);
	    $this->SetTextColor(0);
	    $this->SetFont('');
	    // Data
	    $fill = false;
	    foreach($data as $row)
	    {
	        $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
	        $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
	        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
	        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
	        $this->Ln();
	        $fill = !$fill;
	    }
	    // Closing line
	    $this->Cell(array_sum($w),0,'','T');
	}			
}
?>