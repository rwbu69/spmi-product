<?php

namespace App\Http\Requests\Referensi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitPenunjangRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('unit_penunjang')?->id;

        return [
            'kode'             => ['required', 'string', 'max:50', Rule::unique('unit_penunjang', 'kode')->ignore($id)],
            'nama_unit'        => ['required', 'string', 'max:255'],
            'auditee_pusat_id' => ['required', 'exists:auditee_pusat,id'],
            'jenjang'          => ['nullable', 'string', 'max:100'],
            'alamat'           => ['nullable', 'string'],
            'keterangan'       => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode'             => 'Kode',
            'nama_unit'        => 'Nama Unit',
            'auditee_pusat_id' => 'Auditee Pusat',
            'jenjang'          => 'Jenjang',
        ];
    }
}
