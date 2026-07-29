<?php

/*

Plugin Name: Group assignment of products to users

Description: Adds a custom admin pages with sample styles and scripts.

Version: 1.0.0

Author: Aram Afsar

Text Domain: group_assignment_admin-page

*/



function group_assignment_admin_menu() {

add_menu_page(

__( 'تخصیص گروهی آزمونها', 'my-textdomain' ),

__( 'تخصیص گروهی آزمونها', 'my-textdomain' ),

'pscotests_group_assignment',

'group_assignment-page',

'group_assignment_page_contents',

'dashicons-schedule',

4

);

}

function update_general_user_organization() {
	// Get all the users
	$users = get_users();

	// Loop through the users
	foreach ($users as $user) {
		// Get the user ID
		$user_id = $user->ID;

		// Get the organization meta value
		$organization = get_user_meta($user_id, 'organization', true);

		// Check if the organization meta value is empty
		if (empty($organization) || $organization == ' ') {
			// Set the organization meta value to a blank value as general user
			update_user_meta($user_id, 'organization', 'کاربر عمومی');
		}
	}
}

add_action( 'admin_menu', 'group_assignment_admin_menu' );

function group_assignment_page_contents() {

?>

<h1>

<?php esc_html_e( 'تخصیص آزمونها به کاربران', 'my-plugin-textdomain' ); ?>

</h1>

<?php
	update_general_user_organization();
	$args = array(
				'orderby'=> array ('organization'=>'ASC', 'display_name'=>'ASC')
				,'meta_key'=>'organization',
				'order'=> 'ASC');
    $user_list = get_users($args);

	$query = new WC_Product_Query();
	$query->set( 'sku', 'PSYCHOTESTS_' );
	$query->set('orderby', 'id');
	$query->set ('order', 'DESC');
	$query->set ('category', 'packages');
	$query->set ('limit', -1); // if not set it shows only 10 result !! SAG TOO ROOHESHOON!
	
    $package_list = $query->get_products();
	$query->set ('category', 'psychology_test');
	
	$tests_list = $query->get_products();

//   $package_list = wc_get_products(array('orderby'=>'date');//!!!(array ('sku'=>'PSYCHOTESTS_'));
?>
  <form action="#" method="post">
    <ol>
        <li>ابتدا پکیج یا آزمونهایی را که می خواهید به کاربران اختصاص دهید انتخاب نمایید  </li>
        <li>کاربرانی که می خواهید آزمون از آنها گرفته شود را انتخاب کنید  </li>
        <li>دکمه تخصیص گروهی را فشار دهید  </li>
    </ol>
  <input type="submit" name="submit" value="تخصیص گروهی"/>
  <br>
  <div id="packages_checkboxes" style="width:30%;float:right">
  <h2>محصولات</h2>
  <ul>
  <?php 
	echo '<ul><li><input type="checkbox">آزمونها و پیکجها</li>';
	echo '<ul style="padding-right:30px"><li><input type="checkbox">پکیج ها</li><ul style="padding-right:30px">';
    foreach ($package_list as $package)
    {
        echo '<li><input type="radio" name = "package_check_list[]" value = '.esc_html( $package->get_id() ).'>'.esc_html( $package->get_name() ).'</li>';
    }
	echo '</ul></ul>';

	echo '<ul style="padding-right:30px"><li><input type="checkbox">آزمونها</li><ul style="padding-right:30px">';
    foreach ($tests_list as $package)
    {
        echo '<li><input type="checkbox" name = "package_check_list[]" value = '.esc_html( $package->get_id() ).'>'.esc_html( $package->get_name() ).'</li>';
    }
	echo '</ul></ul></ul>';
  ?>
  </ul>
  </div>
  
  <div id="user_checkboxes" style="width:30%;float:right">
  <h2>کاربران</h2>
  <ul>
  <?php
	  if (count($user_list)>0)
	  {
		  echo "<li><input type='checkbox' value = '0'>کاربران سایت</li><ul style='padding-right:30px'>";
		  $last_organization = "";
		  foreach ($user_list as $user)
		  {
			$user_id =$user->ID;
			$phone_number = get_user_meta( $user_id, 'billing_phone', true ); 
			$organization = get_user_meta ($user_id, 'organization', true);
			
			if ($organization==""){
				
				$organization = "عمومی";
			}
			if ($organization != $last_organization)
			{
				if ($last_organization != ""){
					echo '</ul>';
				}
				$last_organization = $organization;
				echo "<li><input type='checkbox' value = '0'>$organization</li><ul style='padding-right:30px'>";
			}
			echo '<li><input type="checkbox" name = "user_check_list[]" value = '.esc_html( $user->ID ).'>'.esc_html( $user->display_name).'</li>';

		  }
		  echo '</ul></ul></ul>';
	}
  ?>
  </ul>
</div>



</form>
<script>
	set_tree_checkbox_click_handler();
</script>
<?php
    if(isset($_POST['submit'])){//to run PHP script on submit
        if(!empty($_POST['user_check_list']) && !empty($_POST['package_check_list'])){
			echo '<div class="test_assined_result" style="width:30%;float:right">';
            foreach ($_POST['user_check_list'] as $user_id)
            {
                CreateOrder ($user_id, $_POST['package_check_list']);
				if (function_exists ('SendSMS'))
				{
					$user = get_user_by( 'id', $user_id );
					$phone_number = get_user_meta( $user_id, 'billing_phone', true ); 
					if (isset($phone_number))
					{
						$template_name = 'GreetingToEnterWebsite';
						$response = SendSMS ($phone_number, $template_name, $user->display_name);
					}
				}
            }
			echo '</div>';

        }
    }
}
function CreateOrder ($user_id, $products)
{
    $user = get_user_by( 'id', $user_id );
   

    $order = wc_create_order(array ('customer_id'=>$user_id));
    
    // add products
    foreach ($products as $product_id)
    {
        $product = wc_get_product( $product_id );
        $children = $product->get_children();
        if ($children != null)
        {
            foreach ($children as $child)
            {
                $order->add_product( wc_get_product ($child), 1 );
            }
        }
        else
        {
            $order->add_product($product);
        }
    }
    
    // add shipping
    $shipping = new WC_Order_Item_Shipping();
    $shipping->set_method_title( 'Free shipping' );
    $shipping->set_method_id( 'free_shipping:1' ); // set an existing Shipping method ID
    $shipping->set_total( 0 ); // optional
    $order->add_item( $shipping );
    
    // add billing and shipping addresses
    $address = array(
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'company'    => $user->company,
        'email'      => $user->email,
        'phone'      => $user->phone,
        'address_1'  => $user->address_1,
        'address_2'  => $user->address_2,
        'city'       => $user->city,
        'state'      => '',
        'postcode'   => $user->postcode,
        'country'    => $user->country
    );
    
    $order->set_address( $address, 'billing' );
    $order->set_address( $address, 'shipping' );
    
    // add payment method
    $order->set_payment_method( 'stripe' );
    $order->set_payment_method_title( 'Credit/Debit card' );
    
    // order status
    $order->set_status( 'wc-completed', 'Order is created programmatically' );
    
    // calculate and save
    $order->calculate_totals();
    $order->save();
    echo '<br>'.'آزمون(ها) به '.$user->display_name.' اختصاص یافت';

}

function load_group_assignment_scripts( $hook ) {
// Load only on ?page=group_assignment-page
	if( $hook !== 'toplevel_page_group_assignment-page' ) {
		return;
	}

// Load style & scripts.
	//wp_enqueue_style( 'psyco_tests_style', plugins_url ('psyco-tests/css/style.css', ''));
	if (!wp_script_is ('psyco-tools_js', 'enqueued')){

		wp_enqueue_script('psyco-tools_js', 'https://choicescale.ir/wp-content/plugins/psyco-tests/js/psyco-tools.js', array());
	}
}

add_action( 'admin_enqueue_scripts', 'load_group_assignment_scripts' );
