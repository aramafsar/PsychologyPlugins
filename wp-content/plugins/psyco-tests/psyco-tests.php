<?php
/**
 * Plugin Name:       PsycoTests
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Add Psychological tests as 'Glawser Basic Needs', 'MBTI', ... to your website.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Aram Afsar
 * Author URI:        https://aramafsar.ir/
 */

// Remove some woocommerce items from account menu
/*
function remove_wc_account_menu_items( $items ) {
    
    unset( $items['orders'] );
	unset( $items['edit-address'] );

    // Remove the "Downloads" item
    unset( $items['downloads'] );

    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'remove_wc_account_menu_items', 999 );
*/

include 'psyco-functions.php'  ;


add_action( 'init', 'psycotests_load_textdomain' );
 
function psycotests_load_textdomain() {
  load_plugin_textdomain( 'psyco-tests', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


add_action( 'woocommerce_after_my_account', 'my_custom_account_content' );

function my_custom_account_content() {
	echo ("<h2>با کلیک بر روی لینک آزمونهای من می توانید به آزمونهایتان دسترسی پیدا کنید.</h2>");
} 
// */


function pluginprefix_setup_post_type() {
 /*
  * Part 1. Add Link (Tab) to My Account menu
  */
  add_filter('woocommerce_account_menu_items', 'uw_my_tests_link', PHP_INT_MAX );
/*
  * Part 2. Register Permalink Endpoint
  */
  //add_action('init', 'add_my_tests_endpoint');
	add_my_tests_endpoint();
 /*
  * Part 3. Content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
  */
  add_action('woocommerce_account_my-tests_endpoint', 'uw_my_tests_endpoint_content');
// Clear the permalinks after the post type has been registered.

 
} 
add_action( 'init', 'pluginprefix_setup_post_type' );


/**
 * Activate the plugin.
 */
function pluginprefix_activate() { 
	// Trigger our function that registers the custom post type plugin.
	pluginprefix_setup_post_type(); 
    
}
register_activation_hook( __FILE__, 'pluginprefix_activate' );

/**
 * Deactivation hook.
 */
function pluginprefix_deactivate() {
    remove_action('woocommerce_account_my-tests_endpoint', 'uw_my_tests_endpoint_content');
    remove_action('init', 'add_my_tests_endpoint');
    remove_filter('woocommerce_account_menu_items', 'uw_my_tests_link', PHP_INT_MAX );
    
	// Clear the permalinks to remove our post type's rules from the database.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pluginprefix_deactivate' );
/*
function IsPluginLoaded ($plugin_name)
{
	$list_of_fules_arr = get_included_files();
	$string_to_look_for = $plugin_name;

	foreach ($list_of_fules_arr as $file_path) {
		if (false !== strpos($file_path, $string_to_look_for)) {
			return true;
			
		} 
	}
	return false;
}
*/
if (!class_exists('nusoap_base')) {
	require_once(WP_PLUGIN_DIR . '/psyco-tests/lib/nusoap.php');
}
 
 /* #region  Add Tab To My Account menu */
 function uw_my_tests_link($menu_links)
 {
     $menu_links = array_slice($menu_links, 0, 2, true)
         + array('my-tests' => 'آزمونهای من')
         + array_slice($menu_links, 2, NULL, true);
 
     return $menu_links;
 
 }
 function add_my_tests_endpoint()
 {
     add_rewrite_endpoint('my-tests', EP_ROOT | EP_PAGES);
     flush_rewrite_rules(); 
}
 
 function uw_my_tests_endpoint_content()
 {
 
     // Of course, you can print dynamic content here, one of the most useful functions here is get_current_user_id()
 
     show_tests(get_current_user_id());
     //get_order_details(35);
 }
 // #end region

 function get_user_orders($user_id)
{
	$args = array(
		'customer_id' => $user_id,
		'limit' => -1, // to retrieve _all_ orders by this user
	);
	$orders = wc_get_orders($args);
	return $orders;
}


function convert_to_utf8($value)
{
	return mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value));
}





function show_var_dump($var, $caption)
{
	echo '<pre dir=ltr>' . $caption . ': ';
	var_dump($var);
	echo '</pre>';
}

function show_var_dump_die($var, $caption='مقدار آرایه')
{
	echo '<pre dir=ltr>' . $caption . ': ';
	var_dump($var);
	echo '</pre>';
	wp_die();
}

function soap_operation($host, $port, $url, $action, $vars)
{
	/////////////////////////////////////////////////////////////////////////////
	// simple function to call a soap operation                                //
	//   without the new php5 soap objects                                     //
	// martin kouba - 24.01.2005                                               //
	//   updated for ssl connections - 12.08.2005                              //
	//   updated for multiline results - 04.04.2006                            //    
	// parameters:                                                             //
	//   host .... address of the server that is running the webservice        //
	//   port .... port to access the webservice; http => 80                   //
	//   url ..... url of the webservice; where shall be posted to             //
	//   action .. the soap action; including the namespace                    //
	//   vars .... the arguments of the webservice in an associative array     //
	/////////////////////////////////////////////////////////////////////////////

	// define eol that is used by the server
	// windows webservices seem to need a \r\n
	$eol = "\r\n";

	// extract namespace from soap action
	preg_match('/(https?:\/\/.*\/)(.*)/', $action, $matches) or die("Invalid SOAPAction: '$action'");
	$soap_ns = $matches[1];
	$soap_action = $matches[2];

	// create soap envelope
	// convert to utf8 and get the content length


	$content =
		"<?xml version=\"1.0\" encoding=\"utf-8\"?>" . $eol .
		"<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">" . $eol .
		"  <soap:Body>" . $eol .
		"    <" . $soap_action . " xmlns=\"" . $soap_ns . "\">" . $eol;


	while (list($key, $value) = each($vars)) {
		$content .= "      &lt" . $key . "&gt" . $value . "&lt/" . $key . "&gt" . $eol;
	}
	$content .=
		"    </" . $soap_action . ">" . $eol .
		"  </soap:Body>" . $eol .
		"</soap:Envelope>";
	$content = utf8_encode($content);
	$content_length = strlen($content);


	// create soap header
	// the connection should be closed right after the response
	// since HTTP 1.1 connections are Keep-Alive per default
	// we set "Connection: close"
	$headers =
		"POST " . $url . " HTTP/1.1" . $eol .
		"Host: " . str_replace("ssl://", "", $host) . "" . $eol .
		"Content-Type: text/xml; charset=utf-8" . $eol .
		"Content-Length: " . $content_length . "" . $eol .
		"SOAPAction: \"" . $soap_ns . $soap_action . "\"" . $eol . $eol;

	// debug
	$s = str_replace($headers . $content, "<", "&lt");
	$s = str_replace($s, ">", "&gt");
	echo "<pre dir=ltr>" . $s . "</pre>";
	echo '<pre dir=ltr>$host:     ';
	var_dump($host);
	echo '</pre>';
	echo '<pre dir=ltr>$port:     ';
	var_dump($port);
	echo '</pre>';
	echo '<pre dir=ltr>$soap_ns:  ';
	var_dump($soap_ns);
	echo '</pre>';
	// make connection to server and post header and the soap envelope
	$fp = fsockopen($host, $port, $errno, $errstr, 10);

	// debug
	echo '<pre dir=ltr>$errorno:  ';
	var_dump($errno);
	echo '</pre>';
	echo '<pre dir=ltr>$errstr:   ';
	var_dump($errstr);
	echo '</pre>';

	if (!$fp) {
		return false;
	}
	fputs($fp, $headers);
	fputs($fp, $content);

	// the connection should be closed after the response
	// yet, we want to set a timeout to react before the php script timeout comes in
	stream_set_timeout($fp, 20);

	$data = "";
	$status = socket_get_status($fp);
	while (!feof($fp) && !$status['timed_out']) {
		$data .= fgets($fp, 1024);
		$status = socket_get_status($fp);
	}

	fclose($fp);

	// debug
	echo '<pre dir=ltr>$data:  ';
	var_dump($data);
	echo '</pre>';
	echo '<pre dir=ltr>$soap_action:  ' . $soap_action . '</pre>';

	// extract the soap result
	preg_match('/<' . $soap_action . 'Result>(.*)<\/' . $soap_action . 'Result>/is', $data, $matches) or die("Invalid SOAPResponse: '$data'");
	return $matches[1];
}

function prepare_element()
{
	$name = 'aram';
	$host = "localhost";
	$port = 44377;
	$soap_url = "/PsycoTestsService.asmx";
	$soap_action = "http://tempuri.org/HelloWorld";
	$soap_parameter = array("name" => $name);

	$result = soap_operation($host, $port, $soap_url, $soap_action, $soap_parameter);

	// show result in a textarea
	print("<form><textarea style='width:500;height:300'>");
	print_r($result);
	print("</textarea></form>");

	/*
	$name = 'jentelman';
	$soapCon = new Utils_SoapClient("http://www.clients-domain.com/erpsync/erp_sync.asmx", array());
	$soapCon->setCredentialsHeader(User, Password);
	//$client = new SoapClient();
	// 'https://localhost:44377/PsycoTestsService.asmx?WSDL'
	$params = array(
	'name' => $name
	);
	$results = 'Hi';
	//$results = $client->HelloWorld( $params );
	return $results;
	*/


	/*
	
	$name='abbas booazar!';
	$form = '<form target="_blank" action="https://localhost:44377/PsycoTestsService.asmx/HelloWorld" method="POST">                      
	<table cellspacing="0" cellpadding="4" frame="box" bordercolor="#dcdcdc" rules="none" style="border-collapse: collapse;">
	<tbody><tr>
	<td class="frmHeader" background="#dcdcdc" style="border-right: 2px solid white;">Parameter</td>
	<td class="frmHeader" background="#dcdcdc">Value</td>
	</tr>
	<tr>
	<td class="frmText" style="color: #000000; font-weight: normal;">name:</td>
	<td><input type="hidden" name="name" value = "'.$name.'"></td>
	</tr>
	<tr>
	<td></td>
	<td align="right"> <input type="submit" value="Invoke" class="button"></td>
	</tr>
	</tbody></table>
	
	</form>';
	return $form;
	*/
}
function show_order_items($order_id)
{
	// get and order object
	$order = wc_get_order($order_id);

	// let's decide what order item types we would like to get
	$types = array('line_item', 'fee', 'shipping', 'coupon');
	// defaults to line_item which allows to get products from order

	// iterating through each order item in the order
	foreach ($order->get_items($types) as $item_id => $item) {

		// line_item | fee | shipping | coupon
		$item_type = $item->get_type();

		// WC_Order object
		$item_order = $item->get_order();

		// order ID
		$item_order_id = $item->get_order_id();

		// order item name (product title, name of a shipping method or coupon code)
		$item_name = $item->get_name();

		// product only
		if ($item->is_type('line_item')) {
			$p = $item->get_product();
			echo $p;
			// product quantity
			$item_quantity = $item->get_quantity();

			// product subtotal without discounts
			$item_subtotal = $item->get_subtotal();

			// WC_Product object
			$item_product = $item->get_product();

			// product ID
			$item_product_id = $item->get_product_id();

			// get order item variation ID
			$item_product_variation_id = $item->get_variation_id();

		}

		// products, fees and shipping
		if ($item->is_type(array('line_item', 'fee', 'shipping'))) {
			// order item total
			$item_total = $item->get_total();
		}

	}
}
function get_order_details($order_id)
{

	// 1) Get the Order object
	$order = wc_get_order($order_id);

	// OUTPUT
	echo '<h3>RAW OUTPUT OF THE ORDER OBJECT: </h3>';
	print_r($order);
	echo '<br><br>';
	echo '<h3>THE ORDER OBJECT (Using the object syntax notation):</h3>';
	echo '$order->order_type: ' . $order->order_type . '<br>';
	echo '$order->id: ' . $order->id . '<br>';
	echo '<h4>THE POST OBJECT:</h4>';
	echo '$order->post->ID: ' . $order->post->ID . '<br>';
	echo '$order->post->post_author: ' . $order->post->post_author . '<br>';
	echo '$order->post->post_date: ' . $order->post->post_date . '<br>';
	echo '$order->post->post_date_gmt: ' . $order->post->post_date_gmt . '<br>';
	echo '$order->post->post_content: ' . $order->post->post_content . '<br>';
	echo '$order->post->post_title: ' . $order->post->post_title . '<br>';
	echo '$order->post->post_excerpt: ' . $order->post->post_excerpt . '<br>';
	echo '$order->post->post_status: ' . $order->post->post_status . '<br>';
	echo '$order->post->comment_status: ' . $order->post->comment_status . '<br>';
	echo '$order->post->ping_status: ' . $order->post->ping_status . '<br>';
	echo '$order->post->post_password: ' . $order->post->post_password . '<br>';
	echo '$order->post->post_name: ' . $order->post->post_name . '<br>';
	echo '$order->post->to_ping: ' . $order->post->to_ping . '<br>';
	echo '$order->post->pinged: ' . $order->post->pinged . '<br>';
	echo '$order->post->post_modified: ' . $order->post->post_modified . '<br>';
	echo '$order->post->post_modified_gtm: ' . $order->post->post_modified_gtm . '<br>';
	echo '$order->post->post_content_filtered: ' . $order->post->post_content_filtered . '<br>';
	echo '$order->post->post_parent: ' . $order->post->post_parent . '<br>';
	echo '$order->post->guid: ' . $order->post->guid . '<br>';
	echo '$order->post->menu_order: ' . $order->post->menu_order . '<br>';
	echo '$order->post->post_type: ' . $order->post->post_type . '<br>';
	echo '$order->post->post_mime_type: ' . $order->post->post_mime_type . '<br>';
	echo '$order->post->comment_count: ' . $order->post->comment_count . '<br>';
	echo '$order->post->filter: ' . $order->post->filter . '<br>';
	echo '<h4>THE ORDER OBJECT (again):</h4>';
	echo '$order->order_date: ' . $order->order_date . '<br>';
	echo '$order->modified_date: ' . $order->modified_date . '<br>';
	echo '$order->customer_message: ' . $order->customer_message . '<br>';
	echo '$order->customer_note: ' . $order->customer_note . '<br>';
	echo '$order->post_status: ' . $order->post_status . '<br>';
	echo '$order->prices_include_tax: ' . $order->prices_include_tax . '<br>';
	echo '$order->tax_display_cart: ' . $order->tax_display_cart . '<br>';
	echo '$order->display_totals_ex_tax: ' . $order->display_totals_ex_tax . '<br>';
	echo '$order->display_cart_ex_tax: ' . $order->display_cart_ex_tax . '<br>';
	echo '$order->formatted_billing_address->protected: ' . $order->formatted_billing_address->protected . '<br>';
	echo '$order->formatted_shipping_address->protected: ' . $order->formatted_shipping_address->protected . '<br><br>';
	echo '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - <br><br>';

	// 2) Get the Order meta data
	$order_meta = get_post_meta($order_id);

	echo '<h3>RAW OUTPUT OF THE ORDER META DATA (ARRAY): </h3>';
	print_r($order_meta);
	echo '<br><br>';
	echo '<h3>THE ORDER META DATA (Using the array syntax notation):</h3>';
	echo '$order_meta[_order_key][0]: ' . $order_meta[_order_key][0] . '<br>';
	echo '$order_meta[_order_currency][0]: ' . $order_meta[_order_currency][0] . '<br>';
	echo '$order_meta[_prices_include_tax][0]: ' . $order_meta[_prices_include_tax][0] . '<br>';
	echo '$order_meta[_customer_user][0]: ' . $order_meta[_customer_user][0] . '<br>';
	echo '$order_meta[_billing_first_name][0]: ' . $order_meta[_billing_first_name][0] . '<br><br>';
	echo 'And so on ……… <br><br>';
	echo '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - <br><br>';

	// 3) Get the order items
	$items = $order->get_items();

	echo '<h3>RAW OUTPUT OF THE ORDER ITEMS DATA (ARRAY): </h3>';

	foreach ($items as $item_id => $item_data) {

		echo '<h4>RAW OUTPUT OF THE ORDER ITEM NUMBER: ' . $item_id . '): </h4>';
		print_r($item_data);
		echo '<br><br>';
		echo 'Item ID: ' . $item_id . '<br>';
		echo '$item_data["product_id"] <i>(product ID)</i>: ' . $item_data['product_id'] . '<br>';
		echo '$item_data["name"] <i>(product Name)</i>: ' . $item_data['name'] . '<br>';

		// Using get_order_item_meta() method
		echo 'Item quantity <i>(product quantity)</i>: ' . $order->get_item_meta($item_id, '_qty', true) . '<br><br>';
		echo 'Item line total <i>(product quantity)</i>: ' . $order->get_order_item_meta($item_id, '_line_total', true) . '<br><br>';
		echo 'And so on ……… <br><br>';
		echo '- - - - - - - - - - - - - <br><br>';
	}
	echo '- - - - - - E N D - - - - - <br><br>';
}