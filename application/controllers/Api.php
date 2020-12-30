<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . 'libraries/REST_Controller.php');

class Api extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->API_ACCESS_KEY = 'AIzaSyBlRI5PaIZ6FJPwOdy0-hc8bTiLF5Lm0FQ';
        // (iOS) Private key's passphrase.
        $this->passphrase = 'joashp';
        // (Windows Phone 8) The name of our push channel.
        $this->channelName = "joashp";

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

    function broadCastMessgeAdmin($messagedict) {
        $this->db->where('user_type', "Admin");
        $query = $this->db->get('gcm_registration');
        $regarray2 = $query->result_array();
        $temparray = [];
        foreach ($regarray2 as $key => $value) {
            array_push($temparray, $value['reg_id']);
        }
        $this->androidAdmin($messagedict, $temparray);
    }

    function broadCastMessge($messagedict) {
//        $this->db->where('user_type', "Guest");
        $query = $this->db->get('gcm_registration');
        $regarray2 = $query->result_array();
        $temparray = [];
        foreach ($regarray2 as $key => $value) {
            array_push($temparray, $value['reg_id']);
        }
        $this->android($messagedict, $temparray);
    }

    function singleMessage($messagedict, $userid) {
        $this->db->where('user_id', $userid);
        $query = $this->db->get('gcm_registration');
        $regarray2 = $query->result_array();
        $temparray = [];
        foreach ($regarray2 as $key => $value) {
            array_push($temparray, $value['reg_id']);
        }
        $this->android($messagedict, $temparray);
    }

    function updateCurd_post() {
        $fieldname = $this->post('name');
        $value = $this->post('value');
        $pk_id = $this->post('pk');
        $tablename = $this->post('tablename');
        if ($this->checklogin) {
            $data = array($fieldname => $value);
            $this->db->set($data);
            $this->db->where("id", $pk_id);
            $this->db->update($tablename, $data);
        }
    }

    //function for product list
    function loginOperation_get() {
        $userid = $this->user_id;
        $this->db->select('au.id,au.first_name,au.last_name,au.email,au.contact_no');
        $this->db->from('admin_users au');
        $this->db->where('id', $userid);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row();
        $this->response($result);
    }

    //Login Function 
    //function for product list
    function loginOperation_post() {
        $email = $this->post('contact_no');
        $password = $this->post('password');
        $this->db->select('au.id,au.first_name,au.last_name,au.email,au.contact_no');
        $this->db->from('admin_users au');
        $this->db->where('contact_no', $email);
        $this->db->where('password', md5($password));
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row();

        $sess_data = array(
            'username' => $result->email,
            'first_name' => $result->first_name,
            'last_name' => $result->last_name,
            'login_id' => $result->id,
        );
        $this->session->set_userdata('logged_in', $sess_data);
        $this->response($result);
    }

    function registerMobileUpdate_get($reg_id, $user_id) {
        $regArray = array(
            "user_id" => $user_id,
        );
        $this->db->set($regArray);
        $this->db->where('reg_id', $reg_id); //set column_name and value in which row need to update
        $this->db->update("gcm_registration");
    }

    function registerMobileGuest_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $reg_id = $this->post('reg_id');
        $model = $this->post('model');
        $manufacturer = $this->post('manufacturer');
        $uuid = $this->post('uuid');
        $regArray = array(
            "reg_id" => $reg_id,
            "manufacturer" => $manufacturer,
            "uuid" => $uuid,
            "model" => $model,
            "user_id" => "Guest",
            "user_type" => "Guest",
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->where('reg_id', $reg_id);
        $query = $this->db->get('gcm_registration');
        $regarray2 = $query->result_array();
        if ($regarray2) {
            $this->response(array("status" => "already", "data" => $regArray));
        } else {
            $this->db->insert('gcm_registration', $regArray);
        }
        $this->response(array("status" => "done", "data" => $regArray));
    }

    function registerMobileAdmin_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $reg_id = $this->post('reg_id');
        $model = $this->post('model');
        $manufacturer = $this->post('manufacturer');
        $uuid = $this->post('uuid');
        $regArray = array(
            "reg_id" => $reg_id,
            "manufacturer" => $manufacturer,
            "uuid" => $uuid,
            "model" => $model,
            "user_id" => "Admin",
            "user_type" => "Admin",
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->where('reg_id', $reg_id);
        $query = $this->db->get('gcm_registration');
        $regarray2 = $query->result_array();
        if ($regarray2) {
            $this->db->set(array("user_type" => "Admin"));
            $this->db->where('reg_id', $reg_id); //set column_name and value in which row need to update
            $this->db->update("gcm_registration");
            $this->response(array("status" => "already", "data" => $regArray));
        } else {
            $this->db->insert('gcm_registration', $regArray);
        }
        $this->response(array("status" => "done", "data" => $regArray));
    }

    function getUserList_get() {
        $this->db->order_by("id desc");
        $query = $this->db->get('app_user');
        $userlistdata = $query->result_array();
        $this->response($userlistdata);
    }

    function registration_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $name = $this->post('name');
        $contact_no = $this->post('contact_no');
        $email = "";
        $password = rand(1000, 9999);
        $usercode = rand(1000, 9999);
        $profileimageurl = "";
        $regArray = array(
            "name" => $name,
            "email" => $email,
            "contact_no" => $contact_no,
            "password" => $password,
            "usercode" => $usercode,
            "datetime" => date("Y-m-d H:i:s a"),
            "profile_image" => $profileimageurl,
            "cardimage" => "",
        );
        $this->db->where('contact_no', $contact_no);
        $query = $this->db->get('app_user');
        $userdata = $query->row();
        $message = array("title" => "New Member Registration", "message" => "$name recently register on mobile app");
        $this->broadCastMessgeAdmin($message);
        if ($userdata) {
            $regArray = array(
                "name" => $name,
            );
            $this->db->set($regArray);
            $this->db->where('contact_no', $contact_no); //set column_name and value in which row need to update
            $this->db->update("app_user");
            $this->response(array("status" => "200", "userdata" => $userdata));
        } else {
            $this->db->insert('app_user', $regArray);
            $last_id = $this->db->insert_id();
            $updateArray = array(
                "usercode" => "" . $usercode . $last_id,
            );
            $this->db->set($updateArray);
            $this->db->where('id', $last_id); //set column_name and value in which row need to update
            $this->db->update("app_user");
            $regArray["usercode"] = $usercode . $last_id;
            $regArray["id"] = $last_id;
            $this->response(array("status" => "200", "userdata" => $regArray));
        }
    }

    function registrationAdmin_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $password = $this->post('password');
        $contact_no = $this->post('contact_no');
        $this->db->where('contact_no', $contact_no);
        $this->db->where('password', md5($password));
        $query = $this->db->get('admin_users');
        $userdata = $query->row();
        if ($userdata) {
            $this->response(array("status" => "200", "userdata" => $userdata));
        } else {
            $this->response(array("status" => "100", "userdata" => []));
        }
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

    function getUsersCard_get($user_id) {
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('card_share');
        $userdata = $query->result_array();
        $usercarddata = [];
        foreach ($userdata as $key => $value) {
            $this->db->where('usercode', $value['scanner_id']);
            $query = $this->db->get('app_user');
            $user = $query->row();
            $user->cardid = $value['id'];
            $user->cardimage = base_url() . "assets/usercard/" . $user->cardimage;
            array_push($usercarddata, $user);
        }
        return $this->response($usercarddata);
    }

    function removeUsersCard_get($cardid) {
        $this->db->where('id', $cardid);
        $this->db->delete('card_share');
    }

    function createPost_post() {
        $this->config->load('rest', TRUE);
        $class_assignment = array(
            'title' => "",
            'description' => $this->post('description'),
            "datetime" => date("Y-m-d H:i:s a"),
            'user_id' => $this->post('user_id'),
        );
        $this->db->insert('post', $class_assignment);
        $last_id = $this->db->insert_id();
        $desc = $this->post('description');
        $message = array("title" => "Loyalty Card Offer", "message" => "$desc\nNew offer now available, Hurry up.");
        $this->broadCastMessge($message);
        $this->response(array("last_id" => $last_id));
    }

    function uploadFileImage_post() {
        $this->config->load('rest', TRUE);
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $config['upload_path'] = 'assets/postfiles';
        $config['allowed_types'] = '*';
        $tableid = $this->post('file_table_id');
        $tempfilename = rand(10000, 1000000);
        $tempfilename = "" . $tempfilename . $tableid;

        $file_newname = $tempfilename . '.jpg';
        $config['file_name'] = $file_newname;
        //Load upload library and initialize configuration
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if ($this->upload->do_upload('file')) {
            $uploadData = $this->upload->data();
            $tableid = $this->post('file_table_id');
            $file_newname = $uploadData['file_name'];
            $tablename = $this->post('file_tablename');
            $filecreate = array(
                'table_name' => $tablename,
                'table_id' => $this->post('file_table_id'),
                "file_name" => $file_newname,
                'file_real_name' => $this->post('name'),
                'file_type' => "jpg",
                "date" => date("Y-m-d"),
                "time" => date("H:i:s a"),
            );
            $this->db->insert('post_files', $filecreate);
            $this->response($file_newname);
        }
    }

    function postDelete_get($pid) {
        $this->db->where('id', $pid);
        $this->db->delete('post');


        $this->db->where('table_id', $pid);
        $this->db->delete('post_files');
    }

    function getPostData_get() {
        $imagepath = base_url() . "assets/postfiles/";
        $this->db->order_by("id desc");
        $query = $this->db->get('post');
        $postdata = $query->result();
        $postdataarray = array();
        foreach ($postdata as $key => $value) {
            $this->db->where("table_name", "post");
            $this->db->where("table_id", $value->id);
            $this->db->order_by("id desc");
            $query = $this->db->get('post_files');
            $images = $query->result();
            $postimages = array();

            $queryr = "SELECT pl.datetime, ap.name  as name FROM `post_like` as pl  join app_user as ap on ap.id = pl.user_id"
                    . " where pl.post_id=$value->id group by ap.id   order by pl.id desc";
            $query = $this->db->query($queryr);
            $totallikes = $query->result_array();
            $totallikecount = count($totallikes);

            foreach ($images as $key2 => $value2) {
                $temp = array(
                    "img" => $imagepath . $value2->file_name,
                    "index" => $key2,
                    "id" => $value2->id,
                );
                array_push($postimages, $temp);
            }
            $value->userlikes = $totallikes;
            $value->likes = $totallikecount;
            $value->images = $postimages;
            array_push($postdataarray, $value);
        }
        $this->response($postdataarray);
    }

    function getGallaryDelete_get($imgid) {
        $this->db->where('id', $imgid);
        $this->db->delete('post_files');
    }

    function getGallary_get() {
        $imagepath = base_url() . "assets/postfiles/";
        $this->db->where("table_name", "gallery");
        $this->db->order_by("id desc");
        $query = $this->db->get('post_files');
        $gallaryImages = $query->result();
        $img1 = array();
        $img2 = array();
        $img3 = array();
        $albumData = array();
        foreach ($gallaryImages as $key => $value) {
            $temp = array(
                "img" => $imagepath . $value->file_name,
                "index" => $key,
                "id" => $value->id,
            );
            array_push($img2, $temp);
        }
        $this->response($img2);
    }

    function getUserPoints($userid) {
        $this->db->where('user_id', $userid);
        $this->db->order_by("id desc");
        $query = $this->db->get('points');
        $userpointdata = $query->result_array();
        $creditList = [];
        $debititList = [];
        $creditsum = 0;
        $debitsum = 0;
        foreach ($userpointdata as $pkey => $pvalue) {
            if ($pvalue['point_type'] == "Credit") {
                array_push($creditList, $pvalue);
                $creditsum += $pvalue["points"];
            } else {
                $debitsum += $pvalue["points"];
                array_push($debititList, $pvalue);
            }
        }
        return array("pointlist" => $userpointdata, "credit" => $creditsum, "debitsum" => $debitsum, "totalremain" => ($creditsum - $debitsum));
    }

    function getUserPoints_get($user_id) {
        $userpoints = $this->getUserPoints($user_id);
        $this->response($userpoints);
    }

    function getUserByMobCod_get($userinput) {
        $this->db->where('contact_no', $userinput);
        $this->db->or_where('usercode', $userinput);
        $query = $this->db->get('app_user');
        $userdata = $query->row();
        if ($userdata) {
            $userpoints = $this->getUserPoints($userdata->id);
        } else {
            $userpoints = [];
        }
        $this->response(array("userpoints" => $userpoints, "userdata" => $userdata));
    }

    function getUserByMobId_get($userinput) {
        $this->db->where('id', $userinput);
        $query = $this->db->get('app_user');
        $userdata = $query->row();
        if ($userdata) {
            $userpoints = $this->getUserPoints($userdata->id);
        } else {
            $userpoints = [];
        }
        $this->response(array("userpoints" => $userpoints, "userdata" => $userdata));
    }

    function createPoints_post() {
        $this->config->load('rest', TRUE);
        $class_assignment = array(
            'points' => $this->post('points'),
            'description' => $this->post('description'),
            "datetime" => date("Y-m-d H:i:s a"),
            'user_id' => $this->post('user_id'),
            "point_type" => $this->post('point_type'),
        );
        $userid = $this->post('user_id');
        $this->db->insert('points', $class_assignment);
        $last_id = $this->db->insert_id();
        $points = $this->post('points');
        $ctype = $this->post('point_type');
        $cctype = $ctype == 'Credit' ? 'Credited' : 'Debited';
        $message = array("title" => "Loyalty Card Points $cctype", "message" => "Your Card Has Been $cctype With $points Points");
        $this->singleMessage($message, $userid);
        $this->response(array("last_id" => $last_id));
    }

    function deletePoints_get($pointid) {
        $this->db->where('id', $pointid);
        $this->db->delete('points');
    }

    function getPostlike_get($postid, $user_id) {
        $this->db->where('post_id', $postid);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('post_like');
        $checklikes = $query->result_array();

        $this->db->where('id', $user_id);
        $query = $this->db->get('app_user');
        $userdata = $query->row();

        $this->db->where('post_id', $postid);
        $query = $this->db->get('post_like');
        $totallikes = $query->result_array();
        $totallikecount = count($totallikes);
        if ($checklikes) {
            $msg = "yes";
        } else {
            $msg = "no";
            $class_assignment = array(
                "datetime" => date("Y-m-d H:i:s a"),
                'user_id' => $user_id,
                "post_id" => $postid,
            );
            $this->db->insert('post_like', $class_assignment);
            $totallikecount += 1;
        }
        $title = "New Post Like";
        if ($userdata) {
            if ($userdata->name) {
                $title = $userdata->name . " Has Liked Post";
            }
        }
        $message = array("title" => $title, "message" => "Post ID #$postid Now Have $totallikecount Like");
        $this->broadCastMessgeAdmin($message);
        $this->response(array("likes" => $totallikecount, "msg" => $msg));
    }

    function testRend_get() {
        echo rand(1000, 9999);
    }

}

?>