<?php
  private function _get_data_query_pps($filter=array()){
    $DB2 = $this->load->database('old_db', TRUE);
    $search = $_POST['search']['value']; 
    $columns = $_POST['columns'] ?? [];
    $criteria = array();

    $sql = "SELECT * FROM accfin_pph_supplier_dasar ";
        
    if (!empty($filter['tahun'])) { //filter tahun
      $criteria[] .= " `accfin_pph_supplier_dasar`.`tahun` = '" . $DB2->escape_str($filter['tahun']) . "' ";
    }

    if (!empty($search)) { //pencarian global
        $searchLower = strtolower(trim($search));
        $bulanList = array_map('bulanIndo', range(1, 12)); //konversi angka ke bulan karena value yg kesimpen angka
        $bulanMatches = [];

        foreach ($bulanList as $i => $namaBulan) { //buat pencarian bulan okt, okto, oktob (like)
            if (strpos(strtolower($namaBulan), $searchLower) !== false) {
                $bulanMatches[] = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            }
        } if (is_numeric($search) && (int)$search >= 1 && (int)$search <= 12) {
            $bulanMatches[] = str_pad($search, 2, '0', STR_PAD_LEFT);
        }

        $bulanMatches = array_unique($bulanMatches);

        $searchTanggal = str_replace('-', '/', $search); //ganti - jadi /
        $dateObj = DateTime::createFromFormat('d/m/Y', $searchTanggal); //ubah ke d/m/Y

        $tanggalQuery = [];
        if ($dateObj && $dateObj->format('d/m/Y') === $searchTanggal) {
            $tanggalQuery[] = "DATE_FORMAT(`tanggal_bayar`, '%d/%m/%Y') LIKE '%" . $DB2->escape_like_str($searchTanggal) . "%'";
            $tanggalQuery[] = "DATE_FORMAT(`tanggal_lapor`, '%d/%m/%Y') LIKE '%" . $DB2->escape_like_str($searchTanggal) . "%'";
            $tanggalQuery[] = "`tanggal_bayar` LIKE '%" . $DB2->escape_like_str($dateObj->format('Y-m-d')) . "%'";
            $tanggalQuery[] = "`tanggal_lapor` LIKE '%" . $DB2->escape_like_str($dateObj->format('Y-m-d')) . "%'";
        } else {
            $tanggalQuery[] = "DATE_FORMAT(`tanggal_bayar`, '%d/%m/%Y') LIKE '%" . $DB2->escape_like_str($searchTanggal) . "%'";
            $tanggalQuery[] = "DATE_FORMAT(`tanggal_lapor`, '%d/%m/%Y') LIKE '%" . $DB2->escape_like_str($searchTanggal) . "%'";
        }

        $bulanQuery = ""; // nyocokin bulan
        if (!empty($bulanMatches)) {
            $inClause = implode("','", array_map([$DB2, 'escape_str'], $bulanMatches));
            $bulanQuery = " OR `bulan` IN ('{$inClause}')";
        } else {
            $bulanQuery = " OR `bulan` LIKE '%" . $DB2->escape_like_str($search) . "%'";
        }

        // query pencarian global
        $criteria[] = "( 
            `no_laporan` LIKE '%" . $DB2->escape_like_str($search) . "%'
            {$bulanQuery}
            OR `status_spt` LIKE '%" . $DB2->escape_like_str($search) . "%'
            OR `billing` LIKE '%" . $DB2->escape_like_str(str_replace(['.', ','], '', $search)) . "%'
            OR " . implode(' OR ', $tanggalQuery) . "
        )";
    }

    foreach ($columns as $index => $column) { //pencarian per kolom
      $searchValue = $column['search']['value'];
      if (!empty($searchValue)) {
        switch ($index) {
          case 1:
            $criteria[] .= " `no_laporan` LIKE '%" . $DB2->escape_like_str($searchValue) . "%'";
            break;
          case 2:
            // Normalisasi input
            $searchLower = strtolower(trim($searchValue));
            $bulanList = array_map('bulanIndo', range(1, 12));
            $bulanAngka = null;
            $bulanMatches = [];

            foreach ($bulanList as $i => $namaBulan) {
                if (strpos(strtolower($namaBulan), $searchLower) !== false) {
                    $bulanMatches[] = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                }
            } if (is_numeric($searchValue) && (int)$searchValue >= 1 && (int)$searchValue <= 12) {
                $bulanMatches[] = str_pad($searchValue, 2, '0', STR_PAD_LEFT);
            }

            $bulanMatches = array_unique($bulanMatches);

            if (!empty($bulanMatches)) {
                $inClause = implode("','", array_map([$DB2, 'escape_str'], $bulanMatches));
                $criteria[] = " `bulan` IN ('{$inClause}')";
            } else {
                $criteria[] = " `bulan` LIKE '%" . $DB2->escape_like_str($searchValue) . "%'";
            }
            break;
          case 3:
            $criteria[] .= " `status_spt` LIKE '%" . $DB2->escape_like_str($searchValue) . "%'";
            break;
          case 4:
            $searchNumeric = str_replace(['.', ','], '', $searchValue);
            $criteria[] = " `billing` LIKE '%" . $DB2->escape_like_str($searchNumeric) . "%'";
            break;
          case 5: 
            $searchTanggal = trim($searchValue);
            $searchTanggal = str_replace('-', '/', $searchTanggal);

            $dateObj = DateTime::createFromFormat('d/m/Y', $searchTanggal);
            if ($dateObj && $dateObj->format('d/m/Y') === $searchTanggal) {
                $criteria[] = " DATE_FORMAT(`tanggal_bayar`, '%d/%m/%Y') LIKE '%" . $DB2->escape_like_str($searchTanggal) . "%'
                                OR `tanggal_bayar` LIKE '%" . $DB2->escape_like_str($dateObj->format('Y-m-d')) . "%'";
            } else {
                $criteria[] = " DATE_FORMAT(`tanggal_bayar`, '%d/%m/%Y') LIKE '%" . $DB2->escape_like_str($searchTanggal) . "%'";
            }
            break;
          case 6: 
            $searchTanggal = trim($searchValue);
            $searchTanggal = str_replace('-', '/', $searchTanggal);

            $dateObj = DateTime::createFromFormat('d/m/Y', $searchTanggal);
            if ($dateObj && $dateObj->format('d/m/Y') === $searchTanggal) {
                $criteria[] = " DATE_FORMAT(`tanggal_lapor`, '%d/%m/%Y') LIKE '%" . $DB2->escape_like_str($searchTanggal) . "%'
                                OR `tanggal_lapor` LIKE '%" . $DB2->escape_like_str($dateObj->format('Y-m-d')) . "%'";
            } else {
                $criteria[] = " DATE_FORMAT(`tanggal_lapor`, '%d/%m/%Y') LIKE '%" . $DB2->escape_like_str($searchTanggal) . "%'";
            }
            break;
        }
      }
    }

    if ($criteria) {
        $sql .= " WHERE " . implode(' AND ', $criteria);
    }
    
    if (isset($_POST['order'])) { //mapping index kolom
        $order = $_POST['order'][0];
        $order_column_index = $order['column'];
        $order_dir = strtoupper($order['dir']);

        $orderable_columns = [
            0  => null,                 
            1  => '`no_laporan`',          
            2  => '`bulan`',
            3  => '`status_spt`',
            4  => '`billing`',
            5  => '`tanggal_bayar`',
            6  => '`tanggal_lapor`'
        ];

        if (isset($orderable_columns[$order_column_index])) {
            $sql .= " ORDER BY " . $orderable_columns[$order_column_index] . " " . $order_dir;
        } else {
            $sql .= " ORDER BY `accfin_pph_supplier_dasar`.`no_laporan` DESC"; 
        }
    } else {
        $sql .= " ORDER BY `accfin_pph_supplier_dasar`.`no_laporan` DESC"; 
    }

    return $sql;
  } public function getPPSServerside($search, $start, $length, $order_column, $order_dir, $filter){
    $DB2 = $this->load->database('old_db', TRUE);
    $sql = $this->_get_data_query_pps($filter);
    if ($_POST['length'] != -1) {
      $sql .= " LIMIT " . $DB2->escape_str($_POST['start']) . ", " . $DB2->escape_str($_POST['length']);
    }
    $query = $DB2->query($sql);
    return $query->result();
  } public function count_filtered_data_pps($search, $filter){
    $DB2 = $this->load->database('old_db', TRUE);
    $sql = $this->_get_data_query_pps($filter);
    $query = $DB2->query($sql);
    return $query->num_rows();
  } public function count_all_data_pps(){
    $DB2 = $this->load->database('old_db', TRUE);
    $query = $DB2->query('SELECT COUNT(*) as count FROM `accfin_pph_supplier_dasar`');
    $result = $query->row();
    return $result->count;
  }
