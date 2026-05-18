<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FourC;
use App\Models\Dalil;

class RppAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. INPUT DATA BATASAN 4C (Superadmin B1)
        $data4C = [
            [
                'kategori' => 'Creativity',
                'batasan_deskripsi' => 'Siswa harus diarahkan untuk membuat produk fisik kreatif sederhana seperti peta pikiran (mind mapping) siklus air atau sketsa gambar menggunakan bahan daur ulang.'
            ],
            [
                'kategori' => 'Critical Thinking',
                'batasan_deskripsi' => 'Siswa wajib diberikan studi kasus nyata atau pertanyaan pemantik (essential questions) seperti "apa yang terjadi jika air di bumi habis?" untuk melatih penalaran analitis mereka.'
            ],
            [
                'kategori' => 'Communication',
                'batasan_deskripsi' => 'Di akhir sesi, setiap kelompok siswa harus mempresentasikan hasil diskusi mereka di depan kelas atau membuat poster presentasi kelompok yang komunikatif.'
            ],
            [
                'kategori' => 'Collaboration',
                'batasan_deskripsi' => 'Semua aktivitas eksperimen atau praktikum harus dilakukan secara berkelompok (maksimal 4-5 siswa per kelompok) dengan pembagian tugas yang adil.'
            ]
        ];

        foreach ($data4C as $item) {
            FourC::create($item);
        }

        // 2. INPUT DATA DALIL AGAMA (Superadmin B2)
        $dataDalil = [
            [
                'kategori' => 'Al-Quran',
                'referensi' => 'QS. Al-Baqarah: 164',
                'arti' => '...dan apa yang Allah turunkan dari langit berupa air, lalu dengan air itu Dia hidupkan bumi setelah mati (kering)-nya...',
                'deskripsi' => 'Dalil tentang penciptaan siklus air hujan dan kehidupan di bumi.',
                'keyword' => 'siklus air, hujan, bumi, air'
            ],
            [
                'kategori' => 'Al-Quran',
                'referensi' => 'QS. Al-Anbya: 30',
                'arti' => '...Dan dari air Kami jadikan segala sesuatu yang hidup. Maka mengapa mereka tiada juga beriman?',
                'deskripsi' => 'Dalil bahwa air adalah sumber utama segala bentuk kehidupan di bumi.',
                'keyword' => 'sumber kehidupan, air hidup, makhluk hidup'
            ],
            [
                'kategori' => 'Hadis',
                'referensi' => 'HR. Ibnu Majah No. 2472',
                'arti' => 'Kaum muslimin berserikat dalam tiga perkara: air, padang rumput (pakan), dan api.',
                'deskripsi' => 'Hadis tentang kepemilikan bersama sumber daya alam vital termasuk air.',
                'keyword' => 'sosial, air bersih, pelestarian lingkungan'
            ]
        ];

        foreach ($dataDalil as $dalil) {
            Dalil::create($dalil);
        }
    }
}