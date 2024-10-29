<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
class cstr
{
    function pdo()
    {
        try
        {
            $db     = new PDO('mysql:host=localhost;dbname=pilihape', 'admin', 'password');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
        }
    }
}

?>