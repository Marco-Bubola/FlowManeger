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
    public $avatarOptions;

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

        // Define opções de avatares com Avataaars
        $this->avatarOptions = [
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairShortFlat&hairColor=Brown&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Light',
            'https://avataaars.io/?avatarStyle=Circle&topType=LongHairStraight&hairColor=Black&clotheType=Hoodie&eyeType=Happy&eyebrowType=RaisedExcited&mouthType=Smile&skinColor=Brown',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairDreads01&hairColor=Blonde&clotheType=GraphicShirt&eyeType=Squint&eyebrowType=Default&mouthType=Default&skinColor=DarkBrown',
            'https://avataaars.io/?avatarStyle=Circle&topType=LongHairCurly&hairColor=Red&clotheType=BlazerSweater&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Black',
            'https://avataaars.io/?avatarStyle=Circle&topType=NoHair&clotheType=ShirtCrewNeck&eyeType=Happy&eyebrowType=RaisedExcited&mouthType=Smile&skinColor=Tanned',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairShortWaved&hairColor=SilverGray&clotheType=Hoodie&eyeType=Squint&eyebrowType=Default&mouthType=Default&skinColor=Yellow',
            'https://avataaars.io/?avatarStyle=Circle&topType=LongHairStraight2&hairColor=Auburn&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Pale',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairFrizzle&hairColor=Brown&clotheType=ShirtCrewNeck&eyeType=Happy&eyebrowType=RaisedExcited&mouthType=Smile&skinColor=Light',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairShortCurly&hairColor=Black&clotheType=Hoodie&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=DarkBrown',
            'https://avataaars.io/?avatarStyle=Circle&topType=LongHairCurvy&hairColor=Red&clotheType=BlazerSweater&eyeType=Happy&eyebrowType=RaisedExcited&mouthType=Smile&skinColor=Brown',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairTheCaesar&hairColor=SilverGray&clotheType=ShirtCrewNeck&eyeType=Squint&eyebrowType=Default&mouthType=Default&skinColor=Black',
            'https://avataaars.io/?avatarStyle=Circle&topType=LongHairBigHair&hairColor=Blonde&clotheType=GraphicShirt&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Yellow',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairSides&hairColor=Auburn&clotheType=BlazerShirt&eyeType=Happy&eyebrowType=RaisedExcited&mouthType=Smile&skinColor=Pale',
            'https://avataaars.io/?avatarStyle=Circle&topType=LongHairMiaWallace&hairColor=Brown&clotheType=Hoodie&eyeType=Squint&eyebrowType=Default&mouthType=Default&skinColor=Tanned',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairRound&hairColor=Black&clotheType=BlazerSweater&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Light',
            'https://avataaars.io/?avatarStyle=Circle&topType=LongHairStraightStrand&hairColor=Red&clotheType=ShirtCrewNeck&eyeType=Happy&eyebrowType=RaisedExcited&mouthType=Smile&skinColor=Brown',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairDreads02&hairColor=SilverGray&clotheType=GraphicShirt&eyeType=Squint&eyebrowType=Default&mouthType=Default&skinColor=DarkBrown',
            'https://avataaars.io/?avatarStyle=Circle&topType=LongHairNotTooLong&hairColor=Blonde&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Black',
            'https://avataaars.io/?avatarStyle=Circle&topType=ShortHairShortFlat&hairColor=Brown&clotheType=Hoodie&eyeType=Happy&eyebrowType=RaisedExcited&mouthType=Smile&skinColor=Yellow',
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
