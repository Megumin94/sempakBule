<?php

class m_mrCatfish extends Null_Model {

	function findTrick($key) {
		$kiatbisnis = $this->db->query(
							"select trik from kiatbisnis where node = '$key'"
						);
		
		// return $kiatbisnis->num_rows();
		// return $kiatbisnis->result_array();
		return $kiatbisnis->row()->trik;
	}

	function showDataTable(){
		$data=$this->db->query("SELECT * FROM recentresult");
		return $data->result_array();
	}

	function saveResult($Post){
		$data=$this->db->query("SELECT * FROM recentresult WHERE `ukuran_kolam` = ? AND `material_kolam` = ? AND `musim` = ? AND `pilihan_pakan` = ?"
				, array(
					"$Post[ukuran_kolam]"
					, "$Post[material]"
					, "$Post[musim]"
					, "$Post[pakan]"
				));
		
		if ($data->num_rows() == 0) {
			return $this->db->query("INSERT INTO `recentresult`(`id_hasil_analisa`, `ukuran_kolam`, `material_kolam`, `musim`, `pilihan_pakan`, `rincian_analisa`) 
				VALUES (?,?,?,?,?,?)"
				, array(
					NULL
					, "$Post[ukuran_kolam]"
					, "$Post[material]"
					, "$Post[musim]"
					, "$Post[pakan]"
					, "$Post[saran]"
				));
		} else {
			return FALSE;
		}
	}

	function getDetail($id){
		return $this->db->query("SELECT * FROM `recentresult` WHERE id_hasil_analisa=$id")->result_array();
	}
}