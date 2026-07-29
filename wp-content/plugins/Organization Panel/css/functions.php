<?php

add_action('after_setup_theme', 'replace_parent_theme_features');
function replace_parent_theme_features() {
  // First we remove the parent shortcode
  remove_shortcode('woocommerce_my_account');
  // Then we add our own replacement shortcode
  add_shortcode('woocommerce_my_account', 'my_child_shortcode');
}

//add_action ('woostify_header_account_subbox_start_logged_in', 'show_popup_header');
function show_popup_header ()
{
	if (is_user_logged_in())
	{
		$user = wp_get_current_user();
		
		echo $user->display_name;
	}
}
function findUserByMobileNumber($mobileNumber) {
    // Search for user by mobile number using WordPress functions
    $users = get_users(array(
        'meta_key'     => 'mobile_number',
        'meta_value'   => $mobileNumber,
        'meta_compare' => '='
    ));

    if (!empty($users)) {
        // User found
        return $users[0]->ID; // Return the first user found
    }

    return null; // User not found
}

function getLastUserID() {
	$args = array(
		'role'         => 'author', // authors only
		'orderby'      => 'registered', // registered date
		'order'        => 'DESC', // last registered goes first
		'number'       => 1 // limit to the last one, not required
	);

	$users = get_users( $args );

	$last_user_registered = $users[0]; // the first user from the list

	return $last_user_registered->ID; // return user ID
}



function addPhoneNumberToUser($user_id, $phone_number) {
    // Update user meta with the phone number
    update_user_meta($user_id, 'mobile_number', $phone_number);
    update_user_meta($user_id, 'billing_phone', $phone_number);
}

function VerifyPhoneNo ($user_id, $phone_number){
	$mobile_verification_code =  mt_rand(100000, 999999);
	update_user_meta($user_id, 'mobile_verification_code', $mobile_verification_code);
	$template_name = 'VerifcationCode';
	$response = SendSMS ($phone_number, $template_name, $mobile_verification_code);
}
function SendVerificationCode ($receptor_phone_no, $verification_code)
{
	SendSMS ($receptor_phone_no, "VerificationCode", $verification_code);
}
function SendSMS ($receptor_phone_no, $template_name, $param1){
	$api_key = "Z779phq9rq+XIwN7dsGpeEbM2dFSnjo3uLdmSQ0Kv5A"; // khodemoon api key
    $curl = curl_init();
    curl_setopt_array($curl,
        array(
            CURLOPT_URL => "http://api.ghasedaksms.com/v2/send/verify",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "type=1&receptor=".$receptor_phone_no." &template=".$template_name." &param1=".$param1,
            CURLOPT_HTTPHEADER => array(
                "apikey: ".$api_key,
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
        )));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    }
    return $response;
}
function SendGreetingSMS ($receptor_phone_no, $username)
{
	SendSMS ($receptor_phone_no, "GreetingToEnterWebsite", $username);
}

function createNewUser ($phone, $username){
    $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
    
	if (!validate_username($username)){ //is $username has a valid syntax
		$user_nicename = $username;
		$username = wp_generate_password( $length = 6, $include_standard_special_chars = false );
	}
    if ( username_exists( $username ) ) { // if already exists choose another username
        wc_add_notice ('User already exists! choose another user name.');
        return NULL;
    } else { // is a valid username and 
        $user_id = wp_create_user( $username , $random_password);
        if (is_wp_error($user_id))
        {
            wc_add_notice ($user_id);
            return NULL;
        }
        else {
            $user_id = wp_update_user( array( 'ID' => $user_id, 'display_name' => $username, 'nickname' => $username ) );
            if (is_wp_error($user_id)){
                wc_add_notice('Cannot update user info!');
            }
            else {
				SendGreetingSMS ($phone,  $username);
			}
            return $user_id;
        }
    }
}
function CreateUser($phone, $username) {
	$userID = createNewUser($phone, $username);
	if ($userID)
	{
		addPhoneNumberToUser ($userID, $phone);
	}
	return $userID;
}


function wpse_init_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
// Start session on init hook.
add_action( 'init', 'wpse_init_session' );

// Remove address fields in woocommerce billing page
	
//add_filter( 'cfw_get_billing_checkout_fields', 'remove_checkout_fields', 100 );
add_filter( 'woocommerce_checkout_fields', 'remove_checkout_fields', 100 );

function remove_checkout_fields( $fields ) {
	unset( $fields['billing']['billing_phone'] );
	unset( $fields['billing']['billing_email'] );



	unset( $fields['billing']['billing_company'] );
	unset( $fields['billing']['billing_city'] );
	unset( $fields['billing']['billing_postcode'] );
	unset( $fields['billing']['billing_country'] );
	unset( $fields['billing']['billing_state'] );
	unset( $fields['billing']['billing_address_1'] );
	unset( $fields['billing']['billing_address_2'] );

	unset( $fields['order']['order_comments'] );

	unset( $fields['additional'] );


	return $fields;
}
// Set billing address fields to not required
add_filter( 'woocommerce_checkout_fields', 'unrequire_checkout_fields' );

function unrequire_checkout_fields( $fields ) {
	$fields['billing']['billing_phone']['required']   = false;
	$fields['billing']['billing_email']['required']   = false;

	$fields['billing']['billing_company']['required']   = false;
	$fields['billing']['billing_city']['required']      = false;
	$fields['billing']['billing_postcode']['required']  = false;
	$fields['billing']['billing_country']['required']   = false;
	$fields['billing']['billing_state']['required']     = false;
	$fields['billing']['billing_address_1']['required'] = false;
	$fields['billing']['billing_address_2']['required'] = false;
	return $fields;
}

add_filter ('woocommerce_checkout_fields', 'reorder_checkout_fields', 100);
function reorder_checkout_fields ($fields)
{
  return $fields;
}

add_filter( 'woocommerce_checkout_fields', 'add_checkout_fields', 100 );
function add_checkout_fields ($fields) {
     $fields['billing']['billing_sex'] = array(
         'type' => 'select',
        'label'     => 'جنسیت',
         'placeholder'   => _x('جنسیت فرد در تحلیل نتایج تست ها موثر است', 'placeholder', 'woocommerce'),
        'required'  => true,
        'class'     => array('form-row-wide'),
        'clear'     => true
     );
    $fields['billing']['billing_sex']['options'] = array(
        'option_0'=> '',
		'option_1' => 'مذکر',
        'option_2' => 'مونث',
    );


     return $fields;
}

add_action( 'woocommerce_after_checkout_validation', 'validate_sex', 10, 2);
 
function validate_sex( $fields, $errors ){
   if ( $fields[ 'billing_sex' ]!='option_1' and $fields[ 'billing_sex' ]!='option_2'   ){
        $errors->add( 'validation', 'جنسیت فرد در تحلیل نتایج موثر است لطفا آن را پر کنید' );
    }
}




add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Sex From Checkout Form').':</strong> ' . get_post_meta( $order->get_id(), '_billing_sex', true ) . '</p>';
}

// remove unwanted items from 

add_filter ( 'woocommerce_account_menu_items', 'customize_account_menu_items' );
function customize_account_menu_items( $menu_items ){
	 if ( wp_is_mobile() && is_account_page() && is_wc_endpoint_url()===false) {
		// Run this only for mobile visitors
	unset( $menu_items['dashboard'] ); // Remove Dashboard from My Account Menu
	unset( $menu_items['orders'] ); // Remove Orders from My Account Menu
	unset( $menu_items['downloads'] ); // Remove Downloads from My Account Menu
	unset( $menu_items['edit-account'] ); // Remove Account details from My Account Menu
	unset( $menu_items['payment-methods'] ); // Remove Payment Methods from My Account Menu
	unset( $menu_items['edit-address'] ); // Addresses from My Account Menu
	unset( $menu_items['customer-logout'] ); // Remove Logout link from My Account Menu
	} else {
		//If we are not on mobile, then run this filter
	//unset( $menu_items['dashboard'] ); // Remove Dashboard from My Account Menu
	//unset( $menu_items['orders'] ); // Remove Orders from My Account Menu
	unset( $menu_items['downloads'] ); // Remove Downloads from My Account Menu
	//unset( $menu_items['edit-account'] ); // Remove Account details from My Account Menu
	unset( $menu_items['payment-methods'] ); // Remove Payment Methods from My Account Menu
	unset( $menu_items['edit-address'] ); // Addresses from My Account Menu
	//unset( $menu_items['customer-logout'] ); // Remove Logout link from My Account Menu
	}
return $menu_items;
 
}


add_action ('woostify_site_tool_after_last_item', 'show_loggedin_name');
function show_loggedin_name (){
	if (is_user_logged_in())
	{
		$user = wp_get_current_user();
		
		echo '<div style="display: inline-block;  width: 100%; min-width:100px; text-align:left;" >'.
			 '<a href="https://www.choicescale.ir/my-account/" > سلام '. $user->display_name . '</a>    </div>';
	}	
}

add_action( 'woocommerce_thankyou', 'custom_content_thankyou', 10, 1 );
function custom_content_thankyou( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( isset ($order) ) {
		$state = $order->get_status();

		if ($state == 'completed')
		{
			echo '<p><div><a href = "'.get_permalink( get_option('woocommerce_myaccount_page_id')).'my-tests/">'
				.'<div style="background:blue;text-align:center;position: fixed;bottom: 0;left: 0;right: 0;'
				.' top: unset !important; z-index: 1000;color:white;min-height:3rem;"> آزمونهای من </div></a></p></div>';
		}
	} 
}

/*
function wpse_wc_disable_shop_archive( $post_type_args ) {
    $post_type_args['has_archive'] = false;
    return $post_type_args;
}
add_filter( 'woocommerce_register_post_type_product', 'wpse_wc_disable_shop_archive' );
*/

/**
 * @snippet       Programmatically Complete Paid WooCommerce Orders
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @testedwith    WooCommerce 3.8
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
 
add_filter( 'woocommerce_payment_complete_order_status', 'bbloomer_autocomplete_processing_orders', 9999 );
 
function bbloomer_autocomplete_processing_orders() {
   return 'completed';
}
/*
function wpse_wc_disable_shop_archive( $post_type_args ) {
    $post_type_args['has_archive'] = false;
    return $post_type_args;
}
add_filter( 'woocommerce_register_post_type_product', 'wpse_wc_disable_shop_archive' );
*/
// customize admin footer text
function custom_admin_footer() {
        echo ' ';
} 
add_filter('admin_footer_text', 'custom_admin_footer');

function add_custom_fonts() {
	wp_enqueue_style ('my-styles', WP_CONTENT_URL.'/plugins/psyco-tests/assets/fonts/psyco-tests-fonts', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'add_custom_fonts');
add_action('admin_enqueue_scripts', 'add_custom_fonts');


function organization_styles() {
  echo '<link id="organization-style-css" rel="stylesheet" href="'.WP_CONTENT_URL.'/plugins/Organization%20Panel/css/organization.css" media="all">';
}
add_action('admin_head', 'organization_styles');

?>