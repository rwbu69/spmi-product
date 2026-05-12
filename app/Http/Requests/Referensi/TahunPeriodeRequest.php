<?php

namespace App\Http\Requests\Referensi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TahunPeriodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('tahun_periode')?->id;

        return [
            'tahun'  => ['required', 'integer', 'min:2000', 'max:2100', Rule::unique('tahun_periode', 'tahun')->ignore($id)],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'tahun'  => 'Tahun',
            'status' => 'Status',
        ];
    }
}
