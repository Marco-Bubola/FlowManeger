<?php

namespace Tests\Feature;

use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class FlashToNotifyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        request()->headers->set('X-Livewire', '1');
    }

    public function test_flash_sem_alerta_na_tela_vira_aviso_global(): void
    {
        Livewire::test(FlashToNotifyFixture::class)
            ->call('save')
            ->assertDispatched('notify', type: 'success', message: 'Salvo com sucesso!');
    }

    public function test_nao_duplica_quando_a_tela_ja_mostra_o_alerta(): void
    {
        Livewire::test(FlashToNotifyFixture::class, ['inline' => true])
            ->call('save')
            ->assertNotDispatched('notify');
    }

    public function test_com_redirect_a_mensagem_fica_para_a_proxima_pagina(): void
    {
        Livewire::test(FlashToNotifyFixture::class)
            ->call('go')
            ->assertNotDispatched('notify')
            ->assertRedirect('/x');
    }
}

class FlashToNotifyFixture extends Component
{
    public bool $inline = false;

    public function save(): void
    {
        session()->flash('success', 'Salvo com sucesso!');
    }

    public function go()
    {
        session()->flash('success', 'Ok');

        return $this->redirect('/x');
    }

    public function render()
    {
        return $this->inline
            ? '<div>{{ session("success") }}</div>'
            : '<div>lista</div>';
    }
}
