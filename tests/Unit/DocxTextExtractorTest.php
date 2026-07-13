<?php

use App\Services\DocxTextExtractor;

it('extracts plain text from a .docx file preserving paragraph breaks', function (): void {
    $file = makeDocxFile(docxParagraphs(['Pierwszy akapit.', 'Drugi akapit z więcej treści.']));

    $text = (new DocxTextExtractor)->extract($file->getRealPath());

    expect($text)->toBe("Pierwszy akapit.\n\nDrugi akapit z więcej treści.");
});

it('collapses adjacent text runs within the same paragraph without inserting breaks', function (): void {
    $body = '<w:p><w:r><w:t>Hello </w:t></w:r><w:r><w:t>world</w:t></w:r></w:p>';
    $file = makeDocxFile($body);

    $text = (new DocxTextExtractor)->extract($file->getRealPath());

    expect($text)->toBe('Hello world');
});

it('converts <w:br/> line breaks within a paragraph to newlines', function (): void {
    $body = '<w:p><w:r><w:t>Linia 1</w:t></w:r><w:br/><w:r><w:t>Linia 2</w:t></w:r></w:p>';
    $file = makeDocxFile($body);

    $text = (new DocxTextExtractor)->extract($file->getRealPath());

    expect($text)->toBe("Linia 1\n\nLinia 2");
});

it('throws for a file that is not a valid zip archive', function (): void {
    $tmpPath = tempnam(sys_get_temp_dir(), 'notdocx');
    file_put_contents($tmpPath, 'not a zip file at all');

    expect(fn () => (new DocxTextExtractor)->extract($tmpPath))->toThrow(RuntimeException::class);
});

it('throws for a valid zip archive missing word/document.xml', function (): void {
    $tmpPath = tempnam(sys_get_temp_dir(), 'emptyzip').'.zip';
    $zip = new ZipArchive;
    $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('readme.txt', 'hello');
    $zip->close();

    expect(fn () => (new DocxTextExtractor)->extract($tmpPath))->toThrow(RuntimeException::class);
});
