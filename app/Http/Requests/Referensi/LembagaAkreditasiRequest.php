<?php

namespace App\Http\Requests\Referensi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LembagaAkreditasiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('lembaga_akreditasi')?->id;

        return [
            'nama_lembaga' => ['required', 'string', 'max:255', Rule::unique('lembaga_akreditasi', 'nama_lembaga')->ignore($id)],
            'keterangan'   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['nama_lembaga' => 'Nama Lembaga', 'keterangan' => 'Keterangan'];
    }
}
