<?php 
function ojabooking_register_price_categories_taxonomy(){
    $labels=array(
        'name'  => _x('Price categories','Price category tags', 'ojabooking'),
        'singular_name'  => _x('Price category','Price category tag', 'ojabooking'),
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
        'public'            => true,
    );
    register_taxonomy('ojabooking_price_categories','ojabooking_event',$args);
}
add_action( 'init', 'ojabooking_register_price_categories_taxonomy');

add_action( 'ojabooking_price_categories_add_form_fields', 'ojabooking_price_categories_add_term_fields' );
add_action( 'ojabooking_price_categories_edit_form_fields', 'ojabooking_price_categories_edit_term_fields' );
add_action('edited_ojabooking_price_categories', 'ojabooking_price_categories_update_term_fields', 10, 2);
add_action('created_ojabooking_price_categories', 'ojabooking_price_categories_update_term_fields', 10, 2);

function ojabooking_price_categories_update_term_fields($term_id, $tt_id) {
    if (isset($_POST['private_party'])){
        $group = $_POST['private_party'];
        update_term_meta($term_id, 'private_party', true);
    }else{
        delete_term_meta($term_id, 'private_party');
    }
}

function ojabooking_price_categories_add_term_fields( $taxonomy ) {

	echo '<div class="form-field">
	<label for="private-party">' . __('Private party','ojabooking') . '</label>
	<input type="checkbox" name="private_party" id="private-party" />
	<p>'.__('No one else can book on the same term.','ojabooking') . '</p>
	</div>';
}

function ojabooking_price_categories_edit_term_fields( $term ) {
    $private_party = get_term_meta( $term->term_id, 'private_party', true);

	echo '<tr class="form-field">
	<th>
        <label for="private-party">' . __('Private party','ojabooking') . '</label>
	</th>
	<td>
        <input type="checkbox" name="private_party" id="private-party" ' . checked($private_party,true,false) . ' />
        <p>'.__('No one else can book on the same term.','ojabooking') . '</p>
    </td>
	</tr>';
}