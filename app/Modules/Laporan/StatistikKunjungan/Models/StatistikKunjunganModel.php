<?php

namespace StatistikKunjungan\Models;

class StatistikKunjunganModel extends \App\Models\BaseModel
{
    protected $DBGroup = 'data';
    protected $table = 'memberguesses';
    protected $returnType = 'object';

    /**
     * Tahun yang memiliki data kunjungan, diurutkan dari yang terbaru.
     */
    public function getAvailableYears(): array
    {
        $sql = "SELECT tahun
                FROM (
                    SELECT YEAR(CreateDate) AS tahun
                    FROM memberguesses
                    WHERE CreateDate IS NOT NULL
                    UNION
                    SELECT YEAR(CreateDate) AS tahun
                    FROM groupguesses
                    WHERE CreateDate IS NOT NULL
                ) AS daftar_tahun
                ORDER BY tahun DESC";

        $rows = $this->db->query($sql)->getResult();

        return array_map(static fn ($row) => (int) $row->tahun, $rows);
    }

    /**
     * Ringkasan kunjungan pada rentang tanggal tertentu.
     *
     * Kunjungan anggota dan non-anggota berasal dari memberguesses.
     * Rombongan berasal dari groupguesses, sedangkan total kunjungan
     * memakai jumlah personel rombongan (bukan jumlah rombongan).
     */
    public function getPeriodSummary($startDate = null, $endDate = null): array
    {
        $memberSql = "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN NoAnggota IS NOT NULL AND NoAnggota <> '' THEN 1 ELSE 0 END), 0) AS anggota,
                COALESCE(SUM(CASE WHEN NoAnggota IS NULL OR NoAnggota = '' THEN 1 ELSE 0 END), 0) AS non_anggota
            FROM memberguesses
            WHERE 1 = 1";
        $memberParams = [];

        if (!empty($startDate)) {
            $memberSql .= " AND CreateDate >= ?";
            $memberParams[] = $startDate . ' 00:00:00';
        }

        if (!empty($endDate)) {
            $memberSql .= " AND CreateDate <= ?";
            $memberParams[] = $endDate . ' 23:59:59';
        }

        $member = $this->db->query($memberSql, $memberParams)->getRow();

        $groupSql = "SELECT
                COUNT(*) AS rombongan,
                COALESCE(SUM(CountPersonel), 0) AS personel
            FROM groupguesses
            WHERE 1 = 1";
        $groupParams = [];

        if (!empty($startDate)) {
            $groupSql .= " AND CreateDate >= ?";
            $groupParams[] = $startDate . ' 00:00:00';
        }

        if (!empty($endDate)) {
            $groupSql .= " AND CreateDate <= ?";
            $groupParams[] = $endDate . ' 23:59:59';
        }

        $group = $this->db->query($groupSql, $groupParams)->getRow();

        $anggota    = (int) ($member->anggota ?? 0);
        $nonAnggota = (int) ($member->non_anggota ?? 0);
        $rombongan  = (int) ($group->rombongan ?? 0);
        $personel   = (int) ($group->personel ?? 0);

        return [
            'anggota'     => $anggota,
            'non_anggota' => $nonAnggota,
            'rombongan'   => $rombongan,
            'personel'    => $personel,
            'total'       => $anggota + $nonAnggota + $personel,
        ];
    }

    /**
     * Rekap 12 bulan untuk tahun tertentu.
     */
    public function getMonthlySummary($year): array
    {
        $memberRows = $this->db->query(
            "SELECT
                MONTH(CreateDate) AS bulan,
                COALESCE(SUM(CASE WHEN NoAnggota IS NOT NULL AND NoAnggota <> '' THEN 1 ELSE 0 END), 0) AS anggota,
                COALESCE(SUM(CASE WHEN NoAnggota IS NULL OR NoAnggota = '' THEN 1 ELSE 0 END), 0) AS non_anggota
            FROM memberguesses
            WHERE YEAR(CreateDate) = ?
            GROUP BY MONTH(CreateDate)
            ORDER BY MONTH(CreateDate)",
            [(int) $year]
        )->getResult();

        $groupRows = $this->db->query(
            "SELECT
                MONTH(CreateDate) AS bulan,
                COUNT(*) AS rombongan,
                COALESCE(SUM(CountPersonel), 0) AS personel
            FROM groupguesses
            WHERE YEAR(CreateDate) = ?
            GROUP BY MONTH(CreateDate)
            ORDER BY MONTH(CreateDate)",
            [(int) $year]
        )->getResult();

        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = [
                'bulan'       => $month,
                'anggota'     => 0,
                'non_anggota' => 0,
                'rombongan'   => 0,
                'personel'    => 0,
                'total'       => 0,
            ];
        }

        foreach ($memberRows as $row) {
            $month = (int) $row->bulan;
            if (isset($months[$month])) {
                $months[$month]['anggota']     = (int) $row->anggota;
                $months[$month]['non_anggota'] = (int) $row->non_anggota;
            }
        }

        foreach ($groupRows as $row) {
            $month = (int) $row->bulan;
            if (isset($months[$month])) {
                $months[$month]['rombongan'] = (int) $row->rombongan;
                $months[$month]['personel']  = (int) $row->personel;
            }
        }

        foreach ($months as &$month) {
            $month['total'] = $month['anggota'] + $month['non_anggota'] + $month['personel'];
        }
        unset($month);

        return array_values($months);
    }

    /**
     * Rekap harian untuk bulan tertentu.
     */
    public function getDailySummary($year, $month): array
    {
        $daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', (int) $year, (int) $month)));
        $days = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $days[$day] = [
                'hari'        => $day,
                'anggota'     => 0,
                'non_anggota' => 0,
                'rombongan'   => 0,
                'personel'    => 0,
                'total'       => 0,
            ];
        }

        $memberRows = $this->db->query(
            "SELECT
                DAY(CreateDate) AS hari,
                COALESCE(SUM(CASE WHEN NoAnggota IS NOT NULL AND NoAnggota <> '' THEN 1 ELSE 0 END), 0) AS anggota,
                COALESCE(SUM(CASE WHEN NoAnggota IS NULL OR NoAnggota = '' THEN 1 ELSE 0 END), 0) AS non_anggota
            FROM memberguesses
            WHERE YEAR(CreateDate) = ? AND MONTH(CreateDate) = ?
            GROUP BY DAY(CreateDate)
            ORDER BY DAY(CreateDate)",
            [(int) $year, (int) $month]
        )->getResult();

        $groupRows = $this->db->query(
            "SELECT
                DAY(CreateDate) AS hari,
                COUNT(*) AS rombongan,
                COALESCE(SUM(CountPersonel), 0) AS personel
            FROM groupguesses
            WHERE YEAR(CreateDate) = ? AND MONTH(CreateDate) = ?
            GROUP BY DAY(CreateDate)
            ORDER BY DAY(CreateDate)",
            [(int) $year, (int) $month]
        )->getResult();

        foreach ($memberRows as $row) {
            $day = (int) $row->hari;
            if (isset($days[$day])) {
                $days[$day]['anggota']     = (int) $row->anggota;
                $days[$day]['non_anggota'] = (int) $row->non_anggota;
            }
        }

        foreach ($groupRows as $row) {
            $day = (int) $row->hari;
            if (isset($days[$day])) {
                $days[$day]['rombongan'] = (int) $row->rombongan;
                $days[$day]['personel']  = (int) $row->personel;
            }
        }

        foreach ($days as &$day) {
            $day['total'] = $day['anggota'] + $day['non_anggota'] + $day['personel'];
        }
        unset($day);

        return array_values($days);
    }

    /**
     * Rekap seluruh tahun yang memiliki data.
     */
    public function getYearlySummary(): array
    {
        $memberRows = $this->db->query(
            "SELECT
                YEAR(CreateDate) AS tahun,
                COALESCE(SUM(CASE WHEN NoAnggota IS NOT NULL AND NoAnggota <> '' THEN 1 ELSE 0 END), 0) AS anggota,
                COALESCE(SUM(CASE WHEN NoAnggota IS NULL OR NoAnggota = '' THEN 1 ELSE 0 END), 0) AS non_anggota
            FROM memberguesses
            WHERE CreateDate IS NOT NULL
            GROUP BY YEAR(CreateDate)
            ORDER BY YEAR(CreateDate)"
        )->getResult();

        $groupRows = $this->db->query(
            "SELECT
                YEAR(CreateDate) AS tahun,
                COUNT(*) AS rombongan,
                COALESCE(SUM(CountPersonel), 0) AS personel
            FROM groupguesses
            WHERE CreateDate IS NOT NULL
            GROUP BY YEAR(CreateDate)
            ORDER BY YEAR(CreateDate)"
        )->getResult();

        $years = [];

        foreach ($memberRows as $row) {
            $year = (int) $row->tahun;
            if (!isset($years[$year])) {
                $years[$year] = [
                    'tahun'       => $year,
                    'anggota'     => 0,
                    'non_anggota' => 0,
                    'rombongan'   => 0,
                    'personel'    => 0,
                    'total'       => 0,
                ];
            }
            $years[$year]['anggota']     = (int) $row->anggota;
            $years[$year]['non_anggota'] = (int) $row->non_anggota;
        }

        foreach ($groupRows as $row) {
            $year = (int) $row->tahun;
            if (!isset($years[$year])) {
                $years[$year] = [
                    'tahun'       => $year,
                    'anggota'     => 0,
                    'non_anggota' => 0,
                    'rombongan'   => 0,
                    'personel'    => 0,
                    'total'       => 0,
                ];
            }
            $years[$year]['rombongan'] = (int) $row->rombongan;
            $years[$year]['personel']  = (int) $row->personel;
        }

        foreach ($years as &$year) {
            $year['total'] = $year['anggota'] + $year['non_anggota'] + $year['personel'];
        }
        unset($year);

        krsort($years);

        return array_values($years);
    }

    /**
     * Ekspresi untuk mengambil kolom profil pengunjung perorangan:
     * anggota memakai data dari tabel members, non-anggota memakai isian buku tamu.
     */
    private function profileColumn($memberColumn, $guestColumn): string
    {
        return "CASE WHEN mg.NoAnggota IS NOT NULL AND mg.NoAnggota <> '' THEN m.{$memberColumn} ELSE mg.{$guestColumn} END";
    }

    /**
     * Jumlah baris per kategori untuk pengunjung perorangan pada tahun tertentu.
     */
    private function individualBreakdown($year, $join, $labelColumn, $limit = null): array
    {
        $sql = "SELECT COALESCE(NULLIF(TRIM({$labelColumn}), ''), 'Tidak diketahui') AS label, COUNT(*) AS total
            FROM memberguesses mg
            LEFT JOIN members m ON m.MemberNo = mg.NoAnggota
            {$join}
            WHERE YEAR(mg.CreateDate) = ?
            GROUP BY label
            ORDER BY total DESC";

        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $rows = $this->db->query($sql, [(int) $year])->getResult();

        return array_map(static fn ($row) => ['label' => $row->label, 'total' => (int) $row->total], $rows);
    }

    /**
     * Jenis kelamin pengunjung, gabungan perorangan dan personel rombongan.
     */
    public function getGenderSummary($year): array
    {
        $rows = $this->individualBreakdown(
            $year,
            'LEFT JOIN jenis_kelamin jk ON jk.ID = ' . $this->profileColumn('Sex_id', 'JenisKelamin_id'),
            'jk.Name'
        );

        $group = $this->db->query(
            "SELECT COALESCE(SUM(CountLaki), 0) AS laki, COALESCE(SUM(CountPerempuan), 0) AS perempuan
            FROM groupguesses
            WHERE YEAR(CreateDate) = ?",
            [(int) $year]
        )->getRow();

        $summary = [];
        foreach ($rows as $row) {
            $summary[$row['label']] = $row['total'];
        }

        $summary['Laki-laki'] = ($summary['Laki-laki'] ?? 0) + (int) $group->laki;
        $summary['Perempuan'] = ($summary['Perempuan'] ?? 0) + (int) $group->perempuan;

        $summary = array_filter($summary);
        arsort($summary);

        $result = [];
        foreach ($summary as $label => $total) {
            $result[] = ['label' => $label, 'total' => $total];
        }

        return $result;
    }

    /**
     * Pekerjaan pengunjung perorangan.
     */
    public function getJobSummary($year, $limit = 10): array
    {
        return $this->individualBreakdown(
            $year,
            'LEFT JOIN master_pekerjaan mp ON mp.id = ' . $this->profileColumn('Job_id', 'Profesi_id'),
            'mp.Pekerjaan',
            $limit
        );
    }

    /**
     * Pendidikan terakhir pengunjung perorangan.
     */
    public function getEducationSummary($year, $limit = 10): array
    {
        return $this->individualBreakdown(
            $year,
            'LEFT JOIN master_pendidikan md ON md.id = ' . $this->profileColumn('EducationLevel_id', 'PendidikanTerakhir_id'),
            'md.Nama',
            $limit
        );
    }

    /**
     * Ruang yang dikunjungi pengunjung perorangan.
     */
    public function getRoomSummary($year, $limit = 10): array
    {
        return $this->individualBreakdown(
            $year,
            'LEFT JOIN locations l ON l.ID = mg.Location_Id',
            'l.Name',
            $limit
        );
    }

    /**
     * Kunjungan per hari dalam seminggu (Senin s.d. Minggu), gabungan perorangan dan rombongan.
     */
    public function getWeekdaySummary($year): array
    {
        $names = [2 => 'Senin', 3 => 'Selasa', 4 => 'Rabu', 5 => 'Kamis', 6 => 'Jumat', 7 => 'Sabtu', 1 => 'Minggu'];
        $days  = [];

        foreach ($names as $index => $name) {
            $days[$index] = ['label' => $name, 'total' => 0];
        }

        $memberRows = $this->db->query(
            "SELECT DAYOFWEEK(CreateDate) AS hari, COUNT(*) AS total
            FROM memberguesses
            WHERE YEAR(CreateDate) = ?
            GROUP BY DAYOFWEEK(CreateDate)",
            [(int) $year]
        )->getResult();

        $groupRows = $this->db->query(
            "SELECT DAYOFWEEK(CreateDate) AS hari, COALESCE(SUM(CountPersonel), 0) AS total
            FROM groupguesses
            WHERE YEAR(CreateDate) = ?
            GROUP BY DAYOFWEEK(CreateDate)",
            [(int) $year]
        )->getResult();

        foreach (array_merge($memberRows, $groupRows) as $row) {
            $index = (int) $row->hari;
            if (isset($days[$index])) {
                $days[$index]['total'] += (int) $row->total;
            }
        }

        return array_values($days);
    }

    /**
     * Komposisi personel rombongan berdasarkan kategori pekerjaan yang diisi pada buku tamu rombongan.
     */
    public function getGroupCategorySummary($year): array
    {
        $columns = [
            'CountPelajar'    => 'Pelajar',
            'CountMahasiswa'  => 'Mahasiswa',
            'CountGuru'       => 'Guru',
            'CountDosen'      => 'Dosen',
            'CountPNS'        => 'PNS',
            'CountPSwasta'    => 'Pegawai Swasta',
            'CountPeneliti'   => 'Peneliti',
            'CountTNI'        => 'TNI/Polri',
            'CountWiraswasta' => 'Wiraswasta',
            'CountPensiunan'  => 'Pensiunan',
            'CountLainnya'    => 'Lainnya',
        ];

        $select = [];
        foreach ($columns as $column => $label) {
            $select[] = "COALESCE(SUM({$column}), 0) AS {$column}";
        }

        $row = $this->db->query(
            'SELECT ' . implode(', ', $select) . ' FROM groupguesses WHERE YEAR(CreateDate) = ?',
            [(int) $year]
        )->getRowArray();

        $result = [];
        foreach ($columns as $column => $label) {
            if ((int) $row[$column] > 0) {
                $result[] = ['label' => $label, 'total' => (int) $row[$column]];
            }
        }

        usort($result, static fn ($a, $b) => $b['total'] <=> $a['total']);

        return $result;
    }

    /**
     * Instansi asal rombongan dengan jumlah personel terbanyak.
     */
    public function getTopInstitutions($year, $limit = 5): array
    {
        $rows = $this->db->query(
            "SELECT TRIM(AsalInstansi) AS instansi, COUNT(*) AS rombongan, COALESCE(SUM(CountPersonel), 0) AS personel
            FROM groupguesses
            WHERE YEAR(CreateDate) = ? AND AsalInstansi IS NOT NULL AND TRIM(AsalInstansi) <> ''
            GROUP BY TRIM(AsalInstansi)
            ORDER BY personel DESC, rombongan DESC
            LIMIT " . (int) $limit,
            [(int) $year]
        )->getResult();

        return array_map(static fn ($row) => [
            'instansi'  => $row->instansi,
            'rombongan' => (int) $row->rombongan,
            'personel'  => (int) $row->personel,
        ], $rows);
    }
}
