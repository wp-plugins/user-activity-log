<?php
/* ------------for add usermeta start--------------- */
if (!function_exists('ual_user_activity_table_create')) {
    function ual_user_activity_table_create() {
        global $wpdb;
        $table_name = $wpdb->prefix . "user_activity";
        //table is not created. you may create the table here.
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $create_table_query = "CREATE TABLE $table_name (uactid bigint(20) unsigned NOT NULL auto_increment,post_id int(20) unsigned NOT NULL,post_title varchar(250) NOT NULL,user_id bigint(20) unsigned NOT NULL default '0',user_name varchar(50) NOT NULL,user_role varchar(50) NOT NULL,user_email varchar(50) NOT NULL,ip_address varchar(50) NOT NULL,modified_date datetime NOT NULL default '0000-00-00 00:00:00',object_type varchar(50) NOT NULL default 'post',action varchar(50) NOT NULL,PRIMARY KEY (uactid))";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($create_table_query);
        }
    }
}
add_action('activate_plugin', 'ual_user_activity_table_create');
if (!function_exists('ual_user_activity_add')) {
    function ual_user_activity_add($post_id, $post_title, $obj_type, $current_user_id, $current_user, $user_role, $user_mail, $modified_date, $ip, $action) {
        global $wpdb;
        $table_name = $wpdb->prefix . "user_activity";
        $insert_query = $wpdb->query("INSERT INTO $table_name (post_id,post_title,user_id, user_name, user_role, user_email, ip_address, modified_date, object_type, action) VALUES ('$post_id','$post_title','$current_user_id', '$current_user', '$user_role','$user_mail', '$ip', '$modified_date', '$obj_type', '$action')");
    }
}
if (!function_exists('ual_get_activity_function')) {
    function ual_get_activity_function($action, $obj_type, $post_id, $post_title) {
        $modified_date = current_time('mysql');
        $ip = $_SERVER['REMOTE_ADDR'];
        $current_user_id = get_current_user_id();
        $current_user1 = wp_get_current_user();
        $current_user = $current_user1->user_login;
        $user = new WP_User($current_user_id);
        global $wpdb;
        $table_name = $wpdb->prefix . "users";
        $get_emails = "SELECT * from $table_name where user_login='$current_user'";
        $mails = $wpdb->get_results($get_emails);
        foreach ($mails as $k => $v) {
            $user_mail = $v->user_email;
        }
        if (!empty($user->roles) && is_array($user->roles)) {
            foreach ($user->roles as $role)
                $user_role = $role;
        }
        ual_user_activity_add($post_id, $post_title, $obj_type, $current_user_id, $current_user, $user_role, $user_mail, $modified_date, $ip, $action);
    }
}
if(!function_exists('ual_shook_wp_login')):
function ual_shook_wp_login($user_login) {
    global $wpdb;
    $table_name = $wpdb->prefix . "users";
    $action = "logged in";
    $obj_type = "user";
    $current_user = $user_login;
    $get_uid = "SELECT * from $table_name where user_login='$current_user'";
    $c_uid = $wpdb->get_results($get_uid);
    foreach ($c_uid as $k => $v) {
        $user_idis = $v->ID;
        $user_mail = $v->user_email;
    }
    $current_user_id = $user_idis;
    $user = new WP_User($current_user_id);
    if (!empty($user->roles) && is_array($user->roles)) {
        foreach ($user->roles as $role)
            $user_role = $role;
    }
    $post_id = $current_user_id;
    $post_title = $current_user;
    $modified_date = current_time('mysql');
    $ip = $_SERVER['REMOTE_ADDR'];
    ual_user_activity_add($post_id, $post_title, $obj_type, $current_user_id, $current_user, $user_role, $user_mail, $modified_date, $ip, $action);
}
endif;
if(!function_exists('ual_shook_wp_logout')):
function ual_shook_wp_logout() {
    $action = "logged out";
    $obj_type = "user";
    $post_id = get_current_user_id();
    $user_nm = get_user_by('id', $post_id);
    $post_title = $user_nm->user_login;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_delete_user')):
function ual_shook_delete_user($user) {
    $action = "delete user";
    $obj_type = "user";
    $post_id = $user;
    $user_nm = get_user_by('id', $post_id);
    $post_title = $user_nm->user_login;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_user_register')):
function ual_shook_user_register($user) {
    $action = "user register";
    $obj_type = "user";
    $post_id = $user;
    $user_nm = get_user_by('id', $post_id);
    $post_title = $user_nm->user_login;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_profile_update')):
function ual_shook_profile_update($user) {
    $action = "profile update";
    $obj_type = "user";
    $post_id = $user;
    $user_nm = get_user_by('id', $post_id);
    $post_title = $user_nm->user_login;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_add_attachment')):
function ual_shook_add_attachment($attach) {
    $action = "add attachment";
    $obj_type = "attachment";
    $post_id = $attach;
    $post_title = get_the_title($post_id);
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_edit_attachment')):
function ual_shook_edit_attachment($attach) {
    $post_id = $attach;
    $post_title = get_the_title($post_id);
    $action = "edit attachment";
    $obj_type = "attachment";
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_delete_attachment')):
function ual_shook_delete_attachment($attach) {
    $post_id = $attach;
    $post_title = get_the_title($post_id);
    $action = "delete attachment";
    $obj_type = "attachment";
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_wp_insert_comment')):
function ual_shook_wp_insert_comment($comment) {
    $action = "insert comment";
    $obj_type = "comment";
    $comment_id = $comment;
    $com = get_comment($comment_id);
    $post_id = $com->comment_post_ID;
    $post_title = get_the_title($post_id);
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_edit_comment')):
function ual_shook_edit_comment($comment) {
    $action = "edit comment";
    $obj_type = "comment";
    $comment_id = $comment;
    $com = get_comment($comment_id);
    $post_id = $com->comment_post_ID;
    $post_title = get_the_title($post_id);
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_trash_comment')):
function ual_shook_trash_comment($comment) {
    $action = "trash comment";
    $obj_type = "comment";
    $comment_id = $comment;
    $com = get_comment($comment_id);
    $post_id = $com->comment_post_ID;
    $post_title = get_the_title($post_id);
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_spam_comment')):
function ual_shook_spam_comment($comment) {
    $action = "spam comment";
    $obj_type = "comment";
    $comment_id = $comment;
    $com = get_comment($comment_id);
    $post_id = $com->comment_post_ID;
    $post_title = get_the_title($post_id);
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_unspam_comment')):
function ual_shook_unspam_comment($comment) {
    $action = "unspam comment";
    $obj_type = "comment";
    $comment_id = $comment;
    $com = get_comment($comment_id);
    $post_id = $com->comment_post_ID;
    $post_title = get_the_title($post_id);
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_delete_comment')):
function ual_shook_delete_comment($comment) {
    $action = "delete comment";
    $obj_type = "comment";
    $comment_id = $comment;
    $com = get_comment($comment_id);
    $post_id = $com->comment_post_ID;
    $post_title = get_the_title($post_id);
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_created_term')):
function ual_shook_created_term($term, $tt_id, $taxonomy) {
    $action = "created term";
    $obj_type = "term";
    if ('nav_menu' === $taxonomy)
        return;
    global $wpdb;
    $post_id = $term;
    $tab_nm = $wpdb->prefix . "terms";
    $get_term_name = "SELECT * from $tab_nm where term_id=$post_id";
    $terms_nm = $wpdb->get_results($get_term_name);
    foreach ($terms_nm as $k => $v) {
        $post_title = $v->name;
    }
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_edited_term')):
function ual_shook_edited_term($term, $tt_id, $taxonomy) {
    $action = "edited term";
    $obj_type = "term";
    if ('nav_menu' === $taxonomy)
        return;
    global $wpdb;
    $post_id = $term;
    $tab_nm = $wpdb->prefix . "terms";
    $get_term_name = "SELECT * from $tab_nm where term_id=$post_id";
    $terms_nm = $wpdb->get_results($get_term_name);
    foreach ($terms_nm as $k => $v) {
        $post_title = $v->name;
    }
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_delete_term')):
function ual_shook_delete_term($term_id, $tt_id, $taxonomy_name, $deleted_term = null) {
    if ('nav_menu' === $taxonomy_name)
        return;
    $term = $deleted_term;
    if ($term && !is_wp_error($term)) {
        global $wpdb;
        $action = 'delete term';
        $obj_type = 'Term';
        ual_get_activity_function($action, $obj_type, $term_id, $term->name);
    }
}
endif;
if(!function_exists('ual_shook_wp_update_nav_menu')):
function ual_shook_wp_update_nav_menu($menu) {
    $action = "update nav menu";
    $obj_type = "menu";
    $post_id = $menu;
    $menu_object = wp_get_nav_menu_object($post_id);
    $post_title = $menu_object->name;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_wp_create_nav_menu')):
function ual_shook_wp_create_nav_menu($menu) {
    $action = "create nav menu";
    $obj_type = "menu";
    $post_id = $menu;
    $menu_object = wp_get_nav_menu_object($post_id);
    $post_title = $menu_object->name;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_delete_nav_menu')):
function ual_shook_delete_nav_menu($term, $tt_id, $deleted_term) {
    $action = "delete nav menu";
    $obj_type = "menu";
    $post_id = $tt_id;
    $post_title = $deleted_term->name;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_switch_theme')):
function ual_shook_switch_theme($theme) {
    $action = "switch theme";
    $obj_type = "theme";
    $post_id = "";
    $post_title = $theme;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('shook_delete_site_transient_update_themes')):
function shook_delete_site_transient_update_themes() {
    $action = "delete_site_transient_update_themes";
    $obj_type = "theme";
    $post_id = "";
    $post_title = $theme;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_customize_save')):
function ual_shook_customize_save($theme) {
    $action = "customize save";
    $obj_type = "theme";
    $post_id = "";
    $post_title = "Theme Customizer";
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_activated_plugin')):
function ual_shook_activated_plugin($plugin) {
    $action = "activated plugin";
    $obj_type = "plugin";
    $post_id = "";
    $post_title = $plugin;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_transition_post_status')):
function ual_shook_transition_post_status($new_status, $old_status, $post) {
    $action = '';
        $obj_type = $post->post_type;
        $post_id = $post->ID;
        $post_title = $post->post_title;
        if ('auto-draft' === $old_status && ( 'auto-draft' !== $new_status && 'inherit' !== $new_status )) {
            $action = $obj_type.' created';
        } elseif ('auto-draft' === $new_status || ( 'new' === $old_status && 'inherit' === $new_status )) {
            return;
        } elseif ('trash' === $new_status) {
            $action = $obj_type.' deleted';
        } else {
            $action = $obj_type.' updated';
        }
        ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_deactivated_plugin')):
function ual_shook_deactivated_plugin($plugin) {
    $action = "deactivated plugin";
    $obj_type = "plugin";
    $post_id = "";
    $post_title = $plugin;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('shook_core_updated_successfully')):
function shook_core_updated_successfully() {
    $action = "core updated successfully";
    $obj_type = "update";
    $post_id="";
    $post_title=$obj_type;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_export_wp')):
function ual_shook_export_wp() {
    $action = "export wp";
    $obj_type = "export";
    $post_id="";
    $post_title=$obj_type;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('shook_upgrader_process_complete')):
function shook_upgrader_process_complete() {
    $action = "upgrade process complete";
    $obj_type = "upgrade";
    $post_id="";
    $post_title=$obj_type;
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
if(!function_exists('ual_shook_theme_deleted')):
function ual_shook_theme_deleted() {
    $backtrace_history = debug_backtrace();
    $delete_theme_call = null;
    foreach ($backtrace_history as $call) {
        if (isset($call['function']) && 'delete_theme' === $call['function']) {
            $delete_theme_call = $call;
            break;
        }
    }
    if (empty($delete_theme_call))
        return;
    $name = $delete_theme_call['args'][0];
    $action = 'Theme deleted';
    $obj_type = 'Theme';
    $post_title = $name;
    $post_id="";
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
add_action('wp_login', 'ual_shook_wp_login');
add_action('wp_logout', 'ual_shook_wp_logout');
add_action('delete_user', 'ual_shook_delete_user');
add_action('user_register', 'ual_shook_user_register');
add_action('profile_update', 'ual_shook_profile_update');
add_action('add_attachment', 'ual_shook_add_attachment');
add_action('edit_attachment', 'ual_shook_edit_attachment');
add_action('delete_attachment', 'ual_shook_delete_attachment');
add_action('wp_insert_comment', 'ual_shook_wp_insert_comment');
add_action('edit_comment', 'ual_shook_edit_comment');
add_action('trash_comment', 'ual_shook_trash_comment');
add_action('spam_comment', 'ual_shook_spam_comment');
add_action('unspam_comment', 'ual_shook_unspam_comment');
add_action('delete_comment', 'ual_shook_delete_comment');
add_action('wp_update_nav_menu', 'ual_shook_wp_update_nav_menu');
add_action('wp_create_nav_menu', 'ual_shook_wp_create_nav_menu');
add_action('delete_nav_menu', 'ual_shook_delete_nav_menu', 10, 3);
add_action('activated_plugin', 'ual_shook_activated_plugin');
add_action('deactivated_plugin', 'ual_shook_deactivated_plugin');
add_action('created_term', 'ual_shook_created_term', 10, 3);
add_action('edited_term', 'ual_shook_edited_term', 10, 4);
add_action('delete_term', 'ual_shook_delete_term', 10, 4);
add_action('switch_theme', 'ual_shook_switch_theme');
add_action('customize_save', 'ual_shook_customize_save');
add_action('export_wp', 'ual_shook_export_wp');
add_action('transition_post_status', 'ual_shook_transition_post_status', 10, 3);
add_action('delete_site_transient_update_themes','ual_shook_theme_deleted');
if(!function_exists('ual_shook_wp_login_failed')):
function ual_shook_wp_login_failed($user) {
    $action = "login failed";
    $obj_type = "user";
    $post_id = "";
    $post_title = $user;
    $current_user = $user;
    $modified_date = current_time('mysql');
    $ip = $_SERVER['REMOTE_ADDR'];
    $user=get_user_by('login', $current_user);
    if (!empty($user->roles) && is_array($user->roles)) {
        foreach ($user->roles as $role)
            $user_role = $role;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . "users";
    $get_emails = "SELECT * from $table_name where user_login='$current_user'";
    $mails = $wpdb->get_results($get_emails);
    foreach ($mails as $k => $v) {
        $user_mail = $v->user_email;
    }
    ual_user_activity_add($post_id, $post_title, $obj_type, $current_user_id, $current_user, $user_role, $user_mail, $modified_date, $ip, $action);
}
endif;
if(!function_exists('ual_shook_widget_update_callback')):
function ual_shook_widget_update_callback($widget) {
    $action = "widget updated";
    $obj_type = "widget";
    $post_id = "";
    $post_title = "Sidebar Widget";
    ual_get_activity_function($action, $obj_type, $post_id, $post_title);
}
endif;
add_filter('wp_login_failed', 'ual_shook_wp_login_failed');
add_filter('widget_update_callback', 'ual_shook_widget_update_callback');
function ual_test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}