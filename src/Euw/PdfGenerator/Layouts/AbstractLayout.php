<?php namespace Euw\PdfGenerator\Layouts;

use Euw\PdfGenerator\Contracts\Layout as LayoutContract;

class AbstractLayout implements LayoutContract {

	public $layout;
	public $height;
	public $margin;
	public $bleed;
	public $cropMarks;
	public $defaultFont;
	public $background;
	public $pages;
	public $imagePath;

	public function __construct( $layout = null ) {

		$this->width  = $layout && isset( $layout->width ) ? $layout->width : 210;
		$this->height = $layout && isset( $layout->height ) ? $layout->height : 297;
		$this->bleed  = $layout && isset( $layout->bleed ) ? $layout->bleed : 0;
		$this->margin = $layout && isset( $layout->margin ) ? $layout->margin : 0;

		$this->defaultFont = $layout && isset( $layout->defaultFont ) ? $layout->defaultFont : 'Helvetica';
		$this->background  = $layout && isset( $layout->background ) ? $layout->background : null;
		$this->cropMarks   = $layout && isset( $layout->cropMarks ) ? $layout->cropMarks : false;

		$this->pages   = $layout && isset( $layout->pages ) ? $layout->pages : [ ];

		$this->setImagePath( public_path() );
	}

	public function setImagePath( $path ) {
		$this->imagePath = $path;
	}

	public function getImagePath() {
		return $this->imagePath;
	}

}