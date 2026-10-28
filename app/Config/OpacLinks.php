<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi tautan OneSearch dan pangkalan data/repositori untuk OPAC.
 * Ubah URL di sini tanpa perlu menyentuh view.
 */
class OpacLinks extends BaseConfig
{
    /**
     * Tautan OneSearch Indonesia.
     */
    public array $onesearch = [
        'label' => 'OneSearch Indonesia',
        'url'   => 'https://onesearch.id/',
    ];

    /**
     * Kelompok tautan pangkalan data dan repositori.
     *
     * @var array<int, array{title: string, icon: string, links: array<int, array{label: string, url: string, description: string}>}>
     */
    public array $groups = [
        [
            'title' => 'Pangkalan Data Nasional',
            'icon'  => 'fa-landmark',
            'links' => [
                [
                    'label'       => 'OneSearch Indonesia',
                    'url'         => 'https://onesearch.id/',
                    'description' => 'Penelusuran koleksi lintas perpustakaan di Indonesia.',
                ],
                [
                    'label'       => 'Perpustakaan Nasional RI',
                    'url'         => 'https://perpusnas.go.id/',
                    'description' => 'Situs resmi Perpustakaan Nasional Republik Indonesia.',
                ],
                [
                    'label'       => 'OPAC Perpusnas',
                    'url'         => 'https://opac.perpusnas.go.id/',
                    'description' => 'Katalog daring koleksi Perpustakaan Nasional RI.',
                ],
                [
                    'label'       => 'e-Resources Perpusnas',
                    'url'         => 'https://e-resources.perpusnas.go.id/',
                    'description' => 'Akses basis data jurnal dan e-book Perpusnas.',
                ],
            ],
        ],
        [
            'title' => 'Jurnal dan Repositori',
            'icon'  => 'fa-book-open',
            'links' => [
                [
                    'label'       => 'Portal Garuda',
                    'url'         => 'https://garuda.kemdikbud.go.id/',
                    'description' => 'Garba Rujukan Digital: artikel jurnal Indonesia.',
                ],
                [
                    'label'       => 'DOAJ',
                    'url'         => 'https://doaj.org/',
                    'description' => 'Directory of Open Access Journals.',
                ],
                [
                    'label'       => 'Google Scholar',
                    'url'         => 'https://scholar.google.com/',
                    'description' => 'Mesin pencari literatur akademik.',
                ],
                [
                    'label'       => 'Crossref',
                    'url'         => 'https://search.crossref.org/',
                    'description' => 'Pencarian metadata dan DOI publikasi ilmiah.',
                ],
                [
                    'label'       => 'Neliti',
                    'url'         => 'https://www.neliti.com/',
                    'description' => 'Repositori jurnal, buku, dan riset Indonesia.',
                ],
                [
                    'label'       => 'Repositori Kemdikbud',
                    'url'         => 'https://repositori.kemdikbud.go.id/',
                    'description' => 'Repositori karya dan publikasi Kementerian Pendidikan.',
                ],
            ],
        ],
        [
            'title' => 'Perpustakaan Digital',
            'icon'  => 'fa-mobile-screen-button',
            'links' => [
                [
                    'label'       => 'iPusnas',
                    'url'         => 'https://ipusnas.id/',
                    'description' => 'Aplikasi perpustakaan digital Perpusnas.',
                ],
                [
                    'label'       => 'Khastara',
                    'url'         => 'https://khastara.perpusnas.go.id/',
                    'description' => 'Koleksi digital naskah dan budaya Perpusnas.',
                ],
                [
                    'label'       => 'Indonesia OneSearch',
                    'url'         => 'https://onesearch.id/',
                    'description' => 'Agregator koleksi perpustakaan Indonesia.',
                ],
            ],
        ],
    ];
}
