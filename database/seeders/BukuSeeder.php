<?php

namespace Database\Seeders;

use App\Models\Buku;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
    {
        // Data buku umum berbagai bidang Eropa
        $bukuData = [
            // Politik Eropa
            [
                'kode_buku' => 'EU-POL-001',
                'judul' => 'Sejarah Politik Eropa Modern',
                'penulis' => 'David Thomson',
                'penerbit' => 'Cambridge University Press',
                'tahun_terbit' => 2020,
                'stock' => 8,
                'kategori' => 'Buku'
            ],
            [
                'kode_buku' => 'EU-POL-002',
                'judul' => 'Uni Eropa: Struktur dan Kebijakan',
                'penulis' => 'John Peterson',
                'penerbit' => 'Oxford University Press',
                'tahun_terbit' => 2021,
                'stock' => 6,
                'kategori' => 'Buku'
            ],

            // Kehidupan dan Budaya
            [
                'kode_buku' => 'EU-LIFE-001',
                'judul' => 'Budaya dan Masyarakat Eropa Kontemporer',
                'penulis' => 'Monica Sassatelli',
                'penerbit' => 'Routledge',
                'tahun_terbit' => 2019,
                'stock' => 7,
                'kategori' => 'Buku'
            ],
            [
                'kode_buku' => 'EU-LIFE-002',
                'judul' => 'Kehidupan Sehari-hari di Eropa',
                'penulis' => 'Eva Garrosa',
                'penerbit' => 'Penguin Books',
                'tahun_terbit' => 2022,
                'stock' => 5,
                'kategori' => 'Buku'
            ],

            // Sejarah Eropa
            [
                'kode_buku' => 'EU-HIS-001',
                'judul' => 'Sejarah Eropa Abad 20',
                'penulis' => 'Eric Hobsbawm',
                'penerbit' => 'Vintage Books',
                'tahun_terbit' => 2018,
                'stock' => 4,
                'kategori' => 'Buku'
            ],

            // Ekonomi Eropa
            [
                'kode_buku' => 'EU-ECO-001',
                'judul' => 'Ekonomi Negara-Negara Eropa',
                'penulis' => 'Andrea Boltho',
                'penerbit' => 'Springer',
                'tahun_terbit' => 2021,
                'stock' => 3,
                'kategori' => 'Buku'
            ],

            // Majalah tentang Eropa
            [
                'kode_buku' => 'EU-MAG-001',
                'judul' => 'European Affairs Monthly',
                'penulis' => 'Redaksi European Review',
                'penerbit' => 'European Media Group',
                'tahun_terbit' => 2024,
                'stock' => 10,
                'kategori' => 'Majalah'
            ],

            // Film dokumenter
            [
                'kode_buku' => 'EU-FILM-001',
                'judul' => 'Europa: A Continent Revealed',
                'penulis' => 'BBC Documentary',
                'penerbit' => 'BBC Worldwide',
                'tahun_terbit' => 2022,
                'stock' => 2,
                'kategori' => 'Film'
            ],

            // Seni dan Arsitektur
            [
                'kode_buku' => 'EU-ART-001',
                'judul' => 'Arsitektur Eropa Klasik',
                'penulis' => 'Henry Russell',
                'penerbit' => 'Thames & Hudson',
                'tahun_terbit' => 2020,
                'stock' => 6,
                'kategori' => 'Buku'
            ],

            // Sastra Eropa
            [
                'kode_buku' => 'EU-LIT-001',
                'judul' => 'Masterpieces of European Literature',
                'penulis' => 'Sarah Lawall',
                'penerbit' => 'Norton & Company',
                'tahun_terbit' => 2019,
                'stock' => 5,
                'kategori' => 'Buku'
            ]
        ];

        // Insert data ke database
        foreach ($bukuData as $buku) {
            Buku::create($buku);
        }
    }
}
