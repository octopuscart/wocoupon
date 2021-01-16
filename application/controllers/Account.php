<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    }

    public function index() {
        echo "404";
    }

    //Profile page
    public function profile() {

        $query = $this->db->get('country');
        $countrylist = $query->result();
        $data1['countrylist'] = $countrylist;

        if ($this->user_id == 0) {
            redirect('Account/login');
        }

        $user_details = $this->User_model->user_details($this->user_id);
        $data['user_details'] = $user_details;
        $data['msg'] = "";
        if (isset($_POST['change_password'])) {
            $old_password = $this->input->post('old_password');
            $new_password = $this->input->post('new_password');
            $re_password = $this->input->post('re_password');

            if ($user_details->password == md5($old_password)) {
                if ($new_password == $re_password) {
                    $password = md5($re_password);
                    $this->db->set('password', $password);
                    $this->db->where('id', $this->user_id);
                    $this->db->update('admin_users');
                    redirect('Account/profile');
                } else {
                    $data['msg'] = "Password didn't match.";
                }
            } else {
                $data['msg'] = 'Enterd wrong password.';
            }
        }


        if (isset($_POST['update_profile'])) {
            $this->db->set('first_name', $this->input->post('first_name'));
            $this->db->set('last_name', $this->input->post('last_name'));
            $this->db->set('contact_no', $this->input->post('contact_no'));
            $this->db->set('gender', $this->input->post('gender'));
            $this->db->set('birth_date', $this->input->post('birth_date'));

            $this->db->where('id', $this->user_id);
            $this->db->update('admin_users');

            $session_user = $this->session->userdata('logged_in');
            $session_user['first_name'] = $this->input->post('first_name');
            $session_user['last_name'] = $this->input->post('last_name');
            $this->session->set_userdata('logged_in', $session_user);

            redirect('Account/profile');
        }
        $this->load->view('Account/profile', $data);
    }

    //login page
    //login page
    

    // Logout from admin page
    function logout() {
        $newdata = array(
            'username' => '',
            'password' => '',
            'logged_in' => FALSE,
        );

        $this->session->unset_userdata($newdata);
        $this->session->sess_destroy();

        redirect('Account/login');
    }

    //orders list
    function orderList() {
        if ($this->user_id == 0) {
            redirect('Account/login');
        }
        $this->db->where('user_id', $this->user_id);
        $query = $this->db->get('user_order');
        $orderlist = $query->result();

        $orderslistr = [];
        foreach ($orderlist as $key => $value) {

            $this->db->order_by('id', 'desc');
            $this->db->where('order_id', $value->id);
            $query = $this->db->get('user_order_status');
            $status = $query->row();
            $value->status = $status ? $status->status : $value->status;
            array_push($orderslistr, $value);
        }
        $data['orderslist'] = $orderslistr;


        $this->load->view('Account/orderList', $data);
    }

    function newsletter() {
        if ($this->user_id == 0) {
            redirect('Account/login');
        }


        $this->load->view('Account/newsletter');
    }

    //Address management
    function address() {
        $user_address_details = $this->User_model->user_address_details($this->user_id);
        $data['user_address_details'] = $user_address_details;

        //Get Address
        if (isset($_GET['setAddress'])) {
            $this->db->set('status', "");
            $this->db->where('user_id', $this->user_id);
            $this->db->update('shipping_address');

            $adid = $_GET['setAddress'];
            $this->db->set('status', "default");
            $this->db->where('id', $adid);
            $this->db->update('shipping_address');
            redirect('Account/address');
        }

        //add New address
        if (isset($_POST['add_address'])) {
            $this->db->set('status', "");
            $this->db->where('user_id', $this->user_id);
            $this->db->update('shipping_address');

            $this->db->set('status', "");
            $this->db->where('user_id', $this->user_id);
            $this->db->update('shipping_address');

            $category_array = array(
                'address1' => $this->input->post('address1'),
                'address2' => $this->input->post('address2'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
//                'pincode' => $this->input->post('pincode'),
                'country' => $this->input->post('country'),
                'user_id' => $this->user_id,
                'status' => 'default',
            );
            $this->db->insert('shipping_address', $category_array);
            redirect('Account/address');
        }


        $this->load->view('Account/address', $data);
    }

    //function credits
    function credits() {
        if ($this->user_id == 0) {
            redirect('Account/login');
        }

        $user_id = $this->user_id;

        $user_credits = $this->User_model->user_credits($this->user_id);
        $data['user_credits'] = $user_credits;

        $querys = "select * from (
                   select credit, '' as debit, order_id, remark, c_date, c_time  FROM `user_credit` 
                   where user_id = $user_id and credit>0
                    union
                   select '' as credit, credit as debit, order_id, remark, c_date, c_time  FROM `user_debit`
                   where user_id = $user_id  and credit>0
                   ) as credit order by c_date desc";

        $query = $this->db->query($querys);
        $creditlist = $query->result();
        $data['creditlist'] = $creditlist;


        $this->load->view('Account/credits', $data);
    }

    function testReg() {
        $user_id = $this->user_id;
        $this->User_model->registration_mail($user_id);
    }

}

?>
