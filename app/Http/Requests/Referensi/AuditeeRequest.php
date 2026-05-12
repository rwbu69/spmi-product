<?php

namespace App\Http\Requests\Referensi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('auditee')?->id;

        return [
            'kode'             => ['required', 'string', 'max:50', Rule::unique('auditee', 'kode')->ignore($id)],
            'nama_auditee'     => ['required', 'string', 'max:255'],
            'jenjang'          => ['required', 'string', 'max:100'],
            'auditee_pusat_id' => ['required', 'exists:auditee_pusat,id'],
            'alamat'           => ['nullable', 'string'],
            'akreditasi'       => ['nullable', Rule::in(['A', 'B', 'C', 'Baik', 'Baik Sekali', 'Unggul'])],
            'sk_no'            => ['nullable', 'string', 'max:255'],
            'sk_tanggal'       => ['nullable', 'date'],
            'sk_file'          => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode'             => 'Kode',
            'nama_auditee'     => 'Nama Auditee',
            'jenjang'          => 'Jenjang',
            'auditee_pusat_id' => 'Auditee Pusat',
            'akreditasi'       => 'Akreditasi',
            'sk_no'            => 'Nomor SK',
            'sk_tanggal'       => 'Tanggal SK',
            'sk_file'          => 'File SK (PDF)',
        ];
    }
}
