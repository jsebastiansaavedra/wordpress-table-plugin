<?php
/**
 * Plugin Name:          CampusPress Theme Developer Plugin
 * Description:          Make Magic
 * Version:              0.1.0
 * Requires at least:    6.1
 * Requires PHP:         7.4
 * Author:               Juan Sebastian Saavedra Alvarez
 * Author URI:           https://github.com/jsebastiansaavedra
 * License:              GPLv2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          theme-developer-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action( 'init', 'tde_plugin_init' );
add_action( 'rest_api_init', 'tde_register_rest_routes' );

function tde_plugin_init() {
    maybe_create_my_table();
    add_shortcode( 'my_form', 'tde_shortcode_form' );
    add_shortcode( 'my_list', 'tde_shortcode_list' );
}

function maybe_create_my_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'theme_developer_plugin';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

function tde_shortcode_form() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['value'] ) ) {
        insert_data_to_my_table( sanitize_text_field( $_POST['value'] ) );
        wp_redirect( esc_url( remove_query_arg( array_keys( $_GET ) ) ) );
        exit;
    }

    ob_start();
    ?>
    <form method="POST">
        <label for="value">Insert Value:</label>
        <input type="text" name="value" id="value" required>
        <input type="submit" value="Submit">
    </form>
    <?php
    return ob_get_clean();
}

function tde_shortcode_list() {
    $output = '<form method="GET">
        <label for="search">Search:</label>
        <input type="text" name="search" id="search">
        <input type="submit" value="Search">
    </form>';

    $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
    $data = get_my_table_data( $search );

    if ( ! empty( $data ) ) {
        $output .= '<h3>Records</h3>
        <table>
            <thead>
                <tr>
                    <th>Value</th>
                    <th>Date of Creation</th>
                </tr>
            </thead>
            <tbody>';
        foreach ( $data as $item ) {
            $output .= '<tr>
                <td>' . esc_html( $item->name ) . '</td>
                <td>' . esc_html( $item->created_at ) . '</td>
            </tr>';
        }
        $output .= '</tbody>
        </table>';
    } else {
        $output .= 'No items found.';
    }

    return $output;
}

function get_my_table_data( $search = '' ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'theme_developer_plugin';

    $search_query = '%' . $wpdb->esc_like( $search ) . '%';

    $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE name LIKE %s ORDER BY created_at DESC", $search_query );

    return $wpdb->get_results( $sql );
}

function insert_data_to_my_table( $name ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'theme_developer_plugin';

    $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
        ),
        array(
            '%s',
        )
    );
}

require_once plugin_dir_path( __FILE__ ) . 'includes/rest-api.php';
