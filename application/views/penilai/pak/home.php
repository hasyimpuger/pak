<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Data Pengajuan PAK</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tabel Pengajuan PAK</h6>
    </div>
    <div class="card-body">

        <?php if($this->session->flashdata('flash')) : ?>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Data Pengajuan <strong>berhasil</strong> <?= $this->session->flashdata('flash') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
            <tr>
                <th>No</th>
                <th>Nama Pendaftar</th>
                <th>Pengajuan Ke</th>
                <th>Tanggal Pengajuan</th>
                <th>Jumlah AK</th>
                <th>Status</th>
                <th>Alamat Sekolah</th>
                <th>Option</th>
            </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach($rekap_nilai as  $r) : 
                    if($r->lengkap == 0){ continue; }  ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $r->nama; ?></td>
                        <td align="center"><?= $r->pengajuan_ke; ?></td>
                        <td><?= date('d-m-Y',$r->tanggal) ?></td>
                        <td><?php 
                            $nilaiTotal = $queryKegiatan = "SELECT SUM(`kegiatan`.`angka_kredit`) as `nilai`
                                FROM `kegiatan` 
                                JOIN `nilai`
                                ON `kegiatan`.`id` = `nilai`.`kegiatan_id`
                                WHERE `nilai`.`rekap_nilai_id` = $r->id AND `status` = 1
                                ";
                            $nilai = $this->db->query($nilaiTotal)->row();
                            echo $nilai->nilai;
                        ?></td>
                        <td><?php if($r->status == 0){ echo "<span class='badge badge-danger'>Belum divalidasi</span>"; } elseif($r->status == 1) { echo "<span class='badge badge-success'>Data valid semua</span>"; } else { echo "<span class='badge badge-warning'>Data ada yg belum valid</span>"; } ?></td>
                        <td><?= $r->alamat_sekolah; ?></td>
                        <td>
                            <a href="<?= base_url('penilai/pak/hapus/'.$r->id) ?>" onclick="return confirm('Hapus?');" class="badge badge-danger float-right tombol-hapus mb-1">Hapus</a>
                            <a href="<?= base_url('penilai/pak/validasi/'.$r->id) ?>" class="badge badge-success float-right mr-1">Validasi</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    </div>

</div>
<!-- /.container-fluid -->