<?php namespace Euw\PdfGenerator\Layouts;

use Euw\PdfGenerator\Contracts\Layout as LayoutContract;
use Illuminate\Support\Facades\File;

class JsonLayout extends AbstractLayout implements LayoutContract {

	public function __construct( $path ) {

		if ( ! File::exists( $path ) ) {
			throw new \Exception( 'No file found at provided path!' );
		}

		$layout = json_decode( File::get( $path ) );

		parent::__construct($layout);
	}

}