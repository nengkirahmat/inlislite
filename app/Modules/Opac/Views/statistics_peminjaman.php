<?php
include __DIR__ . '/partials/statistics_helpers.php';

$totalTahun = array_sum($bulanan);

$bulanTertinggi = null;
foreach ($bulanan as $bulan => $total) {
    if ($total > 0 && ($bulanTertinggi === null || $total > $bulanan[$bulanTertinggi])) {
        $bulanTertinggi = $bulan;
    }
}

$grafikBulanan = ['labels' => $labelBulan, 'data' => array_values($bulanan)];
$grafikHari    = ['labels' => array_column($hariPeminjaman, 'label'), 'data' => array_column($hariPeminjaman, 'total')];
$grafikAnggota = ['labels' => array_column($jenisAnggota, 'label'), 'data' => array_column($jenisAnggota, 'total')];
?>
<?= $this->extend('App\Views\layout\opac\layout'); ?>

<?= $this->section('content') ?>
<?= view('Opac\Views\partials\statistics_style') ?>

<div class="container-fluid op-container py-5" style="padding-top: 100px !important; padding-bottom: 40px !important;">

    <div class="op-page-header">
        <div class="op-icon-badge"><i class="fas fa-book-open-reader"></i></div>
        <h1>Statistik Peminjaman</h1>
        <p class="mb-0">Rekap peminjaman buku perpustakaan</p>
    </div>

    <div class="op-stat-tabs">
        <a href="<?= base_url('opac/statistics_peminjaman?year=' . (int) $tahun) ?>" class="active"><i class="fas fa-book-open-reader me-1"></i> Peminjaman</a>
        <a href="<?= base_url('opac/statistics_pengembalian?year=' . (int) $tahun) ?>"><i class="fas fa-rotate-left me-1"></i> Pengembalian</a>
    </div>

    <?= $filterTahun(base_url('opac/statistics_peminjaman'), $tahunTersedia, $tahun) ?>

    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-primary"><i class="fas fa-book"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($ringkasan['eksemplar']) ?></div>
                    <div class="op-stat-label">Buku Dipinjam <?= (int) $tahun ?></div>
                    <div class="op-stat-sub"><?= $angka($ringkasan['transaksi']) ?> transaksi peminjaman</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-success"><i class="fas fa-user-check"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($ringkasan['peminjam']) ?></div>
                    <div class="op-stat-label">Anggota Meminjam <?= (int) $tahun ?></div>
                    <div class="op-stat-sub"><?= $angka($ringkasan['koleksi']) ?> eksemplar berbeda dipinjam</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-info"><i class="fas fa-hand-holding"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($saatIni['dipinjam']) ?></div>
                    <div class="op-stat-label">Sedang Dipinjam</div>
                    <div class="op-stat-sub">Oleh <?= $angka($saatIni['peminjam']) ?> anggota, per hari ini</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="op-stat-card">
                <div class="op-stat-icon i-danger"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="op-stat-value"><?= $angka($saatIni['terlambat']) ?></div>
                    <div class="op-stat-label">Melewati Jatuh Tempo</div>
                    <div class="op-stat-sub">Belum dikembalikan, per hari ini</div>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-chart-bar"></i></span>
        Tren Peminjaman <?= (int) $tahun ?>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-8">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#1e3a8a,#3b5bdb);"><i class="fas fa-chart-column"></i></span>
                    Peminjaman per Bulan
                </div>
                <div class="op-card-body">
                    <?php if ($totalTahun > 0) : ?>
                        <div class="op-chart-wrap"><canvas id="chartBulanan"></canvas></div>
                        <?php if ($bulanTertinggi !== null) : ?>
                            <div class="op-card-note">
                                <i class="fas fa-star text-warning me-1"></i>
                                Bulan teramai: <b class="text-dark"><?= esc($namaBulan[$bulanTertinggi]) ?></b>
                                (<?= $angka($bulanan[$bulanTertinggi]) ?> buku)
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada peminjaman pada tahun <?= (int) $tahun ?>.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#059669,#34d399);"><i class="fas fa-calendar-week"></i></span>
                    Hari Peminjaman
                </div>
                <div class="op-card-body">
                    <?php if ($totalTahun > 0) : ?>
                        <div class="op-chart-wrap"><canvas id="chartHari"></canvas></div>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="op-section-heading">
        <span class="op-icon-badge-sm"><i class="fas fa-book"></i></span>
        Koleksi yang Dipinjam <?= (int) $tahun ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#d97706,#fbbf24);"><i class="fas fa-trophy"></i></span>
                    Buku Paling Sering Dipinjam
                </div>
                <div class="op-card-body">
                    <?php if (!empty($judulTerpopuler)) : ?>
                        <ol class="op-rank-list">
                            <?php foreach ($judulTerpopuler as $nomor => $buku) : ?>
                                <li>
                                    <span class="op-rank-no"><?= $nomor + 1 ?></span>
                                    <div>
                                        <div class="op-rank-title"><?= esc($buku['judul']) ?></div>
                                        <?php if ($buku['pengarang'] !== '') : ?>
                                            <div class="op-rank-sub"><?= esc($buku['pengarang']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="op-rank-count"><?= $angka($buku['total']) ?><small>kali</small></div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#0891b2,#38bdf8);"><i class="fas fa-layer-group"></i></span>
                    Bidang Ilmu (Klasifikasi DDC)
                </div>
                <div class="op-card-body">
                    <?= $daftarBar($kelasDdc, $totalTahun, 'op-grad-info') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-6">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#667eea,#764ba2);"><i class="fas fa-id-badge"></i></span>
                    Peminjam per Jenis Anggota
                </div>
                <div class="op-card-body">
                    <?php if (!empty($jenisAnggota)) : ?>
                        <div class="row align-items-center g-3">
                            <div class="col-sm-5"><div class="op-chart-wrap" style="height: 200px;"><canvas id="chartAnggota"></canvas></div></div>
                            <div class="col-sm-7"><?= $daftarBar($jenisAnggota, $totalTahun, 'op-grad-violet') ?></div>
                        </div>
                    <?php else : ?>
                        <div class="op-empty-state">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="op-card">
                <div class="op-card-header">
                    <span class="op-header-icon" style="background: linear-gradient(135deg,#059669,#34d399);"><i class="fas fa-tags"></i></span>
                    Kategori Koleksi
                </div>
                <div class="op-card-body">
                    <?= $daftarBar($kategori, $totalTahun, 'op-grad-success') ?>
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
                            <th class="text-end">Buku Dipinjam</th>
                            <th class="text-end">Buku Dikembalikan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tahunan)) : ?>
                            <tr><td colspan="3" class="text-center text-muted">Belum ada data peminjaman.</td></tr>
                        <?php else : ?>
                            <?php foreach ($tahunan as $row) : ?>
                                <tr class="<?= (int) $row['tahun'] === (int) $tahun ? 'op-row-active' : '' ?>">
                                    <td><a href="<?= base_url('opac/statistics_peminjaman?year=' . (int) $row['tahun']) ?>" class="text-decoration-none fw-bold"><?= (int) $row['tahun'] ?></a></td>
                                    <td class="text-end"><strong><?= $angka($row['dipinjam']) ?></strong></td>
                                    <td class="text-end"><?= $angka($row['dikembalikan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="op-card-note">Dihitung per eksemplar buku. Satu transaksi dapat berisi beberapa buku.</div>
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
        var grafikHari = <?= json_encode($grafikHari, $jsonFlags) ?>;
        var grafikAnggota = <?= json_encode($grafikAnggota, $jsonFlags) ?>;
        var formatAngka = new Intl.NumberFormat('id-ID');

        var opsiBatang = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return 'Dipinjam: ' + formatAngka.format(context.parsed.y || 0) + ' buku';
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0, callback: function (value) { return formatAngka.format(value); } } }
            }
        };

        var kanvasBulanan = document.getElementById('chartBulanan');
        if (kanvasBulanan) {
            new Chart(kanvasBulanan, {
                type: 'bar',
                data: {
                    labels: grafikBulanan.labels,
                    datasets: [{ data: grafikBulanan.data, backgroundColor: '#3b5bdb', borderRadius: 6, maxBarThickness: 40 }]
                },
                options: opsiBatang
            });
        }

        var kanvasHari = document.getElementById('chartHari');
        if (kanvasHari) {
            new Chart(kanvasHari, {
                type: 'bar',
                data: {
                    labels: grafikHari.labels,
                    datasets: [{ data: grafikHari.data, backgroundColor: '#059669', borderRadius: 6, maxBarThickness: 36 }]
                },
                options: opsiBatang
            });
        }

        var kanvasAnggota = document.getElementById('chartAnggota');
        if (kanvasAnggota) {
            new Chart(kanvasAnggota, {
                type: 'doughnut',
                data: {
                    labels: grafikAnggota.labels,
                    datasets: [{
                        data: grafikAnggota.data,
                        backgroundColor: ['#667eea', '#059669', '#d97706', '#0891b2', '#dc2626', '#9ca3af'],
                        borderColor: '#fff',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
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
