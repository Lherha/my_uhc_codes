<?php
/**
* Function Name:       delete_column_from_leaderboard_table
* Description:         Function to delete a column named "date" from the "uhc_leaderboard" table in the WordPress database.
* Version:             1.0.0
* Author:              (SGS) Ajao Mueez Bolaji
* Author URI:          [author-website]
* License:             GPL v2 or later
* License URI:         https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:         uhcadoptiontree
* Domain Path:         /languages
*/

// Function to delete a column from the table
function delete_column_from_leaderboard_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'uhc_leaderboard';
    
    // Check if the column exists before attempting to delete it
    $column_name = 'date';
    $column_exists = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
    
    if ($column_exists) {
        // If the column exists, delete it
        $wpdb->query("ALTER TABLE $table_name DROP COLUMN $column_name");
        return "Column '$column_name' deleted successfully.";
    } else {
        // If the column doesn't exist, return a message
        return "Column '$column_name' does not exist in the table.";
    }
}