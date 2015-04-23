<?php namespace Euw\PdfGenerator\Traits;

class PageLayout {

	public $layout;

	public $width;
	public $height;
	public $margin;
	public $bleed;
	public $cropMarks;
	public $imagePath;
	public $defaultFont;

	public function __construct( $layout = null ) {

		$this->layout = $layout;

		$this->width  = $layout && isset( $layout->width ) ? $layout->width : 210;
		$this->height = $layout && isset( $layout->height ) ? $layout->height : 297;
		$this->bleed  = $layout && isset( $layout->bleed ) ? $layout->bleed : 0;
		$this->margin = $layout && isset( $layout->margin ) ? $layout->margin : 0;

		$this->defaultFont     = $layout && isset( $layout->defaultFont ) ? $layout->defaultFont : 'Helvetica';
		$this->backgroundImage = $layout && isset( $layout->backgroundImage ) ? $layout->backgroundImage : null;
		$this->cropMarks       = $layout && isset( $layout->cropMarks ) ? $layout->cropMarks : false;

		$this->setImagePath(public_path());
	}

	public function setImagePath( $path ) {
		$this->imagePath = $path;

		if ( $this->layout && isset( $this->layout->background ) ) {
			$this->backgroundImage = $path . $this->layout->background;
		}
	}

	public function getLayout() {
		return $this->layout;
	}
}

class Content {
	public $text;
	public $layout;

	function __construct( $text, $layout ) {
		$this->text   = $text;
		$this->layout = $layout;
	}
}

class ContentLayout {
	private $content;
	private $layout;

	public function __construct( $content, PageLayout $layout ) {
		$this->content = $content;
		$this->layout  = $layout->getLayout();
	}

	public function contents() {
		$content = [ ];

		if ( $this->layout ) {
			foreach ( $this->layout->contents as $c ) {
				$text      = $this->content->{$c->identifier};
				$content[] = new Content( $text, $c->layout );
			}
		}

		return $content;
	}
}

trait PrintableTrait {

	public function getPageLayout() {
		$layout     = $this->getLayout();
		$pageLayout = new PageLayout( $layout );

		return $pageLayout;
	}

	public function render() {
		$pageLayout = $this->getPageLayout();
		$pageLayout->setImagePath( $this->getImagePath() );

		$contentLayout = new ContentLayout( $this, $pageLayout );
		$contents      = $contentLayout->contents();

		$generator = app()->make( 'Euw\PdfGenerator\Generators\PDFGenerator' );

		return $generator->render( $pageLayout, $contents );
	}

	public function show() {
		$this->render()->show();
	}

	public function download( $filename = 'coupon.pdf' ) {
		$this->render()->download( $filename );
	}

	public function attachment( $filename = 'coupon.pdf' ) {
		return $this->render()->attachment( $filename );
	}

	public function toString() {
		return $this->render()->toString();
	}

	public function getFile( $filename = 'coupon.pdf' ) {

		$path = $this->getOutputPath();

		if ( File::exists( $path . $filename ) ) {
			return $path . $filename;
		}

		return $this->render()->saveToFile( $filename, $path );
	}

}