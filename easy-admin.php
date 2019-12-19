<?php
/*
Plugin Name: Easy Admin
Description: This allows you to simply hide some admin menu items. Super simple.
Version:     1.0.3
Author:      Vincent Dubroeucq
Author URI:  https://vincentdubroeucq.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: easy-admin
Domain Path: /languages
*/

/*
Easy Admin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Easy Admin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Easy Admin. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );



add_action( 'init' , 'easy_admin_load_textdomain' );
/**
 * Load the text domain for the plugin
 */
function easy_admin_load_textdomain(){
	load_plugin_textdomain( 'easy-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}



add_action( 'admin_menu', 'easy_admin_add_menu_page' );
/**
 * Adds a new settings page in the admin area for the Easy Admin settings.
 **/
function easy_admin_add_menu_page(){
    add_menu_page( 
        __( 'Easy Admin Settings', 'easy-admin'), 
        __( 'Easy Admin', 'easy-admin'), 
        'manage_options', 
        'easy-admin', 
        'easy_admin_settings_page_callback', 
        'dashicons-carrot' 
    );
}



/**
 * The Easy Admin page callback function
 **/
function easy_admin_settings_page_callback(){
    ?>
        <div class="wrap">
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <form method="POST" action="options.php">
            <?php 
                settings_fields( 'easy_admin_settings_group' ); 
                do_settings_sections( 'easy-admin' );
                submit_button();
            ?>
            </form>
        </div>    
    <?php
}



add_action( 'admin_init', 'easy_admin_register_settings' );
/**
 * Registers our settings and sections
 **/
function easy_admin_register_settings(){
    
    register_setting( 'easy_admin_settings_group', 'easy_admin_settings' );
    
    add_settings_section( 
        'easy_admin_main_settings', 
        __( 'General Settings', 'easy-admin' ), 
        'easy_admin_main_settings_section_callback', 
        'easy-admin' 
    );

    add_settings_field( 
        'easy_admin_hidden_items', 
        __( 'Select the items to hide.', 'easy-admin' ),
        'easy_admin_hidden_items_setting_callback',
        'easy-admin',
        'easy_admin_main_settings'
    );

}



/**
 * Displays the Easy Admin main settings section.
 * Empty at the moment. Nothing special to display, 
 * except the settings fields handled by easy_admin_hidden_items_setting_callback()
 *
 * @see easy_admin_hidden_items_setting_callback() function
 **/
function easy_admin_main_settings_section_callback(){}



/**
 * Displays the Easy Admin activation field.
 **/
function easy_admin_hidden_items_setting_callback(){
    
    // Get all the menu items to display all the checkboxes.
    $all_menu_items = get_option( 'easy_admin_all_menu_items' );
    $easy_admin_settings = get_option( 'easy_admin_settings' );

    // Loop through them and display a proper checkbox. 
    foreach( $all_menu_items as $slug => $name ){

        $input_name = 'easy_admin_settings[hide-' . $slug . ']';
        $value = isset( $easy_admin_settings['hide-' . $slug] ) ? $easy_admin_settings['hide-' . $slug] : 0;
                
        ?>
            <p>
                <input type="checkbox" id="<?php echo $input_name; ?>" name="<?php echo $input_name; ?>" value="on" <?php checked( $value, 'on' ); ?> />
                <label for="<?php echo $input_name; ?>"><?php echo $name; ?></label>
            </p>
        <?php

    }
}



add_action( 'admin_init', 'easy_admin_get_all_menu_items' );
/**
 * Stores and updates the list of all menu items.
 **/
function easy_admin_get_all_menu_items(){
    if (  is_admin() && isset( $_GET['page'] ) && 'easy-admin' === $_GET['page']  ){
        $all_menu_items = easy_admin_get_menu_items();
        update_option( 'easy_admin_all_menu_items', $all_menu_items );
    }
}



add_action( 'admin_init', 'easy_admin_hide_menu_items', 999 );
/**
 * Hides the unnecessary admin menu pages.
 **/
function easy_admin_hide_menu_items(){
    
    $easy_admin_settings = get_option( 'easy_admin_settings' );
    
    if( empty( $easy_admin_settings ) ){
        return;
    }
    
    // Loop through the settings to get the slug, and remove the corresponding menu item.
    foreach ( $easy_admin_settings as $setting => $value){
        $slug = str_replace( 'hide-', '', $setting );
        remove_menu_page( $slug );
    }

}



/**
 * Get a list of all the admin menu items
 *
 * @return  array   $menu_items   An array of slug => name menu items.
**/
function easy_admin_get_menu_items(){
    
    // Get all the menu items (slug and name)
    global $menu;
    $menu_items = wp_list_pluck( $menu, 0, 2 );

    // Filter the list to remove seperators and Easy Admin itself
    foreach ($menu_items as $slug => $name) {
        if( 'easy-admin' == $slug || ! $name ){
            unset( $menu_items[$slug] );
        }
    }

    return apply_filters( 'easy_admin_menu_items', $menu_items );
    
}

