<?php
include __DIR__ . '/partials/statistics_helpers.php';

$grafikBulanan = [
    'labels'      => $labelBulan,
    'tepat_waktu' => array_values(array_column($bulanan, 'tepat_waktu')),
    'terlambat'   => array_values(array_column($bulanan, 'terlambat')),
];

$persenTepat = $ringkasan['total'] > 0 ? $persen($ringkasan['tepat_waktu'], $ringkasan['total']) : null;
$totalTerlambatSaatIni = array_sum(array_column($terlambatSaatIni, 'total'));
?>
<?= $this->extend('App\Views\layout\opac\layout'); ?>

<?= $this->section('content') ?>
<?= view('Opac\Views\partials\statistics_style') ?>

<div class="container-fluid op-container py-5" style="padding-top: 100px !important; padding-bottom: 40px !important;">

    <div class="op-page-header">
        <div class="op-icon-badge"><i class="fas fa-rotate-left"></i></div>
        <h1>Statistik Pengembalian</h1>
        <p class="mb-0">Rekap pengembalian buku dan ketepatan waktu</p>
    </div>

    <div class="op-stat-tabs">
        <a href="<?= base_url('opac/statistics_peminjaman?year=' . (int) $tahun) ?>"><i class="fas fa-book-open-reader me-1"></i> Peminjaman</a>
        <a href="<?= base_url('opac/statistics_pengembalian?year=' . (int) $tahun) ?>" class="active"><i class="fas fa-rotate-left me-1"></i> Pengembalian</a>
    </div>

    <?= $filterTahun(base_url('opac/statistics_pengembalian'), $tahunTersedia, $tahun) ?>

    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-primary"><i class="fas fa-book"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($ringkasan['total']) ?></div>
                    <div class="op-stat-label">Buku Dikembalikan <?= (int) $tahun ?></div>
                    <div class="op-stat-sub">Oleh <?= $angka($ringkasan['pengembali']) ?> anggota</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-success"><i class="fas fa-circle-check"></i></div>
                <div>
                    <div class="op-stat-value"><?= $persenTepat !== null ? $persenTepat . '%' : '–' ?></div>
                    <div class="op-stat-label">Tepat Waktu</div>
                    <div class="op-stat-sub"><?= $angka($ringkasan['tepat_waktu']) ?> buku sebelum jatuh tempo</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-danger"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($ringkasan['terlambat']) ?></div>
                    <div class="op-stat-label">Dikembalikan Terlambat</div>
                    <div class="op-stat-sub">Melewati tanggal jatuh tempo</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-info"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <div class="op-stat-value"><?= $ringkasan['median_durasi'] !== null ? $angka($ringkasan['median_durasi']) . ' hari' : '–' ?></div>
                    <div class="op-stat-label">Lama Pinjam (Median)</div>
                    <div class="op-stat-sub">Separuh buku kembali dalam waktu ini</div>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-chart-bar"></i></span>
        Tren Pengembalian <?= (int) $tahun ?>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-8">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#1e3a8a,#3b5bdb);"><i class="fas fa-chart-column"></i></span>
                    Pengembalian per Bulan
                </div>
                <div class="op-card-body">
                    <?php if ($ringkasan['total'] > 0) : ?>
                        <div class="op-chart-wrap"><canvas id="chartBulanan"></canvas></div>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada pengembalian pada tahun <?= (int) $tahun ?>.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#059669,#34d399);"><i class="fas fa-chart-pie"></i></span>
                    Ketepatan Waktu
                </div>
                <div class="op-card-body">
                    <?php if ($ringkasan['total'] > 0) : ?>
                        <div class="op-chart-wrap" style="height: 220px;"><canvas id="chartKetepatan"></canvas></div>
                        <?= $daftarBar([['label' => 'Tepat waktu', 'total' => $ringkasan['tepat_waktu']]], $ringkasan['total'], 'op-grad-success') ?>
                        <?= $daftarBar([['label' => 'Terlambat', 'total' => $ringkasan['terlambat']]], $ringkasan['total'], 'op-grad-danger') ?>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-hourglass-half"></i></span>
        Durasi &amp; Keterlambatan
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-4 col-md-6">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#0891b2,#38bdf8);"><i class="fas fa-calendar-days"></i></span>
                    Lama Peminjaman <?= (int) $tahun ?>
                </div>
                <div class="op-card-body">
                    <?= $daftarBar($sebaranDurasi, $ringkasan['total'], 'op-grad-info') ?>
                    <div class="op-card-note">Dari tanggal pinjam sampai tanggal kembali.</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#d97706,#fbbf24);"><i class="fas fa-stopwatch"></i></span>
                    Lama Keterlambatan <?= (int) $tahun ?>
                </div>
                <div class="op-card-body">
                    <?php if ($ringkasan['terlambat'] > 0) : ?>
                        <?= $daftarBar($sebaranTerlambat, $ringkasan['terlambat'], 'op-grad-warning') ?>
                        <div class="op-card-note">Hanya buku yang dikembalikan terlambat.</div>
                    <?php else : ?>
                        <div class="op-empty-state">Tidak ada pengembalian terlambat.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#dc2626,#f87171);"><i class="fas fa-triangle-exclamation"></i></span>
                    Belum Kembali &amp; Lewat Jatuh Tempo
                </div>
                <div class="op-card-body">
                    <?php if ($totalTerlambatSaatIni > 0) : ?>
                        <?= $daftarBar($terlambatSaatIni, $totalTerlambatSaatIni, 'op-grad-danger') ?>
                        <div class="op-card-note">
                            Kondisi per hari ini: <?= $angka($totalTerlambatSaatIni) ?> dari <?= $angka($saatIni['dipinjam']) ?> buku yang sedang dipinjam.
                        </div>
                    <?php else : ?>
                        <div class="op-empty-state">Tidak ada pinjaman yang melewati jatuh tempo.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-table"></i></span>
        Rekap per Tahun
    </div>

    <div class="op-card">
        <div class="op-card-body">
            <div class="op-table-wrap table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th class="text-end">Dikembalikan</th>
                            <th class="text-end">Tepat Waktu</th>
                            <th class="text-end">Terlambat</th>
                            <th class="text-end">% Tepat Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tahunan)) : ?>
                            <tr><td colspan="5" class="text-center text-muted">Belum ada data pengembalian.</td></tr>
                        <?php else : ?>
                            <?php foreach ($tahunan as $row) : ?>
                                <?php if ($row['dikembalikan'] === 0) continue; ?>
                                <tr class="<?= (int) $row['tahun'] === (int) $tahun ? 'op-row-active' : '' ?>">
                                    <td><a href="<?= base_url('opac/statistics_pengembalian?year=' . (int) $row['tahun']) ?>" class="text-decoration-none fw-bold"><?= (int) $row['tahun'] ?></a></td>
                                    <td class="text-end"><strong><?= $angka($row['dikembalikan']) ?></strong></td>
                                    <td class="text-end"><?= $angka($row['dikembalikan'] - $row['terlambat']) ?></td>
                                    <td class="text-end"><?= $angka($row['terlambat']) ?></td>
                                    <td class="text-end"><?= $persen($row['dikembalikan'] - $row['terlambat'], $row['dikembalikan']) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="op-card-note">Dihitung per eksemplar buku, berdasarkan tanggal pengembalian.</div>
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
        var ketepatan = <?= json_encode([(int) $ringkasan['tepat_waktu'], (int) $ringkasan['terlambat']], $jsonFlags) ?>;
        var formatAngka = new Intl.NumberFormat('id-ID');
        var warna = { tepat: '#059669', terlambat: '#dc2626' };

        var kanvasBulanan = document.getElementById('chartBulanan');
        if (kanvasBulanan) {
            new Chart(kanvasBulanan, {
                type: 'bar',
                data: {
                    labels: grafikBulanan.labels,
                    datasets: [
                        { label: 'Tepat waktu', data: grafikBulanan.tepat_waktu, backgroundColor: warna.tepat, borderRadius: 4, maxBarThickness: 40 },
                        { label: 'Terlambat', data: grafikBulanan.terlambat, backgroundColor: warna.terlambat, borderRadius: 4, maxBarThickness: 40 }
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
                                    return 'Total: ' + formatAngka.format(total) + ' buku';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0, callback: function (value) { return formatAngka.format(value); } } }
                    }
                }
            });
        }

        var kanvasKetepatan = document.getElementById('chartKetepatan');
        if (kanvasKetepatan) {
            new Chart(kanvasKetepatan, {
                type: 'doughnut',
                data: {
                    labels: ['Tepat waktu', 'Terlambat'],
                    datasets: [{ data: ketepatan, backgroundColor: [warna.tepat, warna.terlambat], borderColor: '#fff', borderWidth: 3 }]
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
                                    return context.label + ': ' + formatAngka.format(Number(context.parsed || 0)) + ' buku';
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
