<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditClient extends Component
{
    public Client $client;
    
    // Propriedades do formulário
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $avatar_cliente = '';

    // Lista de avatares predefinidos
    protected $avatarOptions;

    public function mount(Client $client): void
    {
        // Verificar se o cliente pertence ao usuário autenticado
        if ($client->user_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }

        $this->client = $client;
        $this->name = $client->name;
        $this->email = $client->email ?? '';
        $this->phone = $client->phone ?? '';
        $this->address = $client->address ?? '';
        $this->avatar_cliente = $client->caminho_foto ?? '';

        // Define opções de avatares (você pode personalizar essas URLs)
        $this->avatarOptions = [
            'https://ui-avatars.com/api/?name=User&background=6366f1&color=fff&size=100',
            'https://ui-avatars.com/api/?name=Client&background=059669&color=fff&size=100',
            'https://ui-avatars.com/api/?name=Person&background=dc2626&color=fff&size=100',
            'https://ui-avatars.com/api/?name=Customer&background=7c3aed&color=fff&size=100',
            'https://ui-avatars.com/api/?name=User&background=ea580c&color=fff&size=100',
            'https://ui-avatars.com/api/?name=Client&background=0891b2&color=fff&size=100',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'avatar_cliente' => 'required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'phone.max' => 'O telefone não pode ter mais de 15 caracteres.',
            'avatar_cliente.required' => 'Selecione um avatar para o cliente.',
            'avatar_cliente.url' => 'O avatar deve ser uma URL válida.',
        ];
    }

    public function update(): void
    {
        // Valida os dados
        $validated = $this->validate();

        // Atualiza o cliente
        $this->client->update([
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'caminho_foto' => $this->avatar_cliente,
        ]);

        // Emite evento para atualizar a lista
        $this->dispatch('client-updated');

        // Flash message e redirecionamento
        session()->flash('success', 'Cliente atualizado com sucesso!');
        
        $this->redirect(route('clients.index'), navigate: true);
    }

    public function getAvatarOptionsProperty()
    {
        return $this->avatarOptions;
    }

    public function render()
    {
        return view('livewire.clients.edit-client');
    }
}
