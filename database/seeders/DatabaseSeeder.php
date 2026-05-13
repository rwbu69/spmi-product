<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Users
        $user = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Admin SPMI',
                'email' => 'login@test.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        $user->assignRole('Administrator');

        // Tahun Periode
        $tahunId = DB::table('tahun_periode')->insertGetId([
            'tahun' => 2025,
            'status' => 'Aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Lembaga Akreditasi
        $lembagaId = DB::table('lembaga_akreditasi')->insertGetId([
            'nama_lembaga' => 'BAN-PT',
            'keterangan' => 'Badan Akreditasi Nasional',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Auditee Pusat
        $pusatId = DB::table('auditee_pusat')->insertGetId([
            'nama' => 'Fakultas Teknik',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Auditee
        $auditeeId1 = DB::table('auditee')->insertGetId([
            'kode' => 'TI-01',
            'nama_auditee' => 'S1 Teknik Informatika',
            'jenjang' => 'S1',
            'auditee_pusat_id' => $pusatId,
            'akreditasi' => 'Unggul',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $auditeeId2 = DB::table('auditee')->insertGetId([
            'kode' => 'TE-01',
            'nama_auditee' => 'S1 Teknik Elektro',
            'jenjang' => 'S1',
            'auditee_pusat_id' => $pusatId,
            'akreditasi' => 'Baik Sekali',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Standar Mutu
        $standarId = DB::table('standar_mutu')->insertGetId([
            'kode' => 'A.1',
            'nama_standar' => 'Visi dan Misi',
            'lembaga_akreditasi_id' => $lembagaId,
            'tahun_periode_id' => $tahunId,
            'level' => 1,
            'deskripsi' => 'Standar Visi Misi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('indikator')->insert([
            'standar_mutu_id' => $standarId,
            'deskripsi' => 'Kejelasan dan Keterkaitan Visi Misi',
            'bobot' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Pengaturan Periode
        $pengaturanId = DB::table('pengaturan_periode')->insertGetId([
            'tahun_periode_id' => $tahunId,
            'lembaga_akreditasi_id' => $lembagaId,
            'status' => 'Aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Target Nilai Mutu
        DB::table('target_nilai_mutu')->insert([
            ['pengaturan_periode_id' => $pengaturanId, 'auditee_id' => $auditeeId1, 'target_nilai' => 88.0, 'created_at' => $now, 'updated_at' => $now],
            ['pengaturan_periode_id' => $pengaturanId, 'auditee_id' => $auditeeId2, 'target_nilai' => 80.0, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Evaluasi Diri & Nilai
        DB::table('evaluasi_diri')->insert([
            ['auditee_id' => $auditeeId1, 'pengaturan_periode_id' => $pengaturanId, 'nilai_evaluasi' => 90.0, 'status' => 'Submitted', 'created_at' => $now, 'updated_at' => $now],
        ]);
        
        DB::table('nilai_mutu')->insert([
            ['auditee_id' => $auditeeId1, 'pengaturan_periode_id' => $pengaturanId, 'lembaga_akreditasi_id' => $lembagaId, 'nilai' => 91.5, 'created_at' => $now, 'updated_at' => $now],
            ['auditee_id' => $auditeeId2, 'pengaturan_periode_id' => $pengaturanId, 'lembaga_akreditasi_id' => $lembagaId, 'nilai' => 82.5, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Auditor & Temuan
        DB::table('auditor')->insert([
            'nama' => 'Dr. John Doe',
            'email' => 'auditor@test.com',
            'keahlian' => 'Sistem Informasi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $jenisTemuanId = DB::table('jenis_temuan')->insertGetId([
            'nama' => 'Observasi', 
            'status' => 'Negatif', 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
        
        DB::table('kategori_temuan')->insert([
            'nama_kategori' => 'Mayor', 
            'jenis_temuan_id' => $jenisTemuanId, 
            'created_at' => $now, 
            'updated_at' => $now
        ]);
    }
}
