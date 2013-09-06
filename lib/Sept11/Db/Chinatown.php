<?php
class Sept11_Db_Chinatown
{
    const HOST     = 'localhost';
    const USERNAME = '';
    const PASSWORD = '';
    const DBNAME   = 'cdp';
    
    private static $_instance;
    
    private function __construct()
    {}
    
    public static function getInstance()
    {
        // Initialize the database object.
        if (null === self::$_instance) {
            self::$_instance = Zend_Db::factory('Pdo_Mysql', array(
                'host'     => self::HOST, 
                'username' => self::USERNAME, 
                'password' => self::PASSWORD, 
                'dbname'   => self::DBNAME, 
                'charset'  => 'utf8', // must pass utf8 connection charset
            ));
        }
        return self::$_instance;
    }
}
