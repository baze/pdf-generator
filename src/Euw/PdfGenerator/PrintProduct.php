<?php namespace Euw\PdfGenerator; 

use Euw\PdfGenerator\Contracts\Printable;
use Illuminate\Database\Eloquent\Model;

class PrintProduct extends Model implements Printable {

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

		if ( \File::exists( $path . $filename ) && ! $overwrite ) {
			return $path . $filename;
		}

		return $this->render()->saveToFile( $filename, $path );
	}

}