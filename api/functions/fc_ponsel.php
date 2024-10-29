<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/pilihape/api/db_cstr/db_cstr.php');

class functions extends cstr
{

    private $phone_params = array(
                                'merk_type',
                                'layar_ukuran',
                                'layar_resolusi',
                                'layar_model', 
                                'kapasitas_baterai', 
                                'kamera_jumlah', 
                                'kamera_res_depan', 
                                'kamera_res_belakang', 
                                'prosesor',
                                'ranking_prosesor', 
                                'kapasitas_penyimpanan',
                                'kapasitas_ram', 
                                'umur',
                                'software', 
                                'kondisi_fisik', 
                                'kelengkapan', 
                                'harga',
                                'store_id',
                                'image_url'
                            );

    function fc_validation_input($input_params)
    {
        $result = array('status'=>true,'msg'=>'');
        $input_params['ranking_prosesor'] = '0';
        foreach ($this->phone_params as $var)
        {
            if(!isset($input_params[$var]) || $input_params[$var] == null || $input_params[$var] == '')
            {
                $result['status'] = false;
                $result['msg'] .= $var.' kosong';
            }
        }
        return $result;
    }

    function fc_validation_photo($input_params, $vars)
    {
        $result = array('status'=>true,'msg'=>'');
        foreach ($vars as $var)
        {
            if(!isset($input_params[$var]) || $input_params[$var] == null || $input_params[$var] == '')
            {
                $result['status'] = false;
                $result['msg'] .= $var.' kosong';
            }
        }
        return $result;
    }

    function fc_validation_perhitungan($input_params)
    {
        $result = array('status'=>true, 'msg'=>'');
        $params = $this->phone_params;
        unset($params['0'], $params['8'], $params['17'], $params['18']);
        foreach ($params as $var)
        {
            if(!isset($input_params[$var]) || $input_params[$var] == null || $input_params[$var] == '')
            {
                $result['status'] = false;
                $result['msg'] .= $var.' kosong';
            }
        }
        return $result;
    }

    function fc_get_proc_score($processsor_nm, $pdo)
    {
        // $pdo = new cstr;
        $sql = "SELECT score FROM tb_centurionmark WHERE prosesor = '$processsor_nm'";
        $result =  $pdo->pdo()->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result;
    }


// BOBOT KRITERIA & WK->(nilai BK yang di normalisasi dengan ROC) =================================================================================================================
    function fc_convert_bobotkriteria($input_params)
    {
        unset($input_params['prosesor']);
        unset($input_params['image_url']);
        $nilai = array(
            'layar_resolusi'=>array(
                '720p'=>1,
                '1080p'=>2,
                '1440p'=>3
            ),
            'layar_model'=>array(
                'konvensional'=>1,
                'full view'=>2,
                'notch'=>3,
                'punch hole'=>4,
                'all screen'=>5
            ), 
             'kamera_jumlah'=>array(
                 'single'=>1,
                 'dual'=>2,
                 'triple'=>3,
                 'quad'=>4
             ),
             'kamera_res_depan'=>array(
                5=>1,
                8=>2,
                12=>3,
                13=>3,
                16=>4,
                21=>5,
                25=>6,
                32=>7
             ),
             'kamera_res_depan'=>array(
                8=>1,
                12=>2,
                13=>2,
                16=>3,
                21=>4,
                25=>5,
                32=>6,
                48=>7
             ),
             'kapasitas_penyimpanan'=>array(
                 16=>1,
                 32=>2,
                 64=>3,
                 128=>4
             ),
             'kapasitas_ram'=>array(
                 2=>1,
                 3=>2,
                 4=>3,
                 6=>4
             ),
             'umur'=>array(
                 '<12 bulan'=>3,
                 '12-24 bulan'=>2,
                 '>24 bulan'=>1
             ),
             'software'=>array(
                 'android 6'=>1,
                 'android 7'=>2,
                 'android 8'=>3,
                 'android 9'=>4,
                 'android 10'=>5
             ),
             'kondisi_fisik'=>array(
                 'mulus'=>3,
                 'lecet ringan'=>2,
                 'lecet parah'=>1
             ),
             'kelengkapan'=>array(
                 'fullset'=>3,
                 'unit dan charger'=>2,
                 'hanya unit'=>1
             )
        );

        $bk = $input_params;
        foreach($input_params as $key=>$val)
        {
            foreach($nilai as $k=>$v)
            {
                if($key = $k)
                {
                    foreach($v as $x=>$y)
                    if($val == $x)
                    {
                        $bk[$key] = $y;
                    }
                }  
            }
        }

        // layar_ukuran
        if($input_params['layar_ukuran'] <= 5.4)
        {
            $bk['layar_ukuran'] = 1;
        }
        else if($input_params['layar_ukuran'] >= 5.5 && $input_params['layar_ukuran'] <= 6)
        {
            $bk['layar_ukuran'] = 2;
        }
        else if($input_params['layar_ukuran'] >= 6.1 && $input_params['layar_ukuran'] <= 6.5)
        {
            $bk['layar_ukuran'] = 3;
        }
        else if($input_params['layar_ukuran'] > 6.5)
        {
            $bk['layar_ukuran'] = 4;
        }

        // kapasitas_baterai
        if($input_params['kapasitas_baterai'] <= 3200 )
        {
            $bk['kapasitas_baterai'] = 1;
        }
        else if($input_params['kapasitas_baterai'] >= 3201 && $input_params['kapasitas_baterai'] <= 3999)
        {
            $bk['kapasitas_baterai'] = 2;
        }
        else if($input_params['kapasitas_baterai'] >= 4000 && $input_params['kapasitas_baterai'] <= 4499)
        {
            $bk['kapasitas_baterai'] = 3;
        }
        else if($input_params['kapasitas_baterai'] >= 4500 && $input_params['kapasitas_baterai'] <= 5000)
        {
            $bk['kapasitas_baterai'] = 4;
        }
        else if($input_params['kapasitas_baterai'] >= 5000)
        {
            $bk['kapasitas_baterai'] = 5;
        }

        // kamera_res_depan
        if($input_params['kamera_res_depan'] == '5')
        {
            $bk['kamera_res_depan'] = 1;
        }
        else if($input_params['kamera_res_depan'] == '8')
        {
            $bk['kamera_res_depan'] = 2;
        }
        else if($input_params['kamera_res_depan'] == '12')
        {
            $bk['kamera_res_depan'] = 3;
        }
        else if($input_params['kamera_res_depan'] == '16')
        {
            $bk['kamera_res_depan'] = 4;
        }
        else if($input_params['kamera_res_depan'] == '21')
        {
            $bk['kamera_res_depan'] = 5;
        }
        else if($input_params['kamera_res_depan'] == '25')
        {
            $bk['kamera_res_depan'] = 6;
        }
        else if($input_params['kamera_res_depan'] == '32')
        {
            $bk['kamera_res_depan'] = 7;
        }

        // kamera_res_belakang
        if($input_params['kamera_res_belakang'] =='8')
        {
            $bk['kamera_res_belakang'] = 1;
        }
        else if($input_params['kamera_res_belakang'] =='12')
        {
            $bk['kamera_res_belakang'] = 2;
        }
        else if($input_params['kamera_res_belakang'] =='16')
        {
            $bk['kamera_res_belakang'] = 3;
        }
        else if($input_params['kamera_res_belakang'] =='21')
        {
            $bk['kamera_res_belakang'] = 4;
        }
        else if($input_params['kamera_res_belakang'] =='25')
        {
            $bk['kamera_res_belakang'] = 5;
        }
        else if($input_params['kamera_res_belakang'] =='32')
        {
            $bk['kamera_res_belakang'] = 6;
        }
        else if($input_params['kamera_res_belakang'] =='48')
        {
            $bk['kamera_res_belakang'] = 7;
        }

        // prosesor
        if($input_params['ranking_prosesor'] <55)
        {
            $bk['ranking_prosesor'] = 1;
        }
        else if($input_params['ranking_prosesor'] >=55 && $input_params['ranking_prosesor'] <65)
        {
            $bk['ranking_prosesor'] = 2;
        }
        else if($input_params['ranking_prosesor'] >=65 && $input_params['ranking_prosesor'] <90)
        {
            $bk['ranking_prosesor'] = 3;
        }
        else if($input_params['ranking_prosesor'] >=90 && $input_params['ranking_prosesor'] <=120)
        {
            $bk['ranking_prosesor'] = 4;
        }
        else if($input_params['ranking_prosesor'] >120)
        {
            $bk['ranking_prosesor'] = 5;
        }

        // harga
        if($input_params['harga'] >= 1800000 && $input_params['harga'] <= 1999000)
        {
            $bk['harga'] = 1;
        }
        else if($input_params['harga'] >= 1600000 && $input_params['harga'] <= 1799000)
        {
            $bk['harga'] = 2;
        }
        else if($input_params['harga'] >= 1400000 && $input_params['harga'] <= 1599000)
        {
            $bk['harga'] = 3;
        }
        else if($input_params['harga'] >= 1200000 && $input_params['harga'] <= 1399000)
        {
            $bk['harga'] = 4;
        }
        else if($input_params['harga'] >= 1000000 && $input_params['harga'] <= 1199000)
        {
            $bk['harga'] = 5;
        }
        return $bk;
    }

    function fc_convert_wk($bk)
    {
        $nmax = array(
            'layar_ukuran'=>4,
            'layar_resolusi'=>3,
            'layar_model'=>5, 
            'kapasitas_baterai'=>5, 
            'kamera_jumlah'=>4, 
            'kamera_res_depan'=>7, 
            'kamera_res_belakang'=>7,
            'ranking_prosesor'=>5, 
            'kapasitas_penyimpanan'=>4,
            'kapasitas_ram'=>4, 
            'umur'=>3,
            'software'=>5, 
            'kondisi_fisik'=>3, 
            'kelengkapan'=>3, 
            'harga'=>5
        );
        $nilai_wk = $bk;
        foreach($nmax as $key=>$val)
        {
            foreach($bk as $k=>$v)
            {
                if($key == $k)
                {
                    $nilai_wk[$key] = 1/$val/(1/$v);
                }
            }
        } 
        return $nilai_wk;
    }

    function fc_get_bk_id($id)
    {
        $pdo = new cstr;
        $sql = "SELECT * FROM tb_bobot_kriteria WHERE id = $id"; 
        $result = $pdo->pdo()->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    function fc_get_wk_id($id)
    {
        $pdo = new cstr;
        $sql = "SELECT * FROM tb_wk WHERE id = $id"; 
        $result = $pdo->pdo()->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    function fc_input_bk_wk($input_params, $pdo){
        // $pdo = new cstr;
        $bk = $this->fc_convert_bobotkriteria($input_params);
        $wk = $this->fc_convert_wk($bk);
        $sql_bk = "INSERT INTO tb_bobot_kriteria (";
        foreach($bk as $key=>$val)
        {
            $sql_bk .= "$key,";
        }
        $sql_bk = substr($sql_bk,0,-1).') VALUES (';
        foreach($bk as $key=>$val)
        {
            $sql_bk .= "'$val',";
        }
        $sql_bk = substr($sql_bk,0,-1).');'; 

        $sql_wk = "INSERT INTO tb_wk (";
        foreach($wk as $key=>$val)
        {
            $sql_wk .= "$key,";
        }
        $sql_wk = substr($sql_wk,0,-1).') VALUES (';
        foreach($wk as $key=>$val)
        {
            $sql_wk .= "'$val',";
        }
        $sql_wk = substr($sql_wk,0,-1).');'; 
        try
        {
            $input_bk = $pdo->pdo()->prepare($sql_bk)->execute();
            if($input_bk == true)
            {
                $input_wk = $pdo->pdo()->prepare($sql_wk)->execute();
                $result = $input_wk;
            }
        }
        catch(PDOException $e)
        {
            return $e;
        }
        return $result;
    }

    function fc_get_wk()
    {
        $pdo = new cstr;
        $sql = "SELECT * FROM tb_wk;";
        $result = $pdo->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function fc_update_bk_wk($input_params)
    {
        $pdo = new cstr;
        $bk = $this->fc_convert_bobotkriteria($input_params);
        $wk = $this->fc_convert_wk($bk);
        unset($bk['id']);
        // BK
        $sql_bk = "UPDATE tb_bobot_kriteria SET ";
        foreach($bk as $key=>$val)
        {
            $sql_bk .= "$key = '$val',";
        }
        $sql_bk = substr($sql_bk,0,-1)." WHERE id = ".$input_params['id'].";"; #echo $sql;

        // WK
        $sql_wk = "UPDATE tb_wk SET ";
        foreach($wk as $key=>$val)
        {
            $sql_wk .= "$key = '$val',";
        }
        $sql_wk = substr($sql_wk,0,-1)." WHERE id = ".$input_params['id'].";";

        $update_bk = $pdo->pdo()->prepare($sql_bk)->execute();
        if($update_bk == true)
        {
            $update_wk = $pdo->pdo()->prepare($sql_wk)->execute();
            if($update_wk == true)
            {
                $result = $update_wk;
                return $result;
            }
        }
    }

    function fc_delete_bk_wk($id)
    {
        try{
            $pdo = new cstr;
            $sql_bk = "DELETE FROM tb_bobot_kriteria WHERE id = $id";
            $sql_wk = "DELETE FROM tb_wk WHERE id = $id";
            $delete_bk = $pdo->pdo()->prepare($sql_bk)->execute();
            if($delete_bk == true)
            {
                $delete_wk = $pdo->pdo()->prepare($sql_wk)->execute();
                if($delete_wk == true)
                {
                    $result = $delete_wk;
                    return $result;
                }
            }
        } catch (PDOException $e) {
            return $e;
        }
        
    }

// DATA PONSEL =====================================================================================================================
    function fc_get_all_data()
    {
        $pdo = new cstr;
        $sql = "SELECT * FROM tb_ponsel";
        $result = $pdo->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function fc_get_processor_list()
    {
        $pdo = new cstr;
        $sql = "SELECT * FROM tb_centurionmark";
        $result = $pdo->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function fc_get_data_by_id($id)
    {
        $pdo = new cstr;
        $sql = "SELECT * FROM tb_ponsel WHERE id = $id";
        $result = $pdo->pdo()->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    function fc_get_data_by_store_id($id)
    {
        $pdo = new cstr;
        $sql = "SELECT * FROM tb_ponsel WHERE store_id = '$id'";
        $result = $pdo->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function fc_input_data($input_params, $pdo)
    {
        $sql = "INSERT INTO tb_ponsel (";
        foreach($this->phone_params as $key)
        {
            $sql .= "$key,";
        }
        $sql = substr($sql,0,-1).') VALUES (';
        foreach($this->phone_params as $val)
        {
            $sql .= "'$input_params[$val]',";
        }
        $sql = substr($sql,0,-1).');';
        $result = $pdo->pdo()->prepare($sql)->execute();
        return $result;
    }

    function fc_update_data($input_params)
    {
        $pdo = new cstr;
        $sql = "UPDATE tb_ponsel SET ";
        foreach($this->phone_params as $key)
        {
            $sql .= "$key = '$input_params[$key]',";
        }
        $sql = substr($sql,0,-1)." WHERE id = ".$input_params['id'].";"; 
        $result = $pdo->pdo()->prepare($sql)->execute();
        return $result;
    }

    function fc_delete_data($id)
    {
        $pdo = new cstr;
        $sql = "DELETE FROM tb_ponsel WHERE id = $id";
        $result = $pdo->pdo()->prepare($sql)->execute();
        return $result;
    }

// MENGHITUNG NILAI AKHIR =====================================================================================================

    function fc_get_wj($input_params)
    {
        $result = array();
        foreach($input_params as $key=>$val)
        {
            $result[$key] = ((intval($val)-1) / (4-1));
        }
        return $result;
    }

    function fc_get_utility($wk, $input_params)
    {
        $result = array();
        foreach($input_params as $key=>$val)
        {
           foreach($wk as $k=>$v)
            {
                foreach ($v as $m=>$n)
                {
                    if($key == $m)
                    {
                        $result[$k][$key] = $val * $n;
                        $result[$k]['id'] = $wk[$k]['id'];
                        $result[$k]['merk_type'] = $wk[$k]['merk_type'];
                        $result[$k]['store_id'] = $wk[$k]['store_id'];
                    }
                }
            }
        }
        return $result;
    }

    function get_image_url($id)
    {
        $pdo = new cstr();
        $sql = "SELECT image_url FROM tb_ponsel WHERE id = $id;";
        $result = $pdo->pdo()->query($sql)->fetch(PDO::FETCH_COLUMN);
        return $result;
    }

    function fc_get_na($utility)
    {
        for($i=0;$i<count($utility); $i++)
        {
            $na[$i]['id']           = $utility[$i]['id'];
            $na[$i]['merk_type']    = $utility[$i]['merk_type'];
            $na[$i]['store_id']     = $utility[$i]['store_id'];
            $na[$i]['image_url']    = $this->get_image_url($utility[$i]['id']);
            $na[$i]['nilai_akhir']  = 
                $utility[$i]["layar_ukuran"] +
                $utility[$i]["layar_resolusi"] +
                $utility[$i]["layar_model"] +
                $utility[$i]["kapasitas_baterai"] +
                $utility[$i]["kamera_jumlah"] +
                $utility[$i]["kamera_res_depan"] +
                $utility[$i]["kamera_res_belakang"] +
                $utility[$i]["ranking_prosesor"] +
                $utility[$i]["kapasitas_penyimpanan"] +
                $utility[$i]["kapasitas_ram"] +
                $utility[$i]["umur"] +
                $utility[$i]["software"] +
                $utility[$i]["kondisi_fisik"] +
                $utility[$i]["kelengkapan"] +
                $utility[$i]["harga"];
        }
        return $na;
    }
    
    function sorting($na)
    {
        $data = $na;
        $keys = array_column($data, 'nilai_akhir');
        array_multisort($keys, SORT_DESC, $data);
        return $data;
    }

}

?>