<?php namespace Euw\PdfGenerator\Generators;

use Euw\PdfGenerator\Renderers\PDFRendererInterface;

class PDFGenerator implements PDFGeneratorInterface {

    /**
     * @var PDFRendererInterface
     */
    private $renderer;

    public function __construct(PDFRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render($layout, $contents = [])
    {
        return $this->renderer->render($layout, $contents);
    }

    public function show()
    {
        return $this->renderer->show();
    }

    public function download($fileName)
    {
        return $this->renderer->download($fileName);
    }

    public function attachment($fileName = 'coupon.pdf')
    {
        return $this->renderer->attachment($fileName);
    }

    public function toString()
    {
        return $this->renderer->toString();
    }

    public function saveToFile($fileName, $path)
    {
        return $this->renderer->saveToFile($fileName, $path);
    }

    public function setTargetId($id)
    {
        $this->renderer->setTargetId($id);
    }
}