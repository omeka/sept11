<?php
class Sept11_Db_Sonic
{
    private static $_instance;
    
    private function __construct()
    {}
    
    public static function getInstance()
    {
        // Initialize the database object.
        if (null === self::$_instance) {
            self::$_instance = Zend_Db::factory('Pdo_Mysql', array(
                'host'     => DB_HOST, 
                'username' => DB_USERNAME, 
                'password' => DB_PASSWORD, 
                'dbname'   => 'sonic', 
                'charset'  => 'utf8', // must pass utf8 connection charset
            ));
        }
        return self::$_instance;
    }
}
