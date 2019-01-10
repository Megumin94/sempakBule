<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_DB_mssql_utility extends Null_DB_utility {

    protected $_list_databases = 'EXEC sp_helpdb'; // Can also be: EXEC sp_databases
    protected $_optimize_table = 'ALTER INDEX all ON %s REORGANIZE';

    protected function _backup($params = array()) {
        // Currently unsupported
        return $this->db->display_error('db_unsupported_feature');
    }

}
