<?php namespace Euw\PdfGenerator\Contracts; 

use App\User;

interface Printable {

	public function attachment( $filename );
	public function download( $filename );
	public function getFile();
	public function getOutputPath();
	public function render();
	public function show();
	public function toString();

	// implement
	public function getLayout();
	public function personalize( User $user );

}