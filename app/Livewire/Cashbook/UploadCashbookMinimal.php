<?php

namespace App\Livewire\Cashbook;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadCashbookMinimal extends Component
{
    use WithFileUploads;

    public $file;
    public $message = '';

    public function mount(): void
    {
        Log::info('UploadCashbookMinimal mounted', ['user_id' => Auth::id()]);
    }

    public function updatedFile(): void
    {
        Log::info('UploadCashbookMinimal updatedFile', [
            'user_id' => Auth::id(),
            'has_file' => (bool) $this->file,
            'class' => is_object($this->file) ? get_class($this->file) : gettype($this->file),
        ]);
    }

    public function submit(): void
    {
        $this->resetValidation();

        if (!$this->file) {
            $this->addError('file', 'Selecione um arquivo.');
            return;
        }

        Log::info('UploadCashbookMinimal submit', [
            'user_id' => Auth::id(),
            'name' => $this->file->getClientOriginalName(),
            'ext' => $this->file->getClientOriginalExtension(),
            'size' => $this->file->getSize(),
        ]);

        $this->message = 'Arquivo recebido: ' . $this->file->getClientOriginalName();
    }

    public function render()
    {
        return view('livewire.cashbook.upload-cashbook-minimal');
    }
}
