<?php namespace Euw\PdfGenerator\Generators;

interface PDFGeneratorInterface {
    public function render($layout, $contents = []);
    public function show();
    public function download($fileName);
    public function attachment($fileName);
    public function toString();
    public function saveToFile($fileName, $path);

    public function setTargetId($id);
}