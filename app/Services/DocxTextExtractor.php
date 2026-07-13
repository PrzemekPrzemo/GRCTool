<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Wyciąga zwykły tekst z pliku .docx bez zewnętrznych zależności — .docx to
 * archiwum ZIP zawierające word/document.xml z akapitami <w:p> i fragmentami
 * tekstu <w:t>. Zachowuje podział na akapity; nie zachowuje formatowania
 * (pogrubienia, tabel jako tabel, list numerowanych) — wystarczające do
 * wypełnienia pola opisu polityki/procedury tekstem źródłowym.
 */
class DocxTextExtractor
{
    public function extract(string $absolutePath): string
    {
        $zip = new ZipArchive;
        if ($zip->open($absolutePath) !== true) {
            throw new RuntimeException('Nie udało się otworzyć pliku .docx (uszkodzony lub to nie jest archiwum ZIP).');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('Plik nie zawiera word/document.xml — to nie jest prawidłowy dokument .docx.');
        }

        // Koniec akapitu i twarde entery -> znak nowej linii, zanim usuniemy tagi.
        $xml = str_replace('</w:p>', "</w:p>\n", $xml);
        $xml = preg_replace('/<w:br\s*\/?>/', "\n", $xml);

        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $lines = array_map('trim', explode("\n", $text));
        $lines = array_values(array_filter($lines, fn (string $line): bool => $line !== ''));

        return implode("\n\n", $lines);
    }
}
