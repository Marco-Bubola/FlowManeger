<?php

namespace App\Livewire\Clients\Concerns;

use App\Models\Client;
use App\Models\ClientTag;
use App\Services\Clients\ClientStatus;
use Illuminate\Support\Facades\Auth;

/**
 * Origem e etiquetas do cliente no cadastro (criar e editar).
 * A tela usa <x-client-crm-fields />.
 */
trait HasClientCrmFields
{
    public string $source = '';
    public array $tagIds = [];
    public string $newTagName = '';
    public string $newTagColor = 'indigo';

    protected function crmRules(): array
    {
        return [
            'source' => 'nullable|in:' . implode(',', array_keys(ClientStatus::SOURCES)),
            'tagIds' => 'array',
            'tagIds.*' => 'integer',
        ];
    }

    protected function fillCrmFields(Client $client): void
    {
        $this->source = (string) ($client->source ?? '');
        $this->tagIds = $client->tags()->where('client_tags.user_id', Auth::id())->pluck('client_tags.id')->map(fn ($v) => (int) $v)->all();
    }

    protected function crmData(): array
    {
        return ['source' => $this->source !== '' ? $this->source : null];
    }

    protected function saveCrmTags(Client $client): void
    {
        $valid = ClientTag::where('user_id', Auth::id())->whereIn('id', $this->tagIds)->pluck('id')->all();
        $client->tags()->sync($valid);
    }

    public function toggleTag(int $id): void
    {
        $this->tagIds = in_array($id, $this->tagIds, true)
            ? array_values(array_diff($this->tagIds, [$id]))
            : array_merge($this->tagIds, [$id]);
    }

    public function addTag(): void
    {
        $name = trim($this->newTagName);
        if ($name === '') {
            return;
        }
        $name = mb_substr($name, 0, 40);
        $tag = ClientTag::where('user_id', Auth::id())->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? ClientTag::create(['user_id' => Auth::id(), 'name' => $name, 'color' => in_array($this->newTagColor, ClientTag::COLORS, true) ? $this->newTagColor : 'indigo']);
        if (! in_array($tag->id, $this->tagIds, true)) {
            $this->tagIds[] = $tag->id;
        }
        $this->newTagName = '';
    }

    public function getAllTagsProperty()
    {
        return ClientTag::where('user_id', Auth::id())->orderBy('name')->get();
    }
}
