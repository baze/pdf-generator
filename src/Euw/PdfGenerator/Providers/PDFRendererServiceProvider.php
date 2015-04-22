<?php namespace Euw\PdfGenerator\Providers;

use Illuminate\Support\ServiceProvider;

class PDFRendererServiceProvider extends ServiceProvider{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->bind('Euw\PdfGenerator\Renderers\PDFRendererInterface', 'Euw\PdfGenerator\Renderers\TCPDFRenderer');
        $this->app->bind('Euw\PdfGenerator\Renderers\PDFRendererInterface', 'Euw\PdfGenerator\Renderers\FPDIRenderer');
    }
}