<?php
/*
Plugin Name: WP Admin No Show
Plugin URI: http://www.dougsparling.org
Description: Efectively blocks admin portion of site for selected user roles. Any attempt to manually navigate to wp-admin section of site and user will be redirected to selected site page. Hides admin bar.
Version: 1.1.0
Author: Doug Sparling
Author URI: http://www.dougsparling.org
License: MIT License - http://www.opensource.org/licenses/mit-license.php

Copyright (c) 2012 Doug Sparling
Based on WP Hide Dashboard plugin by Kim Parsell and Admin Bar Disabler plugin by Scott Kingsley Clark

Permission is hereby granted, free of charge, to any person obtaining a copy of this
software and associated documentation files (the "Software"), to deal in the Software
without restriction, including without limitation the rights to use, copy, modify, merge,
publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or
substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/**
 * Redirect users on any wp-admin pages
 */
function wp_admin_no_show_admin_redirect() {
    global $wp_admin_no_show_wp_user_role;
    $disable = false;

    $blacklist_roles = get_option( 'wp_admin_no_show_blacklist_roles', array() );
    if ( false === $disable && !empty( $blacklist_roles ) ) {
        if ( !is_array( $blacklist_roles ) )
            $blacklist_roles = array( $blacklist_roles );
        foreach ( $blacklist_roles as $role ) {
            if (preg_match("/administrator/i", $role )) {
              break;
            } else if ( current_user_can( $role ) ) {
                $disable = true;
            }
        }
    }

    if ( false !== $disable ) {
        $redirect = get_bloginfo('url');

        if( is_admin() ) {
            if ( headers_sent() ) {
                echo '<meta http-equiv="refresh" content="0;url=' . $redirect . '">';
                echo '<script type="text/javascript">document.location.href="' . $redirect . '"</script>';
            } else {
                wp_redirect($redirect);
                exit();
            }
        }

    }
}
add_action( 'admin_head', 'wp_admin_no_show_admin_redirect', 0 );

/**
 * Disable admin bar for users with selected role
 */
function wp_admin_no_show_admin_bar_disable() {
    global $wp_admin_no_show_wp_user_role;
    $disable = false;

    $blacklist_roles = get_option( 'wp_admin_no_show_blacklist_roles', array() );
    if ( false === $disable && !empty( $blacklist_roles ) ) {
        if ( !is_array( $blacklist_roles ) )
            $blacklist_roles = array( $blacklist_roles );
        foreach ( $blacklist_roles as $role ) {
            if ( current_user_can( $role ) ) {
                $disable = true;
            }
        }
    }

    if ( false !== $disable ) {
        add_filter( 'show_admin_bar', '__return_false' );
        remove_action( 'personal_options', '_admin_bar_preferences' );
    }
}
add_action( 'init', 'wp_admin_no_show_admin_bar_disable' );


function wp_admin_no_show_create_menu() {
    add_options_page( __( 'WP Admin No Show', 'wp-admin-no-show' ), __( 'WP Admin No Show', 'wp-admin-no-show' ), 'administrator', __FILE__, 'wp_admin_no_show_settings_page' );
    add_action( 'admin_init', 'wp_admin_no_show_register_settings' );
}
add_action( 'admin_menu', 'wp_admin_no_show_create_menu' );

function wp_admin_no_show_register_settings() {
    register_setting( 'wp-admin-no-show-settings-group', 'wp_admin_no_show_blacklist_roles' );
}

function wp_admin_no_show_settings_page() {
    global $wp_roles;
    if ( !isset( $wp_roles ) )
        $wp_roles = new WP_Roles();
    $roles = $wp_roles->get_names();
    if ( isset( $_GET['settings-updated'] ) ) {
?>
    <div id="message" class="updated"><p><?php _e( 'Options saved', 'wp-admin-no-show' ); ?></p></div>
<?php
    }
?>
<div class="wrap">
    <h2><?php _e( 'WP Admin No Show', 'wp-admin-no-show' ); ?></h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'wp-admin-no-show-settings-group' ); ?>
        <?php do_settings_sections( 'wp-admin-no-show-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'Roles Blacklist', 'wp-admin-no-show' ); ?></th>
                <td>
                    <select name="wp_admin_no_show_blacklist_roles[]" size="10" style="height:auto;" MULTIPLE>
<?php
    $blacklist_roles = get_option( 'wp_admin_no_show_blacklist_roles', array() );
    if ( !is_array( $blacklist_roles ) )
        $blacklist_roles = array( $blacklist_roles );
    foreach ( $roles as $role => $name ) {
?>
                            <option value="<?php echo esc_attr( $role ); ?>"<?php echo ( in_array( $role, $blacklist_roles ) ? ' SELECTED' : '' ); ?>><?php echo $name; ?></option>
<?php
    }
?>
                    </select>
                    <br/><em><?php _e( 'Block wp-admin pages and do not show the Admin Bar for Users with these Role(s)<br />CTRL + Click for multiple selections', 'admin-bar-disabler' ); ?></em>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wp-admin-no-show' ) ?>"/>&nbsp;&nbsp;
        </p>
    </form>
</div>
<?php
}

?>
