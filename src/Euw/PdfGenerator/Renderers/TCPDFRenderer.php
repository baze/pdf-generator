<?php namespace Euw\PdfGenerator\Renderers;

use Illuminate\Support\Facades\File;
use TCPDF;
use TCPDF_FONTS;

class TCPDFRenderer implements PDFRendererInterface {

    private $pdf;
    private $layout;
    private $margin = 0;
    private $bleed = 0;
    public $cropMarks = true;

    public function __construct(TCPDF $pdf)
    {
        $this->pdf = $pdf;
        date_default_timezone_set("Europe/Berlin");
    }

    public function render($layout)
    {
        $this->layout = $layout;

        if ($this->cropMarks) {
            $this->margin = $this->layout->margin;
            $this->bleed = $this->layout->bleed;
        }

        $this->addPage();

        $this->drawBackground();

        if ($this->cropMarks) {
            $this->drawCropMarks();
        }

        $this->writeTextContent();

        return $this;
    }

    private function addPage()
    {
        // for full background image
        $this->pdf->SetAutoPageBreak(false);

        // margins
        $this->pdf->setMargins(0,0,0);
        $this->pdf->SetHeaderMargin(0);
        $this->pdf->SetTopMargin(0);
        $this->pdf->SetFooterMargin(0);

        // disable header and footer
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // add a new Page. P = Portrait, L = Landscape
        $format = $this->layout->width > $this->layout->height ? 'L' : 'P';

        $page_format = [
            'MediaBox' => array('llx' => 0, 'lly' => 0, 'urx' => $this->layout->width + 2 * $this->margin, 'ury' => $this->layout->height + 2 * $this->margin),
            'CropBox'  => array('llx' => 0, 'lly' => 0, 'urx' => $this->layout->width + 2 * $this->margin, 'ury' => $this->layout->height + 2 * $this->margin),
            'BleedBox' => array('llx' => $this->margin - $this->bleed, 'lly' => $this->margin - $this->bleed, 'urx' => $this->layout->width + $this->margin + $this->bleed, 'ury' => $this->layout->height + $this->margin + $this->bleed),
            'TrimBox'  => array('llx' => $this->margin, 'lly' => $this->margin, 'urx' => $this->layout->width + $this->margin, 'ury' => $this->layout->height + $this->margin),
        ];

        $this->pdf->AddPage($format, $page_format);


    }

    private function writeTextContent()
    {
        $fontName = 'Helvetica';

//        $font = 'VWHeadlineOT/VWHeadlineOT-Black.ttf';

        if (isset($font)) {
            $fontfile = $this->getFontsPath() . $font;

            if (File::exists($fontfile)) {

                $path = $this->getFontsPath() . 'converted/';
                File::exists($path) or File::makeDirectory($path, 755, true);

                $fontName = TCPDF_FONTS::addTTFfont(
                    $fontfile,
                    $fonttype = '',
                    $enc = '',
                    $flags = 32,
                    $outpath = $path,
                    $platid = 3,
                    $encid = 1,
                    $addcbbox = false
                );

//            dd($fontName);
            }
        }


        foreach ($this->layout->contents as $content) {
            $contentLayouts = $content->layouts()->where('layout_id', '=', $this->layout->id)->get();

            foreach ($contentLayouts as $contentLayout) {
                $colorString = $contentLayout->color ?: '0,0,0,100';
                $colors = explode(',', $colorString);

                $fontFamily = $contentLayout->fontFamily ?: 'Helvetica';
                $fontFamily = $fontName;
                $fontSize = (float)$contentLayout->fontSize > 0 ? (float)$contentLayout->fontSize : 12.0;

                $this->pdf->SetTextColor($colors[0], $colors[1], $colors[2], $colors[3]);
                $this->pdf->SetFont($fontFamily, '', $fontSize);
                $this->pdf->MultiCell(
                    $w = (float)$contentLayout->width,
                    $h = (float)$contentLayout->height,
                    $txt = $content->content,
                    $border = 0,
                    $align = 'L',
                    $fill = false,
                    $ln = 1,
                    $x = (float)$contentLayout->x + $this->margin,
                    $y = (float)$contentLayout->y + $this->margin,
                    $reseth = true,
                    $stretch = 0,
                    $ishtml = false,
                    $autopadding = true,
                    $maxh = 0,
                    $valign = 'T',
                    $fitcell = false
                );
            }
        }
    }

    private function drawBackground()
    {
        $backgroundImage = public_path() . \Config::get('paths.campaigns.components.print') . $this->layout->pivot->background;

        if ($this->layout->pivot->background && File::exists($backgroundImage)) {
            // get the current page break margin
            $bMargin = $this->pdf->getBreakMargin();

            // get current auto-page-break-mode
            $autoPageBreak = $this->pdf->getAutoPageBreak();

            // disable auto-page-break
            $this->pdf->SetAutoPageBreak(false, 0);

            $this->pdf->Image(
                $backgroundImage,
                $x = $this->margin - $this->bleed,
                $y = $this->margin - $this->bleed,
                $w = $this->layout->width + 2 * $this->bleed,
                $h = $this->layout->height + 2 * $this->bleed,
                $type = '',
                $link = '',
                $align = 'M',
                $resize = false,
                $dpi = 300,
                $palign = 'C',
                $ismask = false,
                $imgmask = false,
                $border = 0,
                $fitbox = false,
                $hidden = false,
                $fitonpage = false,
                $alt = false,
                $altimgs = array());

            // restore auto-page-break status
            $this->pdf->SetAutoPageBreak($autoPageBreak, $bMargin);

            //set the starting point for the page content
            // $this->pdf->setPageMark();
        }
    }

    private function drawCropMarks()
    {
        $pdf = $this->pdf;

        $pdf->cropMark($this->margin, $this->margin, 10, 10, 'TL');
        $pdf->cropMark($this->layout->width + $this->margin, $this->margin, 10, 10, 'TR');
        $pdf->cropMark($this->margin, $this->layout->height + $this->margin, 10, 10, 'BL');
        $pdf->cropMark($this->layout->width + $this->margin, $this->layout->height + $this->margin, 10, 10, 'BR');
    }

    /**
     * output methods:
     * E: return the document as base64 mime multi-part email attachment (RFC 2045)
     * Options: I = display inline, F = output as file, D = download
     */

    private function getFileName() {
        $time = time();
        return $time . '.pdf';
    }

    private function getFilePath()
    {
        return public_path() . \Config::get('paths.output.print');
    }

    private function getFontsPath()
    {
        return public_path() . \Config::get('paths.fonts');
    }

    public function show()
    {
        $fileName = $this->getFileName();
        $this->pdf->Output($fileName, 'I');
    }

    public function download($fileName)
    {
        $this->pdf->Output($fileName, 'D');
    }

    public function attachment()
    {
        // TODO: Implement attachment() method.
    }

    public function saveToFile($fileName)
    {
        $path = $this->getFilePath();

        File::exists($path) or File::makeDirectory($path, 755, true);

        $this->pdf->Output($path . $fileName, 'F');

        return $fileName;
    }
}