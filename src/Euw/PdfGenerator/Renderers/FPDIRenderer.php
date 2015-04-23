<?php namespace Euw\PdfGenerator\Renderers;

use fpdi\FPDI;
use Illuminate\Support\Facades\File;
use TCPDF;
use TCPDF_FONTS;

class FPDIRenderer implements PDFRendererInterface {
	private $pdf;
	private $layout;
	private $margin = 0;
	private $bleed = 0;
	public $cropMarks = true;
	private $targetId;
	private $debug = 0;

	public function __construct() {
		new \TCPDF;
		$this->pdf = new FPDI();

		date_default_timezone_set( "Europe/Berlin" );
	}

	private function getFontsPath() {
		return public_path() . '/fonts/';
	}

	public function render( $layout, $contents = [ ] ) {

		$this->layout   = $layout;
		$this->contents = $contents;

		if ( $this->cropMarks ) {
			$this->margin = $this->layout->margin;
			$this->bleed  = $this->layout->bleed;
		}

		$this->addPage();

		// set document information
		$this->pdf->SetCreator( PDF_CREATOR );
		$this->pdf->SetAuthor( 'eberle & wollweber COMMUNICATIONS GmbH' );
		$this->pdf->SetTitle( 'Document title' );
		$this->pdf->SetSubject( 'Document subject' );
		$this->pdf->SetKeywords( 'e&w, test, pdf, generator' );

		if ( $this->layout->backgroundImage ) {
			$this->drawBackground();
		}

		if ( $this->cropMarks ) {
			$this->drawCropMarks();
		}

		$this->writeContent();

		return $this;
	}

	private function addPage() {
		// for full background image
		$this->pdf->SetAutoPageBreak( false );

		// margins
		$this->pdf->setMargins( 0, 0, 0 );
		$this->pdf->SetHeaderMargin( 0 );
		$this->pdf->SetTopMargin( 0 );
		$this->pdf->SetFooterMargin( 0 );

		// disable header and footer
		$this->pdf->setPrintHeader( false );
		$this->pdf->setPrintFooter( false );

		// add a new Page. P = Portrait, L = Landscape
		$format = $this->layout->width > $this->layout->height ? 'L' : 'P';

		$page_format = [
			'MediaBox' => array(
				'llx' => 0,
				'lly' => 0,
				'urx' => $this->layout->width + 2 * $this->margin,
				'ury' => $this->layout->height + 2 * $this->margin
			),
			'CropBox'  => array(
				'llx' => 0,
				'lly' => 0,
				'urx' => $this->layout->width + 2 * $this->margin,
				'ury' => $this->layout->height + 2 * $this->margin
			),
			'BleedBox' => array(
				'llx' => $this->margin - $this->bleed,
				'lly' => $this->margin - $this->bleed,
				'urx' => $this->layout->width + $this->margin + $this->bleed,
				'ury' => $this->layout->height + $this->margin + $this->bleed
			),
			'TrimBox'  => array(
				'llx' => $this->margin,
				'lly' => $this->margin,
				'urx' => $this->layout->width + $this->margin,
				'ury' => $this->layout->height + $this->margin
			),
		];

		$this->pdf->AddPage( $format, $page_format );
	}

	private function prepareFont( $fontName ) {

		$fontfile = $this->getFontsPath() . $fontName;

		if ( File::exists( $fontfile ) ) {

			/*
			 * *.otf fonts don't work, they return false on conversion. use fontforge to convert them:
			 *
			 * save as otf2ttf.sh:
				#!/usr/local/bin/fontforge
				# Quick and dirty hack: converts a font to truetype (.ttf)
				Print("Opening "+$1);
				Open($1);
				Print("Saving "+$1:r+".ttf");
				Generate($1:r+".ttf");
				Quit(0);
			 *
			 * fontforge -script otf2ttf.sh FONTNAME.otf
			 *
			 * for i in *.otf; do fontforge -script otf2ttf.sh $i; done
			 */

			$fontName = TCPDF_FONTS::addTTFfont(
				$fontfile,
				$fonttype = '',
				$enc = '',
				$flags = 32
			);

		} else {
			$fontName = 'Helvetica';
		}

		return $fontName;
	}

	private function drawContent( $content ) {
		$colorString = isset( $content->layout->color ) ? $content->layout->color : '0,0,0,100';
		$colors      = explode( ',', $colorString );

		$fontFamily = isset( $content->layout->fontFamily ) ? $content->layout->fontFamily : 'Helvetica';

		// fonts tested successfully
//		$fontFamily = 'VWHeadlineOT/VWHeadlineOT-Black.ttf';
//		$fontFamily = 'SkodaPro/SkodaPro_Bold.ttf';
//		$fontFamily = 'Roskrift/Roskrift_Clean.ttf';
//		$fontFamily = 'ToyotaText/ToyotaText_Bd.ttf';
//		$fontFamily = 'GillSans.ttf';
//		$fontFamily = 'Strangelove/strangelove-next-narrow.ttf';
//		$fontFamily = 'ToyotaDisplay/ToyotaDisplay_Bd.ttf';
//		$fontFamily = 'handsean.ttf';
//		$fontFamily = 'AudiTypeV01/AudiTypeV01-Bold.ttf';

		$fontFamily = $this->prepareFont( $fontFamily );

//        dd($fontFamily);

		$fontSize = isset( $content->layout->fontSize ) && (float) $content->layout->fontSize > 0 ? (float) $content->layout->fontSize : 12.0;
		$this->pdf->SetFont( $fontFamily, '', $fontSize, '', false );

		$this->pdf->SetTextColor( $colors[0], $colors[1], $colors[2], $colors[3] );

		$this->pdf->MultiCell(
			$w = isset( $content->layout->width ) ? (float) $content->layout->width : 0,
			$h = isset( $content->layout->height ) ? (float) $content->layout->height : 0,
			$txt = $content->text,
			$border = $this->debug,
			$align = 'L',
			$fill = false,
			$ln = 1,
			$x = isset( $content->layout->x ) ? (float) $content->layout->x + $this->margin : $this->margin,
			$y = isset( $content->layout->y ) ? (float) $content->layout->y + $this->margin : $this->margin,
			$reseth = true,
			$stretch = 0,
			$ishtml = false,
			$autopadding = true,
			$maxh = 0,
			$valign = 'T',
			$fitcell = false
		);
	}

	private function writeContent() {
		foreach ( $this->contents as $content ) {
			$this->drawContent( $content );
		}
	}

	private function drawBackground() {
		$backgroundImage = $this->layout->backgroundImage;

		if ( File::exists( $backgroundImage ) ) {

			$this->pdf->setSourceFile( $backgroundImage );

			$tplIdx = $this->pdf->importPage( 1, '/MediaBox' );

			$this->pdf->useTemplate(
				$tplIdx,
				$x = $this->margin - $this->bleed,
				$y = $this->margin - $this->bleed,
				$w = $this->layout->width + 2 * $this->bleed,
				$h = $this->layout->height + 2 * $this->bleed,
				$adjustPageSize = false );
		}
	}

	private function drawCropMarks() {
		$pdf = $this->pdf;

		$pdf->cropMark( $this->margin, $this->margin, 10, 10, 'TL' );
		$pdf->cropMark( $this->layout->width + $this->margin, $this->margin, 10, 10, 'TR' );
		$pdf->cropMark( $this->margin, $this->layout->height + $this->margin, 10, 10, 'BL' );
		$pdf->cropMark( $this->layout->width + $this->margin, $this->layout->height + $this->margin, 10, 10, 'BR' );
	}

	/**
	 * output methods:
	 * E: return the document as base64 mime multi-part email attachment (RFC 2045)
	 * Options: I = display inline, F = output as file, D = download
	 */

	private function getFileName() {
		$time = time();

		return $time . '.pdf';
	}

	public function show() {
		$fileName = $this->getFileName();
		$this->pdf->Output( $fileName, 'I' );
	}

	public function download( $fileName ) {
		$this->pdf->Output( $fileName, 'D' );
	}

	public function attachment( $fileName ) {
		return $this->pdf->Output( $fileName, 'E' );
	}

	public function toString() {
		return $this->pdf->Output( '', 'S' );
	}

	public function saveToFile( $fileName, $path ) {
		File::exists( $path ) or File::makeDirectory( $path, 755, true );

		$this->pdf->Output( $path . $fileName, 'F' );

		return $path . $fileName;
	}

	public function setTargetId( $id ) {
		$this->targetId = $id;
	}
}