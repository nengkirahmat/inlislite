<?php // Gaya bersama halaman statistik publik (kunjungan, peminjaman, pengembalian). ?>
<style>
    :root {
        --op-primary: #1e3a8a;
        --op-primary-light: #3b5bdb;
        --op-primary-soft: #eef2ff;
        --op-ink: #111827;
        --op-muted: #6b7280;
        --op-border: #e5e7eb;
        --op-bg: #f6f7fb;
        --op-radius-lg: 20px;
        --op-radius-md: 14px;
        --op-shadow-sm: 0 1px 3px rgba(17, 24, 39, 0.06), 0 1px 2px rgba(17, 24, 39, 0.04);
        --op-shadow-md: 0 10px 30px -12px rgba(30, 58, 138, 0.18);
    }

    body { background: var(--op-bg); }

    .op-container { max-width: 1600px; margin-left: auto; margin-right: auto; }
    @media (min-width: 1400px) {
        .op-container { padding-left: 2.5rem; padding-right: 2.5rem; }
    }

    .op-page-header { text-align: center; margin-bottom: 2rem; }
    .op-page-header .op-icon-badge {
        width: 64px; height: 64px;
        border-radius: 18px;
        background: linear-gradient(135deg, var(--op-primary), var(--op-primary-light));
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.5rem;
        box-shadow: var(--op-shadow-md);
        margin-bottom: 1rem;
    }
    .op-page-header h1 { font-weight: 800; letter-spacing: -0.02em; color: var(--op-ink); font-size: 2.15rem; }
    .op-page-header p { color: var(--op-muted); font-size: 1.02rem; }

    .op-year-filter { display: flex; justify-content: center; align-items: center; gap: .6rem; margin-bottom: 2.5rem; }
    .op-year-filter label { color: var(--op-muted); font-weight: 600; font-size: .9rem; margin: 0; }
    .op-year-filter select { width: auto; min-width: 120px; border-radius: 999px; font-weight: 700; }

    .op-section-heading {
        font-weight: 800;
        color: var(--op-ink);
        font-size: 1.3rem;
        display: flex; align-items: center; gap: .6rem;
        margin-bottom: 1.25rem;
    }
    .op-section-heading .op-icon-badge-sm {
        width: 38px; height: 38px; border-radius: 11px;
        background: var(--op-primary-soft); color: var(--op-primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem;
    }

    .op-stat-card {
        background: #fff;
        border: 1px solid var(--op-border);
        border-radius: var(--op-radius-lg);
        box-shadow: var(--op-shadow-sm);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        height: 100%;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .op-stat-card:hover { transform: translateY(-3px); box-shadow: var(--op-shadow-md); }
    .op-stat-icon {
        flex: 0 0 auto;
        width: 56px; height: 56px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem;
        color: #fff;
    }
    .op-stat-icon.i-primary { background: linear-gradient(135deg, #1e3a8a, #3b5bdb); }
    .op-stat-icon.i-success { background: linear-gradient(135deg, #059669, #34d399); }
    .op-stat-icon.i-info    { background: linear-gradient(135deg, #0891b2, #38bdf8); }
    .op-stat-icon.i-warning { background: linear-gradient(135deg, #d97706, #fbbf24); }
    .op-stat-value { font-weight: 800; font-size: 1.55rem; color: var(--op-ink); line-height: 1.1; }
    .op-stat-label { color: var(--op-muted); font-size: .85rem; font-weight: 500; }
    .op-stat-sub { color: #9ca3af; font-size: .75rem; font-weight: 500; }

    .op-card {
        background: #fff;
        border: 1px solid var(--op-border);
        border-radius: var(--op-radius-lg);
        box-shadow: var(--op-shadow-sm);
        overflow: hidden;
        height: 100%;
    }
    .op-card-header {
        padding: 1.1rem 1.4rem;
        display: flex; align-items: center; gap: .6rem;
        border-bottom: 1px solid var(--op-border);
        font-weight: 700;
        color: var(--op-ink);
        font-size: 1rem;
    }
    .op-card-header .op-header-icon {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: .85rem;
        flex: 0 0 auto;
    }
    .op-card-body { padding: 1.4rem; }
    .op-chart-wrap { position: relative; height: 320px; }

    .op-table-wrap { border: 1px solid var(--op-border); border-radius: var(--op-radius-md); overflow: hidden; }
    .op-table-wrap table { margin-bottom: 0; }
    .op-table-wrap thead th {
        background: #f8f9fc; color: var(--op-ink); font-size: .74rem;
        text-transform: uppercase; letter-spacing: .04em; font-weight: 700;
        border: none; padding: .75rem 1rem; white-space: nowrap;
    }
    .op-table-wrap tbody td, .op-table-wrap tfoot td { padding: .65rem 1rem; vertical-align: middle; font-size: .875rem; border-color: #f0f1f5; }
    .op-table-wrap tbody tr:hover { background: #fafbff; }
    .op-table-wrap tfoot td { font-weight: 800; color: var(--op-ink); background: #f8f9fc; }
    .op-table-wrap tr.op-row-active td { background: var(--op-primary-soft); font-weight: 700; }

    .op-empty-state { text-align: center; color: var(--op-muted); padding: 2.5rem 1rem; }

    .op-bar-list { list-style: none; padding: 0; margin: 0; }
    .op-bar-list li { padding: .45rem 0; }
    .op-bar-head { display: flex; justify-content: space-between; gap: .75rem; font-size: .875rem; color: var(--op-ink); margin-bottom: .3rem; }
    .op-bar-head small { color: var(--op-muted); }
    .op-progress { height: 8px; border-radius: 999px; background: #f1f2f6; overflow: hidden; }
    .op-progress-bar { height: 100%; border-radius: 999px; }
    .op-grad-primary { background: linear-gradient(90deg,#1e3a8a,#3b5bdb); }
    .op-grad-success { background: linear-gradient(90deg,#059669,#34d399); }
    .op-grad-info    { background: linear-gradient(90deg,#0891b2,#38bdf8); }
    .op-grad-warning { background: linear-gradient(90deg,#d97706,#fbbf24); }
    .op-grad-violet  { background: linear-gradient(90deg,#667eea,#764ba2); }
    .op-card-note { color: var(--op-muted); font-size: .78rem; margin-top: .75rem; }
    .op-grad-danger  { background: linear-gradient(90deg,#dc2626,#f87171); }
    .op-stat-icon.i-danger { background: linear-gradient(135deg, #dc2626, #f87171); }

    /* Tautan antar halaman statistik sirkulasi */
    .op-stat-tabs { display: flex; justify-content: center; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.25rem; }
    .op-stat-tabs a {
        border: 1px solid var(--op-border); background: #fff; color: var(--op-muted);
        border-radius: 999px; padding: .45rem 1rem; font-weight: 600; font-size: .875rem; text-decoration: none;
    }
    .op-stat-tabs a.active { background: var(--op-primary); border-color: var(--op-primary); color: #fff; }
    .op-stat-tabs a:not(.active):hover { color: var(--op-primary); border-color: #c7d2fe; }

    /* Daftar peringkat (judul terpopuler) */
    .op-rank-list { list-style: none; padding: 0; margin: 0; }
    .op-rank-list li { display: flex; align-items: center; gap: .85rem; padding: .7rem 0; border-bottom: 1px solid #f0f1f5; }
    .op-rank-list li:last-child { border-bottom: 0; }
    .op-rank-no {
        flex: 0 0 32px; height: 32px; border-radius: 10px; background: var(--op-primary-soft); color: var(--op-primary);
        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .85rem;
    }
    .op-rank-list li:nth-child(-n+3) .op-rank-no { background: linear-gradient(135deg,#d97706,#fbbf24); color: #fff; }
    .op-rank-title { font-weight: 700; color: var(--op-ink); font-size: .9rem; line-height: 1.3; }
    .op-rank-sub { color: var(--op-muted); font-size: .78rem; }
    .op-rank-count { margin-left: auto; text-align: right; font-weight: 800; color: var(--op-ink); white-space: nowrap; }
    .op-rank-count small { display: block; color: var(--op-muted); font-weight: 500; font-size: .72rem; }

    @media (max-width: 767.98px) {
        .op-page-header h1 { font-size: 1.6rem; }
        .op-chart-wrap { height: 260px; }
    }
</style>
