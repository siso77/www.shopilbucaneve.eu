<?php
define('IN_CB', true);

if(!defined('IN_CB')) die('You are not allowed to access to this page.');
define('VERSION', '4.0.0');
if(version_compare(phpversion(), '5.0.0', '>=') !== true)
	exit('Sorry, but you have to run this script with PHP5... You currently have the version <b>' . phpversion() . '</b>.');

if(!function_exists('imagecreate'))
	exit('Sorry, make sure you have the GD extension installed before running this script.');

include('config.php');
require('function.php');
include('LSTable.php');

class BaseBarCodeGenerator
{
	var $barcode_type;
	var $output;
	var $dpi;
	var $thickness;
	var $res;
	var $rotation;
	var $font_family;
	var $font_size;
	var $text2display;
	
	function BaseBarCodeGenerator()
	{
		//$this->configure($barcode_type,$output,$dpi,$thickness,$res,$rotation,$font_family,$font_size,$text2display);
	}
	
	function output()
	{
		// FileName & Extension
		$system_temp_array = explode('/', $_SERVER['PHP_SELF']);
		$system_temp_array2 = explode('.', $system_temp_array[count($system_temp_array) - 1]);
		
		$default_value = array();
		$default_value['output'] = 1;
		$default_value['dpi'] = 72;
		$default_value['thickness'] = 30;
		$default_value['res'] = 1;
		$default_value['rotation'] = 0.0;
		$default_value['font_family'] = '0';
		$default_value['font_size'] = 8;
		$default_value['text2display'] = '';
		$default_value['a1'] = '';
		$default_value['a2'] = '';
		$default_value['a3'] = '';
		
		$output = intval(isset($this->output) ? $this->output : $default_value['output']);
		$dpi = isset($this->dpi) ? $this->dpi : $default_value['dpi'];
		$thickness = intval(isset($this->thickness) ? $this->thickness : $default_value['thickness']);
		$res = intval(isset($this->res) ? $this->res : $default_value['res']);
		$rotation = isset($this->rotation) ? $this->rotation : $default_value['rotation'];
		$font_family = isset($this->font_family) ? $this->font_family : $default_value['font_family'];
		$font_size = intval(isset($this->font_size) ? $this->font_size : $default_value['font_size']);
		$text2display = isset($this->text2display) ? $this->text2display : $default_value['text2display'];
		$a1 = isset($this->a1) ? $this->a1 : $default_value['a1'];
		$a2 = isset($this->a2) ? $this->a2 : $default_value['a2'];
		$a3 = isset($this->a3) ? $this->a3 : $default_value['a3'];
		
		echo $this->getHtml($output,$dpi,$thickness,$res,$rotation,urlencode($text2display),$font_family,$font_size,$a1,$a2,$a3);
//		echo '<img src="'.WWW_ROOT.'/libs/ext/BARCODE_GENERATOR/html/image.php?code=' . $this->barcode_type . '&amp;o=' . $output . '&amp;dpi=' . $dpi . '&amp;t=' . $thickness . '&amp;r=' . $res . '&amp;rot=' . $rotation . '&amp;text=' . urlencode($text2display) . '&amp;f1=' . $font_family . '&amp;f2=' . $font_size . '&amp;a1=' . $a1 . '&amp;a2=' . $a2 . '&amp;a3=' . $a3 . '" alt="Barcode Image" />';
	}
	
	function configureCode39($text2display)
	{
		$barcode_type = 'code11';
		$output = '1';
		$dpi = '72';
		$thickness = '30';
		$res = '2';
		$rotation = '0';
		$font_family = 'Arial.ttf';
		$font_size = '10';
		$BaseBarCodeGenerator = new BaseBarCodeGenerator();
		
		$this->barcode_type = $barcode_type;
		$this->output = $output;
		$this->dpi = $dpi;
		$this->thickness = $thickness;
		$this->res = $res;
		$this->rotation = $rotation;
		$this->font_family = $font_family;
		$this->font_size = $font_size;
		$this->text2display = $text2display;
	}
	
	function getHtml($output,$dpi,$thickness,$res,$rotation,$text2display,$font_family,$font_size,$a1,$a2,$a3)
	{
		header("Content-Type: application/vnd.ms-word"); 
		header("content-disposition: attachment;filename=".$this->barcode_type.".doc");
		$html = '<html xmlns:v="urn:schemas-microsoft-com:vml"
		xmlns:o="urn:schemas-microsoft-com:office:office"
		xmlns:w="urn:schemas-microsoft-com:office:word"
		xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
		xmlns="http://www.w3.org/TR/REC-html40">
		
		<head>
		<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
		<meta name=ProgId content=Word.Document>
		<meta name=Generator content="Microsoft Word 14">
		<meta name=Originator content="Microsoft Word 14">
		<link rel=File-List href="ETICHETTA%20TEC_file/filelist.xml">
		<link rel=Edit-Time-Data href="ETICHETTA%20TEC_file/editdata.mso">
		<!--[if !mso]>
		<style>
		v\:* {behavior:url(#default#VML);}
		o\:* {behavior:url(#default#VML);}
		w\:* {behavior:url(#default#VML);}
		.shape {behavior:url(#default#VML);}
		</style>
		<![endif]--><!--[if gte mso 9]><xml>
		 <o:DocumentProperties>
		  <o:Author>utente</o:Author>
		  <o:LastAuthor>User</o:LastAuthor>
		  <o:Revision>2</o:Revision>
		  <o:TotalTime>4</o:TotalTime>
		  <o:LastPrinted>2011-12-01T10:20:00Z</o:LastPrinted>
		  <o:Created>2011-12-22T09:59:00Z</o:Created>
		  <o:LastSaved>2011-12-22T09:59:00Z</o:LastSaved>
		  <o:Pages>1</o:Pages>
		  <o:Words>28</o:Words>
		  <o:Characters>162</o:Characters>
		  <o:Lines>1</o:Lines>
		  <o:Paragraphs>1</o:Paragraphs>
		  <o:CharactersWithSpaces>189</o:CharactersWithSpaces>
		  <o:Version>14.00</o:Version>
		 </o:DocumentProperties>
		 <o:OfficeDocumentSettings>
		  <o:TargetScreenSize>800x600</o:TargetScreenSize>
		 </o:OfficeDocumentSettings>
		</xml><![endif]-->
		<link rel=themeData href="ETICHETTA%20TEC_file/themedata.thmx">
		<link rel=colorSchemeMapping href="ETICHETTA%20TEC_file/colorschememapping.xml">
		<!--[if gte mso 9]><xml>
		 <w:WordDocument>
		  <w:SpellingState>Clean</w:SpellingState>
		  <w:TrackMoves>false</w:TrackMoves>
		  <w:TrackFormatting/>
		  <w:HyphenationZone>14</w:HyphenationZone>
		  <w:PunctuationKerning/>
		  <w:ValidateAgainstSchemas/>
		  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
		  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
		  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
		  <w:DoNotPromoteQF/>
		  <w:LidThemeOther>IT</w:LidThemeOther>
		  <w:LidThemeAsian>X-NONE</w:LidThemeAsian>
		  <w:LidThemeComplexScript>X-NONE</w:LidThemeComplexScript>
		  <w:Compatibility>
		   <w:BreakWrappedTables/>
		   <w:SnapToGridInCell/>
		   <w:WrapTextWithPunct/>
		   <w:UseAsianBreakRules/>
		   <w:DontGrowAutofit/>
		   <w:DontUseIndentAsNumberingTabStop/>
		   <w:FELineBreak11/>
		   <w:WW11IndentRules/>
		   <w:DontAutofitConstrainedTables/>
		   <w:AutofitLikeWW11/>
		   <w:HangulWidthLikeWW11/>
		   <w:UseNormalStyleForList/>
		   <w:DontVertAlignCellWithSp/>
		   <w:DontBreakConstrainedForcedTables/>
		   <w:DontVertAlignInTxbx/>
		   <w:Word11KerningPairs/>
		   <w:CachedColBalance/>
		  </w:Compatibility>
		  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
		  <m:mathPr>
		   <m:mathFont m:val="Cambria Math"/>
		   <m:brkBin m:val="before"/>
		   <m:brkBinSub m:val="&#45;-"/>
		   <m:smallFrac m:val="off"/>
		   <m:dispDef/>
		   <m:lMargin m:val="0"/>
		   <m:rMargin m:val="0"/>
		   <m:defJc m:val="centerGroup"/>
		   <m:wrapIndent m:val="1440"/>
		   <m:intLim m:val="subSup"/>
		   <m:naryLim m:val="undOvr"/>
		  </m:mathPr></w:WordDocument>
		</xml><![endif]--><!--[if gte mso 9]><xml>
		 <w:LatentStyles DefLockedState="false" DefUnhideWhenUsed="false"
		  DefSemiHidden="false" DefQFormat="false" LatentStyleCount="267">
		  <w:LsdException Locked="false" QFormat="true" Name="Normal"/>
		  <w:LsdException Locked="false" QFormat="true" Name="heading 1"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="heading 2"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="heading 3"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="heading 4"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="heading 5"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="heading 6"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="heading 7"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="heading 8"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="heading 9"/>
		  <w:LsdException Locked="false" SemiHidden="true" UnhideWhenUsed="true"
		   QFormat="true" Name="caption"/>
		  <w:LsdException Locked="false" QFormat="true" Name="Title"/>
		  <w:LsdException Locked="false" QFormat="true" Name="Subtitle"/>
		  <w:LsdException Locked="false" QFormat="true" Name="Strong"/>
		  <w:LsdException Locked="false" QFormat="true" Name="Emphasis"/>
		  <w:LsdException Locked="false" Priority="99" SemiHidden="true"
		   Name="Placeholder Text"/>
		  <w:LsdException Locked="false" Priority="1" QFormat="true" Name="No Spacing"/>
		  <w:LsdException Locked="false" Priority="60" Name="Light Shading"/>
		  <w:LsdException Locked="false" Priority="61" Name="Light List"/>
		  <w:LsdException Locked="false" Priority="62" Name="Light Grid"/>
		  <w:LsdException Locked="false" Priority="63" Name="Medium Shading 1"/>
		  <w:LsdException Locked="false" Priority="64" Name="Medium Shading 2"/>
		  <w:LsdException Locked="false" Priority="65" Name="Medium List 1"/>
		  <w:LsdException Locked="false" Priority="66" Name="Medium List 2"/>
		  <w:LsdException Locked="false" Priority="67" Name="Medium Grid 1"/>
		  <w:LsdException Locked="false" Priority="68" Name="Medium Grid 2"/>
		  <w:LsdException Locked="false" Priority="69" Name="Medium Grid 3"/>
		  <w:LsdException Locked="false" Priority="70" Name="Dark List"/>
		  <w:LsdException Locked="false" Priority="71" Name="Colorful Shading"/>
		  <w:LsdException Locked="false" Priority="72" Name="Colorful List"/>
		  <w:LsdException Locked="false" Priority="73" Name="Colorful Grid"/>
		  <w:LsdException Locked="false" Priority="60" Name="Light Shading Accent 1"/>
		  <w:LsdException Locked="false" Priority="61" Name="Light List Accent 1"/>
		  <w:LsdException Locked="false" Priority="62" Name="Light Grid Accent 1"/>
		  <w:LsdException Locked="false" Priority="63" Name="Medium Shading 1 Accent 1"/>
		  <w:LsdException Locked="false" Priority="64" Name="Medium Shading 2 Accent 1"/>
		  <w:LsdException Locked="false" Priority="65" Name="Medium List 1 Accent 1"/>
		  <w:LsdException Locked="false" Priority="99" SemiHidden="true" Name="Revision"/>
		  <w:LsdException Locked="false" Priority="34" QFormat="true"
		   Name="List Paragraph"/>
		  <w:LsdException Locked="false" Priority="29" QFormat="true" Name="Quote"/>
		  <w:LsdException Locked="false" Priority="30" QFormat="true"
		   Name="Intense Quote"/>
		  <w:LsdException Locked="false" Priority="66" Name="Medium List 2 Accent 1"/>
		  <w:LsdException Locked="false" Priority="67" Name="Medium Grid 1 Accent 1"/>
		  <w:LsdException Locked="false" Priority="68" Name="Medium Grid 2 Accent 1"/>
		  <w:LsdException Locked="false" Priority="69" Name="Medium Grid 3 Accent 1"/>
		  <w:LsdException Locked="false" Priority="70" Name="Dark List Accent 1"/>
		  <w:LsdException Locked="false" Priority="71" Name="Colorful Shading Accent 1"/>
		  <w:LsdException Locked="false" Priority="72" Name="Colorful List Accent 1"/>
		  <w:LsdException Locked="false" Priority="73" Name="Colorful Grid Accent 1"/>
		  <w:LsdException Locked="false" Priority="60" Name="Light Shading Accent 2"/>
		  <w:LsdException Locked="false" Priority="61" Name="Light List Accent 2"/>
		  <w:LsdException Locked="false" Priority="62" Name="Light Grid Accent 2"/>
		  <w:LsdException Locked="false" Priority="63" Name="Medium Shading 1 Accent 2"/>
		  <w:LsdException Locked="false" Priority="64" Name="Medium Shading 2 Accent 2"/>
		  <w:LsdException Locked="false" Priority="65" Name="Medium List 1 Accent 2"/>
		  <w:LsdException Locked="false" Priority="66" Name="Medium List 2 Accent 2"/>
		  <w:LsdException Locked="false" Priority="67" Name="Medium Grid 1 Accent 2"/>
		  <w:LsdException Locked="false" Priority="68" Name="Medium Grid 2 Accent 2"/>
		  <w:LsdException Locked="false" Priority="69" Name="Medium Grid 3 Accent 2"/>
		  <w:LsdException Locked="false" Priority="70" Name="Dark List Accent 2"/>
		  <w:LsdException Locked="false" Priority="71" Name="Colorful Shading Accent 2"/>
		  <w:LsdException Locked="false" Priority="72" Name="Colorful List Accent 2"/>
		  <w:LsdException Locked="false" Priority="73" Name="Colorful Grid Accent 2"/>
		  <w:LsdException Locked="false" Priority="60" Name="Light Shading Accent 3"/>
		  <w:LsdException Locked="false" Priority="61" Name="Light List Accent 3"/>
		  <w:LsdException Locked="false" Priority="62" Name="Light Grid Accent 3"/>
		  <w:LsdException Locked="false" Priority="63" Name="Medium Shading 1 Accent 3"/>
		  <w:LsdException Locked="false" Priority="64" Name="Medium Shading 2 Accent 3"/>
		  <w:LsdException Locked="false" Priority="65" Name="Medium List 1 Accent 3"/>
		  <w:LsdException Locked="false" Priority="66" Name="Medium List 2 Accent 3"/>
		  <w:LsdException Locked="false" Priority="67" Name="Medium Grid 1 Accent 3"/>
		  <w:LsdException Locked="false" Priority="68" Name="Medium Grid 2 Accent 3"/>
		  <w:LsdException Locked="false" Priority="69" Name="Medium Grid 3 Accent 3"/>
		  <w:LsdException Locked="false" Priority="70" Name="Dark List Accent 3"/>
		  <w:LsdException Locked="false" Priority="71" Name="Colorful Shading Accent 3"/>
		  <w:LsdException Locked="false" Priority="72" Name="Colorful List Accent 3"/>
		  <w:LsdException Locked="false" Priority="73" Name="Colorful Grid Accent 3"/>
		  <w:LsdException Locked="false" Priority="60" Name="Light Shading Accent 4"/>
		  <w:LsdException Locked="false" Priority="61" Name="Light List Accent 4"/>
		  <w:LsdException Locked="false" Priority="62" Name="Light Grid Accent 4"/>
		  <w:LsdException Locked="false" Priority="63" Name="Medium Shading 1 Accent 4"/>
		  <w:LsdException Locked="false" Priority="64" Name="Medium Shading 2 Accent 4"/>
		  <w:LsdException Locked="false" Priority="65" Name="Medium List 1 Accent 4"/>
		  <w:LsdException Locked="false" Priority="66" Name="Medium List 2 Accent 4"/>
		  <w:LsdException Locked="false" Priority="67" Name="Medium Grid 1 Accent 4"/>
		  <w:LsdException Locked="false" Priority="68" Name="Medium Grid 2 Accent 4"/>
		  <w:LsdException Locked="false" Priority="69" Name="Medium Grid 3 Accent 4"/>
		  <w:LsdException Locked="false" Priority="70" Name="Dark List Accent 4"/>
		  <w:LsdException Locked="false" Priority="71" Name="Colorful Shading Accent 4"/>
		  <w:LsdException Locked="false" Priority="72" Name="Colorful List Accent 4"/>
		  <w:LsdException Locked="false" Priority="73" Name="Colorful Grid Accent 4"/>
		  <w:LsdException Locked="false" Priority="60" Name="Light Shading Accent 5"/>
		  <w:LsdException Locked="false" Priority="61" Name="Light List Accent 5"/>
		  <w:LsdException Locked="false" Priority="62" Name="Light Grid Accent 5"/>
		  <w:LsdException Locked="false" Priority="63" Name="Medium Shading 1 Accent 5"/>
		  <w:LsdException Locked="false" Priority="64" Name="Medium Shading 2 Accent 5"/>
		  <w:LsdException Locked="false" Priority="65" Name="Medium List 1 Accent 5"/>
		  <w:LsdException Locked="false" Priority="66" Name="Medium List 2 Accent 5"/>
		  <w:LsdException Locked="false" Priority="67" Name="Medium Grid 1 Accent 5"/>
		  <w:LsdException Locked="false" Priority="68" Name="Medium Grid 2 Accent 5"/>
		  <w:LsdException Locked="false" Priority="69" Name="Medium Grid 3 Accent 5"/>
		  <w:LsdException Locked="false" Priority="70" Name="Dark List Accent 5"/>
		  <w:LsdException Locked="false" Priority="71" Name="Colorful Shading Accent 5"/>
		  <w:LsdException Locked="false" Priority="72" Name="Colorful List Accent 5"/>
		  <w:LsdException Locked="false" Priority="73" Name="Colorful Grid Accent 5"/>
		  <w:LsdException Locked="false" Priority="60" Name="Light Shading Accent 6"/>
		  <w:LsdException Locked="false" Priority="61" Name="Light List Accent 6"/>
		  <w:LsdException Locked="false" Priority="62" Name="Light Grid Accent 6"/>
		  <w:LsdException Locked="false" Priority="63" Name="Medium Shading 1 Accent 6"/>
		  <w:LsdException Locked="false" Priority="64" Name="Medium Shading 2 Accent 6"/>
		  <w:LsdException Locked="false" Priority="65" Name="Medium List 1 Accent 6"/>
		  <w:LsdException Locked="false" Priority="66" Name="Medium List 2 Accent 6"/>
		  <w:LsdException Locked="false" Priority="67" Name="Medium Grid 1 Accent 6"/>
		  <w:LsdException Locked="false" Priority="68" Name="Medium Grid 2 Accent 6"/>
		  <w:LsdException Locked="false" Priority="69" Name="Medium Grid 3 Accent 6"/>
		  <w:LsdException Locked="false" Priority="70" Name="Dark List Accent 6"/>
		  <w:LsdException Locked="false" Priority="71" Name="Colorful Shading Accent 6"/>
		  <w:LsdException Locked="false" Priority="72" Name="Colorful List Accent 6"/>
		  <w:LsdException Locked="false" Priority="73" Name="Colorful Grid Accent 6"/>
		  <w:LsdException Locked="false" Priority="19" QFormat="true"
		   Name="Subtle Emphasis"/>
		  <w:LsdException Locked="false" Priority="21" QFormat="true"
		   Name="Intense Emphasis"/>
		  <w:LsdException Locked="false" Priority="31" QFormat="true"
		   Name="Subtle Reference"/>
		  <w:LsdException Locked="false" Priority="32" QFormat="true"
		   Name="Intense Reference"/>
		  <w:LsdException Locked="false" Priority="33" QFormat="true" Name="Book Title"/>
		  <w:LsdException Locked="false" Priority="37" SemiHidden="true"
		   UnhideWhenUsed="true" Name="Bibliography"/>
		  <w:LsdException Locked="false" Priority="39" SemiHidden="true"
		   UnhideWhenUsed="true" QFormat="true" Name="TOC Heading"/>
		 </w:LatentStyles>
		</xml><![endif]-->
		<style>
		<!--
		 /* Style Definitions */
		 p.MsoNormal, li.MsoNormal, div.MsoNormal
			{mso-style-unhide:no;
			mso-style-qformat:yes;
			mso-style-parent:"";
			margin:0cm;
			margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:12.0pt;
			font-family:"Times New Roman","serif";
			mso-fareast-font-family:"Times New Roman";}
		@page WordSection1
			{size:120.5pt 4.0cm;
			margin:8.5pt 3.7pt 0cm 8.5pt;
			mso-header-margin:0cm;
			mso-footer-margin:0cm;
			mso-vertical-page-align:middle;
			mso-paper-source:0;}
		div.WordSection1
			{page:WordSection1;}
		-->
		</style>
		<!--[if gte mso 10]>
		<style>
		 /* Style Definitions */
		 table.MsoNormalTable
			{mso-style-name:"Tabella normale";
			mso-tstyle-rowband-size:0;
			mso-tstyle-colband-size:0;
			mso-style-noshow:yes;
			mso-style-unhide:no;
			mso-style-parent:"";
			mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
			mso-para-margin:0cm;
			mso-para-margin-bottom:.0001pt;
			mso-pagination:widow-orphan;
			font-size:10.0pt;
			font-family:"Times New Roman","serif";}
		</style>
		<![endif]-->
		</head>
		
		<body lang=IT style=\'tab-interval:35.4pt\'>
		
		<div class=WordSection1>
		
		<p class=MsoNormal><!--[if gte vml 1]><v:shapetype id="_x0000_t75" coordsize="21600,21600"
		 o:spt="75" o:preferrelative="t" path="m@4@5l@4@11@9@11@9@5xe" filled="f"
		 stroked="f">
		 <v:stroke joinstyle="miter"/>
		 <v:formulas>
		  <v:f eqn="if lineDrawn pixelLineWidth 0"/>
		  <v:f eqn="sum @0 1 0"/>
		  <v:f eqn="sum 0 0 @1"/>
		  <v:f eqn="prod @2 1 2"/>
		  <v:f eqn="prod @3 21600 pixelWidth"/>
		  <v:f eqn="prod @3 21600 pixelHeight"/>
		  <v:f eqn="sum @0 0 1"/>
		  <v:f eqn="prod @6 1 2"/>
		  <v:f eqn="prod @7 21600 pixelWidth"/>
		  <v:f eqn="sum @8 21600 0"/>
		  <v:f eqn="prod @7 21600 pixelHeight"/>
		  <v:f eqn="sum @10 21600 0"/>
		 </v:formulas>
		 <v:path o:extrusionok="f" gradientshapeok="t" o:connecttype="rect"/>
		 <o:lock v:ext="edit" aspectratio="t"/>
		</v:shapetype><v:shape id="_x0000_i1025" type="#_x0000_t75" alt="Barcode Image" style=\'width:114pt;height:55.5pt\'>
		 <v:imagedata src="'.WWW_ROOT.'/libs/ext/BARCODE_GENERATOR/html/image.php?code=' . $this->barcode_type . '&amp;o=' . $output . '&amp;dpi=' . $dpi . '&amp;t=' . $thickness . '&amp;r=' . $res . '&amp;rot=' . $rotation . '&amp;text=' . $text2display . '&amp;f1=' . $font_family . '&amp;f2=' . $font_size . '&amp;a1=' . $a1 . '&amp;a2=' . $a2 . '&amp;a3=' . $a3 . '" o:href="'.WWW_ROOT.'/libs/ext/BARCODE_GENERATOR/html/image.php?code=' . $this->barcode_type . '&amp;o=' . $output . '&amp;dpi=' . $dpi . '&amp;t=' . $thickness . '&amp;r=' . $res . '&amp;rot=' . $rotation . '&amp;text=' . urlencode($text2display) . '&amp;f1=' . $font_family . '&amp;f2=' . $font_size . '&amp;a1=' . $a1 . '&amp;a2=' . $a2 . '&amp;a3=' . $a3 . '"/>
		</v:shape><![endif]-->
		<![if !vml]>
		<img width=152 height=74 src="'.WWW_ROOT.'/libs/ext/BARCODE_GENERATOR/html/image.php?code=' . $this->barcode_type . '&amp;o=' . $output . '&amp;dpi=' . $dpi . '&amp;t=' . $thickness . '&amp;r=' . $res . '&amp;rot=' . $rotation . '&amp;text=' . $text2display . '&amp;f1=' . $font_family . '&amp;f2=' . $font_size . '&amp;a1=' . $a1 . '&amp;a2=' . $a2 . '&amp;a3=' . $a3 . '" alt="Barcode Image" v:shapes="_x0000_i1025">
		<![endif]></p>
		</div>
		</body>
		</html>';
		return $html;		
	}
}
?>