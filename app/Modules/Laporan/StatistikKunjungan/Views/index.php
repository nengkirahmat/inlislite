<?php
$angka = static function ($nilai) {
    return number_format((int) $nilai, 0, ',', '.');
};

$totalBulanan = [
    'anggota'     => 0,
    'non_anggota' => 0,
    'rombongan'   => 0,
    'personel'    => 0,
    'total'       => 0,
];

foreach ($bulanan as $row) {
    $totalBulanan['anggota']     += $row['anggota'];
    $totalBulanan['non_anggota'] += $row['non_anggota'];
    $totalBulanan['rombongan']   += $row['rombongan'];
    $totalBulanan['personel']    += $row['personel'];
    $totalBulanan['total']       += $row['total'];
}

$totalTahunan = [
    'anggota'     => 0,
    'non_anggota' => 0,
    'rombongan'   => 0,
    'personel'    => 0,
    'total'       => 0,
];

foreach ($tahunan as $row) {
    $totalTahunan['anggota']     += $row['anggota'];
    $totalTahunan['non_anggota'] += $row['non_anggota'];
    $totalTahunan['rombongan']   += $row['rombongan'];
    $totalTahunan['personel']    += $row['personel'];
    $totalTahunan['total']       += $row['total'];
}

$jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
$grafikBulananJson = json_encode($grafikBulanan, $jsonFlags);
$grafikHarianJson  = json_encode($grafikHarian, $jsonFlags);
$komposisiJson     = json_encode($komposisi, $jsonFlags);
?>

<?= $this->extend('App\Views\layout\main'); ?>

<?= $this->section('style'); ?>
<style>
.statistik-kunjungan .kpi-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    height: 100%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
    border-top: 4px solid #5e72e4;
}
.statistik-kunjungan .kpi-card.kpi-success { border-top-color: #2dce89; }
.statistik-kunjungan .kpi-card.kpi-warning { border-top-color: #fb6340; }
.statistik-kunjungan .kpi-card.kpi-info    { border-top-color: #11cdef; }
.statistik-kunjungan .kpi-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}
.statistik-kunjungan .kpi-label {
    color: #8898aa;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .02em;
    text-transform: uppercase;
}
.statistik-kunjungan .kpi-value {
    color: #32325d;
    font-size: 32px;
    font-weight: 800;
    line-height: 1.15;
    margin-top: 4px;
}
.statistik-kunjungan .kpi-icon {
    align-items: center;
    background: #f4f6fb;
    border-radius: 12px;
    color: #5e72e4;
    display: flex;
    font-size: 22px;
    height: 48px;
    justify-content: center;
    width: 48px;
    flex: 0 0 48px;
}
.statistik-kunjungan .kpi-success .kpi-icon { background: #e8faf0; color: #2dce89; }
.statistik-kunjungan .kpi-warning .kpi-icon { background: #fff1eb; color: #fb6340; }
.statistik-kunjungan .kpi-info .kpi-icon    { background: #e6f9fd; color: #11cdef; }
.statistik-kunjungan .kpi-breakdown {
    border-top: 1px solid #f0f2f7;
    color: #8898aa;
    display: flex;
    flex-wrap: wrap;
    font-size: 12px;
    gap: 8px 14px;
    margin-top: 16px;
    padding-top: 12px;
}
.statistik-kunjungan .kpi-breakdown b { color: #525f7f; }

.statistik-kunjungan .quick-stat {
    background: #fff;
    border-radius: 12px;
    padding: 16px 18px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, .04);
    height: 100%;
}
.statistik-kunjungan .quick-stat-label {
    color: #8898aa;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.statistik-kunjungan .quick-stat-value {
    color: #32325d;
    font-size: 24px;
    font-weight: 800;
    margin-top: 2px;
}
.statistik-kunjungan .quick-stat-detail {
    color: #8898aa;
    font-size: 12px;
    margin-top: 4px;
}

.statistik-kunjungan .chart-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
    padding: 20px;
    height: 100%;
}
.statistik-kunjungan .chart-card-head {
    align-items: flex-start;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}
.statistik-kunjungan .chart-card-head h5 {
    color: #32325d;
    font-size: 16px;
    font-weight: 800;
    margin: 0;
}
.statistik-kunjungan .chart-card-head small { color: #8898aa; }
.statistik-kunjungan .chart-wrap {
    height: 320px;
    position: relative;
}

.statistik-kunjungan .table thead th {
    border-top: 0;
    color: #8898aa;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .03em;
    text-transform: uppercase;
    white-space: nowrap;
}
.statistik-kunjungan .table td,
.statistik-kunjungan .table th { vertical-align: middle; }
.statistik-kunjungan .table tfoot td {
    border-top: 2px solid #e9ecef;
    color: #32325d;
    font-weight: 800;
}
.statistik-kunjungan .filter-label {
    color: #525f7f;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 4px;
}

@media (max-width: 767.98px) {
    .statistik-kunjungan .kpi-value { font-size: 26px; }
    .statistik-kunjungan .chart-wrap { height: 260px; }
}

@media print {
    .sidebar-argon,
    .argon-header,
    .footer-argon,
    .mobile-menu-toggle,
    .no-print { display: none !important; }

    .app-container { padding-left: 0 !important; }
    .content-wrapper { padding: 0 !important; }
    body { background: #fff !important; }
    .statistik-kunjungan .app-page-title { color: #000 !important; }
    .statistik-kunjungan .kpi-card,
    .statistik-kunjungan .quick-stat,
    .statistik-kunjungan .chart-card,
    .statistik-kunjungan .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
        break-inside: avoid;
    }
    .statistik-kunjungan .chart-wrap { height: 250px; }
}
</style>
<?= $this->endSection('style'); ?>

<?= $this->section('page'); ?>
<div class="app-main__inner statistik-kunjungan">
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-graph2 icon-gradient bg-strong-bliss"></i>
                </div>
                <div>Statistik Kunjungan
                    <div class="page-title-subheading">Rekap kunjungan anggota, non-anggota, dan rombongan</div>
                </div>
            </div>
            <div class="page-title-actions">
                <button type="button" class="btn btn-light btn-sm no-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-3 no-print">
        <div class="card-body py-3">
            <form method="get" action="<?= base_url('report/statistik-kunjungan') ?>" class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <div class="filter-label">Tahun</div>
                    <select name="year" class="form-control" onchange="this.form.submit()">
                        <?php foreach ($tahunTersedia as $tahunItem) : ?>
                            <option value="<?= (int) $tahunItem ?>" <?= (int) $tahunItem === (int) $tahun ? 'selected' : '' ?>>
                                <?= (int) $tahunItem ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="filter-label">Bulan (grafik harian)</div>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        <?php for ($i = 1; $i <= 12; $i++) : ?>
                            <option value="<?= $i ?>" <?= $i === (int) $bulan ? 'selected' : '' ?>>
                                <?= esc($namaBulan[$i]) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter"></i> Tampilkan
                    </button>
                    <a href="<?= base_url('report/statistik-kunjungan') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <span class="text-muted ms-2" style="font-size: 12px;">
                        Data: <?= esc($namaBulan[$bulan]) ?> <?= (int) $tahun ?>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-head">
                    <div>
                        <div class="kpi-label">Total Kunjungan <?= (int) $tahun ?></div>
                        <div class="kpi-value"><?= $angka($ringkasanTahun['total']) ?></div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                </div>
                <div class="kpi-breakdown">
                    <span><b><?= $angka($ringkasanTahun['anggota']) ?></b> anggota</span>
                    <span><b><?= $angka($ringkasanTahun['non_anggota']) ?></b> non-anggota</span>
                    <span><b><?= $angka($ringkasanTahun['personel']) ?></b> rombongan</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card kpi-success">
                <div class="kpi-head">
                    <div>
                        <div class="kpi-label">Kunjungan Anggota <?= (int) $tahun ?></div>
                        <div class="kpi-value"><?= $angka($ringkasanTahun['anggota']) ?></div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-user-check"></i></div>
                </div>
                <div class="kpi-breakdown">
                    <span>Kunjungan dengan nomor anggota</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card kpi-warning">
                <div class="kpi-head">
                    <div>
                        <div class="kpi-label">Kunjungan Non-Anggota <?= (int) $tahun ?></div>
                        <div class="kpi-value"><?= $angka($ringkasanTahun['non_anggota']) ?></div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-user-plus"></i></div>
                </div>
                <div class="kpi-breakdown">
                    <span>Kunjungan tanpa nomor anggota</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card kpi-info">
                <div class="kpi-head">
                    <div>
                        <div class="kpi-label">Kunjungan Rombongan <?= (int) $tahun ?></div>
                        <div class="kpi-value"><?= $angka($ringkasanTahun['personel']) ?></div>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-people-group"></i></div>
                </div>
                <div class="kpi-breakdown">
                    <span><b><?= $angka($ringkasanTahun['rombongan']) ?></b> rombongan</span>
                    <span>dihitung per orang</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="quick-stat">
                <div class="quick-stat-label">Hari Ini</div>
                <div class="quick-stat-value"><?= $angka($ringkasanHariIni['total']) ?></div>
                <div class="quick-stat-detail">
                    <?= $angka($ringkasanHariIni['anggota']) ?> anggota ·
                    <?= $angka($ringkasanHariIni['non_anggota']) ?> non-anggota ·
                    <?= $angka($ringkasanHariIni['personel']) ?> rombongan
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="quick-stat">
                <div class="quick-stat-label">Bulan Ini (<?= esc($namaBulan[(int) date('n')]) ?> <?= date('Y') ?>)</div>
                <div class="quick-stat-value"><?= $angka($ringkasanBulanIni['total']) ?></div>
                <div class="quick-stat-detail">
                    <?= $angka($ringkasanBulanIni['anggota']) ?> anggota ·
                    <?= $angka($ringkasanBulanIni['non_anggota']) ?> non-anggota ·
                    <?= $angka($ringkasanBulanIni['personel']) ?> rombongan
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="quick-stat">
                <div class="quick-stat-label">Tahun Ini (<?= date('Y') ?>)</div>
                <div class="quick-stat-value"><?= $angka($ringkasanTahunIni['total']) ?></div>
                <div class="quick-stat-detail">
                    <?= $angka($ringkasanTahunIni['anggota']) ?> anggota ·
                    <?= $angka($ringkasanTahunIni['non_anggota']) ?> non-anggota ·
                    <?= $angka($ringkasanTahunIni['personel']) ?> rombongan
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="quick-stat">
                <div class="quick-stat-label">Total Keseluruhan</div>
                <div class="quick-stat-value"><?= $angka($ringkasanSemua['total']) ?></div>
                <div class="quick-stat-detail">
                    <?= $angka($ringkasanSemua['anggota']) ?> anggota ·
                    <?= $angka($ringkasanSemua['non_anggota']) ?> non-anggota ·
                    <?= $angka($ringkasanSemua['personel']) ?> rombongan
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="chart-card">
                <div class="chart-card-head">
                    <div>
                        <h5>Tren Kunjungan Bulanan <?= (int) $tahun ?></h5>
                        <small>Anggota, non-anggota, dan personel rombongan</small>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartBulanan"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="chart-card">
                <div class="chart-card-head">
                    <div>
                        <h5>Komposisi <?= (int) $tahun ?></h5>
                        <small>Berdasarkan jenis pengunjung</small>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="chartKomposisi"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-card mb-3">
        <div class="chart-card-head">
            <div>
                <h5>Kunjungan Harian — <?= esc($namaBulan[$bulan]) ?> <?= (int) $tahun ?></h5>
                <small>Total kunjungan per hari pada bulan terpilih</small>
            </div>
            <div class="text-end">
                <span class="badge bg-primary" style="font-size: 13px;">
                    <?= $angka($ringkasanBulan['total']) ?> kunjungan
                </span>
            </div>
        </div>
        <div class="chart-wrap">
            <canvas id="chartHarian"></canvas>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Rekap Bulanan <?= (int) $tahun ?></h5>
                    <span class="badge bg-primary"><?= $angka($totalBulanan['total']) ?> kunjungan</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-end">Anggota</th>
                                <th class="text-end">Non-Anggota</th>
                                <th class="text-end">Rombongan (orang)</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bulanan as $row) : ?>
                                <tr class="<?= (int) $row['bulan'] === (int) $bulan ? 'table-active' : '' ?>">
                                    <td><?= esc($namaBulan[$row['bulan']]) ?></td>
                                    <td class="text-end"><?= $angka($row['anggota']) ?></td>
                                    <td class="text-end"><?= $angka($row['non_anggota']) ?></td>
                                    <td class="text-end"><?= $angka($row['personel']) ?></td>
                                    <td class="text-end"><strong><?= $angka($row['total']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="text-end"><?= $angka($totalBulanan['anggota']) ?></td>
                                <td class="text-end"><?= $angka($totalBulanan['non_anggota']) ?></td>
                                <td class="text-end"><?= $angka($totalBulanan['personel']) ?></td>
                                <td class="text-end"><?= $angka($totalBulanan['total']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Rekap per Tahun</h5>
                    <span class="badge bg-info"><?= count($tahunan) ?> tahun</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th class="text-end">Anggota</th>
                                <th class="text-end">Non-Anggota</th>
                                <th class="text-end">Rombongan (orang)</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tahunan)) : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data kunjungan.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($tahunan as $row) : ?>
                                    <tr class="<?= (int) $row['tahun'] === (int) $tahun ? 'table-active' : '' ?>">
                                        <td><strong><?= (int) $row['tahun'] ?></strong></td>
                                        <td class="text-end"><?= $angka($row['anggota']) ?></td>
                                        <td class="text-end"><?= $angka($row['non_anggota']) ?></td>
                                        <td class="text-end"><?= $angka($row['personel']) ?></td>
                                        <td class="text-end"><strong><?= $angka($row['total']) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="text-end"><?= $angka($totalTahunan['anggota']) ?></td>
                                <td class="text-end"><?= $angka($totalTahunan['non_anggota']) ?></td>
                                <td class="text-end"><?= $angka($totalTahunan['personel']) ?></td>
                                <td class="text-end"><?= $angka($totalTahunan['total']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('page'); ?>

<?= $this->section('script'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js" integrity="sha384-9MhbyIRcBVQiiC7FSd7T38oJNj2Zh+EfxS7/vjhBi4OOT78NlHSnzM31EZRWR1LZ" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            document.querySelectorAll('.chart-wrap').forEach(function (element) {
                element.innerHTML = '<div class="text-muted text-center py-5">Grafik tidak dapat dimuat. Periksa koneksi internet.</div>';
            });
            return;
        }

        var grafikBulanan = <?= $grafikBulananJson ?>;
        var grafikHarian = <?= $grafikHarianJson ?>;
        var komposisi = <?= $komposisiJson ?>;

        var formatAngka = new Intl.NumberFormat('id-ID');
        var warna = {
            anggota: '#5e72e4',
            nonAnggota: '#2dce89',
            rombongan: '#fb6340'
        };

        var opsiUmum = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 16
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + formatAngka.format(context.parsed.y || 0);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        callback: function (value) {
                            return formatAngka.format(value);
                        }
                    }
                }
            }
        };

        new Chart(document.getElementById('chartBulanan'), {
            type: 'bar',
            data: {
                labels: grafikBulanan.labels,
                datasets: [
                    {
                        label: 'Anggota',
                        data: grafikBulanan.anggota,
                        backgroundColor: warna.anggota,
                        borderColor: warna.anggota,
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 28
                    },
                    {
                        label: 'Non-Anggota',
                        data: grafikBulanan.non_anggota,
                        backgroundColor: warna.nonAnggota,
                        borderColor: warna.nonAnggota,
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 28
                    },
                    {
                        label: 'Rombongan (orang)',
                        data: grafikBulanan.personel,
                        backgroundColor: warna.rombongan,
                        borderColor: warna.rombongan,
                        borderWidth: 1,
                        borderRadius: 4,
                        maxBarThickness: 28
                    }
                ]
            },
            options: opsiUmum
        });

        new Chart(document.getElementById('chartHarian'), {
            type: 'line',
            data: {
                labels: grafikHarian.labels,
                datasets: [
                    {
                        label: 'Anggota',
                        data: grafikHarian.anggota,
                        borderColor: warna.anggota,
                        backgroundColor: warna.anggota,
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Non-Anggota',
                        data: grafikHarian.non_anggota,
                        borderColor: warna.nonAnggota,
                        backgroundColor: warna.nonAnggota,
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Rombongan (orang)',
                        data: grafikHarian.personel,
                        borderColor: warna.rombongan,
                        backgroundColor: warna.rombongan,
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: opsiUmum
        });

        var totalKomposisi = komposisi.data.reduce(function (total, nilai) {
            return total + Number(nilai || 0);
        }, 0);

        new Chart(document.getElementById('chartKomposisi'), {
            type: 'doughnut',
            data: {
                labels: komposisi.labels,
                datasets: [{
                    data: komposisi.data,
                    backgroundColor: [warna.anggota, warna.nonAnggota, warna.rombongan],
                    borderColor: '#fff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                var nilai = Number(context.parsed || 0);
                                var persen = totalKomposisi > 0 ? Math.round((nilai / totalKomposisi) * 100) : 0;
                                return context.label + ': ' + formatAngka.format(nilai) + ' (' + persen + '%)';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
<?= $this->endSection('script'); ?>
