<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Usuario\Controller\Index' => 'Usuario\Controller\IndexController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'usuario' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/usuario',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Usuario\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                
                
         
            
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action[/:in_id]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
              'usuario-micuenta' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/micuenta',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Usuario\Controller',
                        'controller' => 'Index',
                        'action' => 'editarusuario'
                    )
                )
            ), 
               'usuario-misgrupos' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/cuenta/misgrupos',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Usuario\Controller',
                        'controller' => 'Index',
                        'action' => 'misgrupos'
                    )
                )
            ), 
            'usuario-miseventos' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/cuenta/miseventos',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Usuario\Controller',
                        'controller' => 'Index',
                        'action' => 'miseventos'
                    )
                )
            ), 
            'usuario-eventosparticipo' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/cuenta/eventosparticipo',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Usuario\Controller',
                        'controller' => 'Index',
                        'action' => 'eventosparticipo'
                    )
                )
            ), 
            'usuario-grupoparticipo' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/cuenta/grupoparticipo',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Usuario\Controller',
                        'controller' => 'Index',
                        'action' => 'grupoparticipo'
                    )
                )
            ), 
        ),
    ),

    
//           'agregar' => array(
//                'type' => 'Literal',
//                'options' => array(
//                    'route' => '/agregar',
//                    'defaults' => array(
//                        '__NAMESPACE__' => 'Usuario\Controller',
//                        'controller' => 'Index',
//                        'action' => 'agregarusuario'
//                    )
//                )
//            ), 
    'view_manager' => array(
        'template_path_stack' => array(
            'Usuario' => __DIR__ . '/../view',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
            'usuario/index'           => __DIR__ . '/../view/usuario/index/header-usuario.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
        'view_helpers' => array(
        'invokables' => array(
            'host' => 'Application\View\Helper\Host',
        )
    ),
);
