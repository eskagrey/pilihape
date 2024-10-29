<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
header("Access-Control-Allow-Origin: http://0016d99f0ba3.ngrok.io");
require_once $_SERVER['DOCUMENT_ROOT'].'/pilihape/api/functions/fc_admin.php';

$input_params = json_decode(file_get_contents('php://input'), true);

// login
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    try{
        $func = new functions_adm;
        $result = array();
        $valid = $func->fc_validation_login($input_params);
        $check_user_or_phone = $func->fc_check_user_or_phone($input_params); #echo $check_user_or_phone;die;
        if($check_user_or_phone == false)
        {
            header("HTTP/1.0 401 username atau no ponsel tidak terdaftar");
            // $result['msg'] .= "username atau no ponsel tidak terdaftar";
        }
        else
        {
            $check_pass = $func->fc_check_pass($input_params);
            if($check_pass == false || $check_pass['password'] !== $input_params['password'])
            {
                header('HTTP/1.0 401 password salah');
                // $result['msg'] .= "password salah";
            }
            else
            {
                header("store_name:".$check_pass['store_name']);
                header("store_id:".$check_pass['store_id']);
                $session = $func->fc_get_store_name($check_pass['store_id']).' - '.date('ddmmyy', time());
                $upd_session = $func->fc_activate_session($input_params, $session);
                $result['msg'] = "login berhasil";
                session_start();
                $_SESSION['user_session'] = $session;
            }
            echo json_encode($result);
        }
    }
    catch (Exception $e)
    {
        header("HTTP/1.0 401 ".$e);
    }
}
else
{
    header(http_response_code(405));
}

?>