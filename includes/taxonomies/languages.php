<?php 
function oja_register_language_taxonomy(){
    $labels=array(
        'name'  => _x('Languages','language tags', 'oja'),
        'singular_name'  => _x('Language','language tag', 'oja'),
    );
    $args=array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
    );
    register_taxonomy('oja_languages','oja_event',$args);
}
add_action( 'init', 'oja_register_language_taxonomy', 0 );