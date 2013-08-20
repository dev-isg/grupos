<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Grupo\Controller\Index' => 'Grupo\Controller\IndexController',
            'Grupo\Controller\Evento' => 'Grupo\Controller\EventoController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'grupos' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/grupo',//
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Grupo\Controller',
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
                                'controller' => 'Grupo\Controller\Index',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),
            'detalle-grupo' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/detalle-grupo[/:in_id]',
                    'defaults' => array(
                        'controller' => 'Grupo\Controller\Index',
                        'action' => 'detallegrupo'
                    )
                ),
               ),
            'agregar-grupo' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/grupo[/:in_id]',
                    'defaults' => array(
                        'controller' => 'Grupo\Controller\Index',
                        'action' => 'agregargrupo'
                    )
                ),
            ),
            'evento' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/evento[/:in_id]',
                    'defaults' => array(
                        'controller' => 'Grupo\Controller\Evento',
                        'action' => 'detalleevento'
                    )
                ),
            ),
            'agregar-evento' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/agregar-evento[/:in_id]',
                    'defaults' => array(
                        'controller' => 'Grupo\Controller\Evento',
                        'action' => 'agregarevento'
                    )
                ),
            ),
        
        ),
    ),
    'view_manager' => array(
//        'display_not_found_reason' => true,
//        'display_exceptions'       => true,
//        'doctype'                  => 'HTML5',
//        'not_found_template'       => 'error/404',
//        'exception_template'       => 'error/index',
        'template_map' => array(
//            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
//             'layout/header'                => __DIR__ . '/../view/layout/header.phtml',
//             'layout/footer'                => __DIR__ . '/../view/layout/footer.phtml',
            'grupo/evento/agregarevento'     => __DIR__ . '/../view/grupo/evento/agregarevento.phtml',
//            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
//            'error/404'               => __DIR__ . '/../view/error/404.phtml',
//            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'grupo' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),

);
