<?php
/** 
* Plugin Name:       Database Leaderboard Table Plugin
* Plugin URI:        https://uhcadoptiontree.com
* Description:       This plugin facilitates the management of a leaderboard table within the WordPress environment, allowing for the insertion and retrieval of data via custom functionalities.
* Version:           1.0.0
* Author:            (SGS) Ajao Mueez Bolaji
* Author URI:        [author-website]
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:       uhcadoptiontree
* Domain Path:       /languages
*/

// Function to create the custom table
function create_leaderboard_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uhc_leaderboard';

    $charset_collate = $wpdb->get_charset_collate();

    // SQL query to create the table with "IF NOT EXISTS" condition
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        referral_id VARCHAR(100) NOT NULL,
        ip_address VARCHAR(100) NOT NULL,
        country VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_status VARCHAR(20) NOT NULL,
        order_id INT(20) NOT NULL,
        point SMALLINT NOT NULL,
        date DATETIME NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Include upgrade.php to use dbDelta()
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // Execute the SQL query
    dbDelta( $sql );
}

// Hook the table creation function to plugin activation
//register_activation_hook( __FILE__, 'create_leaderboard_table' );
// register_activation_hook( __FILE__, function(){
// 	global $wpdb;

// 	$table_name = $wpdb->prefix . 'uhc_leaderboard';
// 	$default_value = 'pending';
// 	// Alter table to add created_at and updated_at columns
// 	$query = "ALTER TABLE $table_name 
// 		ADD created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
// 		ADD updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
// 	$wpdb->query($query);
// });


register_activation_hook( __FILE__, function(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'uhc_leaderboard';
	$default_value = 'pending';

	// Alter table to add default value to the payment_status column
	$query = "ALTER TABLE $table_name MODIFY COLUMN payment_status VARCHAR(20) DEFAULT %s";
	$wpdb->query($wpdb->prepare($query, $default_value));

});


// Add custom REST API endpoint
function custom_get_uhc_leaderboard_data_() {
    global $wpdb;
    
    // Table name
    $table_name = $wpdb->prefix . 'uhc_leaderboard';
    
    // Select all data from the table
    $query = "SELECT * FROM $table_name";
    $results = $wpdb->get_results($query, ARRAY_A);

    // Check if results were found
    if ($results) {
        // Return data as JSON
       return wp_send_json($results);
    } else {
        // No data found
        wp_send_json_error('No data found', 404);
    }
}

// Register REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('uhc/v1', '/leaderboard', array(
        'methods' => 'GET',
        'callback' => 'custom_get_uhc_leaderboard_data_',
		'permission_callback' => function(){
			return true;
		}
    ));
});


//set cookie
function set_referral_cookie(){ 
    setcookie('insert_referral_data_executed', 'true', 0, '/');
} 
// Register shortcode
add_shortcode('run_insert_query', 'insert_referral_data_once_per_visit_function');

function insert_referral_data_once_per_visit_function() {
        global $wpdb;
        
        // Table name
        $table_name = $wpdb->prefix . 'uhc_leaderboard';
        // Check if the insert query has already been executed during this visit
        if (!isset($_COOKIE['insert_referral_data_executed'])) {
            // Check if the insert query has already been executed during this visit
            //if (!isset($_COOKIE['uhc_referral_code'])) {
                // Get referral ID from cookies
                $referral_id = $_GET['referral-code']; 
            //} else {
                // If referral ID is not set in cookies, handle accordingly
            //    $referral_id = $_COOKIE['uhc_referral_code']; 
           // }

            // Assigning values directly
            $ip_address = ''; // You can implement logic to get IP address if needed
            $country = ''; // You can implement logic to get country if needed

            // Prepare data to be inserted
            $data = array(
                'referral_id' => $referral_id,
                'ip_address' => $ip_address,
                'country' => $country,
                'created_at' => current_time('mysql', 1)
            );

            // Format for inserting data
            $format = array(
                '%s', // referral_id
                '%s', // ip_address
                '%s', // country
                '%s'  // created_at
            );

            // Insert data into the table
            $inserted = $wpdb->insert($table_name, $data, $format);

            // Set cookie to mark that the insert query has been executed during this visit
            add_action('wp_head', 'set_referral_cookie', 10); 
            // Check if insertion was successful
            if ($inserted) {
                $message = 'Data inserted successfully.';
            } else {
                $message =  'Error inserting data.';
            }
        } else {
            $message =  'Insertion already executed during this visit.';
        }
}



// Hook to execute when an order is completed
add_action('woocommerce_new_order', 'update_points_on_order_completion');

function update_points_on_order_completion($order_id) {
    global $wpdb;

    // Get the order object
    $order = wc_get_order($order_id);

    // Check if order is valid and has a referral code
    if ($order && isset($_COOKIE['referral_code'])) {
        $referral_code = $_COOKIE['referral_code'];

        // Get the user ID associated with the referral code
        $user_meta_table = $wpdb->prefix.'usermeta';
        $user_id = $wpdb->get_var($wpdb->prepare("
            SELECT user_id
            FROM $user_meta_table
            WHERE meta_key = 'uhc_referral_code'
            AND meta_value = %s
        ", $referral_code));

        // If user ID found, update points in the database
        if ($user_id) {
            // Get the current points and amount from the database
            $current_amount = (float) $wpdb->get_var($wpdb->prepare("
                SELECT SUM(amount)
                FROM $wpdb->prefix"."uhc_leaderboard
                WHERE referral_id = %s
            ", $referral_code));

            // Get the order total amount
            $amount = $order->get_total();

            // Update points and amount in the database
            if ($amount > 0) { // Ensure only non-zero amounts are considered
                $wpdb->insert(
                    $wpdb->prefix."uhc_leaderboard",
                    array(
                        'referral_id' => $referral_code,
                        'ip_address' => '', // Add logic to get IP address if needed
                        'country' => '', // Add logic to get country where payment is made
                        'order_id' => $order_id,
                        'amount' => $amount,
                        'payment_status' => 'completed',
                        'point' => 1,
                        'date' => current_time('mysql', 1)
                    ),
                    array('%s', '%s', '%s', '%d', '%f', '%s', '%d', '%s')
                );
            }
        }
    }
}

// Function to display leaderboard data
function display_uhc_leaderboard_function() {
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . 'uhc_leaderboard';

    // SQL query to retrieve data from the table
    $query = "
        SELECT 
            referral_id,
            country,
            SUM(amount) AS cumulative_amount
        FROM 
            $table_name
        GROUP BY 
            referral_id
        HAVING 
            cumulative_amount > 0
        ORDER BY 
            cumulative_amount DESC
        LIMIT 5";

    // Execute the query
    $results = $wpdb->get_results($query);

    // Output the results in table format
    if ($results) {
        $output = '<table>';
        $output .= '<tr><th>Ranking</th><th>Participant Name</th><th>Referral Code</th><th>Country</th><th>Amount</th></tr>';
        $rank = 1;
        foreach ($results as $result) {
            $referral_id = $result->referral_id;
            $country = $result->country;
            
            // Get user ID by referral ID
            $user_id = $wpdb->get_var($wpdb->prepare("
                SELECT user_id
                FROM $wpdb->users
                WHERE ID IN (
                    SELECT user_id
                    FROM $wpdb->usermeta
                    WHERE meta_key = 'uhc_referral_code' AND meta_value = %s
                )
            ", $referral_id));

            // Get user name by user ID
            $user = get_userdata($user_id);
            $name = $user ? $user->display_name : 'N/A';

            // Add medal icons for top three
            $medal = '';
            if ($rank == 1) {
                $medal = '<img src="gold_medal_icon_url" alt="Gold Medal" />';
            } elseif ($rank == 2) {
                $medal = '<img src="silver_medal_icon_url" alt="Silver Medal" />';
            } elseif ($rank == 3) {
                $medal = '<img src="bronze_medal_icon_url" alt="Bronze Medal" />';
            }

            // Format amount with separators
            $formatted_amount = number_format($result->cumulative_amount, 2);

            $output .= '<tr>';
            $output .= '<td>' . $medal . $rank . '</td>';
            $output .= '<td>' . $name . '</td>';
            $output .= '<td>' . $referral_id . '</td>';
            $output .= '<td>' . $country . '</td>';
            $output .= '<td>' . $formatted_amount . '</td>';
            $output .= '</tr>';

            $rank++;
        }
        $output .= '</table>';
    } else {
        $output = 'No data found.';
    }

    return $output;
}

// Register shortcode for displaying leaderboard
add_shortcode('display_uhc_leaderboard', 'display_uhc_leaderboard_function');
