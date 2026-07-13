<?php

namespace App\Services;

use App\Models\EvidenceObject;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Zapisuje wgrany plik na dysku prywatnym ('local' — brak publicznego URL,
 * dostęp wyłącznie przez kontroler z autoryzacją) i tworzy dla niego
 * EvidenceObject ze źródłem 'upload'. Powiązanie z konkretnym rekordem
 * (polityką, procedurą...) tworzy wywołujący przez EvidenceLink — ten
 * serwis odpowiada wyłącznie za sam plik.
 */
class EvidenceUploadService
{
    public function store(UploadedFile $file, string $directory, ?string $title = null): EvidenceObject
    {
        $uuid = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension();
        $filename = $uuid.($extension ? '.'.$extension : '');
        $storagePath = $file->storeAs($directory, $filename, 'local');

        return EvidenceObject::create([
            'uuid' => $uuid,
            'title' => $title ?: $file->getClientOriginalName(),
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $storagePath,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'source' => 'upload',
            'uploaded_by' => auth()->id(),
        ]);
    }
}
