<?php

/*

Plugin Name: Consultant Assistant

Description: Compare profile tests

Version: 1.0.0

Author: Aram Afsar

Text Domain: psyco-tests

*/

if (!defined ('ABSPATH')) {
	die();
}

function on_register_consultant_plugin() {
	$roles = get_editable_roles();
	foreach ($roles as $role) {
		if ($role = 'consultant') {
			return;
		}
	}
	add_role('consultant', 'مشاور',array('read'=>true, 'level_1'=>true, 'level_0'=>true));
}			

register_activation_hook (__FILE__, 'on_register_consultant_plugin');

function consultant_admin_menu() {

add_menu_page(
	__( 'consultant', 'psyco-tests' ),
	__( 'consultant tools', 'psyco-tests' ),
	'consultant_capability',
	'consultant-assistant-page',
	'my_admin_page_contents',
	'dashicons-star-half',
	4
);
	add_submenu_page(
		'consultant-assistant-page',
		'داشبورد',
		'داشبورد',
		'consultant_capability',
		'my_admin_page',
		'my_admin_page_contents'
	);
	add_submenu_page(
		'consultant-assistant-page',
		'شارژ حساب',
		'شارژ حساب',
		'consultant_capability',
		'buy_ticket',
		'buy_ticket_contents'
	);

	remove_submenu_page ('consultant-assistant-page','consultant-assistant-page');
}
function make_table($array, &$headers, &$rows) {
    foreach( $array as $key => $value) {
        if (is_array($value)) {
            make_table($value, $headers, $rows);
        } else {
			if ($key == '!name' &&  $header_completed == false){
				if ($value == 'TicketingUsage' || $value == 'NewDataSet' ){
					$header_completed = true;
				} else{
					$headers []= $value;
				}
			}
			
			if (in_array ($key, $headers, true))
			{
				$row [] = $value;
			}
			else if ($key == '!diffgr:id') {
				$rows[] = $row;
			}
        }
    }
}

function buy_ticket_contents() {	
	
	if (isset ($_POST["package_ticket_id"]))
	{
		$ticket_id = $_POST["package_ticket_id"];
		$ticket_packages = ["پکیج طلایی","پکیج نقره ای","پکیج برنزی"];
		if (is_numeric ($ticket_id)) {
			echo "<h2>$ticket_packages[$ticket_id] پس از تایید پرداخت به اعتبار شما افزوده خواهد شد.</h2> <br>";
		}
	}
	else {
	?>
	<div id = "packages">
		<div style="background-color: gold;float:right;width:33%;height:300px;text-align:center;"><p>خصوصیات پکیج طلایی</p>
		<form id = "gold" method="post">
			<input type="submit" value="پکیج طلایی" />
			<input type = "hidden" value = "0" name = "package_ticket_id" />
		</form>
		</div>
		<div style="background-color: silver;float:right;width:33%;height:300px;text-align:center;"><p>خصوصیات پکیج نقره ای</p>
		<form id = "silver"  method="post" >
			<input type="submit" value="پکیج نقره ای" />
			<input type = "hidden" value = "1"  name = "package_ticket_id" />
		</form>
		</div>
		<div style="background-color: orange;float:right;width:33%;height:300px;text-align:center;"><p>خصوصیات پکیج برنزی</p>
		<form id = "bronze"  method="post">
			<input type="submit" value="پکیج برنزی" />
			<input type = "hidden" value = "2"  name = "package_ticket_id" />
		</form>
		</div>
	</div>
<?php
	}	
}


function my_admin_page_contents() {	
	$params = array();
	$res = callService ('GetMerchantSummary', $params);
	echo $res;
	
	$params = array();
	$res = callService ('GetMerchantTicketUsage', $params);
	$headers = array ();
	$rows = array ();
	make_table($res, $headers, $rows);
	echo '<div class="TicketingUsage"><table><tr>';
	foreach ($headers as $hd) {
		$hd = __($hd, 'psyco-tests');
		echo "<th>$hd</th>";
	}
	echo '</tr>';
	foreach ($rows as $r) {
		echo "<tr>";
		foreach ($r as $field) {
			$field = __($field, 'psyco-tests');
			echo "<td>$field</td>";
		}
		echo "</tr>";
	}
	echo '</table></div>';
}



function show_users_items(){
	// Define the user ID range
	$user_id_min = 134;
	$user_id_max = 164;

	// Loop through the user IDs
	for ( $user_id = $user_id_min; $user_id <= $user_id_max; $user_id++ ) {
		// Get the last order ID for the user
		$last_order_id = wc_get_customer_last_order( $user_id )->get_id();

		// Get the order object for the last order ID
		$last_order = wc_get_order( $last_order_id );

		// Get the order items for the last order
		$order_items = $last_order->get_items();

		// Print the user ID and the last order ID
		echo "<p>User ID: $user_id, Last Order ID: $last_order_id</p>";

		$values = "insert temp2 (uid, glasser, mbti, neo, baron) values ($user_id,{glasser},{mbti},{neo},{baron});";
		// Loop through the order items
		foreach ( $order_items as $item_id => $item ) {
			// Get the product ID for the order item
			$product_id = $item->get_product_id();

			// Get the product name for the order item
			$product_name = $item->get_name();

			// Print the order item ID, product ID, and product name
			echo "<p>Order Item ID: $item_id, Product ID: $product_id, Product Name: $product_name</p>";
			switch ($product_id) {
				case 45:
					$values = str_replace("{neo}", $item_id, $values);
					break;
				case 46:
					$values = str_replace("{glasser}", $item_id, $values);
					break;
				case 47:
					$values = str_replace("{baron}", $item_id, $values);
					break;
				case 52:
					$values = str_replace("{mbti}", $item_id, $values);
					break;
			}
		}

		// Print a blank line for separation
		echo "<p>$values</p>";
	}
}

function homogenity_page_contents() {
	//if (!current_user_can('consultant') || !is_admin()) {return;}
	//show_users_items();
	echo '
	<h1> مقایسه تست همگنی </h1>
	<p>

	</p>

	  <form action="#" method="post">
		<ol>
			<li>جهت انجام تست همگنی شماره های سفارشات زوج و زوجه را وارد نمایید</li>
			<li> سپس دکمه مشاهده نتیجه تست راکلیک کنید</li>
		</ol>
		<label for="male_order_id" >شماره سفارش زوج:</lable>
		<input type = "text"  id= "male_order_id" name = "male_order_id" value = "327" />
		<label for="female_order_id" >شماره سفارش زوجه:</lable>
		<input type = "text"  id= "female_order_id" name = "female_order_id" value = "268" />
	  <input type="submit" name="submit" value="مشاهده نتیجه همگنی"/>

	</form>';

    if(isset($_POST['submit'])){//to run PHP script on submit
		if (isset ($_POST['male_order_id']) and isset ($_POST['female_order_id'])){
			$male_order_id = $_POST['male_order_id'];
			$female_order_id = $_POST['female_order_id'];
		}
        if(isset ($male_order_id, $female_order_id)) {
			$male_exam_ids = get_completed_exam_ids($male_order_id);
			$female_exam_ids = get_completed_exam_ids($female_order_id);
			if ($male_exam_ids != '' &&  $female_exam_ids != ''){
				echo '<br/>Male Exams IDs:'.$male_exam_ids;
				echo '<br/>Female Exams IDs:'.$female_exam_ids;
				$order = wc_get_order( $male_order_id );
				$male_user_name = $order->get_user();
		
			
				$params = array('exam_id_str_m' => $male_exam_ids, 'exam_id_str_f' => $female_exam_ids);
				$res = callService('GetHomogeneityProfile', $params);

				$order = wc_get_order( $male_order_id );
				$male_user_name = $order->get_user();

				$order = wc_get_order( $female_order_id );
				$female_user_name = $order->get_user();
				$cur_date = date("Y/m/d");
				$res = str_replace (
					array ("{user_name_m}","{user_name_f}", "{order_id_m}", "{order_id_f}", "{current_date}"), 
					array ($male_user_name->display_name, $female_user_name->display_name, $male_order_id, $female_order_id, $cur_date) , $res);

				echo $res;
			}
        }
    }
}

function get_completed_exam_ids ($order_id){
	$completed_exam_ids = [];
	$order = wc_get_order( $order_id );
	
	if ($order !== false)
	{
		$items = $order->get_items();
		foreach ($items as $item_id => $item_data) {
			$item_count = wc_get_order_item_meta($item_id, '_qty', true);

			$test_status = get_exam_action($item_id, 0, $action, $exam_id);
			$completed_exam_ids[] = $exam_id;	
		}
	
	}
	return implode (',', $completed_exam_ids);
}

function register_consultant_assistant_scripts() {
	$jspath = plugins_url ('psyco-tests/js/neo240.js', '');
	wp_register_script ('neo240_js', $jspath);
}

function load_consultant_assistant_scripts( $hook ) {

// Load only on ?page=homogenity_page
	if( !(str_ends_with ($hook,'_page_homogenity_page') or str_ends_with ($hook,'_page_tests_results_page')) ) {
		return;
	}
	wp_enqueue_style( 'psyco_tests_style', plugins_url ('psyco-tests/css/style.css', ''));

// Load style & scripts.
	if (!wp_script_is ('neo240_js', 'enqueued')){
		wp_enqueue_script('highcharts', 'https://code.highcharts.com/highcharts.js', array('jquery'));
		wp_enqueue_script('highcharts-accessibility', 'https://code.highcharts.com/modules/accessibility.js', array('jquery', 'highcharts'));
		wp_enqueue_script('highcharts-more', 'https://code.highcharts.com/highcharts-more.js', array('jquery', 'highcharts'));
		wp_enqueue_script('highcharts-exporting', 'https://code.highcharts.com/modules/exporting.js', array('jquery', 'highcharts'));
		
		
		// Enqueue the custom script with highcharts as a dependency.
		wp_enqueue_script('neo240_js', 'https://choicescale.ir/wp-content/plugins/psyco-tests/js/neo240.js', array('highcharts'));
	}
	if (!wp_script_is ('psyco_tools_js', 'enqueued')){
		wp_enqueue_script('psyco_tools_js', 'https://choicescale.ir/wp-content/plugins/psyco-tests/js/psyco-tools.js');
	}
	if (!wp_script_is ('pdfreport_js', 'enqueued')){
		wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array('jquery'));
		wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array('jquery'));
		wp_script_add_data(
			'html2canvas', // Handle for the script.
			'integrity', // Key.
			'sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==' // Value for the key.
			);
		wp_script_add_data('html2canvas', 'crossorigin', 'anonymous');
		wp_enqueue_script(
			'pdfreport_js',  
			'https://choicescale.ir/wp-content/plugins/psyco-tests/js/pdfreport.js?'.time(), 
			array('jspdf','html2canvas')
		);
	}
}

 function tests_results_page_contents() {
	// check user capabilities
	if (!(current_user_can('consultant') || is_admin())) {return;}
	if (isset($_POST["uid"])) { // uid defined?
		$user_id = $_POST["uid"];
		$user = get_user_by('id',$user_id);
		echo "<p><a href=''>بازگشت به لیست کاربران</a></p>";
		echo "<form target=_self method='post'><input type='submit' value='لیست آزمونهای فرد' /><input type=hidden name='uid' value=$user_id /></form>";
		echo "<h2>$user->display_name</h2>";
		echo "<form method='post'>";
		show_tests ($user_id);
	}
	else { // no uid defined?
	?>
	
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php 
			
			$user_list = get_users();
			if (count($user_list) > 0)
			{
				echo '<input type="text" id="search_box" placeholder="نام، نام خانوادگی یا شماره تلفن را وارد کنید " autofocus>';
				echo '	<label for="organization_filter">یک سازمان را انتخاب کنید:</label>
						<select name="organization" id="organization_filter">
						<option value="">All</option>
						</select>';
				echo '<div id="examinee_list">';
				echo '<table id="examinee_table" style="width:100%"><thead><th style="width:10%;">&nbsp;</th><th>نام و نام خانوادگی</th><th>نام کاربری</th><th>شماره تلفن</th><th>سازمان</th></thead><tbody>';
				$organizations = array();
				foreach ($user_list as $user)
				{
					$user_id =$user->ID;
					$phone_number = get_user_meta( $user_id, 'billing_phone', true ); 
					$organization = get_user_meta ($user_id, 'organization', true);				
					
					if (empty($phone_number)) {
						$phone_number = "&nbsp;";
					}
					if (empty($organization)) {
						$organization = "&nbsp;";
					}
					else {
						$organizations[] = $organization;
					}
					
					$first_name = get_user_meta ($user_id, 'first_name', true);	
					$last_name = get_user_meta ($user_id, 'last_name', true);	
					$user_name = "$first_name $last_name";//
					$user_display_name = $user->display_name;
					echo "<tr><td style='text-align:center;'><form method='post'><input type='submit' value='مشاهده تست ها' /><input type='hidden' name='uid' value=$user_id /></form></td><td>$user_name</td><td>$user_display_name</td>" ;
					echo "<td>$phone_number</td>";
					echo "<td>$organization</td></tr>";
				}
				echo '</tbody></table>';
				echo '</div>';
				$organizations = array_unique ($organizations);
				$items='';

				foreach ($organizations as $organization)
				{
					$items .= "<option value='$organization'>$organization</option>";
				}
				$items = "<option value=''>All</option>".$items; 
				echo "<script>jQuery('#organization_filter').html(\"$items\"); </script>";
				echo "<script>
				jQuery('#organization_filter').change(function() {
					// Get the selected value
					var selected = jQuery(this).val();
					// Loop through the table rows
					jQuery('#examinee_table tbody tr').each(function() {
						// Get the organization name of the row
						var organization = jQuery(this).find('td:last').text();
						// Check if the row matches the selected value
						if (selected == '' || organization == selected) {
							// Show the row
							jQuery(this).show();
						} else {
							// Hide the row
							jQuery(this).hide();
						}
					})
					Zebra();
				});</script>";

				echo '<script>Zebra();</script>';
				echo '<script> set_search_box("search_box", "examinee_list");</script>';
			}
		?>			
			<?php
/*
			// output security fields for the registered setting "wporg_options"
			settings_fields( 'wporg_options' );
			// output setting sections and their fields
			// (sections are registered for "wporg", each field is registered to a specific section)
			do_settings_sections( 'wporg' );
			// output save settings button
			submit_button( __( 'Save Settings', 'textdomain' ) );
*/
			?>
	</div>
	<?php
	}//else no uid defined 
}

function tests_results_submenu(){
	add_submenu_page(
		'consultant-assistant-page',
		'مشاهده نتایج',
		'مشاهده نتایج',
		'consultant_capability',
		'tests_results_page',
		'tests_results_page_contents'
	);
	
}

function homogenity_page(){
	add_submenu_page(
		'consultant-assistant-page',
		'تست همگنی',
		'تست همگنی',
		'consultant_capability',
		'homogenity_page',
		'homogenity_page_contents'
	);
}


add_action( 'wp_loaded', 'register_consultant_assistant_scripts' );
add_action( 'admin_menu', 'consultant_admin_menu' );
add_action('admin_menu', 'homogenity_page');
add_action('admin_menu', 'tests_results_submenu');
add_action( 'admin_enqueue_scripts', 'load_consultant_assistant_scripts' );
