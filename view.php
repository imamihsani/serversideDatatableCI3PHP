<html!>
 <div class="table-responsive">
              <table class="table table-sm table-bordered table-striped nowrap" width="100%" id="table-list-serverside">
                <thead class="text-light bg-birumsa">
                  <tr>
                    <th class="searchable bg-birumsa" style="width:1%">No.</th>
                    <th class="searchable bg-birumsa" style="">Laporan</th>
                    <th class="searchable bg-birumsa" style="">Bulan</th>
                    <th class="searchable" style="">Status SPT</th>
                    <th class="searchable" style="">Billing</th>
                    <th class="searchable" style="">Tgl. Bayar</th>
                    <th class="searchable" style="">Tgl. Lapor</th>
                  </tr>
                </thead>
                <tbody>
                    
                </tbody>
                <tfoot>
                    <tr>
                      <th></th>
                      <th><input type="text" class="form-control form-control-sm" data-column-name="no_laporan" placeholder="Filter No. Laporan" /></th>
                      <th><input type="text" class="form-control form-control-sm" data-column-name="bulan" placeholder="Filter Bulan" /></th>
                      <th><input type="text" class="form-control form-control-sm" data-column-name="status_spt" placeholder="Filter Status SPT" /></th>
                      <th><input type="text" class="form-control form-control-sm" data-column-name="billing" placeholder="Filter Billing" /></th>
                      <th><input type="text" class="form-control form-control-sm" data-column-name="tanggal_bayar" placeholder="Filter Tgl. Bayar" /></th>
                      <th><input type="text" class="form-control form-control-sm" data-column-name="tanggal_lapor" placeholder="Filter Tgl. Lapor" /></th>
                    </tr>
                </tfoot>
              </table>
            </div>

</html>
<script>
  document.addEventListener("DOMContentLoaded", function(event) {

    $('[data-widget="pushmenu"]').click(); //auto klik hamburger menu biar layar lebar

    //fungsi bikin dd/mm/yyyy diilangin kalau gaada isinya
    function displayKolomTanggal() {
        $('.tanggal-input').on('input change blur focus', function () {
            if (this.value.length === 10) {
                $(this).css('color', 'black');
            } else if (this === document.activeElement) {
                $(this).css('color', '#999'); 
            } else {
                $(this).css('color', 'transparent');
            }
        }).trigger('change');
    }
    displayKolomTanggal(); //langsung setting display kolom tanggal

    /////datatble serverside
    var config = 'getrekapppsserverside';
    var table = $('#table-list-serverside').DataTable({
            "searching": true,
            "processing": true,
            "serverSide": true,
            "ordering": true,
            "filter": true,
            "order": [1, "desc"],
            // lengthMenu: [
            //   [10, 15, 25, 50, 100, -1],
            //   [10, 15, 25, 50, 100, 'All']
            // ],
            lengthMenu: [
                [10, 15, 25, 50, 100],
                [10, 15, 25, 50, 100]
            ],
            "pageLength": 100,
            "scrollX": true,
            "scrollY": 400,
            "scrollCollapse": true,
            // "language": {
            //     "infoFiltered": "", //buat gausah nampilin jumlah data keseluruhan di tabel
            //     "info": "Showing _START_ to _END_ of _END_ entries" // gausah tampilin total data, soalnya udah pake All lengthset nya
            // },
            "fixedColumns": {
                leftColumns: 3
            },
            "dom": '<"row pt-2 px-2"<"col-sm-12 col-md-auto"l><"col-sm-12 col-md"<"#toolbar.row justify-content-md-center">><"col-sm-12 col-md-auto"f>><"row px-2 m-0"<"col-sm-12"<"#toolbar2.col-12">>>rt<"row"<"col-sm-12 col-md"i><"col-sm-12 col-md-7"p>>',

            "ajax": {
              
                "url": "<?= base_url('finance/financepphsupplier/config?user_token=') . $this->session->userdata('user_token'); ?>",
                "type": "POST",
                "data": function (d) {  
                    d.config = config; 
                    d.tahun = $('#filter-tahun').val();
                }
            },
            "columnDefs": [
              {
                "targets": -1,
                "orderable": true,
                "searchable": true,
                "serverSide": true
              },
              { targets: 4, className: 'text-right' } ,
              { targets: [5,6], className: 'text-center' } 

            ],
            "initComplete": function() {
                var table = this.api();
                $("#toolbar").html(
                    `<div class="d-flex">

                    <select class="form-control form-control-sm" name="tahun" id="filter-tahun">
                      <option value="">All Tahun</option>
                      <?php foreach($list_tahun as $tahun):?>
                      <option value="<?=$tahun['tahun']?>"><?=$tahun['tahun']?></option>
                      <?php endforeach;?>
                    </select>

                    
                </div>`
                );

                $('#toolbar2').html(`
                    
                `);//jaga2 siapa tau butuh toolbar 2

                // Filter tahun
                $('#filter-tahun').on('change', function() {
                    var tahun = $('#filter-tahun').val();
                    var url = `<?= base_url('finance/financepphsupplier/config?user_token=') . $this->session->userdata('user_token'); ?>&tahun=${encodeURIComponent(tahun)}`;
                    table.ajax.url(url).load();

                });

                this.api().columns().every(function() {
                    var column = this;
                    var title = column.header().textContent.trim(); // Menghilangkan spasi ekstra di awal dan akhir judul

                    // Membuat input pencarian
                    var input = document.createElement('input');
                    input.setAttribute('type', 'text');
                    input.setAttribute('placeholder', 'Filter ' + title);
                    input.setAttribute('class', 'form-control form-control-sm');

                    // Menambahkan input pencarian ke footer kolom kecuali kolom 'No.' dan 'Action'
                    if (title !== 'No.' && title !== '') {
                        $(input).appendTo($(column.footer()).empty());
                        $(input).on('keyup change', function() {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                    }

                });

            }

    });
    $('#table-list-serverside tbody').on('click', '.tooltip-trigger', function() {
        var $tooltip = $(this);
        var isTooltipVisible = $tooltip.attr('aria-describedby') !== undefined;
        if (isTooltipVisible) {
            $tooltip.tooltip('hide');
        } else {
            $tooltip.tooltip('show');
        }
    });
    $('#table-list-serverside tfoot input').on('input keydown keyup change', function() { //apply pencarian pas ngetik
        let table = $('#table-list').DataTable();
        table.column($(this).parent().index() + ':visible').search(this.value).draw();
    });

   
    


  });
</script>
