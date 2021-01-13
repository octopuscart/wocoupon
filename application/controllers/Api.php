<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . 'libraries/REST_Controller.php');

class Api extends REST_Controller {

    public function __construct() {
        parent::__construct();


        $this->load->library('session');
        $this->checklogin = $this->session->userdata('logged_in');
        $this->user_id = $this->session->userdata('logged_in')['login_id'];
    }

    public function index() {
        $this->load->view('welcome_message');
    }

    private function useCurl($url, $headers, $fields = null) {
        // Open connection
        $ch = curl_init();
        if ($url) {
            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if ($fields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            }

            // Execute post
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }

            // Close connection
            curl_close($ch);

            return $result;
        }
    }

    public function android($data, $reg_id_array) {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $insertArray = array(
            'title' => $data['title'],
            'message' => $data['message'],
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->insert("notification", $insertArray);

        $message = array(
            'title' => $data['title'],
            'message' => $data['message'],
            'subtitle' => '',
            'tickerText' => '',
            'msgcnt' => 1,
            'vibrate' => 1
        );

        $headers = array(
            'Authorization: key=' . $this->API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $fields = array(
            'registration_ids' => $reg_id_array,
            'data' => $message,
        );

        return $this->useCurl($url, $headers, json_encode($fields));
    }

    public function androidAdmin($data, $reg_id_array) {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $insertArray = array(
            'title' => $data['title'],
            'message' => $data['message'],
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->insert("notification", $insertArray);

        $message = array(
            'title' => $data['title'],
            'message' => $data['message'],
            'subtitle' => '',
            'tickerText' => '',
            'msgcnt' => 1,
            'vibrate' => 1
        );

        $headers = array(
            'Authorization: key=' . "AIzaSyBlRI5PaIZ6FJPwOdy0-hc8bTiLF5Lm0FQ",
            'Content-Type: application/json'
        );

        $fields = array(
            'registration_ids' => $reg_id_array,
            'data' => $message,
        );

        return $this->useCurl($url, $headers, json_encode($fields));
    }

    public function iOS($data, $devicetoken) {
        $deviceToken = $devicetoken;
        $ctx = stream_context_create();
        // ck.pem is your certificate file
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);
        // Open a connection to the APNS server
        $fp = stream_socket_client(
                'ssl://gateway.sandbox.push.apple.com:2195', $err,
                $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);
        // Create the payload body
        $body['aps'] = array(
            'alert' => array(
                'title' => $data['mtitle'],
                'body' => $data['mdesc'],
            ),
            'sound' => 'default'
        );
        // Encode the payload as JSON
        $payload = json_encode($body);
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        // Close the connection to the server
        fclose($fp);
        if (!$result)
            return 'Message not delivered' . PHP_EOL;
        else
            return 'Message successfully delivered' . PHP_EOL;
    }

    function updateProfile_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $contact_no = $this->post('mobile_no');
        $profiledata = array(
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'contact_no' => $this->post('mobile_no'),
            'company' => $this->post('company'),
            'designation' => $this->post('designation'),
        );
        $this->db->set($profiledata);
        $this->db->where('contact_no', $contact_no); //set column_name and value in which row need to update
        $this->db->update("app_user");
        $this->db->order_by('name asc');

        $this->db->where('contact_no', $contact_no); //set column_name and value in which row need to update
        $query = $this->db->get('app_user');
        $userData = $query->row();
        $this->response(array("userdata" => $userData));
    }

    function removeUsersCard_get($cardid) {
        $this->db->where('id', $cardid);
        $this->db->delete('card_share');
    }

    function generateCoupon_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $coupondata = $this->post();
        $id = $coupondata['id'];
        unset($coupondata['id']);
        $prefix = $coupondata['prefix'];
        unset($coupondata['prefix']);

        $quantity = $coupondata['quantity'];
        $request_id = $coupondata['request_id'];

        $couponhaslist = array();

        for ($k = 0; $k < $quantity; $k++) {

            $possible_letters = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $code = '';
            $i = 0;
            while ($i < 6) {
                $code .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
                $i++;
            }
            $code = $prefix . $code . $id;
            $coupondata['coupon_code'] = $code;
            $coupondata['coupon_code_hash'] = md5($request_id);
            $this->db->insert("coupon_code", $coupondata);
            $last_id = $this->db->insert_id();
            $insertArray = array(
                "coupon_id" => $last_id,
                "email" => "",
                "status" => "Payment Success",
                "remark" => "Coupon has been purchased",
                'date' => date('Y-m-d'),
                'time' => date('H:i:s'),
            );
            $this->db->insert("coupon_code_status", $insertArray);
            array_push($couponhaslist, $coupondata['coupon_code']);
        }
        $hasArray = $coupondata['coupon_code_hash'];
        $this->response($hasArray);
    }

    function generateCouponTest_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $coupondata = $this->post();
        $this->response($coupondata);
    }

    function testRend_get() {
        echo rand(1000, 9999);
    }

    function getCouponImage_get($couponcodehas) {
        $this->db->where('coupon_code_hash', $couponcodehas); //set column_name and value in which row need to update
        $query = $this->db->get('coupon_code');
        $coupondata = $query->result();

//        $coupondata = array_merge($coupondata, $coupondata);
        $couponcodearray1 = array();
        $couponcodearray2 = array();
        $couponcodearray3 = array();
        foreach ($coupondata as $key => $value) {
            if ($key <= 4) {
                array_push($couponcodearray1, $value->coupon_code);
            }
            if ($key > 4 && $key <= 9) {
                array_push($couponcodearray2, $value->coupon_code);
            }
            if ($key > 9) {
                array_push($couponcodearray3, $value->coupon_code);
            }
        }

//        unset($couponcodearray3[4]);
//        unset($couponcodearray3[3]);
        $couponcode1 = implode(", ", $couponcodearray1);
        $couponcode2 = implode(", ", $couponcodearray2);
        $couponcode3 = implode(", ", $couponcodearray3);
        $codearray = array(
            "1" => array("text" => $couponcode1, "y" => "800"),
            "2" => array("text" => $couponcode2, "y" => "840"),
            "3" => array("text" => $couponcode3, "y" => "880")
        );
        $font_size = 25;
        if ($coupondata) {
            header('Content-type: image/jpeg');
            $font_path1 = APPPATH . "../assets/card/fonts/ABeeZee-Regular.otf";
            $jpg_image = imagecreatefromjpeg(APPPATH . "../assets/images/coupon100.jpg");
            $white = imagecolorallocate($jpg_image, 0, 0, 0);
            $useremail = $this->input->get('client_email');
            $image_width = imagesx($jpg_image);
            $image_height = imagesy($jpg_image);
            foreach ($codearray as $key => $cvalue) {
                $text_box = imagettfbbox($font_size, 0, $font_path1, $cvalue['text']);
                $text_width = $text_box[2] - $text_box[0];
                $x = ($image_width / 2) - ($text_width / 2);
                imagettftext($jpg_image, $font_size, 0, $x + 7, $cvalue['y'], $white, $font_path1, $cvalue['text']);
            }
            imagettftext($jpg_image, 15, 0, 670, 755, $white, $font_path1, "Your Coupon Code(s)");
            // Output the image
            imagejpeg($jpg_image);
        } else {
            echo "No Data Found";
        }

// Free up memory
        imagedestroy($jpg_image);
    }

    function getCouponData_get($coupon_id) {
        $this->db->where("id", $coupon_id);
        $query = $this->db->get('coupon_code');
        $coupondata = $query->row();
        $this->response($coupondata);
    }

    function getCouponDataTable_get($couponsource) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $searchqry = "";

        $search = $this->input->get("search")['value'];
        if ($search) {
            $searchqry = ' and cc.coupon_code like "%' . $search . '%" or cc.email like "%' . $search . '%" or cc.contact_no like "%' . $search . '%" or cc.name like "%' . $search . '%" ';
        }
        $query = "select cc.* from coupon_code as cc where cc.id not in (select ccs.coupon_id from coupon_code_status as ccs where ccs.status ='Used') $searchqry order by cc.id desc limit  $start, $length";
        $query2 = $this->db->query($query);
        $couponlist = $query2->result_array();

        $query = "select cc.* from coupon_code as cc where cc.id not in (select ccs.coupon_id from coupon_code_status as ccs where ccs.status ='Used') $searchqry order by cc.id desc";
        $query3 = $this->db->query($query);
        $return_array = array();


        foreach ($couponlist as $pkey => $pvalue) {
            $temparray = array();
            $temparray['s_n'] = $pkey + 1;
            $temparray["checkbox"] = '<input type="checkbox" name="coupon_id" class="coupon_id" value="' . $pvalue['coupon_code'] . '" ng-click="userCouponBulk()">';


            $name = $pvalue['name'];
            $contact_no = $pvalue['contact_no'];
            $email = $pvalue['email'];
            $sender = "<table class='smalltabledetails'>"
                    . "<tr><td>Name</td><td>$name</td></tr>"
                    . "<tr><td>Cont. No.</td><td>$contact_no</td></tr>"
                    . "<tr><td>Email</td><td>$email</td></tr>"
                    . "</table>";

            $name_receiver = $pvalue['name_receiver'];
            $contact_no_receiver = $pvalue['contact_no_receiver'];
            $email_receiver = $pvalue['email_receiver'];

            $receiver = "<table class='smalltabledetails'>"
                    . "<tr><td>Name</td><td>$name_receiver</td></tr>"
                    . "<tr><td>Cont. No.</td><td>$contact_no_receiver</td></tr>"
                    . "<tr><td>Email</td><td>$email_receiver</td></tr>"
                    . "</table>";

            $temparray['sender'] = $sender;
            $temparray['receiver'] = $receiver;

            $temparray['coupon_code'] = "<b>" . $pvalue['coupon_code'] . "</b>";

            $temparray['datetime'] = $pvalue['date'] . " " . $pvalue['time'];
            $temparray['amount'] = 100.00;
            $temparray['payment_type'] = $pvalue['payment_type'];
            $temparray['edit'] = '<button  class="btn btn-danger" ng-click="userCoupon(' . $pvalue['id'] . ')"><i class="fa fa-edit"></i> Reimburse Coupon</button>';



            array_push($return_array, $temparray);
        }

        $couponlist;
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $query2->num_rows(),
            "recordsFiltered" => $query3->num_rows(),
            "data" => $return_array
        );

        $this->response($output);
    }

    function couponUse_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $email = $this->post('email');
        $remark = $this->post('remark');
        $coupon_id = $this->post('coupon_id');
        $insertArray = array(
            "coupon_id" => $coupon_id,
            "email" => $email,
            "status" => "Used",
            "remark" => $remark,
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
        );
        $this->db->insert("coupon_code_status", $insertArray);
    }

    function couponUseBulk_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $email = $this->post('email');
        $remark = $this->post('remark');
        $coupon_id = $this->post('coupon_code');
        foreach ($coupon_id as $key => $code) {
            $this->db->where("coupon_code", $code);
            $query = $this->db->get('coupon_code');
            $coupondata = $query->row();
            $insertArray = array(
                "coupon_id" => $coupondata->id,
                "email" => $email,
                "status" => "Used",
                "remark" => $remark,
                'date' => date('Y-m-d'),
                'time' => date('H:i:s'),
            );
            $this->db->insert("coupon_code_status", $insertArray);
        }
    }

    function getCouponDataTableReport_get($couponsource) {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $searchqry = "";

        $search = $this->input->get("search")['value'];
        if ($search) {
            $searchqry = ' and cc.coupon_code like "%' . $search . '%" or cc.email like "%' . $search . '%" or cc.contact_no like "%' . $search . '%" or cc.name like "%' . $search . '%" ';
        }
        $query = "select cc.* from coupon_code as cc where cc.id  in (select ccs.coupon_id from coupon_code_status as ccs where ccs.status ='Used') $searchqry order by cc.id desc limit  $start, $length";
        $query2 = $this->db->query($query);
        $couponlist = $query2->result_array();

        $query = "select cc.* from coupon_code as cc where cc.id  in (select ccs.coupon_id from coupon_code_status as ccs where ccs.status ='Used') $searchqry order by cc.id desc";
        $query3 = $this->db->query($query);
        $return_array = array();


        foreach ($couponlist as $pkey => $pvalue) {
            $temparray = array();
            $temparray['s_n'] = ($pkey + 1)*$start;




            $temparray['coupon_code'] = "<b>" . $pvalue['coupon_code'] . "</b>";

            $temparray['datetime'] = $pvalue['date'] . " " . $pvalue['time'];
            $temparray['amount'] = 100.00;
            $temparray['payment_type'] = $pvalue['payment_type'];
            $temparray['edit'] = '<button  class="btn btn-danger" ng-click="userCoupon(' . $pvalue['id'] . ')"><i class="fa fa-edit"></i> Use Coupon</button>';


            $coupon_id = $pvalue['id'];
            $this->db->where("coupon_id", $coupon_id);

            $this->db->order_by("id desc");
            $query = $this->db->get('coupon_code_status');
            $couponstatusdata = $query->result_array();

            $temparray['status'] = $pvalue['status'];
            if ($couponstatusdata) {
                $coupondata = $couponstatusdata[0];
                $temparray['remark'] = $coupondata['remark'];
                $temparray["used_email"] = $coupondata["email"];
                array_push($return_array, $temparray);
            } else {
                
            }
        }

        $couponlist;
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $query2->num_rows(),
            "recordsFiltered" => $query3->num_rows(),
            "data" => $return_array
        );

        $this->response($output);
    }

}

?>