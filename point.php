<?php
function display_uhc_leaderboard_function() {
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . 'uhc_leaderboard';
    $user_meta_table = $wpdb->prefix . 'usermeta';
    
    // Check if the meta key exists
    $meta_key_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $user_meta_table WHERE meta_key = %s", 'uhc_referral_code'));

    if (!$meta_key_exists) {
        return 'Meta key "uhc_referral_code" does not exist in the user meta table.';
    }

    // SQL query to retrieve data from the table, calculating cumulative amount and points
    $query = "
        SELECT 
            u.user_id,
            u.display_name AS referral_name,
            u.meta_value AS referral_code,
            SUM(l.amount) AS cumulative_amount,
            SUM(l.point) AS cumulative_points
        FROM 
            $user_meta_table AS u
        INNER JOIN 
            $table_name AS l 
        ON 
            u.meta_value = l.referral_id
        WHERE 
            u.meta_key = 'uhc_referral_code'
        GROUP BY 
            u.user_id
        ORDER BY 
            SUM(l.point) DESC";

    // Execute the query
    $results = $wpdb->get_results($query);

    // Output the results in table format
    if ($results) {
        $output = '<table>';
        $output .= '<tr><th>Referral Name</th><th>Referral Code</th><th>Cumulative Amount</th><th>Cumulative Points</th></tr>';
        foreach ($results as $result) {
            $output .= '<tr>';
            $output .= '<td>' . $result->referral_name . '</td>';
            $output .= '<td>' . $result->referral_code . '</td>';
            $output .= '<td>' . $result->cumulative_amount . '</td>';
            $output .= '<td>' . $result->cumulative_points . '</td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
    } else {
        $output = 'No data found.';
    }

    return $output;
}
