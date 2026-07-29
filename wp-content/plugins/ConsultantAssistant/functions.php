<?php
function add_psyco_tools () {
	// Register the script with a unique handle, source, and dependencies
	// Enqueue the script to be loaded in the header
	wp_register_script ('neo240_js', WP_PLUGIN_DIR. '/psyco-tests/js/neo240.js');
	wp_register_script ('psyco-tools_js', WP_PLUGIN_DIR . '/psyco-tests/js/psyco-tools.js', array ('jquery'));
	if (!wp_script_is ('psyco-tools_js', 'enqueued')) {
		wp_enqueue_script ('psyco-tools_js');
	}
	if (!wp_script_is ('neo240_js', 'enqueued')) {
		wp_enqueue_script ('neo240_js');
	}
}
// Hook the function to the admin_enqueue_scripts action
add_action ('admin_enqueue_scripts', 'add_psyco_tools');




?>