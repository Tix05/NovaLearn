<?php

namespace App\Service;

use Smalot\PdfParser\Parser;

class PdfExtractorService
{
    private $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function extractText(string $filePath): string
    {
        try {
            $pdf = $this->parser->parseFile($filePath);
            return $pdf->getText();
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to extract PDF content: ' . $e->getMessage());
        }
    }
}