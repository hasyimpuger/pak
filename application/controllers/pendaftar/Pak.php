<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pak extends CI_Controller {

    public  function __construct()
    {
        parent::__construct();
        cek_login();
    }

    public function index()
    {
        $data['_view']= "pendaftar/pak/home";

        $this->load->view('template/index', $data);
    }

    public function pengajuan()
    {
        $queryKegiatan = "SELECT `kegiatan`.`id`, `kegiatan`.`unsur_id`, `kegiatan`.`kode`, 
                        `kegiatan`.`kegiatan`, `kegiatan`.`satuan`, 
                        `kegiatan`.`angka_kredit`, `kegiatan`.`pelaksana`,
                        `unsur`.`unsur`, `unsur`.`sub_unsur`
                        FROM `kegiatan` 
                        JOIN `unsur`
                        ON `kegiatan`.`unsur_id` = `unsur`.`id`
                        GROUP BY `kegiatan`.`unsur_id` 
                        ORDER BY `kegiatan`.`id` 
                        ";
        $data['kegiatan'] = $this->db->query($queryKegiatan)->result();

        if($this->input->post('pendaftar_id')){

            $kegiatan_id = $this->input->post('kegiatan_id');

            $cekPengajuan = $this->db->get_where('rekap_nilai',['pendaftar_id' => $this->session->userdata('id')])->num_rows();

            $cekJabatan = $this->db->get_where('pendaftar',['id' => $this->session->userdata('id')])->row();

            // insert data ke rekap_nilai
            $dataRekapNilai = [
                "nilai_total" => 0,
                "status" => 0,
                "tanggal" => time(),
                "dari" => $cekJabatan->jabatan_id,
                "ke" => $cekJabatan->jabatan_id+1,
                "pengajuan_ke" => $cekPengajuan+1,
                "pendaftar_id" => $this->session->userdata('id'),
            ];

            $this->db->insert('rekap_nilai',$dataRekapNilai);
            $nilai_rekap_id = $this->db->insert_id();

            // insert detail data ke tabel nilai
            foreach ($kegiatan_id as $kegiatan_id) {
                $dataNilai = [
                    "tanggal" => time(),
                    "status" => 0,
                    "kegiatan_id" => $kegiatan_id,
                    "rekap_nilai_id" => $nilai_rekap_id,

                ];
                $this->db->insert('nilai',$dataNilai);
            }

            $this->session->set_flashdata('flash',"Silahkan Upload File");

            redirect('pendaftar/pak/upload/'.$nilai_rekap_id);


        } else {
            $data['_view']= "pendaftar/pak/pengajuan";
            $this->load->view('template/index', $data);
        }
    }

    public function upload($id = null)
    {
        // harus otomatis sesuai data terakhir yg belum divalidasi
        $data['rekap_nilai_id'] = $id;
        $queryKegiatan = "SELECT `kegiatan`.`unsur_id`, `kegiatan`.`kode`, 
                        `kegiatan`.`kegiatan`, `kegiatan`.`satuan`, 
                        `kegiatan`.`angka_kredit`, `kegiatan`.`pelaksana`,
                        `unsur`.`unsur`, `unsur`.`sub_unsur`, `nilai`.`status`, `nilai`.`file`, `nilai`.`id`
                        FROM `kegiatan` 
                        JOIN `unsur`
                        ON `kegiatan`.`unsur_id` = `unsur`.`id`
                        JOIN `nilai`
                        ON `kegiatan`.`id` = `nilai`.`kegiatan_id`
                        WHERE `nilai`.`rekap_nilai_id` = $data[rekap_nilai_id]
                        ORDER BY `nilai`.`id` 
                        ";
        $data['kegiatan'] = $this->db->query($queryKegiatan)->result();

        if($this->input->post('nilai_id')) {

            // $upload_image = $_FILES['file'];
            // var_dump($upload_image);die;
            $nilai_id = $this->input->post('nilai_id');
            $queryData = "SELECT `kegiatan`.`kode`
                        FROM `kegiatan` 
                        JOIN `nilai`
                        ON `kegiatan`.`id` = `nilai`.`kegiatan_id`
                        WHERE `nilai`.`id` = $nilai_id
                        ORDER BY `nilai`.`id` 
                        ";
            $dataKegiatan = $this->db->query($queryData)->row();

            $set_name   = "kodeKegiatan-".$dataKegiatan->kode."-idRekapNilai-".$id;
            $path       = $_FILES['file']['name'];
            $extension  = ".".pathinfo($path, PATHINFO_EXTENSION);

            $config = array(
                'upload_path' => "./uploads/",
                'allowed_types' => "jpg|png|jpeg|pdf",
                'overwrite' => TRUE,
                'max_size' => "2048",
                'file_name' => "$set_name".$extension,
                );

            $this->load->library('upload', $config);

            if($this->upload->do_upload('file'))
            {
                $nama_file = $this->upload->data('file_name');

                $this->db->set('file', $nama_file);
                $this->db->set('status', 0);
                $this->db->where('id',$this->input->post('nilai_id'));
                $this->db->update('nilai');

                $this->session->set_flashdata('flash',"File Berhasil diupload");
                
                return redirect('pendaftar/pak/upload/'.$id);
            }
            else
            {
                $data['error'] = $this->upload->display_errors();
                $data['_view']= "pendaftar/pak/upload";
                $this->load->view('template/index', $data);
            }

            
        } else {
            
            $data['_view']= "pendaftar/pak/upload";
            $this->load->view('template/index', $data);
        }

        

    }

    public function cek_berkas($id = null)
    {
        $queryNilai = "SELECT `status` from `nilai` where `rekap_nilai_id` = $id and `file` = ''";
        $dataNilai = $this->db->query($queryNilai)->result();
        
        if($dataNilai){
            $this->session->set_flashdata('flash',"Berkas Belum Lengkap!");

            return redirect('pendaftar/pak/upload/'.$id);
        } else {
            // jika data lengkap, ubah nilai lengkap di rekap_nilai jadi 1
            $this->db->where('id', $id);
            $this->db->update('rekap_nilai', ['lengkap' => 1,'status' => 0]);

            redirect('pendaftar/pak');
        }
    }

}