<?php namespace Euw\PdfGenerator\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class PDFGeneratorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		$this->app->bind( 'pdfgenerator', 'Euw\PdfGenerator\Generators\PDFGenerator' );

		$this->app->bind( 'Euw\PdfGenerator\Contracts\Layout', 'Euw\PdfGenerator\Layouts\JsonLayout' );
		$this->app->bind( 'Euw\PdfGenerator\Renderers\PDFRendererInterface', 'Euw\PdfGenerator\Renderers\FPDIRenderer' );
	}

	public function boot() {
		AliasLoader::getInstance()->alias( 'PDFGenerator', 'Euw\PdfGenerator\Facades\PDFGenerator' );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return array();
	}

}
