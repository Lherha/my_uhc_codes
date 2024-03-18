<?php

add_shortcode('uhc_referral_cookie', 'uhc_save_cookie');

function uhc_save_cookie() {
    if (isset($_GET['referral-code'])) {
        $referral_code = $_GET['referral-code'];
        ?>
        <script>
            var referral_code = '<?php echo $referral_code; ?>';
            document.cookie = "referral_code=" + referral_code + "; expires= <?php echo gmdate('D, d M Y H:i:s', time() + (86400 * 30)) . ' GMT'; ?>; path=/";
            console.log('this is the code: ' + referral_code);
     
        </script>    
        <?php
        setcookie('uhc_referral_code', $referral_code, time() + (86400 * 30));
        //return 'Referral code cookie set successfully';
    } 
    
}