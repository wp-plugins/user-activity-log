<?php
add_action('init', 'ual_filter_user_role');
if (!function_exists('ual_filter_user_role')):
    function ual_filter_user_role() {
        $paged = 1;
        $admin_url = get_admin_url();
        $display = '';
        if(isset($_POST['user_role'])){
            $display = $_POST['user_role'];
        }
        if (isset($_POST['btn_filter_user_role'])) {
            $display = $_POST['user_role'];
            $header_uri=$admin_url."admin.php?page=user_settings_menu&paged=$paged&display=$display&txtsearch=$search";
            header("Location: ".$header_uri, true);
            exit();
        }
        if (isset($_POST['btnSearch_user_role'])) {
            $search = ual_test_input($_POST['txtSearchinput']);
            $header_uri=$admin_url."admin.php?page=user_settings_menu&paged=$paged&display=$display&txtsearch=$search";
            header("Location: ".$header_uri, true);
            exit();
        }
    }
endif;
/*  users/role page start */
if (!function_exists('ual_user_activity_setting_function')):
    function ual_user_activity_setting_function() {
        global $wpdb;
        $paged = $total_pages = 1;
        $srno = 0;
        $active = $_GET['page'];
        $recordperpage = 10;
        $display = "roles";
        $search = "";
        if (isset($_GET['paged']))
            $paged = $_GET['paged'];
        $offset = ($paged - 1) * $recordperpage;
        $where = "where 1=1";
        if (isset($_GET['display'])) {
            $display = $_GET['display'];
        }
        if (isset($_GET['txtsearch'])) {
            $search = $_GET['txtsearch'];
            if ($search != "") {
                if ($display == "users")
                    $where.=" and user_login like '%$search%' or user_email like '%$search%' or display_name like '%$search%'";
            }
        }
        if (isset($_POST['saveLogin'])) {
            if ($display == "users")
            {
                add_option('enable_user_list');
                $enableuser = $_POST['usersID'];
                update_option('enable_user_list', $enableuser);
            }
            if ($display == "roles")
            {
                $enablerole = $_POST['rolesID'];
                add_option('enable_role_list');
                for ($i = 0; $i < count($enablerole); $i++) {
                $condition = "um.meta_key='" . $wpdb->prefix . "capabilities' and um.meta_value like '%" . $enablerole[$i] . "%' and u.ID = um.user_id";
                $enable_list_user = "SELECT * FROM " . $wpdb->prefix . "usermeta as um, " . $wpdb->prefix . "users as u WHERE $condition";
                $get_user = $wpdb->get_results($enable_list_user);
                    foreach ($get_user as $k => $v) {
                        $enable_user_login[] = $v->user_login;
                    }
                }
                update_option('enable_role_list', $enablerole);
                update_option('enable_user_list', $enable_user_login);
            }
        }
        // query for display all the users data start
        if ($display == "users") {
            $table_name = $wpdb->prefix . "users";
            $select_query = "SELECT * from $table_name $where LIMIT $offset,$recordperpage";
            $get_user_data = $wpdb->get_results($select_query);
            $total_items_query = "SELECT count(*) FROM $table_name $where";
            $total_items = $wpdb->get_var($total_items_query, 0, 0);
        } else {
            $table_name = $wpdb->prefix . "usermeta as um";
            $where.=" and um.meta_key='" . $wpdb->prefix . "capabilities'";
            $select_query = "SELECT distinct um.meta_value from $table_name $where LIMIT $offset,$recordperpage";
            $get_data = $wpdb->get_results($select_query);
            $total_items_query = "SELECT count(distinct um.meta_value) FROM $table_name $where";
            $total_items = $wpdb->get_var($total_items_query, 0, 0);
        }
        // query for display all the users data end
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
            <h2>Notification Settings</h2>
            <div class="tab_parent_parent">
                <div class="tab_parent">
                    <ul>
                        <li><a href="?page=general_settings_menu" class="<?php
                            if ($active == 'general_settings_menu') {
                                echo 'current';
                            }
                            ?>">General</a></li>
                        <li><a href="?page=user_settings_menu" class="<?php
                            if ($active == 'user_settings_menu') {
                                echo 'current';
                            }
                            ?>">Users/Roles</a></li>
                        <li><a href="?page=email_settings_menu" class="<?php
                            if ($active == 'email_settings_menu') {
                                echo 'current';
                            }
                            ?>">Email</a></li>
                    </ul>
                </div>
            </div>
            <form class="sol-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING']); ?>">
                <div class="sol-box-border">
                    <h3 class="sol-header-text">Select Users/Roles</h3>
                    <p>Email will be sent upon login of these selected users/roles.</p>
                    <!-- Search Box start -->
                    <?php if ($display == 'users') {
                        ?>
                        <div class="sol-search-user-div">
                            <p class="search-box">
                                <label class="screen-reader-text" for="search-input">Search :</label>
                                <input id="user-search-input" class="sol-search-user" type="search" title="Search user by username,email,firstname and lastname" width="275px" placeholder="Username, Email, Firstname, Lastname" value="<?php echo $search; ?>" name="txtSearchinput">
                                <input id="search-submit" class="button" type="submit" value="Search" name="btnSearch_user_role">
                            </p>
                        </div>
                    <?php }
                    ?>
                    <!-- Search Box end -->
                    <div class="tablenav top <?php if ($display == 'roles') echo 'sol-display-roles'; ?>">
                        <!-- Drop down menu for user and Role Start -->
                        <div class="alignleft actions sol-dropdown">
                            <select name="user_role">
                                <option selected value="roles">Role</option>
                                <option <?php selected($display, 'users'); ?> value="users">User</option>
                            </select>
                        </div>
                        <!-- Drop down menu for user and Role end -->
                        <input class="button-secondary action sol-filter-btn" type="submit" value="Filter" name="btn_filter_user_role">
                        <!-- top pagination start -->
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <div class="tablenav-pages" <?php
                            if ((int) $total_pages <= 1) {
                                echo 'style="display:none;"';
                            }
                            ?>>
                                <span class="pagination-links">
                                    <a class="first-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=1&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the first page">&laquo;</a>
                                    <a class="prev-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $prev_page . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the previous page">&lsaquo;</a>
                                    <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page"> of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                    </span>
                                    <a class="next-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $next_page . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the next page">&rsaquo;</a>
                                    <a class="last-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $total_pages . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the last page">&raquo;</a>
                                </span>
                            </div>
                        </div>
                        <!-- top pagination end -->
                    </div>
                    <!-- display users details start -->
                    <table class="widefat post fixed" cellspacing="0" style="
                    <?php
                    if ($display == "users") {
                        echo 'display:table';
                    }
                    if ($display == "roles") {
                        echo 'display:none';
                    }
                    ?>">
                        <thead>
                            <tr>
                                <th scope="col" class="check-column"><input type="checkbox" /></th>
                                <th width="50px" scope="col">No.</th>
                                <th scope="col">User</th>
                                <th scope="col">First name</th>
                                <th scope="col">Last name</th>
                                <th scope="col">Role</th>
                                <th scope="col">Email address</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th scope="col" class="check-column"><input type="checkbox" /></th>
                                <th scope="col">No.</th>
                                <th scope="col">User</th>
                                <th scope="col">First name</th>
                                <th scope="col">Last name</th>
                                <th scope="col">Role</th>
                                <th scope="col">Email address</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php
                            if ($get_user_data) {
                                $srno = 1 + $offset;
                                foreach ($get_user_data as $data) {
                                    $u_d = get_userdata($data->ID);
                                    $first_name = $u_d->user_firstname;
                                    $last_name = $u_d->user_lastname;
                                    ?>
                                    <tr>
                                        <?php
                                        $user_enable = get_option('enable_user_list');
                                        $checked = '';
                                        if($user_enable != ""):
                                        if (in_array($data->user_login, $user_enable)) {
                                            $checked = "checked=checked";
                                        }
                                        endif;
                                        ?>
                                        <th scope="row" class="check-column"><input type="checkbox" <?php echo $checked; ?> name="usersID[]" value="<?php echo $data->user_login; ?>" /></th>
                                        <td><?php
                                            echo $srno;
                                            $srno++;
                                            ?>
                                        </td>
                                        <td><?php echo ucfirst($data->user_login); ?></td>
                                        <td><?php echo ucfirst($first_name); ?></td>
                                        <td><?php echo ucfirst($last_name); ?></td>
                                        <td><?php
                                            $user = new WP_User($data->ID);
                                            if (!empty($user->roles) && is_array($user->roles)) {
                                                foreach ($user->roles as $role)
                                                    echo ucfirst($role);
                                            }
                                            ?></td>
                                        <td><?php echo $data->user_email; ?></td>
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
                    <!-- display users details end -->
                    <!-- display roles details start -->
                    <table class="widefat post fixed sol-display-roles" cellspacing="0" style="
                    <?php
                    if ($display == "users") {
                        echo 'display:none';
                    }
                    if ($display == "roles") {
                        echo 'display:table';
                    }
                    ?>">
                        <thead>
                            <tr>
                                <th scope="col" class="check-column"><input type="checkbox" /></th>
                                <th scope="col">No.</th>
                                <th scope="col">Role</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th scope="col" class="check-column"><input type="checkbox" /></th>
                                <th scope="col">No.</th>
                                <th scope="col">Role</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php
                            if ($get_data) {
                                $srno = 1 + $offset;
                                foreach ($get_data as $data) {
                                    $final_roles = unserialize($data->meta_value);
                                    $final_roles = key($final_roles);
                                    ?>
                                    <tr>
                                        <?php
                                        $role_enable = get_option('enable_role_list');
                                        $checked = '';
                                        if($role_enable != ""):
                                        if (in_array($final_roles, $role_enable)) {
                                            $checked = "checked=checked";
                                        }
                                        endif;
                                        ?>
                                        <th scope="row" class="check-column"><input type="checkbox" <?php echo $checked; ?> name="rolesID[]" value="<?php echo $final_roles; ?>" /></th>
                                        <td><?php
                                            echo $srno;
                                            $srno++;
                                            ?></td>
                                        <td><?php echo ucfirst($final_roles); ?></td>
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
                    <!-- display roles details end -->
                    <!-- bottom pagination start -->
                    <div class="tablenav top <?php if ($display == 'roles') echo 'sol-display-roles'; ?>">
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo $total_items; ?> items</span>
                            <div class="tablenav-pages" <?php
                            if ((int) $total_pages <= 1) {
                                echo 'style="display:none;"';
                            }
                            ?>>
                                <span class="pagination-links">
                                    <a class="first-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=1&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the first page">&laquo;</a>
                                    <a class="prev-page <?php if ($paged == '1') echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $prev_page . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the previous page">&lsaquo;</a>
                                    <span class="paging-input">
                                        <input class="current-page" type="text" size="1" value="<?php echo $paged; ?>" name="paged" title="Current page"> of
                                        <span class="total-pages"><?php echo $total_pages; ?></span>
                                    </span>
                                    <a class="next-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $next_page . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the next page">&rsaquo;</a>
                                    <a class="last-page <?php if ($paged == $total_pages) echo 'disabled'; ?>" href="<?php echo '?page=user_settings_menu&paged=' . $total_pages . '&display=' . $display . '&txtsearch=' . $search; ?>" title="Go to the last page">&raquo;</a>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- bottom pagination end -->
                    <p class="submit">
                        <input id="submit" class="button button-primary" type="submit" value="Save Changes" name="saveLogin">
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
endif;
/* Display users/role start */
/* Email setting start */
if (!function_exists('ual_email_settings')):
    function ual_email_settings() {
        $active = $_GET['page'];
        $msg = "";
        add_option('enable_email');
        add_option('to_email');
        add_option('from_email');
        add_option('email_message');
        global $current_user; 
        get_currentuserinfo();
        $mail_to = $current_user->user_email;
        $mail_from = get_option('admin_email');
        $user_details="[user_details]";
        $mail_msg = "Hi, following user is logged in your site \n$user_details";
        if (isset($_POST['btnsolEmail'])) {
            $to_email = $_POST['sol-mail-to'];
            $from_email = $_POST['sol-mail-from'];
            $mail_msg = ual_test_input($_POST['sol-mail-msg']);
            $emailEnable = $_POST['emailEnable'];
            update_option('enable_email',$emailEnable);
            if (isset($_POST['emailEnable'])) {
                if ($_POST['emailEnable'] == '1') {
                    if ($mail_msg == "") {
                        $msg = "Please enter message";
                    }
                    if ($to_email == "" || $from_email == "") {
                        $msg = "Please enter the email address";
                    }
                    if (!filter_var($to_email, FILTER_VALIDATE_EMAIL) || !filter_var($from_email, FILTER_VALIDATE_EMAIL) || !is_email($to_email) || !is_email($from_email)) {
                        $msg = "Please enter valid email address";
                    }
                    else
                    {
                        update_option('to_email', $to_email);
                        update_option('from_email', $from_email);
                        update_option('email_message', $mail_msg);
                    }
                }
            }
        }
        ?>
        <div class="wrap">
            <h2>Email Settings</h2>
            <?php
            if ($msg != "") {
                ?>
                <div id="message" class="updated notice notice-success is-dismissible below-h2 error">
                    <p><?php echo $msg; ?></p>
                </div>
            <?php }
            ?>
            <div class="tab_parent_parent">
                <div class="tab_parent">
                    <ul>
                        <li><a href="?page=general_settings_menu" class="<?php
                            if ($active == 'general_settings_menu') {
                                echo 'current';
                            }
                            ?>">General</a></li>
                        <li><a href="?page=user_settings_menu" class="<?php
                            if ($active == 'user_settings_menu') {
                                echo 'current';
                            }
                            ?>">Users/Roles</a></li>
                        <li><a href="?page=email_settings_menu" class="<?php
                            if ($active == 'email_settings_menu') {
                                echo 'current';
                            }
                            ?>">Email</a></li>
                    </ul>
                </div>
            </div>
            <form method="POST" class="sol-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING']); ?>">
                <div class="sol-box-border">
                    <h3 class="sol-header-text">Email</h3>
                    <p class="margin_bottom_30">This email will be sent upon login of selected users/roles.</p>
                    <table class="sol-email-table" cellspacing="0">
                        <tr>
                            <th>Enable?</th>
                            <td>
                                <input type="radio" value="1" name="emailEnable">Yes
                                <input type="radio" checked="" value="0" name="emailEnable">No
                            </td>
                        </tr>
                        <tr>
                            <th>From Email</th>
                            <td>
                                <input type="email" name="sol-mail-from" value="<?php echo $mail_from; ?>">
                                <p class="description">The source Email address</p>
                            </td>
                        </tr>
                        <tr>
                            <th>To Email</th>
                            <td>
                                <input type="email" name="sol-mail-to" value="<?php echo $mail_to; ?>">
                                <p class="description">The Email address notifications will be sent to</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Message</th>
                            <td>
                                <textarea cols="50" name="sol-mail-msg" rows="5"><?php echo $mail_msg; ?></textarea>
                                <p class="description">Customize the message as per your requirement</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input class="button button-primary" type="submit" value="Save Changes" name="btnsolEmail">
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
endif;
/* Email setting end */
add_action('wp_login','ual_send_email');
function ual_send_email()
{  
    $current_user1 = wp_get_current_user();
    $current_user = $current_user1->user_login;
    $enable_unm = get_option('enable_user_list');
    for ($i = 0; $i < count($enable_unm); $i++) 
    {
        if ($enable_unm[$i] == $current_user)
        {
            $to_email = get_option('to_email');
            $from_email = get_option('from_email');
            $ip = $_SERVER['REMOTE_ADDR'];
            $user_firstnm=ucfirst($current_user1->user_firstname);
            $user_lastnm=ucfirst($current_user1->user_lastname);
            $user_email=$current_user1->user_email;
            $user_reg=$current_user1->user_registered;
            $current_user=ucfirst($current_user);
            $user_details="<table cellspacing='0' border='1px solid #ccc' class='sol-msg' style='margin-top:30px'>
                                <tr>
                                    <td style='padding:5px 10px;'>Username</td>
                                    <td style='padding:5px 10px;'>Firstname</td>
                                    <td style='padding:5px 10px;'>Lastname</td>
                                    <td style='padding:5px 10px;'>Email</td>
                                    <td style='padding:5px 10px;'>Date Time</td>
                                    <td style='padding:5px 10px;'>IP address</td>
                                </tr>
                                <tr>
                                    <td style='padding:5px 10px;'>$current_user</td>
                                    <td style='padding:5px 10px;'>$user_firstnm</td>
                                    <td style='padding:5px 10px;'>$user_lastnm</td>
                                    <td style='padding:5px 10px;'>$user_email</td>
                                    <td style='padding:5px 10px;'>$user_reg</td>
                                    <td style='padding:5px 10px;'>$ip</td>
                                </tr>
                            </table>";
            $mail_msg = "Hi, following user is logged in your site \n \n$user_details";
            if ($to_email != "" && $mail_msg != "" && $from_email !="") 
            {
                
                $headers = "From: " . strip_tags($from_email) . "\r\n";
                $headers .= "Reply-To: ". strip_tags($from_email) . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                wp_mail($to_email, "User Login Notification", $mail_msg, $headers);
            }
        }
    }
}
add_action('user_register', 'ual_enable_user_notification_at_login');
function ual_enable_user_notification_at_login($user_id) {
    $user_info = get_userdata($user_id);
    print_r($user_info);
    $user_role = $user_info->roles[0];
    $user_role_enable = get_option('enable_role_list');
    $user_enabled = get_option('enable_user_list');
    for ($i = 0; $i < count($user_role_enable); $i++) {
        if ($user_role_enable[$i] == $user_role) {
            array_push($user_enabled, $user_info->user_login);
            update_option('enable_user_list', $user_enabled);
        }
    }
}
/* General setting start */
if (!function_exists('ual_general_settings')):
    function ual_general_settings() {
        $active = $_GET['page'];
        global $wpdb;
        $table_nm = $wpdb->prefix . "user_activity";
        if (isset($_GET['db'])) {
            $wpdb->query('TRUNCATE ' . $table_nm);
        }
        if (isset($_POST['submit_display'])) {
            $time_ago = $_POST['logdel'];
            $wpdb->query("DELETE FROM wp_user_activity WHERE modified_date < NOW() - INTERVAL $time_ago DAY");
        }
        ?>
        <div class="wrap">
            <h2>General Settings</h2>
            <div class="tab_parent_parent">
                <div class="tab_parent">
                    <ul>
                        <li><a href="?page=general_settings_menu" class="<?php
                            if ($active == 'general_settings_menu') {
                                echo 'current';
                            }
                            ?>">General</a></li>
                        <li><a href="?page=user_settings_menu" class="<?php
                            if ($active == 'user_settings_menu') {
                                echo 'current';
                            }
                            ?>">Users/Roles</a></li>
                        <li><a href="?page=email_settings_menu" class="<?php
                            if ($active == 'email_settings_menu') {
                                echo 'current';
                            }
                            ?>">Email</a></li>
                    </ul>
                </div>
            </div>
            <form class="sol-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING']); ?>" method="POST" name="general_setting_form">
                <div class="sol-box-border">
                    <h3 class="sol-header-text">Display Option</h3>
                    <p class="margin_bottom_30">There are some basic options for display User Action Log</p>
                    <table class="sol-email-table">
                        <tr>
                            <th>Keep logs for</th>
                            <td>
                                <input type="number" step="1" min="1" value="30" name="logdel">
                                <p>Maximum number of days to keep activity log. Leave blank to keep activity log forever (not recommended).</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Delete Log Activities</th>
                            <td>
                                <a href="?page=general_settings_menu&db=reset" onClick="return confirm('Are you sure want to Reset Database?');">Reset Database</a>
                                <p>Warning: Clicking this will delete all activities from the database.</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit_display">
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
endif;
/*General setting end*/