<?php 
public function config(){
$data = $this->input->post();
//// bla bla bla code
else if($data['config']==='getrekapppsserverside'){
        $search = $_POST['search']['value'];
        $start = $_POST['start'];
        $length = $_POST['length'];
        $order = $_POST['order'];
        $column_order = ['','no_laporan','bulan','status_spt','billing','tanggal_bayar','tanggal_lapor']; //kolom-kolom , di ujung empty string karena nomor doang.
        $order_column = $column_order[$order[0]['column']];
        $order_dir = $order[0]['dir'];

        $tahun = $this->input->get('tahun') ? $this->input->get('tahun') : '';
        $filter['tahun'] = $tahun;

        $column_filters = $this->input->post('column_filters'); // kirim filter footer

        $results = $this->logistik_ihsan->getPPSServerside($search, $start, $length, $order_column, $order_dir, $filter, $column_filters);

        $data = [];
        $no = $start;

        foreach ($results as $result) {
           
            $row = array();

            $row[] = '<center>' . ++$no . '</center>';

            // No Laporan
            $no_laporan = !empty($result->{'no_laporan'}) ? '<a href="'.base_url('finance/financepphsupplier/view?user_token=').$this->session->userdata('user_token').'&id='.$result->recid.'" target="_blank" class="text-birumsa">' . $result->{'no_laporan'} . '</a>' : '';
            $row[] = $no_laporan;

            // Bulan
            $bulan = !empty($result->{'bulan'}) ? '<span class="">' . bulanIndo($result->{'bulan'}) . '</span>' : '';
            $row[] = $bulan;

            // Status SPT
            $status_spt = !empty($result->{'status_spt'}) ? '<span class="">' . $result->{'status_spt'} . '</span>' : '';
            $row[] = $status_spt;

            // Billing
            $billing = !empty($result->{'billing'}) ? '<span class="text-right">' . number_format($result->{'billing'},0,',','.') . '</span>' : '';
            $row[] = $billing;

            // Tgl Bayar
            $tanggal_bayar = !empty($result->{'tanggal_bayar'}) ? '<span class="text-center">' . date('d/m/Y', strtotime($result->{'tanggal_bayar'})) . '</span>' : '';
            $row[] = $tanggal_bayar;

            // Tgl Lapor
            $tanggal_lapor = !empty($result->{'tanggal_lapor'}) ? '<span class="text-center">' . date('d/m/Y', strtotime($result->{'tanggal_lapor'})) . '</span>' : '';
            $row[] = $tanggal_lapor;
            
            $data[] = $row;
        }


        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->logistik_ihsan->count_all_data_pps(),
            "recordsFiltered" => $this->logistik_ihsan->count_filtered_data_pps($search, $filter),
            "data" => $data
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
    }
}
