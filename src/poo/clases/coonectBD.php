<?php

define("USUARIO", "root");
define("CLAVE", "");

class connectDB{

    private static connectDB $objConnectDB;

    private PDO $objPDO;

    private function __construct()
    {
        try{

            $this->objPDO = new PDO('mysql:host=localhost;dbname=administracion_bd;charset=utf8', USUARIO, CLAVE);

        }catch(PDOException $e){

            echo "Error!!!<br/>" . $e->getMessage();

            die();

        }
    }

    public function rtnSql(string $sql)
    {
        return $this->objPDO->prepare($sql);
    }

    public static function objAccess() : connectDB
    {
        if (!isset(self::$objConnectDB)) {       
            self::$objConnectDB = new connectDB(); 
        }
 
        return self::$objConnectDB;     
    }

    public function __clone()
    {
        trigger_error('La clonaci&oacute;n de este objeto no est&aacute; permitida!!!', E_USER_ERROR);
    }
 

}

?>