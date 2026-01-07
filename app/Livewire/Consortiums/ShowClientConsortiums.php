<?php

namespace App\Livewire\Consortiums;

use Livewire\Component;
use App\Models\Client;
use App\Models\ConsortiumParticipant;
use Livewire\Attributes\Computed;

class ShowClientConsortiums extends Component
{
    public $clientId;
    public $showExportModal = false;

    protected $listeners = ['payment-recorded' => '$refresh'];

    public function mount(Client $client)
    {
        $this->clientId = $client->id;
    }

    #[Computed]
    public function client()
    {
        return Client::findOrFail($this->clientId);
    }

    #[Computed]
    public function participations()
    {
        return ConsortiumParticipant::with([
            'consortium',
            'payments' => function ($query) {
                $query->orderBy('due_date', 'asc');
            },
            'contemplation.draw'
        ])
        ->where('client_id', $this->clientId)
        ->orderBy('entry_date', 'desc')
        ->get();
    }

    public function render()
    {
        return view('livewire.consortiums.show-client-consortiums');
    }
}
