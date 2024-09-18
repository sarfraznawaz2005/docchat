<?php

namespace Package\DocTalk\Traits;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Package\DocTalk\Models\Document;
use Package\DocTalk\Services\LLMUtilities;

trait UploadDocs
{
    use WithFileUploads;

    #[Validate(['files.*' => 'mimes:pdf'])]
    public array $files = [];

    public function saveDocs(): void
    {
        $this->validate([
            //'files.*' => 'mimes:pdf',
            'files' => 'required|max_combined_size:25600', // Custom rule for combined size
        ], [
            'files.max_combined_size' => 'The total size of the files must not exceed 25 MB.',
        ]);

        $this->validate([
            'files.*' => 'mimes:pdf',
        ]);

        // Combine existing bot files with newly uploaded files
        $totalSize = 0;

        foreach ($this->files as $file) {
            $totalSize += $file->getSize();
        }

        // 25 MB limit in bytes (25 * 1024 * 1024)
        if ($totalSize > 25 * 1024 * 1024) {
            $this->addError('files', 'The total size of the files must not exceed 25 MB.');
            return;
        }

        $this->stream(
            to: 'liveUpdateFiles',
            content: 'Saving files...',
            replace: true,
        );

        foreach ($this->files as $file) {
            $name = strtolower(Str::kebab($file->getClientOriginalName()));

            if (file_exists(storage_path('app/files/' . $name))) {
                continue;
            }

            config(['livewire.temporary_file_upload.rules' => ['max:' . config('doctalk.max_files_upload_size', 25600)]]);

            $file->storeAs(path: 'files', name: $name);

            $this->stream(
                to: 'liveUpdateFiles',
                content: "'$name' uploaded...",
                replace: true,
            );
        }

        $this->stream(
            to: 'liveUpdateFiles',
            content: 'Storing file contents in database...',
            replace: true,
        );

        $files = glob(storage_path('app/private/files/*.{pdf,PDF}'), GLOB_BRACE);

        try {

            $this->resetErrorBag();
            $this->resetValidation();

            @File::makeDirectory(storage_path('app/private/files/processed/'));

            foreach ($files as $file) {

                $name = basename($file);

                $this->stream(
                    to: 'liveUpdateFiles',
                    content: "Extracting text from '$name'...",
                    replace: true,
                );

                $texts = LLMUtilities::extractTextFromFile($file, 2000);

                $this->stream(
                    to: 'liveUpdateFiles',
                    content: "Storing text chunks for '$name' in database...",
                    replace: true,
                );

                $texts = LLMUtilities::getTextsWithEmbeddings($texts);

                Document::saveTexts($texts);

                @rename($file, storage_path('app/private/files/processed/' . $name));
            }

            $this->resetForm();

            $this->dispatch('close-dialog');
            $this->dispatch('files-uploaded');

            $this->dispatch('flashMessage', [
                'message' => 'Files uploaded successfully.',
                'type' => 'success'
            ]);

        } catch (Exception $e) {
            $this->addError('files', 'Error: ' . $e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->reset('files');
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
