<?php namespace Euw\PdfGenerator\Layouts;

class ContentLayout {
	public $text;
	public $layout;

	function __construct( $text, $layout ) {
		$this->text   = $text;
		$this->layout = $layout;
	}
}