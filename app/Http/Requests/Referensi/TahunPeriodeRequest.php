<?php

namespace App\Http\Requests\Referensi;

use App\Models\TahunPeriode;
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

    /**
     * Additional validation: Only one TahunPeriode may be 'Aktif' at a time.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('status') === 'Aktif') {
                $currentId = $this->route('tahun_periode')?->id;
                $existing = TahunPeriode::where('status', 'Aktif')
                    ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
                    ->exists();

                if ($existing) {
                    $validator->errors()->add(
                        'status',
                        'Sudah ada Tahun Periode lain yang berstatus Aktif. Hanya boleh ada 1 periode aktif.'
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'tahun'  => 'Tahun',
            'status' => 'Status',
        ];
    }
}
