<?php 
function oja_register_price_categories_taxonomy(){
    $labels=array(
        'name'  => _x('Price categories','Price category tags', 'oja'),
        'singular_name'  => _x('Price category','Price category tag', 'oja'),
    );
    $args=array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_rest'      => true,
        'show_tagcloud'     => true,
        'meta_box_cb'       => false,
        'show_admin_column' => false,
        'query_var'         => true,
        'public'            => false,
    );
    register_taxonomy('oja_price_categories','oja_event',$args);
}
add_action( 'init', 'oja_register_price_categories_taxonomy', 0 );

add_action( 'oja_price_categories_add_form_fields', 'oja_price_categories_add_term_fields' );
add_action( 'oja_price_categories_edit_form_fields', 'oja_price_categories_edit_term_fields' );
add_action('edited_oja_price_categories', 'oja_price_categories_update_term_fields', 10, 2);
add_action('created_oja_price_categories', 'oja_price_categories_update_term_fields', 10, 2);

function oja_price_categories_update_term_fields($term_id, $tt_id) {
    if (isset($_POST['private_party'])){
        $group = $_POST['private_party'];
        update_term_meta($term_id, 'private_party', true);
    }else{
        delete_term_meta($term_id, 'private_party');
    }
}

function oja_price_categories_add_term_fields( $taxonomy ) {

	echo '<div class="form-field">
	<label for="private-party">' . __('Private party','oja') . '</label>
	<input type="checkbox" name="private_party" id="private-party" />
	<p>'.__('No one else can book on the same term.','oja') . '</p>
	</div>';
}

function oja_price_categories_edit_term_fields( $term ) {
    $private_party = get_term_meta( $term->term_id, 'private_party', true);

	echo '<tr class="form-field">
	<th>
        <label for="private-party">' . __('Private party','oja') . '</label>
	</th>
	<td>
        <input type="checkbox" name="private_party" id="private-party" ' . checked($private_party,true,false) . ' />
        <p>'.__('No one else can book on the same term.','oja') . '</p>
    </td>
	</tr>';
}