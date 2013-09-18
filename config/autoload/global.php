 <?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
        'db' => array(
        'driver' => 'Pdo',
        'username' => 'kevin',
        'password' => '123456',
        'dsn' => 'mysql:dbname=bd_grupos;host=192.168.1.50',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        )
    ),
   'upload' => array(
        'images' => APPLICATION_PATH . '/public/imagenes',
       
    ),

    'facebook' => array(
        'appId' => '171038663080276',
        'secret' => '5739ee928ea9149e62096e9bb91c501f'
    ),

    'host' => array(


        'base' => 'http://192.168.1.50:82',
        'static' => 'http://192.168.1.50:82',
        'images' => 'http://192.168.1.50:82/imagenes',
        'img'=>'http://192.168.1.50:82/img',
        'ruta' => 'http://192.168.1.50:82',
        'version'=>1,
    ),

        'mail' => array(
        'transport' => array(
            'options' => array(
                'host'              => 'smtp.innovationssystems.com',
                'connection_class'  => 'login',
                'connection_config' => array(
                    'username' => 'listadelsabor@innovationssystems.com',
                    'password' => 'L1st@d3ls@b0r',
                    // 'ssl' => 'tls'
                ),
            ),
        ),
    ),
//     'translator' => array(
//          'locale' => 'es_ES'
//      ),
     'service_manager' => array(
            'factories' => array(
                'Zend\Db\Adapter\Adapter'
                        => 'Zend\Db\Adapter\AdapterServiceFactory',
            ),
             'aliases' => array(
                'translator' => 'MvcTranslator',
            ),
    ),
//       'session' => array(
//        'config' => array(
//            'class' => 'Zend\Session\Config\SessionConfig',
//            'options' => array(
//                'name' => 'myapp',
//            ),
//        ),
//        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
//        'validators' => array(
//            'Zend\Session\Validator\RemoteAddr',
//            'Zend\Session\Validator\HttpUserAgent',
//        ),
//    ),
);
