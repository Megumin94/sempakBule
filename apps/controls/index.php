<!-- menerima variable dari form -->
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class index extends Null_Controller {
    public function __construct() {
        parent::__construct();

        $this->load->model('m_mrCatfish');
    }

    function index(){
        $isi['textarea'] = NULL;
        $isi['detail'] = $this->m_mrCatfish->showDataTable();
        if (isset($_POST['proses'])){
            $POST = $_POST;

            $this->session->set_userdata('panjang', $POST['panjang']);
            $this->session->set_userdata('lebar', $POST['lebar']);
            $this->session->set_userdata('material', $POST['material']);
            $this->session->set_userdata('musim', $POST['musim']);
            $this->session->set_userdata('pakan', $POST['pakan']);

            $isi['textarea'] = $this->prosesdata($POST);
        } else if (isset($_POST['saveResult'])) {
            $POST = $_POST;
            if ($this->simpan($POST)) {
                echo ("<script>alert('Selamat. Hasil perhitungan Anda berhasil disimpan. :3')</script>");
            } else {
                echo ("<script>alert('Perhitungan anda telah pernah dilakukan. Terimakasih telah menggunakan aplikasi ini :3 :*')</script>");
            }
        }
        $this->load->view('v_mainpage',$isi);
        $this->session->sess_destroy();
    }

    function prosesdata($Post) {
        $lebarLahan = ($Post['lebar']);
        $panjangLahan = ($Post['panjang']);
        $pool = '';
        $material = $Post['material'];
        $season = $Post['musim'];
        $food = $Post['pakan'];
        
        $fish = 0;

        $text = "Data yang diinputkan : ";
        $text .= "\n";
        $text .="Ukuran lahan               : $panjangLahan x $lebarLahan meter";
        $text .= "\n\n";

        if ($panjangLahan >= 4 && $lebarLahan >= 4) {
            if ($panjangLahan > 6 && $lebarLahan > 5 || $panjangLahan > 5 && $lebarLahan > 6) {
                if ($panjangLahan >= 12 && $lebarLahan >= 7 || $panjangLahan >= 7 && $lebarLahan >= 12) {
                    $pool ='p3';
                    $food = 'f1'.$food;

                    $panjangKolamBibit = floor(3 / 9 * ($panjangLahan - 3));
                    $lebarKolamBibit = floor(3 / 5 * ($lebarLahan - 2));
                    $luasPembibitan = $panjangKolamBibit * $lebarKolamBibit;
                    $babyfish = floor(($luasPembibitan * 10000) / 9);

                    $countkolambibit = 0;
                    $dummypanjangKolamBibit = $panjangKolamBibit;
                    $dummylebarKolamBibit = $lebarKolamBibit;

                    while ($dummypanjangKolamBibit >= 3.5) {
                        $dummypanjangKolamBibit = $dummypanjangKolamBibit - 3.5;
                        while ($dummylebarKolamBibit >= 3.5) {
                            $dummylebarKolamBibit = $dummylebarKolamBibit - 3.5;
                        }
                        $countkolambibit ++;
                    }
                    if ($countkolambibit > 0) {
                        $panjangKolamBibit = 3.5;
                        $lebarKolamBibit = 3.5;
                        $luasPembibitan = $panjangKolamBibit * $lebarKolamBibit;
                        $babyfish = floor(($luasPembibitan * 10000) / 9);
                        $text .= "Anda dapat membuat kolam pembibitan sejumlah $countkolambibit kolam";
                        $text .= "\n";
                        $text .= "Dengan ukuran masing-masing kolam $panjangKolamBibit meter x $lebarKolamBibit meter";
                        $text .= "\n";
                        $text .= "Dan masing masing kolam dapat menampung $babyfish ekor";
                        $text .= "\n";
                        $text .= "\n";
                    } else {

                        $text .= "Ukuran kolam pembibitan optimal adalah $panjangKolamBibit meter x $lebarKolamBibit meter";
                        $text .= "\n";
                        $text .= "Dengan luas kolam $luasPembibitan m2 mampu menampung bibit sejumlah $babyfish ekor";
                        $text .= "\n";
                        $text .= "\n";
                    }


                    $panjangKolamPembesaran = floor(6 / 9 * ($panjangLahan - 3));
                    $lebarKolamPembesaran = floor(5 / 5 * ($lebarLahan - 2));
                    $luasPembesaran = $panjangKolamPembesaran * $lebarKolamPembesaran;
                    $fish = floor(($luasPembesaran * 1000) / 30);


                    $countkolampembesaran = 0;
                    $dummypanjangKolamPembesaran = $panjangKolamPembesaran;
                    $dummylebarKolamPembesaran = $lebarKolamPembesaran;

                    while ($dummypanjangKolamPembesaran >= 12) {
                        $dummypanjangKolamPembesaran = $dummypanjangKolamPembesaran - 12;
                        while ($dummylebarKolamPembesaran >= 10) {
                            $dummylebarKolamPembesaran = $dummylebarKolamPembesaran - 10;
                        }
                        $countkolampembesaran ++;
                    }

                    if ($countkolampembesaran > 0) {

                        $panjangKolamPembesaran = 12;
                        $lebarKolamPembesaran = 10;
                        $luasPembesaran = $panjangKolamPembesaran * $lebarKolamPembesaran;
                        $fish = floor(($luasPembesaran * 1000) / 30);
                        $text .= "Anda dapat membuat kolam pembesaran sejumlah $countkolampembesaran kolam";
                        $text .= "\n";
                        $text .= "Dengan ukuran masing-masing kolam $panjangKolamPembesaran meter x $lebarKolamPembesaran meter";
                        $text .= "\n";
                        $text .= "Dan masing masing kolam dapat menampung $fish ekor";
                        $text .= "\n";
                        $text .= "\n";
                    } else {

                        $text .= "Ukuran kolam optimal pembesaran adalah $panjangKolamPembesaran meter x $lebarKolamPembesaran  meter";
                        $text .= "\n";
                        $text .= "Dengan luas kolam $luasPembesaran m2 mampu menampung bibit sejumlah $fish ekor ";
                        $text .= "\n";
                        $text .= "\n";
                    }

                } else {
                    $pool='p2';
                    $panjangKolamPembesaran = $panjangLahan - 1;
                    $lebarKolamPembesaran = $lebarLahan - 1;
                    $luasPembesaran = $panjangKolamPembesaran * $lebarKolamPembesaran;
                    $fish = floor(($luasPembesaran * 1000) / 30);
                    $text .= "Ukuran kolam optimal pembesaran adalah $panjangKolamPembesaran meter x $lebarKolamPembesaran  meter";
                    $text .= "\n";
                    $text .= "Dengan luas kolam $luasPembesaran m2 mampu menampung bibit sejumlah $fish ekor ";
                    $text .= "\n";
                    $text .= "\n";
                }
            } else {
                $pool='p1';
                $food='f1';
                $panjangKolamBibit = $panjangLahan - 1;
                $lebarKolamBibit = $lebarLahan - 1;
                $luasPembibitan = $panjangKolamBibit * $lebarKolamBibit;
                $babyfish = floor(($luasPembibitan * 10000) / 9);
                $text .= "Ukuran kolam optimal adalah $panjangKolamBibit meter x $lebarKolamBibit meter";
                $text .= "\n";
                $text .= "Dengan luas kolam $luasPembibitan m2 mampu menampung bibit sejumlah $babyfish ekor";
                $text .= "\n";
                $text .= "\n";
            }
        } else {
            $text .= "lahan berukuran $panjangLahan x $lebarLahan tidak cocok untuk kolam lele";
            $pool='p0';
            $material='';
            $season='';
            $food='';
        }

        if($fish!=0){
            $text .="Untuk pakan ikan lele (pembesaran) : ";
            if($food =='f1'){
                $konsentrat = (($fish/10)*0.7);
                $daging = (($fish/10)*0.3);
                $text .= "\n";
                $text .= "Jumlah pakan konsentrat yang dibutuhkan $konsentrat kg";
                $text .= "\n";
                $text .= "Jumlah pakan daging yang dibutuhkan $daging kg";
            }else{
                $konsentrat = (($fish/10)*0.6);
                $daging = (($fish/10)*0.4);
                $text .= "\n";
                $text .= "Jumlah pakan konsentrat yang dibutuhkan $konsentrat kg";
                $text .= "\n";
                $text .= "Jumlah pakan daging yang dibutuhkan $daging kg";
            }
        }

        $trik = $this->m_mrCatfish->findTrick($pool.$material.$season.$food);
        $text .= "\n\n===== Trik Usaha =====\n$trik";
        return $text;
    }

    function simpan($Post){

        if ($Post['material']=="m1") {
            $Post['material']="Bambu";
        }else{
            $Post['material']="Bata";
        }

        if ($Post['musim']=="s1") {
            $Post['musim']="Kemarau";
        }else{
            $Post['musim']="Penghujan";
        }

        if ($Post['pakan']=="f2") {
            $Post['pakan']="Konsentrat";
        }else{
            $Post['pakan']="Daging";
        }
    
        $Post['ukuran_kolam']=$Post['panjang']." x ".$Post['lebar'];
        return $this->m_mrCatfish->saveResult($Post);
    }
        function viewDetail($id){
            $isi['detail'] = $this->m_mrCatfish->getDetail($id);
            $this->load->view('v_detailUsaha',$isi);
        }
  }
