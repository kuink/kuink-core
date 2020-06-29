<?php

namespace Kuink\Core\Instruction;

use Kuink\Core\Lib\FileLib;
use Kuink\Core\Lib\UtilsLib;
use KuinkPDF;

/**
 * Creates a PDF
 *
 * @author paulo.tavares
 */
class PdfInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Creates a PDF
	 */
	static public function execute($instManager, $instructionXmlNode) {
		self::create($instManager, $instructionXmlNode);
	}

	static public function create($instManager, $instructionXmlNode) {
		$paper = (string) self::getAttribute ( $instructionXmlNode, 'paper', $instManager->variables, false, 'a4'); //$this->get_inst_attr ( $instruction_xmlnode, 'paper', $instManager->variables, false, 'a4' );
		$orientation = (string) self::getAttribute ( $instructionXmlNode, 'orientation', $instManager->variables, false, 'portrait'); //$this->get_inst_attr ( $instruction_xmlnode, 'orientation', $instManager->variables, false, 'portrait' );
		$unit = (string) self::getAttribute ( $instructionXmlNode, 'unit', $instManager->variables, false, 'mm'); //$this->get_inst_attr ( $instruction_xmlnode, 'unit', $instManager->variables, false, 'mm' );
		
		// if no path is supplied go to tmp file
		$path = (string) self::getAttribute ( $instructionXmlNode, 'path', $instManager->variables, false, 'tmp/'); //$this->get_inst_attr ( $instruction_xmlnode, 'path', $instManager->variables, false, 'tmp/' );

		// handle dupplication of /
		$path .= '/';
		
		// if register then the file will be inserted in file and the file id will be returned
		$register = (string) self::getAttribute ( $instructionXmlNode, 'register', $instManager->variables, false, 'false'); //$this->get_inst_attr ( $instruction_xmlnode, 'register', $instManager->variables, false, 'false' );
		// By default the file will be downloaded
		$download = (string) self::getAttribute ( $instructionXmlNode, 'download', $instManager->variables, false, 'true'); //$this->get_inst_attr ( $instruction_xmlnode, 'download', $instManager->variables, false, 'true' );
		
		$marginLeft = (string) self::getAttribute ( $instructionXmlNode, 'marginleft', $instManager->variables, false, '5'); //$this->get_inst_attr ( $instruction_xmlnode, 'marginleft', $instManager->variables, false, '5' );
		$marginRight = (string) self::getAttribute ( $instructionXmlNode, 'marginright', $instManager->variables, false, '5'); //$this->get_inst_attr ( $instruction_xmlnode, 'marginright', $instManager->variables, false, '5' );
		$marginTop = (string) self::getAttribute ( $instructionXmlNode, 'margintop', $instManager->variables, false, '5'); //$this->get_inst_attr ( $instruction_xmlnode, 'margintop', $instManager->variables, false, '5' );
		$marginBottom = (string) self::getAttribute ( $instructionXmlNode, 'marginbottom', $instManager->variables, false, '10'); //$this->get_inst_attr ( $instruction_xmlnode, 'marginbottom', $instManager->variables, false, '10' );
		
		// Header defaults to false
		$header = (string) self::getAttribute ( $instructionXmlNode, 'header', $instManager->variables, false, 'false'); //$this->get_inst_attr ( $instruction_xmlnode, 'header', $instManager->variables, false, 'false' );
		// Footer defaults to true
		$footer = (string) self::getAttribute ( $instructionXmlNode, 'footer', $instManager->variables, false, 'true'); //$this->get_inst_attr ( $instruction_xmlnode, 'footer', $instManager->variables, false, 'true' );
		
		// Get the background image
		$background = (string) self::getAttribute ( $instructionXmlNode, 'background', $instManager->variables, false, ''); //$this->get_inst_attr ( $instruction_xmlnode, 'background', $instManager->variables, false, '' );
		
		// The file should be overriden if exists?
		$override = (string) self::getAttribute ( $instructionXmlNode, 'override', $instManager->variables, false, 'true'); //$this->get_inst_attr ( $instruction_xmlnode, 'override', $instManager->variables, false, 'true' );
		$guid = new UtilsLib ( $instManager->nodeConfiguration, null );
		$guid = $guid->GuidClean ( null );
		// If the filename is not supplied then return a guid
		$filename = (string) self::getAttribute ( $instructionXmlNode, 'filename', $instManager->variables, false, $guid); //$this->get_inst_attr ( $instruction_xmlnode, 'filename', $instManager->variables, false, $guid );
		$filename = $filename . '.pdf';
		
		$params = $instManager->getParams( $instructionXmlNode); //Get the params defined in params attribute $this->aux_get_named_param_values ( $instManager->nodeConfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $instManager->variables, $exit );
		$html = $params ['content'];
		
		// create new PDF document
		$pdf = new KuinkPDF ( $orientation, $unit, $paper, true, 'UTF-8', false, false );
		
		// set document information

		$meta = $instManager->getCustomParams($instructionXmlNode, 'Meta');
		$metaCreator = isset($meta['creator']) ? ( string ) $meta['creator'] : ''; // $this->get_meta_value ( 'creator', $instManager->nodeConfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $instManager->variables, $exit );
		$metaAuthor = isset($meta['author']) ? ( string ) $meta['author'] : '';  //( string ) $this->get_meta_value ( 'author', $instManager->nodeConfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $instManager->variables, $exit );
		$metaTitle = isset($meta['title']) ? ( string ) $meta['title'] : '';  //( string ) $this->get_meta_value ( 'title', $instManager->nodeConfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $instManager->variables, $exit );
		$metaSubject = isset($meta['subject']) ? ( string ) $meta['subject']: ''; //( string ) $this->get_meta_value ( 'subject', $instManager->nodeConfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $instManager->variables, $exit );
		$metaKeywords = isset($meta['keywords']) ? $meta['keywords'] : array(); //$this->get_meta_value ( 'keywords', $instManager->nodeConfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $instManager->variables, $exit );
		$metaTemplateCode = isset($meta['template']) ? ( string ) $meta['template'] : ''; //$this->get_meta_value ( 'template', $instManager->nodeConfiguration, $nodexml, $action_xmlnode, $instruction_xmlnode, $actionname, $instructionname, $instManager->variables, $exit );

		$pdf->SetCreator ( $metaCreator );
		$pdf->SetAuthor ( $metaAuthor );
		$pdf->SetTitle ( $metaTitle );
		$pdf->SetSubject ( $metaSubject );
		$pdf->SetKeywords ( $metaKeywords );
		$pdf->setTemplateCode ( $metaTemplateCode );
		
		// set header and footer fonts
		$pdf->setHeaderFont ( Array (
				PDF_FONT_NAME_DATA,
				'',
				PDF_FONT_SIZE_DATA 
		) );
		$pdf->setFooterFont ( Array (
				PDF_FONT_NAME_DATA,
				'',
				PDF_FONT_SIZE_DATA 
		) );
		$pdf->SetHeaderMargin ( 0 );
		if ($header == 'false')
			$pdf->setPrintHeader ( false );
		if ($footer == 'false')
			$pdf->setPrintFooter ( false );
			
			// set margins
		
		$pdfMarginTop = $marginTop;
		$pdfMarginRight = $marginRight;
		$pdfMarginBottom = $marginBottom;
		$pdfMarginLeft = $marginLeft;
		
		$pdf->SetMargins ( $pdfMarginLeft, $pdfMarginTop, $pdfMarginRight );
		
		$pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );
		
		// set auto page breaks
		$pdf->SetAutoPageBreak ( TRUE, $pdfMarginBottom );
		
		// set image scale factor
		$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
		
		// set some language-dependent strings
		// $pdf->setLanguageArray($l);
		
		// ---------------------------------------------------------
		
		// set default font subsetting mode
		$pdf->setFontSubsetting ( true );
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont ( 'helvetica', '', 14, '', true );
		
		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage ();
		
		// Setting background image if specified in parameters
		
		if ($background != '')
			if ($orientation == 'landscape')
				$pdf->Image ( $background, $marginLeft, $marginTop, 297, 210, '', '', '', false, 0, '', false, false, 0 );
			else
				$pdf->Image ( $background, $marginLeft, $marginTop, 210, 297, '', '', '', false, 0, '', false, false, 0 );
			
			// Print text using writeHTMLCell()
		$pdf->writeHTML ( $html, true, false, true, false, '' );
		
		$config = $instManager->nodeConfiguration [\Kuink\Core\NodeConfKey::CONFIG];
		
		$baseUpload = Configuration::getInstance()->paths->upload_dir;
		$uploadDir = $baseUpload . '/' . $path;
		
		// Handle dupplication of slashes in configurations
		$uploadDir = str_replace ( '//', '/', $uploadDir );
		
		$myFile = $uploadDir . $filename;
		
		// Create the path if the directory doesn't exist
		if (! is_dir ( $uploadDir )) {
			$dir_parts = explode ( '/', $uploadDir );
			$sub_dirs = '/';
			foreach ( $dir_parts as $dir ) {
				
				if (! is_dir ( $sub_dirs . $dir ))
					mkdir ( $sub_dirs . $dir );
				$sub_dirs .= $dir . '/';
			}
		}
		
		$flag = ($override == 'true') ? 'w+' : 'x+';
		
		$fh = fopen ( $myFile, $flag ) or die ( "can't open file. The file is not marked to be overriden." );
		$stringData = $pdf->Output ( 'example_001.pdf', 'S' );
		fwrite ( $fh, $stringData );
		fclose ( $fh );
		
		$idFile = null;
		
		$utils = new UtilsLib ( $instManager->nodeConfiguration, \Kuink\Core\MessageManager::getInstance () );
		$fileGuid = $utils->GuidClean ( null );
		$filelib = new FileLib ( $instManager->nodeConfiguration, \Kuink\Core\MessageManager::getInstance () );
		if ($register == 'true') {
			// register the file in the database
			$originalName = $filename;
			$name = $filename;
			$size = filesize ( $myFile );
			$ext = 'pdf';
			$mime = 'application/pdf';
			$idUser = ( string ) $instManager->variables ['USER'] ['id'];
			$desc = '';
			
			$idFile = $filelib->register ( $originalName, $path, $name, $size, $ext, $mime, $idUser, $desc, $fileGuid );
		}
		if ($download == 'true') {
			$handler = ($register == 'true') ? 'stream.php?type=file&guid=' . $fileGuid : 'stream.php?type=tmp&guid=' . $filename;
			print '
			<script>
			// open the window
			windowpopup = window.open("' . $handler . '", "Documento", "scrollbars=yes");
			//windowpopup.close();
			</script>';
		}

		return $idFile;
	}
}

?>
