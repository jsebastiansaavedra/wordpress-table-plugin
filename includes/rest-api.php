<?php
/**
 * Base class for REST API registration and management
 * 
 * @version         0.1.0
 *
 * @author        Juan Sebastian Saavedra Alvarez
 * 
 */


/**
 * Register REST API routes.
 */
function tde_register_rest_routes() {
    register_rest_route( 'tde/v1', '/insert', array(
        'methods'  => 'POST',
        'callback' => 'tde_rest_insert_data',
        'permission_callback' => '__return_true',
    ) );

    register_rest_route( 'tde/v1', '/select', array(
        'methods'  => 'GET',
        'callback' => 'tde_rest_select_data',
        'permission_callback' => '__return_true',
    ) );
}

/**
 * Handle data insertion via REST API.
 */
function tde_rest_insert_data( WP_REST_Request $request ) {
    $value = sanitize_text_field( $request->get_param( 'value' ) );

    if ( empty( $value ) ) {
        return new WP_Error( 'no_value', 'No value provided', array( 'status' => 400 ) );
    }

    insert_data_to_my_table( $value );

    return new WP_REST_Response( 'Data inserted successfully', 200 );
}

/**
 * Handle data retrieval via REST API.
 */
function tde_rest_select_data( WP_REST_Request $request ) {
    $search = sanitize_text_field( $request->get_param( 'search' ) );

    $data = get_my_table_data( $search );

    return new WP_REST_Response( $data, 200 );
}
