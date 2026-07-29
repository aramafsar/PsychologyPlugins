<?php
function add_psyco_tools () {
	// Register the script with a unique handle, source, and dependencies
	// Enqueue the script to be loaded in the header

	wp_register_script ('psyco-tools', plugin_dir_url (__FILE__) . '/js/psyco-tools.js', array ('jquery'));
	wp_enqueue_script ('psyco-tools');


	wp_register_script ('neo240_js', plugin_dir_url ( '/js/neo240.js',__FILE__));
	wp_enqueue_script ('neo240_js');
}
// Hook the function to the admin_enqueue_scripts action
add_action ('admin_enqueue_scripts', 'add_psyco_tools');