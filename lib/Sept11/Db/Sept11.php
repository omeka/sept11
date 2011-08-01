<?php
class Sept11_Db_Sept11
{
    const PATH_SEPT11_DB = '/etc/httpd/includes/911da.inc.php';
    
    private static $_instance;
    
    private function __construct()
    {}
    
    public static function getInstance()
    {
        // Initialize the database object.
        if (null === self::$_instance) {
            require_once self::PATH_SEPT11_DB;
            self::$_instance = Zend_Db::factory('Pdo_Mysql', array(
                'host' => $HostNameForMySQL, 
                'username' => $UserNameForMySQL, 
                'password' => $PasswordForMySQL, 
                'dbname' => $DatabaseNameForMySQL, 
                'charset' => 'utf8', // must pass utf8 connection charset
            ));
        }
        
        // Ping the database for a connection. If there is no connection, close 
        // it and reconnect. This is to prevent "server has gone away" errors 
        // after long processes.
        try {
            self::$_instance->fetchAll('SELECT 1');
        } catch (Zend_Db_Exception $e) {
            self::$_instance->closeConnection();
            self::$_instance->getConnection();
        }
        
        return self::$_instance;
    }
}