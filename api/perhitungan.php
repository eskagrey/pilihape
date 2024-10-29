<?php
header("Access-Control-Allow-Origin: *");
session_start();
if(!isset($_SESSION['user_session']))
{
    $_SESSION['user_session'] = 'x';
}
require_once $_SERVER['DOCUMENT_ROOT'].'/pilihape/api/functions/fc_ponsel.php';

$input_params = json_decode(file_get_contents('php://input'), true);

// get data
if($_SERVER['REQUEST_METHOD'] =='POST')
{
    $func = new functions;
    $validation = $func->fc_validation_perhitungan($input_params);
    if($validation['status'] == false)
    {
        echo json_encode($validation);
        die;
    }
    $wj = $func->fc_get_wj($input_params);
    $wk = $func->fc_get_wk();
    $utility = $func->fc_get_utility($wk, $wj);
    if($utility == false)
    {
        $result = array("msg"=>"nilai utility tidak ditemukan");
    }
    else
    {
        $na = $func->fc_get_na($utility);
        if($na == false)
        {
            $result = array("msg"=>"nilai akhir tidak ditemukan");
        }
        else
        {
            // echo var_dump($na);die;
            $result = $func->sorting($na);
        }
    }
    echo json_encode($result);
}
else
{
    header(http_response_code(405));
}

?>