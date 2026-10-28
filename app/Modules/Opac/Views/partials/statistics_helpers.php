<?php
// Fungsi format bersama halaman statistik publik. Di-include (bukan view()) agar variabelnya tersedia di view pemanggil.

$angka = static function ($nilai) {
    return number_format((int) $nilai, 0, ',', '.');
};

$persen = static function ($nilai, $total) {
    return $total > 0 ? number_format(($nilai / $total) * 100, 1, ',', '.') : '0';
};

// Daftar kategori dengan bar persentase terhadap $pembagi.
$daftarBar = static function (array $baris, $pembagi, $kelasWarna) use ($angka, $persen) {
    if (empty($baris) || $pembagi <= 0) {
        return '<div class="op-empty-state">Belum ada data.</div>';
    }

    $html = '<ul class="op-bar-list">';
    foreach ($baris as $item) {
        $html .= '<li>'
            . '<div class="op-bar-head"><span>' . esc($item['label']) . '</span>'
            . '<span><b>' . $angka($item['total']) . '</b> <small>(' . $persen($item['total'], $pembagi) . '%)</small></span></div>'
            . '<div class="op-progress"><div class="op-progress-bar ' . $kelasWarna . '" style="width: '
            . number_format(min(100, ($item['total'] / $pembagi) * 100), 2, '.', '') . '%"></div></div>'
            . '</li>';
    }

    return $html . '</ul>';
};

// Pilihan tahun (form GET) untuk halaman statistik.
$filterTahun = static function ($aksi, array $tahunTersedia, $tahun) {
    $html = '<form method="get" action="' . esc($aksi, 'attr') . '" class="op-year-filter">'
        . '<label for="filterTahun">Tahun</label>'
        . '<select id="filterTahun" name="year" class="form-select" onchange="this.form.submit()">';
    foreach ($tahunTersedia as $tahunItem) {
        $html .= '<option value="' . (int) $tahunItem . '"' . ((int) $tahunItem === (int) $tahun ? ' selected' : '') . '>' . (int) $tahunItem . '</option>';
    }

    return $html . '</select><noscript><button type="submit" class="btn btn-primary btn-sm">Tampilkan</button></noscript></form>';
};

$jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
