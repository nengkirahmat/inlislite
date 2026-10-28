<?= $this->extend('App\Views\layout\opac\layout'); ?>

<?= $this->section('style') ?>
<style>
    :root {
        --pd-primary: #1e3a8a;
        --pd-primary-light: #3b5bdb;
        --pd-primary-soft: #eef2ff;
        --pd-ink: #111827;
        --pd-muted: #6b7280;
        --pd-border: #e5e7eb;
        --pd-bg: #f6f7fb;
    }

    body { background: var(--pd-bg); }

    .pd-hero {
        background: linear-gradient(135deg, #0f2a6b 0%, #1e3a8a 45%, #3b5bdb 100%);
        color: #fff;
        padding: 120px 0 64px;
        position: relative;
        overflow: hidden;
    }
    .pd-hero::after {
        content: "";
        position: absolute;
        width: 420px; height: 420px;
        right: -120px; top: -160px;
        background: rgba(255, 255, 255, .08);
        border-radius: 50%;
    }
    .pd-hero::before {
        content: "";
        position: absolute;
        width: 260px; height: 260px;
        left: -80px; bottom: -140px;
        background: rgba(255, 255, 255, .06);
        border-radius: 50%;
    }
    .pd-hero h1 { font-weight: 800; letter-spacing: -.02em; }
    .pd-hero p { color: rgba(255, 255, 255, .82); max-width: 680px; margin: 0 auto; }

    .pd-onesearch {
        background: #fff;
        border: 1px solid var(--pd-border);
        border-radius: 18px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 18px 40px -22px rgba(30, 58, 138, .45);
        margin-top: -48px;
        position: relative;
        z-index: 2;
    }
    .pd-onesearch .pd-os-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--pd-primary), var(--pd-primary-light));
        color: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .pd-section { padding: 3.25rem 0 0; }
    .pd-group-title {
        font-weight: 700;
        color: var(--pd-ink);
        display: flex; align-items: center; gap: .6rem;
        margin-bottom: 1.25rem;
    }
    .pd-group-title i {
        color: var(--pd-primary);
        background: var(--pd-primary-soft);
        width: 38px; height: 38px;
        border-radius: 11px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .95rem;
    }

    .pd-card {
        display: block;
        height: 100%;
        background: #fff;
        border: 1px solid var(--pd-border);
        border-radius: 14px;
        padding: 1.15rem 1.2rem;
        text-decoration: none;
        color: inherit;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .pd-card:hover {
        transform: translateY(-3px);
        border-color: #c7d2fe;
        box-shadow: 0 16px 32px -18px rgba(30, 58, 138, .35);
        color: inherit;
    }
    .pd-card .pd-card-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem;
        margin-bottom: .45rem;
    }
    .pd-card .pd-card-title { font-weight: 700; color: var(--pd-ink); font-size: .98rem; }
    .pd-card .pd-card-arrow { color: var(--pd-primary); font-size: .8rem; opacity: .65; }
    .pd-card:hover .pd-card-arrow { opacity: 1; }
    .pd-card .pd-card-desc { color: var(--pd-muted); font-size: .86rem; line-height: 1.55; margin: 0; }
    .pd-card .pd-card-host { color: #9ca3af; font-size: .76rem; margin-top: .5rem; }

    .pd-note {
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 1rem 1.25rem;
        color: var(--pd-muted);
        font-size: .88rem;
    }

    @media (max-width: 767.98px) {
        .pd-hero { padding: 104px 0 56px; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$pdDb = db_connect();
$pdNamaPerpustakaan = $pdDb->table('settingparameters')->where('Name', 'NamaPerpustakaan')->get()->getRow()->Value ?? 'Perpustakaan';
$onesearch = $opac_links->onesearch ?? ['label' => 'OneSearch Indonesia', 'url' => 'https://onesearch.id/'];
$groups    = $opac_links->groups ?? [];
$hostOf    = static function (string $url): string {
    $host = parse_url($url, PHP_URL_HOST);

    return $host ? preg_replace('/^www\./', '', $host) : $url;
};
?>

<section class="pd-hero">
    <div class="container position-relative text-center">
        <span class="badge rounded-pill bg-light text-primary fw-semibold mb-3 px-3 py-2">
            <i class="fa-solid fa-link me-1"></i>Rujukan Koleksi Digital
        </span>
        <h1 class="display-6 mb-3">Pangkalan Data &amp; Repositori</h1>
        <p class="lead fs-6 mb-0">
            Telusuri koleksi <?= esc($pdNamaPerpustakaan) ?> melalui katalog lokal, OneSearch Indonesia,
            serta pangkalan data dan repositori ilmiah terpercaya.
        </p>
    </div>
</section>

<div class="container">
    <div class="pd-onesearch">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
            <div class="pd-os-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
            <div class="flex-grow-1">
                <h2 class="h5 fw-bold mb-1"><?= esc($onesearch['label'] ?? 'OneSearch Indonesia') ?></h2>
                <p class="text-secondary mb-0" style="font-size:.9rem;">
                    Cari koleksi lintas perpustakaan di seluruh Indonesia dalam satu pintu.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('opac') ?>" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fa-solid fa-book me-1"></i>Katalog Lokal
                </a>
                <a href="<?= esc($onesearch['url'] ?? 'https://onesearch.id/') ?>" target="_blank" rel="noopener"
                    class="btn btn-primary rounded-pill px-4">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Buka OneSearch
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($groups)): ?>
        <div class="pd-section">
            <div class="pd-note">
                <i class="fa-solid fa-circle-info me-1"></i>
                Daftar tautan belum diatur. Silakan lengkapi <code>app/Config/OpacLinks.php</code>.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
            <div class="pd-section">
                <h2 class="pd-group-title h5">
                    <i class="fa-solid <?= esc($group['icon'] ?? 'fa-link') ?>"></i>
                    <?= esc($group['title'] ?? 'Tautan') ?>
                </h2>
                <div class="row g-3">
                    <?php foreach (($group['links'] ?? []) as $link): ?>
                        <div class="col-md-6 col-xl-4">
                            <a class="pd-card" href="<?= esc($link['url'] ?? '#') ?>" target="_blank" rel="noopener">
                                <div class="pd-card-head">
                                    <span class="pd-card-title"><?= esc($link['label'] ?? 'Tautan') ?></span>
                                    <i class="fa-solid fa-arrow-up-right-from-square pd-card-arrow"></i>
                                </div>
                                <p class="pd-card-desc"><?= esc($link['description'] ?? '') ?></p>
                                <div class="pd-card-host"><?= esc($hostOf($link['url'] ?? '')) ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="pd-section pb-5">
        <div class="pd-note">
            <i class="fa-solid fa-lightbulb me-1 text-warning"></i>
            Tautan pangkalan data pihak ketiga membuka situs di luar <?= esc($pdNamaPerpustakaan) ?>.
            Ketersediaan akses penuh dapat bergantung pada kebijakan penyedia layanan.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
