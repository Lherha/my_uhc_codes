<?php
/**
 * Plugin Name:       UHC Custom QR
 * Plugin URI:        https://uhcadoptiontree.com
 * Description:       Generates unique QR codes for your referral and tracks id.
 * Version:           1.0.1
 * Author:            (SGS) Ajao Mueez Bolaji
 * Author URI:        [author-website]
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       uhcadoptiontree
 * Domain Path:       /languages
 */

 function create_uhc_leaderboard_table() {
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
register_activation_hook( __FILE__, 'create_uhc_leaderboard_table' );






add_action('wp_head', function(){
	?>
	<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css'>
	<?php
});

function generate_random_referral_code($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    $max_index = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, $max_index)];
    }
    return $code;
}

function generate_unique_referral_code() {
    $code_length = rand(5, 10);
    $code = generate_random_referral_code($code_length);
    while (!is_uhc_referral_code_unique($code)) {
        $code = generate_random_referral_code($code_length);
    }
    return $code;
}

function is_uhc_referral_code_unique($code) {
    // Get all users with the user meta 'uhc_referral_code'
    $users = get_users(array(
        'meta_key' => 'uhc_referral_code',
        'meta_value' => $code,
    ));

    // If any users with the same referral code are found, return false
    if (!empty($users)) {
        return false;
    }

    // If no users with the same referral code are found, return true
    return true;
}

add_shortcode('printQR', 'uhc_referQR');
function uhc_referQR(){ 
	global $product;
	global $post;
	$author_id = $post->post_author;
	$user_id = get_current_user_id();
	$referral_code = get_user_meta($user_id, 'uhc_referral_code', true);
	if(empty($referral_code)){
		$referral_code = generate_unique_referral_code();
		update_user_meta($user_id, 'uhc_referral_code', $referral_code);
	}
	
    //if( ! $order_id ) return;
    $product_target_url= get_permalink( 279 ).'?refferal-code='. $referral_code;
	  //$target_url = wcfmmp_get_store_url( $author_id );
    $url= 'https://quickchart.io/qr?text='.urlencode($product_target_url).'&ecLevel=Q&size=250&format=svg'; 
    //$params = urlencode($getparameter); 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    //using the setopt function to send request to the url 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    //response returned but stored not displayed in browser 
    curl_setopt($ch, CURLOPT_TIMEOUT, 1000); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $result = curl_exec($ch); 
    $err = curl_error($ch); 
    curl_close ($ch);
    if (!$err) { 
        $qrCodeContent .=  '<div id = "printQR" style="margin-top:1em">'. 
			$response.
			$result.
		'</div>';
		echo $qrCodeContent;
    }else{
		echo $err;
	}
}  

//Include the cookie saver file
include "cookie_saver.php";