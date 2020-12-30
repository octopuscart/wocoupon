<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class JsonSorting extends CI_Model {

    public function __construct($source) {
        $this->source = $source;
    }

    public function count_values($keyname, $keyval) {
        $count = 0;
        foreach ($this->source as $key => $value) {
            if ($keyval == $value[$keyname]) {
                $count++;
            }
        }
        return [$keyval, $count];
    }

    public function collect_data($keyname) {
        $datalist = array();
        $ll2 = array();
        $count1 = 0;
        foreach ($this->source as $key => $value) {
            $temp = $value[$keyname];
            if (in_array($temp, $datalist)) {
                
            } else {
                array_push($datalist, $temp);
            }
        }
        foreach ($datalist as $key => $value) {
            $temp1 = $this->count_values($keyname, $value);
            $ll2[$temp1[0]] = $temp1[1];
        }
        return $ll2;
    }

    public function data_combination($keyname1, $keyname2) {
        $data_contain = array();
        $key_1 = $this->collect_data($keyname1);
        $key_2 = $this->collect_data($keyname2);
        $key_data1 = array_keys($key_1);
        $key_data2 = array_keys($key_2);
        foreach ($key_data2 as $kd2 => $vl2) {
            $sort_temp = array();
            foreach ($key_data1 as $kd1 => $vl1) {
                $count = 0;
                foreach ($this->source as $kd => $vl) {
                    $temp1 = $vl[$keyname1];
                    $temp2 = $vl[$keyname2];
                    if ($temp1 == $vl1 && $temp2 == $vl2) {
                        $count++;
                        $sort_temp[$vl1] = $count;
                    }
                }
                $data_contain[$vl2] = $sort_temp;
            }
        }
        return $data_contain;
    }

    public function data_combination_quantity($keyname1, $keyname2) {
        $data_contain = array();
        $key_1 = $this->collect_data($keyname1);
        $key_2 = $this->collect_data($keyname2);
        $key_data1 = array_keys($key_1);
        $key_data2 = array_keys($key_2);
        foreach ($key_data2 as $kd2 => $vl2) {
            $sort_temp = array();
            foreach ($key_data1 as $kd1 => $vl1) {
                $count = 0;
                foreach ($this->source as $kd => $vl) {
                    $temp1 = $vl[$keyname1];
                    $temp2 = $vl[$keyname2];
                    if ($temp1 == $vl1 && $temp2 == $vl2) {
                        $count = $count + $vl1;
                        $sort_temp[$vl1] = $count;
                    }
                }
                $data_contain[$vl2] = $sort_temp;
            }
        }
        $temp = array();
        foreach ($data_contain as $key => $value) {
            $temp2 = array_sum($value);
            $temp[$key] = $temp2;
        }

        return $temp;
    }

}

?>