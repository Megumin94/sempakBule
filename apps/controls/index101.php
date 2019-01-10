<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class index extends Null_Controller {

    public function __construct() {
        parent::__construct();
    }

    function index() {
        if (!isset($_SESSION['login_localhost'])) {
            $this->load->view('login');
        } else {
            $this->load->view('portfolio');
        }
    }

    function site() {
        $POST = $_POST;
        if ($POST['form_data'][0]['password'] != "") {
            if ($this->hash($POST['form_data'][0]['password']) == "6dab212eb40fe702cb20a40d76b0e12b2f9ea7ab") {
                $_SESSION['login_localhost'] = TRUE;
                echo 'Selamat! Anda berhasil login!';
            } else {
                echo 'Password SALAH! Silahkan periksa dan coba kembali';
            }
        } else {
            echo 'Password Kosong! Silahkan isi field Password';
        }
    }

    private function hash($data) {
        for ($i = 0, $l = strlen($data); $i < $l; $i++) {
            $data = sha1($data);
        }
        return $data;
    }

    function logout() {
        session_unset("login_localhost");
        redirect();
    }

}
