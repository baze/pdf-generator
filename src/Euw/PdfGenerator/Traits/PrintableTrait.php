<?php namespace Euw\PdfGenerator\Traits;

use Euw\PdfGenerator\Contracts\Layout;
use Euw\PdfGenerator\Layouts\PageLayout;
use Illuminate\Support\Facades\File;

trait PrintableTrait {

	protected $layout;

	public function __construct(Layout $layout) {
		$this->layout = $layout;
	}

	public function render() {

		$this->layout->setImagePath( $this->getImagePath() );

		$contentLayout = new PageLayout( $this, $this->layout );

		$generator = app()->make( 'Euw\PdfGenerator\Generators\PDFGenerator' );

		return $generator->render( $this->layout, $contentLayout->getPages() );
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

	public function getFile( $filename = 'coupon.pdf', $overwrite = false ) {

		$path = $this->getOutputPath();

		if ( File::exists( $path . $filename) && ! $overwrite ) {
			return $path . $filename;
		}

		return $this->render()->saveToFile( $filename, $path );
	}

}