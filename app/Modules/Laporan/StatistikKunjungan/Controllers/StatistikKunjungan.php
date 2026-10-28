<?php

namespace StatistikKunjungan\Controllers;

class StatistikKunjungan extends \Base\Controllers\BaseController
{
    public function index()
    {
        $model = new \StatistikKunjungan\Models\StatistikKunjunganModel();

        $tahunTersedia = $model->getAvailableYears();
        if (empty($tahunTersedia)) {
            $tahunTersedia = [(int) date('Y')];
        }

        $tahunSekarang = (int) date('Y');
        $tahun = (int) ($this->request->getGet('year') ?? $tahunSekarang);
        if (!in_array($tahun, $tahunTersedia, true)) {
            $tahun = in_array($tahunSekarang, $tahunTersedia, true)
                ? $tahunSekarang
                : (int) $tahunTersedia[0];
        }

        $bulanSekarang = (int) date('n');
        $bulan = (int) ($this->request->getGet('month') ?? $bulanSekarang);
        if ($bulan < 1 || $bulan > 12) {
            $bulan = $bulanSekarang;
        }

        $namaBulan = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $labelBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $awalTahun  = sprintf('%04d-01-01', $tahun);
        $akhirTahun = sprintf('%04d-12-31', $tahun);
        $awalBulan  = sprintf('%04d-%02d-01', $tahun, $bulan);
        $akhirBulan = date('Y-m-t', strtotime($awalBulan));

        $ringkasanTahun    = $model->getPeriodSummary($awalTahun, $akhirTahun);
        $ringkasanBulan    = $model->getPeriodSummary($awalBulan, $akhirBulan);
        $ringkasanHariIni  = $model->getPeriodSummary(date('Y-m-d'), date('Y-m-d'));
        $ringkasanBulanIni = $model->getPeriodSummary(date('Y-m-01'), date('Y-m-t'));
        $ringkasanTahunIni = $model->getPeriodSummary(date('Y-01-01'), date('Y-12-31'));
        $ringkasanSemua    = $model->getPeriodSummary();

        $bulanan = $model->getMonthlySummary($tahun);
        $harian  = $model->getDailySummary($tahun, $bulan);
        $tahunan = $model->getYearlySummary();

        $grafikBulanan = [
            'labels'      => $labelBulan,
            'anggota'     => [],
            'non_anggota' => [],
            'personel'    => [],
            'total'       => [],
        ];

        foreach ($bulanan as $row) {
            $grafikBulanan['anggota'][]     = $row['anggota'];
            $grafikBulanan['non_anggota'][] = $row['non_anggota'];
            $grafikBulanan['personel'][]    = $row['personel'];
            $grafikBulanan['total'][]       = $row['total'];
        }

        $grafikHarian = [
            'labels'      => [],
            'anggota'     => [],
            'non_anggota' => [],
            'personel'    => [],
            'total'       => [],
        ];

        foreach ($harian as $row) {
            $grafikHarian['labels'][]      = (string) $row['hari'];
            $grafikHarian['anggota'][]     = $row['anggota'];
            $grafikHarian['non_anggota'][] = $row['non_anggota'];
            $grafikHarian['personel'][]    = $row['personel'];
            $grafikHarian['total'][]       = $row['total'];
        }

        $komposisi = [
            'labels' => ['Anggota', 'Non-Anggota', 'Rombongan'],
            'data'   => [
                $ringkasanTahun['anggota'],
                $ringkasanTahun['non_anggota'],
                $ringkasanTahun['personel'],
            ],
        ];

        return view('StatistikKunjungan\Views\index', [
            'title'              => 'Statistik Kunjungan',
            'tahunTersedia'      => $tahunTersedia,
            'tahun'              => $tahun,
            'bulan'              => $bulan,
            'namaBulan'          => $namaBulan,
            'labelBulan'         => $labelBulan,
            'ringkasanTahun'     => $ringkasanTahun,
            'ringkasanBulan'     => $ringkasanBulan,
            'ringkasanHariIni'   => $ringkasanHariIni,
            'ringkasanBulanIni'  => $ringkasanBulanIni,
            'ringkasanTahunIni'  => $ringkasanTahunIni,
            'ringkasanSemua'     => $ringkasanSemua,
            'bulanan'            => $bulanan,
            'harian'             => $harian,
            'tahunan'            => $tahunan,
            'grafikBulanan'      => $grafikBulanan,
            'grafikHarian'       => $grafikHarian,
            'komposisi'          => $komposisi,
        ]);
    }
}
