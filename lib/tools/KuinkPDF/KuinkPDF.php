<?php
require_once(__DIR__.'/../tcpdf_min/tcpdf.php');
//require_once($KUINK_INCLUDE_PATH.'lib/tools/tcpdf_min/tcpdf.php');
//die('bumm m '.__FILE__);


// Extend the TCPDF class to create custom Header and Footer
//class KuinkPDF extends \TCPDF {
class KuinkPDF extends \TCPDF {
	protected $templateCode; //
	
	//Page header

	public function setTemplateCode($templateCode)
	{
		$this->templateCode = $templateCode;
	}
	
	
	public function Header() {
		if ($this->page != 1) {
			$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
			$this->SetX($this->original_rMargin);
			$this->SetY($this->tMargin - 5);
			//$this->SetY((2.835 / $this->k) + $this->y);
			$this->Cell(0, 0, $this->title, 'B', 0, 'R');
		}
	}
	
	
	// Page footer
	public function Footer() {
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		//set style for cell border
		$line_width = (0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
		//print document barcode

		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		if (empty($this->pagegroups)) {
			$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->SetY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->SetX($this->original_rMargin);
			$this->Cell(0, 0, $this->templateCode, 'T', 0, 'R');
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
		} else {
			$this->SetX($this->original_lMargin);
			$this->Cell(0, 0, $this->templateCode, 'T', 0, 'L');
			$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
		}
		
	}
}