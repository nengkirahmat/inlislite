<?php
include __DIR__ . '/partials/statistics_helpers.php';

$totalBulanan = ['anggota' => 0, 'non_anggota' => 0, 'rombongan' => 0, 'personel' => 0, 'total' => 0];
foreach ($bulanan as $row) {
    foreach ($totalBulanan as $kunci => $nilai) {
        $totalBulanan[$kunci] += $row[$kunci];
    }
}

$bulanTertinggi = null;
foreach ($bulanan as $row) {
    if ($row['total'] > 0 && ($bulanTertinggi === null || $row['total'] > $bulanTertinggi['total'])) {
        $bulanTertinggi = $row;
    }
}

$grafikBulanan = [
    'labels'      => $labelBulan,
    'anggota'     => array_column($bulanan, 'anggota'),
    'non_anggota' => array_column($bulanan, 'non_anggota'),
    'personel'    => array_column($bulanan, 'personel'),
];

$komposisi = [
    'labels' => ['Anggota', 'Non-Anggota', 'Rombongan'],
    'data'   => [$ringkasanTahun['anggota'], $ringkasanTahun['non_anggota'], $ringkasanTahun['personel']],
];

$grafikGender = [
    'labels' => array_column($jenisKelamin, 'label'),
    'data'   => array_column($jenisKelamin, 'total'),
];

$grafikHari = [
    'labels' => array_column($hariKunjungan, 'label'),
    'data'   => array_column($hariKunjungan, 'total'),
];

$totalPerorangan = $ringkasanTahun['anggota'] + $ringkasanTahun['non_anggota'];


?>
<?= $this->extend('App\Views\layout\opac\layout'); ?>

<?= $this->section('content') ?>
<?= view('Opac\Views\partials\statistics_style') ?>

<div class="container-fluid op-container py-5" style="padding-top: 100px !important; padding-bottom: 40px !important;">

    <div class="op-page-header">
        <div class="op-icon-badge"><i class="fas fa-person-walking"></i></div>
        <h1>Statistik Kunjungan</h1>
        <p class="mb-0">Rekap kunjungan anggota, non-anggota, dan rombongan ke perpustakaan</p>
    </div>

    <?= $filterTahun(base_url('opac/statistics_kunjungan'), $tahunTersedia, $tahun) ?>

    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-primary"><i class="fas fa-users"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($ringkasanTahun['total']) ?></div>
                    <div class="op-stat-label">Total Kunjungan <?= (int) $tahun ?></div>
                    <div class="op-stat-sub"><?= $angka($ringkasanTahun['rombongan']) ?> rombongan tercatat</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-success"><i class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($ringkasanHariIni['total']) ?></div>
                    <div class="op-stat-label">Kunjungan Hari Ini</div>
                    <div class="op-stat-sub"><?= date('d') ?> <?= esc($namaBulan[(int) date('n')]) ?> <?= date('Y') ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-info"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($ringkasanBulanIni['total']) ?></div>
                    <div class="op-stat-label">Kunjungan Bulan Ini</div>
                    <div class="op-stat-sub"><?= esc($namaBulan[(int) date('n')]) ?> <?= date('Y') ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-warning"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($ringkasanSemua['total']) ?></div>
                    <div class="op-stat-label">Total Keseluruhan</div>
                    <div class="op-stat-sub">Sejak data pertama tercatat</div>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-chart-bar"></i></span>
        Tren Kunjungan <?= (int) $tahun ?>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-8">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#1e3a8a,#3b5bdb);"><i class="fas fa-chart-column"></i></span>
                    Kunjungan per Bulan
                </div>
                <div class="op-card-body">
                    <?php if ($totalBulanan['total'] > 0) : ?>
                        <div class="op-chart-wrap"><canvas id="chartBulanan"></canvas></div>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada data kunjungan pada tahun <?= (int) $tahun ?>.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#059669,#34d399);"><i class="fas fa-chart-pie"></i></span>
                    Komposisi Pengunjung
                </div>
                <div class="op-card-body">
                    <?php if ($ringkasanTahun['total'] > 0) : ?>
                        <div class="op-chart-wrap" style="height: 240px;"><canvas id="chartKomposisi"></canvas></div>
                        <ul class="list-unstyled mt-3 mb-0 small">
                            <li class="d-flex justify-content-between py-1"><span>Anggota</span><b><?= $angka($ringkasanTahun['anggota']) ?> (<?= $persen($ringkasanTahun['anggota'], $ringkasanTahun['total']) ?>%)</b></li>
                            <li class="d-flex justify-content-between py-1"><span>Non-Anggota</span><b><?= $angka($ringkasanTahun['non_anggota']) ?> (<?= $persen($ringkasanTahun['non_anggota'], $ringkasanTahun['total']) ?>%)</b></li>
                            <li class="d-flex justify-content-between py-1"><span>Rombongan (orang)</span><b><?= $angka($ringkasanTahun['personel']) ?> (<?= $persen($ringkasanTahun['personel'], $ringkasanTahun['total']) ?>%)</b></li>
                        </ul>
                        <?php if ($bulanTertinggi !== null) : ?>
                            <div class="mt-3 pt-3 border-top small text-muted">
                                <i class="fas fa-star text-warning me-1"></i>
                                Bulan teramai: <b class="text-dark"><?= esc($namaBulan[$bulanTertinggi['bulan']]) ?></b>
                                (<?= $angka($bulanTertinggi['total']) ?> kunjungan)
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-id-card"></i></span>
        Profil Pengunjung <?= (int) $tahun ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#667eea,#764ba2);"><i class="fas fa-venus-mars"></i></span>
                    Jenis Kelamin
                </div>
                <div class="op-card-body">
                    <?php if (!empty($jenisKelamin)) : ?>
                        <div class="op-chart-wrap" style="height: 220px;"><canvas id="chartGender"></canvas></div>
                        <?= $daftarBar($jenisKelamin, array_sum(array_column($jenisKelamin, 'total')), 'op-grad-violet') ?>
                        <div class="op-card-note">Termasuk personel rombongan.</div>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#1e3a8a,#3b5bdb);"><i class="fas fa-calendar-week"></i></span>
                    Kunjungan per Hari dalam Seminggu
                </div>
                <div class="op-card-body">
                    <?php if (array_sum($grafikHari['data']) > 0) : ?>
                        <div class="op-chart-wrap"><canvas id="chartHari"></canvas></div>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-4 col-md-6">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#059669,#34d399);"><i class="fas fa-briefcase"></i></span>
                    Pekerjaan
                </div>
                <div class="op-card-body">
                    <?= $daftarBar($pekerjaan, $totalPerorangan, 'op-grad-success') ?>
                    <div class="op-card-note">Pengunjung perorangan, 10 teratas.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#0891b2,#38bdf8);"><i class="fas fa-graduation-cap"></i></span>
                    Pendidikan Terakhir
                </div>
                <div class="op-card-body">
                    <?= $daftarBar($pendidikan, $totalPerorangan, 'op-grad-info') ?>
                    <div class="op-card-note">Pengunjung perorangan.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#d97706,#fbbf24);"><i class="fas fa-door-open"></i></span>
                    Ruang yang Dikunjungi
                </div>
                <div class="op-card-body">
                    <?= $daftarBar($ruang, $totalPerorangan, 'op-grad-warning') ?>
                    <div class="op-card-note">Pengunjung perorangan, 10 teratas.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-people-group"></i></span>
        Kunjungan Rombongan <?= (int) $tahun ?>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-5">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#d97706,#fbbf24);"><i class="fas fa-user-tag"></i></span>
                    Personel Rombongan per Kategori
                </div>
                <div class="op-card-body">
                    <?= $daftarBar($kategoriRombongan, array_sum(array_column($kategoriRombongan, 'total')), 'op-grad-warning') ?>
                    <div class="op-card-note">
                        <?= $angka($ringkasanTahun['rombongan']) ?> rombongan, <?= $angka($ringkasanTahun['personel']) ?> orang.
                        Kategori sesuai isian buku tamu rombongan.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#1e3a8a,#3b5bdb);"><i class="fas fa-building"></i></span>
                    Instansi Asal Rombongan Terbanyak
                </div>
                <div class="op-card-body">
                    <?php if (!empty($instansiRombongan)) : ?>
                        <div class="op-table-wrap table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Instansi</th>
                                        <th class="text-end">Rombongan</th>
                                        <th class="text-end">Personel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($instansiRombongan as $nomor => $row) : ?>
                                        <tr>
                                            <td><?= $nomor + 1 ?></td>
                                            <td><?= esc($row['instansi']) ?></td>
                                            <td class="text-end"><?= $angka($row['rombongan']) ?></td>
                                            <td class="text-end"><strong><?= $angka($row['personel']) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada kunjungan rombongan pada tahun <?= (int) $tahun ?>.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-table"></i></span>
        Rekapitulasi
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#0891b2,#38bdf8);"><i class="fas fa-calendar"></i></span>
                    Rekap Bulanan <?= (int) $tahun ?>
                </div>
                <div class="op-card-body">
                    <div class="op-table-wrap table-responsive">
                        <table class="table">
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
                                    <tr>
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
        </div>

        <div class="col-xl-5">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#d97706,#fbbf24);"><i class="fas fa-calendar-check"></i></span>
                    Rekap per Tahun
                </div>
                <div class="op-card-body">
                    <div class="op-table-wrap table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th class="text-end">Anggota</th>
                                    <th class="text-end">Non-Anggota</th>
                                    <th class="text-end">Rombongan</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tahunan)) : ?>
                                    <tr><td colspan="5" class="text-center text-muted">Belum ada data kunjungan.</td></tr>
                                <?php else : ?>
                                    <?php foreach ($tahunan as $row) : ?>
                                        <tr class="<?= (int) $row['tahun'] === (int) $tahun ? 'op-row-active' : '' ?>">
                                            <td>
                                                <a href="<?= base_url('opac/statistics_kunjungan?year=' . (int) $row['tahun']) ?>" class="text-decoration-none fw-bold"><?= (int) $row['tahun'] ?></a>
                                            </td>
                                            <td class="text-end"><?= $angka($row['anggota']) ?></td>
                                            <td class="text-end"><?= $angka($row['non_anggota']) ?></td>
                                            <td class="text-end"><?= $angka($row['personel']) ?></td>
                                            <td class="text-end"><strong><?= $angka($row['total']) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="small text-muted mt-2">Kolom rombongan dihitung per orang (jumlah personel).</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js" integrity="sha384-9MhbyIRcBVQiiC7FSd7T38oJNj2Zh+EfxS7/vjhBi4OOT78NlHSnzM31EZRWR1LZ" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            document.querySelectorAll('.op-chart-wrap').forEach(function (element) {
                element.innerHTML = '<div class="op-empty-state">Grafik tidak dapat dimuat.</div>';
            });
            return;
        }

        var grafikBulanan = <?= json_encode($grafikBulanan, $jsonFlags) ?>;
        var komposisi = <?= json_encode($komposisi, $jsonFlags) ?>;
        var formatAngka = new Intl.NumberFormat('id-ID');
        var warna = { anggota: '#1e3a8a', nonAnggota: '#059669', rombongan: '#d97706' };

        var kanvasBulanan = document.getElementById('chartBulanan');
        if (kanvasBulanan) {
            new Chart(kanvasBulanan, {
                type: 'bar',
                data: {
                    labels: grafikBulanan.labels,
                    datasets: [
                        { label: 'Anggota', data: grafikBulanan.anggota, backgroundColor: warna.anggota, borderRadius: 4, maxBarThickness: 36 },
                        { label: 'Non-Anggota', data: grafikBulanan.non_anggota, backgroundColor: warna.nonAnggota, borderRadius: 4, maxBarThickness: 36 },
                        { label: 'Rombongan (orang)', data: grafikBulanan.personel, backgroundColor: warna.rombongan, borderRadius: 4, maxBarThickness: 36 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': ' + formatAngka.format(context.parsed.y || 0);
                                },
                                footer: function (items) {
                                    var total = items.reduce(function (jumlah, item) { return jumlah + (item.parsed.y || 0); }, 0);
                                    return 'Total: ' + formatAngka.format(total);
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { precision: 0, callback: function (value) { return formatAngka.format(value); } }
                        }
                    }
                }
            });
        }

        var grafikGender = <?= json_encode($grafikGender, $jsonFlags) ?>;
        var grafikHari = <?= json_encode($grafikHari, $jsonFlags) ?>;

        var kanvasGender = document.getElementById('chartGender');
        if (kanvasGender) {
            new Chart(kanvasGender, {
                type: 'doughnut',
                data: {
                    labels: grafikGender.labels,
                    datasets: [{
                        data: grafikGender.data,
                        backgroundColor: ['#667eea', '#ec4899', '#9ca3af', '#38bdf8'],
                        borderColor: '#fff',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + formatAngka.format(Number(context.parsed || 0));
                                }
                            }
                        }
                    }
                }
            });
        }

        var kanvasHari = document.getElementById('chartHari');
        if (kanvasHari) {
            new Chart(kanvasHari, {
                type: 'bar',
                data: {
                    labels: grafikHari.labels,
                    datasets: [{
                        label: 'Kunjungan',
                        data: grafikHari.data,
                        backgroundColor: '#3b5bdb',
                        borderRadius: 6,
                        maxBarThickness: 48
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return 'Kunjungan: ' + formatAngka.format(context.parsed.y || 0);
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0, callback: function (value) { return formatAngka.format(value); } } }
                    }
                }
            });
        }

        var kanvasKomposisi = document.getElementById('chartKomposisi');
        if (kanvasKomposisi) {
            var totalKomposisi = komposisi.data.reduce(function (total, nilai) { return total + Number(nilai || 0); }, 0);

            new Chart(kanvasKomposisi, {
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
                        legend: { display: false },
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
        }
    });
</script>
<?= $this->endSection() ?>
