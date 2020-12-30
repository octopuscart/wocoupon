<?php
if (!defined('BASEPATH'))
exit('No direct script access allowed');
use 'third_party\PhpOffice\PhpSpreadsheet\Spreadsheet';
use 'third_party\PhpOffice\PhpSpreadsheet\Writer\Xlsx';
use 'third_party\PhpOffice\PhpSpreadsheet\Reader\Xlsx';

class PhpSpreadsheet { 
    public $param;
    public $xlsFile;

    public function writer(){
        $spreadsheet = new Spreadsheet();
        return $spreadsheet;
    }

    public function reader($xlsFile){
        $spreadsheet = $reader->load($xlsFile);
        return $spreadsheet;
    }

}