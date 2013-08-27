<?php
namespace Classes;

include_once APPLICATION_PATH . '/vendor/facebook/facebook.php';


/**
 *
 * @author webmaster
 *        
 */
class fb
{

    static $_instance;

    protected $_fb;

    /**
     */
    function __construct()
    {
        $options = new \Zend\Config\Config(include APPLICATION_PATH . '/config/autoload/global.php');
        if (!$options->fb)
            throw new Exception('Configuration facebook not available.'); 
      $this->_fb = new \Facebook(array(
        'appId' => $options->facebook->appId,
        'secret' => $options->facebook->secret));
     //  var_dump($d);exit;
    }

    static function getInstance()
    {
        if (! isset(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    function getfb()
    {
        return $this->_fb;
    }
}

?>