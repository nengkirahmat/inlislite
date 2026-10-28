<?php

namespace Opac\Models;

/**
 * Statistik peminjaman dan pengembalian buku untuk halaman publik OPAC.
 *
 * Satuan hitungan adalah eksemplar (baris collectionloanitems).
 * Pengembalian dianggap terlambat bila tanggal kembali melewati tanggal jatuh tempo.
 */
class StatistikSirkulasiModel extends \App\Models\BaseModel
{
    protected $DBGroup = 'data';
    protected $table = 'collectionloanitems';
    protected $returnType = 'object';

    private const TERLAMBAT = 'DATE(li.ActualReturn) > DATE(li.DueDate)';

    /**
     * Tahun yang memiliki transaksi peminjaman atau pengembalian, terbaru lebih dulu.
     */
    public function getAvailableYears(): array
    {
        $rows = $this->db->query(
            "SELECT tahun FROM (
                SELECT YEAR(LoanDate) AS tahun FROM collectionloanitems WHERE LoanDate IS NOT NULL
                UNION
                SELECT YEAR(ActualReturn) AS tahun FROM collectionloanitems WHERE ActualReturn IS NOT NULL
            ) AS daftar_tahun
            ORDER BY tahun DESC"
        )->getResult();

        return array_map(static fn ($row) => (int) $row->tahun, $rows);
    }

    /**
     * Kondisi peminjaman saat ini (tidak bergantung tahun).
     */
    public function getCurrentLoanSummary(): array
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS dipinjam,
                COALESCE(SUM(DATE(DueDate) < CURDATE()), 0) AS terlambat,
                COUNT(DISTINCT member_id) AS peminjam
            FROM collectionloanitems
            WHERE LoanStatus = 'Loan'"
        )->getRow();

        return [
            'dipinjam'  => (int) $row->dipinjam,
            'terlambat' => (int) $row->terlambat,
            'peminjam'  => (int) $row->peminjam,
        ];
    }

    /**
     * Ringkasan peminjaman satu tahun.
     */
    public function getLoanSummary($year): array
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS eksemplar,
                COUNT(DISTINCT CollectionLoan_id) AS transaksi,
                COUNT(DISTINCT member_id) AS peminjam,
                COUNT(DISTINCT Collection_id) AS koleksi
            FROM collectionloanitems
            WHERE YEAR(LoanDate) = ?",
            [(int) $year]
        )->getRow();

        return [
            'eksemplar' => (int) $row->eksemplar,
            'transaksi' => (int) $row->transaksi,
            'peminjam'  => (int) $row->peminjam,
            'koleksi'   => (int) $row->koleksi,
        ];
    }

    /**
     * Jumlah peminjaman per bulan (12 baris).
     */
    public function getMonthlyLoans($year): array
    {
        $rows = $this->db->query(
            "SELECT MONTH(LoanDate) AS bulan, COUNT(*) AS total
            FROM collectionloanitems
            WHERE YEAR(LoanDate) = ?
            GROUP BY MONTH(LoanDate)",
            [(int) $year]
        )->getResult();

        $months = array_fill(1, 12, 0);
        foreach ($rows as $row) {
            $months[(int) $row->bulan] = (int) $row->total;
        }

        return $months;
    }

    /**
     * Peminjaman per hari dalam seminggu, Senin s.d. Minggu.
     */
    public function getLoansByWeekday($year): array
    {
        $names = [2 => 'Senin', 3 => 'Selasa', 4 => 'Rabu', 5 => 'Kamis', 6 => 'Jumat', 7 => 'Sabtu', 1 => 'Minggu'];
        $days  = [];
        foreach ($names as $index => $name) {
            $days[$index] = ['label' => $name, 'total' => 0];
        }

        $rows = $this->db->query(
            "SELECT DAYOFWEEK(LoanDate) AS hari, COUNT(*) AS total
            FROM collectionloanitems
            WHERE YEAR(LoanDate) = ?
            GROUP BY DAYOFWEEK(LoanDate)",
            [(int) $year]
        )->getResult();

        foreach ($rows as $row) {
            $days[(int) $row->hari]['total'] = (int) $row->total;
        }

        return array_values($days);
    }

    /**
     * Judul yang paling sering dipinjam.
     */
    public function getTopTitles($year, $limit = 10): array
    {
        $rows = $this->db->query(
            "SELECT c.Title AS judul, c.Author AS pengarang, COUNT(*) AS total
            FROM collectionloanitems li
            JOIN collections co ON co.ID = li.Collection_id
            JOIN catalogs c ON c.ID = co.Catalog_id
            WHERE YEAR(li.LoanDate) = ?
            GROUP BY c.ID, c.Title, c.Author
            ORDER BY total DESC, c.Title ASC
            LIMIT " . (int) $limit,
            [(int) $year]
        )->getResult();

        return array_map(static function ($row) {
            // Judul katalog sering berformat "Judul / Pengarang"; cukup ambil bagian judul.
            $judul = trim(explode(' / ', (string) $row->judul)[0]);

            return [
                'judul'     => $judul !== '' ? $judul : '(Tanpa judul)',
                'pengarang' => trim((string) $row->pengarang),
                'total'     => (int) $row->total,
            ];
        }, $rows);
    }

    /**
     * Peminjaman per kelas utama DDC (000-900) berdasarkan nomor panggil katalog.
     */
    public function getLoansByClass($year): array
    {
        $labels = [];
        $master = $this->db->query(
            "SELECT kdKelas, namakelas FROM master_kelas_besar
            WHERE kdKelas IN ('000','100','200','300','400','500','600','700','800','900')"
        )->getResult();
        foreach ($master as $row) {
            $labels[$row->kdKelas[0]] = $row->namakelas;
        }

        $rows = $this->db->query(
            "SELECT
                CASE WHEN TRIM(c.DeweyNo) REGEXP '^[0-9]' THEN LEFT(TRIM(c.DeweyNo), 1) ELSE 'X' END AS kelas,
                COUNT(*) AS total
            FROM collectionloanitems li
            JOIN collections co ON co.ID = li.Collection_id
            JOIN catalogs c ON c.ID = co.Catalog_id
            WHERE YEAR(li.LoanDate) = ?
            GROUP BY kelas
            ORDER BY kelas",
            [(int) $year]
        )->getResult();

        $result = [];
        $lainnya = 0;
        foreach ($rows as $row) {
            if ($row->kelas === 'X') {
                $lainnya += (int) $row->total;
                continue;
            }
            $result[] = [
                'label' => $labels[$row->kelas] ?? ($row->kelas . '00'),
                'total' => (int) $row->total,
            ];
        }
        if ($lainnya > 0) {
            $result[] = ['label' => 'Fiksi / tanpa nomor klas', 'total' => $lainnya];
        }

        return $result;
    }

    /**
     * Peminjaman per kategori koleksi.
     */
    public function getLoansByCategory($year): array
    {
        return $this->breakdown(
            $year,
            "JOIN collections co ON co.ID = li.Collection_id
             LEFT JOIN collectioncategorys cc ON cc.ID = co.Category_id",
            'cc.Name'
        );
    }

    /**
     * Peminjaman per jenis anggota.
     */
    public function getLoansByMemberType($year): array
    {
        return $this->breakdown(
            $year,
            "LEFT JOIN members m ON m.ID = li.member_id
             LEFT JOIN jenis_anggota ja ON ja.id = m.JenisAnggota_id",
            'ja.jenisanggota'
        );
    }

    private function breakdown($year, $join, $labelColumn): array
    {
        $rows = $this->db->query(
            "SELECT
                CASE
                    WHEN {$labelColumn} IS NULL OR TRIM({$labelColumn}) = '' OR {$labelColumn} LIKE '%Tidak Diketahui%' THEN 'Tidak diketahui'
                    ELSE TRIM({$labelColumn})
                END AS label,
                COUNT(*) AS total
            FROM collectionloanitems li
            {$join}
            WHERE YEAR(li.LoanDate) = ?
            GROUP BY label
            ORDER BY total DESC",
            [(int) $year]
        )->getResult();

        return array_map(static fn ($row) => ['label' => ucwords(strtolower($row->label)), 'total' => (int) $row->total], $rows);
    }

    /**
     * Ringkasan pengembalian satu tahun (berdasarkan tanggal kembali).
     */
    public function getReturnSummary($year): array
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(" . self::TERLAMBAT . "), 0) AS terlambat,
                COUNT(DISTINCT li.member_id) AS pengembali
            FROM collectionloanitems li
            WHERE li.ActualReturn IS NOT NULL AND YEAR(li.ActualReturn) = ?",
            [(int) $year]
        )->getRow();

        $durasi = $this->db->query(
            "SELECT GREATEST(DATEDIFF(ActualReturn, LoanDate), 0) AS hari
            FROM collectionloanitems
            WHERE ActualReturn IS NOT NULL AND LoanDate IS NOT NULL AND YEAR(ActualReturn) = ?
            ORDER BY hari",
            [(int) $year]
        )->getResult();

        $total     = (int) $row->total;
        $terlambat = (int) $row->terlambat;

        return [
            'total'         => $total,
            'tepat_waktu'   => $total - $terlambat,
            'terlambat'     => $terlambat,
            'pengembali'    => (int) $row->pengembali,
            'median_durasi' => $this->median(array_map(static fn ($r) => (int) $r->hari, $durasi)),
        ];
    }

    private function median(array $values)
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }

        $middle = intdiv($count, 2);

        return $count % 2 ? $values[$middle] : ($values[$middle - 1] + $values[$middle]) / 2;
    }

    /**
     * Pengembalian per bulan, dipisah tepat waktu dan terlambat.
     */
    public function getMonthlyReturns($year): array
    {
        $rows = $this->db->query(
            "SELECT
                MONTH(li.ActualReturn) AS bulan,
                COUNT(*) AS total,
                COALESCE(SUM(" . self::TERLAMBAT . "), 0) AS terlambat
            FROM collectionloanitems li
            WHERE li.ActualReturn IS NOT NULL AND YEAR(li.ActualReturn) = ?
            GROUP BY MONTH(li.ActualReturn)",
            [(int) $year]
        )->getResult();

        $months = [];
        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = ['tepat_waktu' => 0, 'terlambat' => 0];
        }
        foreach ($rows as $row) {
            $months[(int) $row->bulan] = [
                'tepat_waktu' => (int) $row->total - (int) $row->terlambat,
                'terlambat'   => (int) $row->terlambat,
            ];
        }

        return $months;
    }

    /**
     * Sebaran lama keterlambatan pengembalian pada tahun tertentu.
     */
    public function getLatenessDistribution($year): array
    {
        $row = $this->db->query(
            "SELECT
                COALESCE(SUM(t.hari BETWEEN 1 AND 3), 0) AS a,
                COALESCE(SUM(t.hari BETWEEN 4 AND 7), 0) AS b,
                COALESCE(SUM(t.hari BETWEEN 8 AND 30), 0) AS c,
                COALESCE(SUM(t.hari > 30), 0) AS d
            FROM (
                SELECT DATEDIFF(DATE(li.ActualReturn), DATE(li.DueDate)) AS hari
                FROM collectionloanitems li
                WHERE li.ActualReturn IS NOT NULL AND YEAR(li.ActualReturn) = ? AND " . self::TERLAMBAT . "
            ) AS t",
            [(int) $year]
        )->getRow();

        return $this->buckets($row, ['1–3 hari', '4–7 hari', '8–30 hari', 'Lebih dari 30 hari']);
    }

    /**
     * Sebaran lama peminjaman (dari tanggal pinjam sampai kembali).
     */
    public function getDurationDistribution($year): array
    {
        $row = $this->db->query(
            "SELECT
                COALESCE(SUM(t.hari <= 7), 0) AS a,
                COALESCE(SUM(t.hari BETWEEN 8 AND 14), 0) AS b,
                COALESCE(SUM(t.hari BETWEEN 15 AND 30), 0) AS c,
                COALESCE(SUM(t.hari > 30), 0) AS d
            FROM (
                SELECT GREATEST(DATEDIFF(ActualReturn, LoanDate), 0) AS hari
                FROM collectionloanitems
                WHERE ActualReturn IS NOT NULL AND LoanDate IS NOT NULL AND YEAR(ActualReturn) = ?
            ) AS t",
            [(int) $year]
        )->getRow();

        return $this->buckets($row, ['Sampai 7 hari', '8–14 hari', '15–30 hari', 'Lebih dari 30 hari']);
    }

    /**
     * Pinjaman yang saat ini melewati jatuh tempo, dikelompokkan menurut lama terlambat.
     */
    public function getCurrentOverdueDistribution(): array
    {
        $row = $this->db->query(
            "SELECT
                COALESCE(SUM(t.hari BETWEEN 1 AND 7), 0) AS a,
                COALESCE(SUM(t.hari BETWEEN 8 AND 30), 0) AS b,
                COALESCE(SUM(t.hari BETWEEN 31 AND 365), 0) AS c,
                COALESCE(SUM(t.hari > 365), 0) AS d
            FROM (
                SELECT DATEDIFF(CURDATE(), DATE(DueDate)) AS hari
                FROM collectionloanitems
                WHERE LoanStatus = 'Loan' AND DATE(DueDate) < CURDATE()
            ) AS t"
        )->getRow();

        return $this->buckets($row, ['1–7 hari', '8–30 hari', '1–12 bulan', 'Lebih dari 1 tahun']);
    }

    private function buckets($row, array $labels): array
    {
        $values = [(int) $row->a, (int) $row->b, (int) $row->c, (int) $row->d];
        $result = [];
        foreach ($labels as $index => $label) {
            $result[] = ['label' => $label, 'total' => $values[$index]];
        }

        return $result;
    }

    /**
     * Rekap peminjaman dan pengembalian per tahun.
     */
    public function getYearlySummary(): array
    {
        $loans = $this->db->query(
            "SELECT YEAR(LoanDate) AS tahun, COUNT(*) AS dipinjam
            FROM collectionloanitems
            WHERE LoanDate IS NOT NULL
            GROUP BY YEAR(LoanDate)"
        )->getResult();

        $returns = $this->db->query(
            "SELECT YEAR(li.ActualReturn) AS tahun, COUNT(*) AS dikembalikan, COALESCE(SUM(" . self::TERLAMBAT . "), 0) AS terlambat
            FROM collectionloanitems li
            WHERE li.ActualReturn IS NOT NULL
            GROUP BY YEAR(li.ActualReturn)"
        )->getResult();

        $years = [];
        $empty = ['dipinjam' => 0, 'dikembalikan' => 0, 'terlambat' => 0];
        foreach ($loans as $row) {
            $years[(int) $row->tahun] = ['tahun' => (int) $row->tahun] + $empty;
            $years[(int) $row->tahun]['dipinjam'] = (int) $row->dipinjam;
        }
        foreach ($returns as $row) {
            $year = (int) $row->tahun;
            $years[$year] = $years[$year] ?? (['tahun' => $year] + $empty);
            $years[$year]['dikembalikan'] = (int) $row->dikembalikan;
            $years[$year]['terlambat']    = (int) $row->terlambat;
        }

        krsort($years);

        return array_values($years);
    }
}
