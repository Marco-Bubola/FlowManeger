<?php

namespace Tests\Unit;

use App\Livewire\Clients\Concerns\HasClientExtraFields;
use PHPUnit\Framework\TestCase;

class ClientDocumentTest extends TestCase
{
    private function valid(string $doc): bool
    {
        $obj = new class { use HasClientExtraFields; };

        return $obj::validDocument($doc);
    }

    public function test_cpf(): void
    {
        $this->assertTrue($this->valid('529.982.247-25'));
        $this->assertFalse($this->valid('529.982.247-24'));
        $this->assertFalse($this->valid('111.111.111-11'));
    }

    public function test_cnpj(): void
    {
        $this->assertTrue($this->valid('11.222.333/0001-81'));
        $this->assertFalse($this->valid('11.222.333/0001-80'));
    }

    public function test_tamanho_errado(): void
    {
        $this->assertFalse($this->valid('123'));
    }
}
