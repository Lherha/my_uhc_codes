<?php
/** 
* Plugin Name:       Display database Leaderboard Table Plugin
* Plugin URI:        https://uhcadoptiontree.com
* Description:       This plugin displays all the data in uhc_leaderboard table
* Version:           1.0.0
* Author:            (SGS) Ajao Mueez Bolaji
* Author URI:        [author-website]
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:       uhcadoptiontree
* Domain Path:       /languages
*/


// Function to display all data from the "uhc_leaderboard" table
function display_uhc_leaderboard_all_data() {
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . 'uhc_leaderboard';
    
    // Select all data from the table
    $query = "SELECT * FROM $table_name";
    $results = $wpdb->get_results($query);

    // Output the results in table format
    if ($results) {
        $output = '<table>';
        $output .= '<tr><th>ID</th><th>Referral ID</th><th>Order ID</th><th>Amount</th><th>Point</th></tr>';
        foreach ($results as $result) {
            $output .= '<tr>';
            $output .= '<td>' . $result->ID . '</td>';
            $output .= '<td>' . $result->referral_id . '</td>';
            $output .= '<td>' . $result->order_id . '</td>';
            $output .= '<td>' . $result->amount . '</td>';
            $output .= '<td>' . $result->point . '</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
    } else {
        $output = 'No data found.';
    }

    return $output;
}

// Register shortcode for displaying all data from the "uhc_leaderboard" table
add_shortcode('display_uhc_leaderboard_all_data', 'display_uhc_leaderboard_all_data');
