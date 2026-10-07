<?php

namespace App\Livewire\Clients\Concerns;

use App\Models\Client;

/**
 * Campos extras do cliente (documento, aniversário e endereço por partes)
 * que antes só o próprio cliente preenchia pelo portal.
 */
trait HasClientExtraFields
{
    public string $cpf_cnpj = '';
    public string $birth_date = '';
    public string $company = '';
    public string $cep = '';
    public string $street = '';
    public string $number = '';
    public string $complement = '';
    public string $neighborhood = '';
    public string $city = '';
    public string $state = '';

    protected function extraFieldRules(): array
    {
        return [
            'cpf_cnpj' => ['nullable', 'string', 'max:18', function ($attribute, $value, $fail) {
                if ($value !== '' && $value !== null && ! self::validDocument($value)) {
                    $fail('CPF ou CNPJ inválido.');
                }
            }],
            'birth_date' => 'nullable|date|before:today',
            'company' => 'nullable|string|max:255',
            'cep' => 'nullable|regex:/^\d{5}-?\d{3}$/',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
        ];
    }

    protected function extraFieldMessages(): array
    {
        return [
            'birth_date.before' => 'A data de nascimento deve ser no passado.',
            'cep.regex' => 'Informe o CEP no formato 00000-000.',
        ];
    }

    protected function fillExtraFields(Client $client): void
    {
        foreach (array_keys($this->extraFieldRules()) as $field) {
            $value = $client->{$field};
            $this->{$field} = $field === 'birth_date' && $value
                ? \Carbon\Carbon::parse($value)->format('Y-m-d')
                : (string) ($value ?? '');
        }
    }

    protected function extraFieldData(): array
    {
        $data = [];
        foreach (array_keys($this->extraFieldRules()) as $field) {
            $value = trim((string) $this->{$field});
            $data[$field] = $value === '' ? null : ($field === 'state' ? mb_strtoupper($value) : $value);
        }

        return $data;
    }

    public static function validDocument(string $value): bool
    {
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) === 11) {
            if (preg_match('/^(\d)\1{10}$/', $digits)) {
                return false;
            }
            for ($t = 9; $t < 11; $t++) {
                $sum = 0;
                for ($i = 0; $i < $t; $i++) {
                    $sum += $digits[$i] * (($t + 1) - $i);
                }
                if ((int) $digits[$t] !== ((10 * $sum) % 11) % 10) {
                    return false;
                }
            }

            return true;
        }

        if (strlen($digits) === 14) {
            if (preg_match('/^(\d)\1{13}$/', $digits)) {
                return false;
            }
            foreach ([12, 13] as $t) {
                $weights = $t === 12 ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2] : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
                $sum = 0;
                for ($i = 0; $i < $t; $i++) {
                    $sum += $digits[$i] * $weights[$i];
                }
                $check = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
                if ((int) $digits[$t] !== $check) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
