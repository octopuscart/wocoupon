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

        $possible_letters = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $code = '';
        $i = 0;
        while ($i < 6) {
            $code .= substr($possible_letters, mt_rand(0, strlen($possible_letters) - 1), 1);
            $i++;
        }
        $code = $prefix . $code . $id;
        $coupondata['coupon_code'] = $code;
        $coupondata['coupon_code_hash'] = md5($code);
        $this->db->insert("coupon_code", $coupondata);
        $last_id = $this->db->insert_id();

        $insertArray = array(
            "coupon_id" => $last_id,
            "status" => "Payment Success",
            "remark" => "Coupon has been purchased",
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
        );
        $this->db->insert("coupon_code_status", $insertArray);

        $this->response($coupondata['coupon_code_hash']);
    }

    function testRend_get() {
        echo rand(1000, 9999);
    }

    function getCouponImage_get($couponcodehas) {
        $this->db->where('coupon_code_hash', $couponcodehas); //set column_name and value in which row need to update
        $query = $this->db->get('coupon_code');
        $coupondata = $query->row();
        if ($coupondata) {
            header('Content-type: image/jpeg');
            $font_path1 = APPPATH . "../assets/card/fonts/ABeeZee-Regular.otf";

            $jpg_image = imagecreatefromjpeg(APPPATH . "../assets/images/coupon100.jpg");
            $white = imagecolorallocate($jpg_image, 0, 0, 0);
            $useremail = $this->input->get('client_email');

            $image_width = imagesx($jpg_image);
            $image_height = imagesy($jpg_image);

            $text_box = imagettfbbox(30, 0, $font_path1, $coupondata->coupon_code);


            $text_width = $text_box[2] - $text_box[0];
            $text_height = $text_box[7] - $text_box[1];

// Calculate coordinates of the text
            $x = ($image_width / 2) - ($text_width / 2);
            $y = ($image_height / 2) - ($text_height / 2);

// Add some shadow to the text
            imagettftext($jpg_image, 30, 0, $x + 7, 252, $white, $font_path1, $coupondata->coupon_code);

            imagettftext($jpg_image, 15, 0, 670, 200, $white, $font_path1, "Your Coupon Code");
            // Output the image
            imagejpeg($jpg_image);
        } else {
            echo "No Data Found";
        }

// Free up memory
        imagedestroy($jpg_image);
    }

    function getCouponData_get($couponsource) {
        $this->db->where("coupon_for", $couponsource);
        $query = $this->db->get('coupon_code');
        $coupondata = $query->result_array();
        $this->response($coupondata);
    }

    function getCouponDataTable_get($couponsource) {
        $this->db->where("coupon_for", $couponsource);
        $query = $this->db->get('coupon_code');
        $coupondata = $query->result_array();
        $this->response($coupondata);
    }

}

?>