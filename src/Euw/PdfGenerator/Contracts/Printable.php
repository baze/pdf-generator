<?php namespace Euw\PdfGenerator\Contracts; 

use App\User;

interface Printable {

	public function attachment( $filename = 'coupon.pdf' );
	public function download( $filename = 'coupon.pdf' );
	public function getFile( $filename = 'coupon.pdf', $overwrite = false );
	public function render();
	public function show();
	public function toString();

	// implement
	public function getLayout();
	public function getImagePath();
	public function getOutputPath();
	public function personalize( User $user );

}