<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Shared helpers
|--------------------------------------------------------------------------
*/

/**
 * Buduje minimalny, ale prawdziwie ważny plik .docx (ZIP z word/document.xml,
 * [Content_Types].xml i _rels/.rels — wystarczające, by wykrywanie MIME po
 * zawartości pliku poprawnie rozpoznało go jako .docx, nie tylko zwykły ZIP)
 * do testowania DocxTextExtractor i walidacji "mimes:docx" bez zależności od
 * zewnętrznych fixture'ów.
 */
function makeDocxFile(string $bodyXml, string $filename = 'test.docx'): UploadedFile
{
    $tmpPath = tempnam(sys_get_temp_dir(), 'docx').'.docx';
    $zip = new ZipArchive;
    $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        .'<Default Extension="xml" ContentType="application/xml"/>'
        .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
        .'</Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
        .'</Relationships>');

    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
        .$bodyXml
        .'</w:body></w:document>';
    $zip->addFromString('word/document.xml', $xml);
    $zip->close();

    return new UploadedFile($tmpPath, $filename, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
}

/** @param  array<int,string>  $paragraphs */
function docxParagraphs(array $paragraphs): string
{
    return implode('', array_map(
        fn (string $p): string => '<w:p><w:r><w:t>'.htmlspecialchars($p, ENT_XML1).'</w:t></w:r></w:p>',
        $paragraphs
    ));
}
