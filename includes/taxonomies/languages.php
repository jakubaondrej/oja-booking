<?php 
function ojabooking_register_language_taxonomy(){
    $labels=array(
        'name'  => _x('Languages','language tags', 'ojabooking'),
        'singular_name'  => _x('Language','language tag', 'ojabooking'),
    );
    $args=array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
    );
    register_taxonomy('ojabooking_languages','ojabooking_event',$args);
}
add_action( 'init', 'ojabooking_register_language_taxonomy', 0 );