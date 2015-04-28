<?php namespace Euw\PdfGenerator\Layouts;

use Euw\PdfGenerator\Contracts\Layout as LayoutContract;

class PageLayout {
	private $pages = [ ];

	public function __construct( $model, LayoutContract $layout ) {

		foreach ( $layout->pages as $page ) {

			$c = [ ];
			$p = [ ];

			if ( isset( $page->background ) ) {
				$p['background'] = $page->background;
			}

			foreach ( $page->content as $ccontent ) {
				$text = $model->{$ccontent->identifier};
				$c[]  = new ContentLayout( $text, $ccontent->layout );
			}

			$p['content'] = $c;

			$this->pages[] = $p;
		}

	}

	public function getPages() {
		return $this->pages;
	}
}