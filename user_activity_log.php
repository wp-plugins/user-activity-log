<?php
/*
  Plugin Name: User Activity Log
  Plugin URI: http://www.solwininfotech.com/
  Description: log the activity of users and roles
  Version:     1.1
  Date: 08 July 2015
  Author:      Solwin Infotech
  Author URI:  http://solwininfotech.com/
  
  
   
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
	
 */
/*
  Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}
/* define variables */
define('UAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
include(UAL_PLUGIN_DIR . 'user_functions.php');
include(UAL_PLUGIN_DIR . 'user_settings_menu.php');
add_action('init', 'ual_filter_data');
/* function for set the value in header */
if(!function_exists('ual_filter_data')):
function ual_filter_data() {
    
    wp_register_style('ual-style-css', plugins_url('css/style.css', __FILE__));
    wp_enqueue_style('ual-style-css');
    $admin_url = get_admin_url();
    $paged = 1;
    $u_role = $u_name = $o_type = $txtSearch = "";
    // For filtering data
    if (isset($_POST['btn_filter'])) {
        if (isset($_POST['role']) && $_POST['role'] != '0') {
            $u_role = ual_test_input($_POST['role']);
            $where.=" and user_role='$u_role'";
        }
        if (isset($_POST['user']) && $_POST['user'] != '0') {
            $u_name = ual_test_input($_POST['user']);
            $where.=" and user_name='$u_name'";
        }
        if (isset($_POST['post_type']) && $_POST['post_type'] != '0') {
            $o_type = ual_test_input($_POST['post_type']);
            $where.=" and object_type='$o_type'";
        }
        header("Location: $admin_url?page=user_action_log&paged=$paged&userrole=$u_role&username=$u_name&type=$o_type&txtsearch=$txtSearch", true);
        exit();
    }
    if (isset($_POST['btnSearch']) && $_POST['btnSearch']) {
        $txtSearch = ual_test_input($_POST['txtSearchinput']);
        header("Location: $admin_url?page=user_action_log&paged=$paged&userrole=$u_role&username=$u_name&type=$o_type&txtsearch=$txtSearch", true);
        exit();
    }
}
endif;
add_action('admin_menu', 'ual_user_activity');
/* for creating admin side pages */
if(!function_exists('ual_user_activity')):
function ual_user_activity() {
    add_menu_page('User Action Log', 'User Action Log', 7, 'user_action_log', 'ual_user_activity_function','dashicons-admin-users');
    add_submenu_page('user_action_log', 'General Settings', 'Settings', 7, 'general_settings_menu', 'ual_general_settings','dashicons-admin-users');
    $generalpage = add_submenu_page('Notification Settings', 'User Action Log', 'General', 7, 'user_settings_menu', 'ual_user_activity_setting_function','dashicons-admin-users');
    $emailpage = add_submenu_page('Email Settings', 'Email Settings', 'Email', 7, 'email_settings_menu', 'ual_email_settings','dashicons-admin-users');
}
endif;
if(!function_exists('ual_user_activity_function')):
function ual_user_activity_function() {
    global $wpdb;
    $paged = $total_pages = 1;
    $srno = 0;
    $recordperpage = 10;
    $table_name = $wpdb->prefix . "user_activity";
    $where = "where 1=1";
    $u_role = $u_name = $o_type = "";
    if (isset($_GET['paged']))
        $paged = ual_test_input($_GET['paged']);
    $offset = ($paged - 1) * $recordperpage;
    $us_role = $us_name = $ob_type = $searchtxt = "";
    if (isset($_GET['userrole']) && $_GET['userrole'] != "") {
        $us_role = ual_test_input($_GET['userrole']);
        $where.=" and user_role='$us_role'";
    }
    if (isset($_GET['username']) && $_GET['username'] != "") {
        $us_name = ual_test_input($_GET['username']);
        $where.=" and user_name='$us_name'";
    }
    if (isset($_GET['type']) && $_GET['type'] != "") {
        $ob_type = ual_test_input($_GET['type']);
        $where.=" and object_type='$ob_type'";
    }
    if (isset($_GET['txtsearch']) && $_GET['txtsearch'] != "") {
        $searchtxt = ual_test_input($_GET['txtsearch']);
        $where.=" and user_name like '$searchtxt' or user_role like '$searchtxt' or object_type like '$searchtxt' or action like '$searchtxt'";
    }
    // query for display all the user activity data start
    $select_query = "SELECT * from $table_name $where ORDER BY modified_date desc LIMIT $offset,$recordperpage";
    $get_data = $wpdb->get_results($select_query);
    $total_items_query = "SELECT count(*) FROM $table_name $where";
    $total_items = $wpdb->get_var($total_items_query, 0, 0);
    // query for display all the user activity data end
    // for pagination
    $total_pages = ceil($total_items / $recordperpage);
    $next_page = (int) $paged + 1;
    if ($next_page > $total_pages)
        $next_page = $total_pages;
    $prev_page = (int) $paged - 1;
    if ($prev_page < 1)
        $prev_page = 1;
    ?>
    <div class="wrap">
        <h2>User Activities</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING']); ?>">
            <div class="tablenav top">
                <!-- Search Box start -->
                <div class="sol-search-div">
                    <p class="search-box">
                        <label class="screen-reader-text" for="search-input">Search :</label>
                        <input id="user-search-input" type="search" placeholder="User, Role, Action" value="<?php echo $searchtxt; ?>" name="txtSearchinput">
                        <input id="search-submit" class="button" type="submit" value="Search" name="btnSearch">
                    </p>
                </div>
                <!-- Search Box end -->
                <!-- Drop down menu for Role Start -->
                <div class="alignleft actions">
                    <select name="role">
                        <option selected value="0">All Role</option>
                        <?php
                        $role_query = "SELECT distinct user_role from $table_name";
                        $get_roles = $wpdb->get_results($role_query);
                        foreach ($get_roles as $role) {
                            $user_role = $role->user_role;
                            if ($user_role != "") {
                                ?>
                                <option value="<?php echo $user_role; ?>" <?php echo selected($us_role, $user_role); ?>><?php echo ucfirst($user_role); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <!-- Drop down menu for Role end -->
                <!-- Drop down menu for User Start -->
                <div class="alignleft actions">
                    <select name="user" class="sol-dropdown">
                        <option selected value="0">All User</option>
                        <?php
                        $username_query = "SELECT distinct user_name from $table_name";
                        $get_username = $wpdb->get_results($username_query);
                        foreach ($get_username as $username) {
                            $user_name = $username->user_name;
                            if ($user_name != "") {
                                ?>
                                <option value="<?php echo $user_name; ?>" <?php echo selected($us_name, $user_name); ?>><?php echo ucfirst($user_name); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <!-- Drop down menu for User end -->
                <!-- Drop down menu for Post type Start -->
                <div class="alignleft actions">
                    <select name="post_type">
                        <option selected value="0">All Type</option>
                        <?php
                        $object_type_query = "SELECT distinct object_type from $table_name";
                        $get_type = $wpdb->get_results($object_type_query);
                        foreach ($get_type as $type) {
                            $object_type = $type->object_type;
                            if ($object_type != "") {
                                ?>
                                <option value="<?php echo $object_type; ?>" <?php echo selected($ob_type, $object_type); ?>><?php echo ucfirst($object_type); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <!-- Drop down menu for Post type end -->
                <input class="button-secondary action sol-filter-btn" type="submit" value="Filter" name="btn_filter">
                <!-- Top pagination start -->
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_items; ?> items</span>
                    <div class="tablenav-pages" <?php
                    if ((int) $total_pages <= 1) {
                        echo 'style="display:none;"';
                    }
                    ?>>
                        <span class="pagination-links">
                            <a class="first-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_action_log&paged=1&userrole=' . $us_role . '&username=' . $us_name . '&type=' . $ob_type . '&txtsearch=' . $searchtxt; ?>" title="Go to the first page">&laquo;</a>
                            <a class="prev-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_action_log&paged=' . $prev_page . '&userrole=' . $us_role . '&username=' . $us_name . '&type=' . $ob_type . '&txtsearch=' . $searchtxt; ?>" title="Go to the previous page">&lsaquo;</a>
                            <span class="paging-input">
                                <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page"> of
                                <span class="total-pages"><?php echo $total_pages; ?></span>
                            </span>
                            <a class="next-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_action_log&paged=' . $next_page . '&userrole=' . $us_role . '&username=' . $us_name . '&type=' . $ob_type . '&txtsearch=' . $searchtxt; ?>" title="Go to the next page">&rsaquo;</a>
                            <a class="last-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_action_log&paged=' . $total_pages . '&userrole=' . $us_role . '&username=' . $us_name . '&type=' . $ob_type . '&txtsearch=' . $searchtxt; ?>" title="Go to the last page">&raquo;</a>
                        </span>
                    </div>
                </div>
                <!-- Top pagination end -->
            </div>
            <!-- Table for display user action start -->
            <table class="widefat post fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Date</th>
                        <th scope="col">Role</th>
                        <th scope="col">User</th>
                        <th scope="col" class="sol-col-width">Email address</th>
                        <th scope="col">IP</th>
                        <th scope="col">Type</th>
                        <th scope="col">Action</th>
                        <th scope="col" class="sol-col-width">Description</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Date</th>
                        <th scope="col">Role</th>
                        <th scope="col">User</th>
                        <th scope="col" class="sol-col-width">Email address</th>
                        <th scope="col">IP</th>
                        <th scope="col">Type</th>
                        <th scope="col">Action</th>
                        <th scope="col" class="sol-col-width">Description</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    if ($get_data) {
                        $srno = 1 + $offset;
                        foreach ($get_data as $data) {
                            ?>
                            <tr>
                                <td><?php
                                    echo $srno;
                                    $srno++;
                                    ?></td>
                                <td><?php echo $data->modified_date; ?></td>
                                <td><?php echo ucfirst($data->user_role); ?></td>
                                <td><?php echo ucfirst($data->user_name); ?></td>
                                <td><?php echo $data->user_email; ?></td>
                                <td><?php echo $data->ip_address; ?></td>
                                <td><?php echo ucfirst($data->object_type); ?></td>
                                <td><?php echo ucfirst($data->action); ?></td>
                                <?php if (($data->object_type == "post" || $data->object_type == "page") && $data->action != 'post deleted' && $data->action != 'page deleted') { ?>
                                    <td><a href="<?php echo get_permalink($data->post_id); ?>"><?php echo ucfirst($data->post_title); ?></a></td>
                                    <?php
                                } else {
                                    ?><td><?php echo ucfirst($data->post_title); ?></td>
                                        <?php
                                    }
                                    ?>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr class="no-items">';
                        echo '<td class="colspanchange" colspan="4">No record found.</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
            <!-- Table for display user action end -->
            <!-- Bottom pagination start -->
            <div class="tablenav top">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_items; ?> items</span>
                    <div class="tablenav-pages" <?php
                    if ((int) $total_pages <= 1) {
                        echo 'style="display:none;"';
                    }
                    ?>>
                        <span class="pagination-links">
                            <a class="first-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_action_log&paged=1&userrole=' . $us_role . '&username=' . $us_name . '&type=' . $ob_type . '&txtsearch=' . $searchtxt; ?>" title="Go to the first page">&laquo;</a>
                            <a class="prev-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_action_log&paged=' . $prev_page . '&userrole=' . $us_role . '&username=' . $us_name . '&type=' . $ob_type . '&txtsearch=' . $searchtxt; ?>" title="Go to the previous page">&lsaquo;</a>
                            <span class="paging-input">
                                <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page"> of
                                <span class="total-pages"><?php echo $total_pages; ?></span>
                            </span>
                            <a class="next-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_action_log&paged=' . $next_page . '&userrole=' . $us_role . '&username=' . $us_name . '&type=' . $ob_type . '&txtsearch=' . $searchtxt; ?>" title="Go to the next page">&rsaquo;</a>
                            <a class="last-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_action_log&paged=' . $total_pages . '&userrole=' . $us_role . '&username=' . $us_name . '&type=' . $ob_type . '&txtsearch=' . $searchtxt; ?>" title="Go to the last page">&raquo;</a>
                        </span>
                    </div>
                </div>
            </div>
            <!-- Bottom pagination end -->
        </form>
    </div>
    <?php
}
endif;