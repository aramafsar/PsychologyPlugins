<?php

/*

Plugin Name: Oganization Panel

Description: Organization Panel to manage examinees
Version: 1.0.0

Author: Aram Afsar

Author URI: http://psychologyengine.ir

Text Domain: psychologyengine

*/



//load_plugin_textdomain( 'psychologyengine', false, basename( dirname( __FILE__ ) ));



function organization_panel_admin_menu() {

add_menu_page(

__( 'پنل سازمانی', 'psychologyengine' ),

__( 'پنل سازمانی', 'psychologyengine' ),

'manage_options',

'organization-panel',

'organization_panel_content',

'dashicons-id',

3

);

}



add_action( 'admin_menu', 'organization_panel_admin_menu' );

//========Test start ========================
add_action( 'admin_footer', 'my_action_javascript' ); // Write our JS below here

function my_action_javascript() { ?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {

		var data = {
			'action': 'my_action',
			'whatever': 1234
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			alert('Got this from the server: ' + response);
		});
	});
	</script> 
<?php }
	//========Test end ========================

function organization_panel_content() {
	if (!isset($_POST['examinee_id']))
	{
		ShowOrganizationPanelTopPart();
	} else {
        GetExamineeProfile ($_POST['examinee_id']);
    }
}

function ShowOrganizationPanelTopPart(){ 
	echo '<form action="#" method="post" target="_blank">
			<h2> آزمون دهندگانی که می خواهید را بر اساس اداره، پست، جنسیت و ... انتخاب نمایید با کلیک بر روی دکمه مشاهده پروفایل، پروفایل نیمرخ روانی وی را مشاهده نمایید  </h2>';
		
	GetFilterSelectors(); // Gets Organization tags from Server and fill it in lists and trees 
	echo '</form>';
} 



function GetFilterSelectors ()
{
	$params = array(); 
	$res = callService ('GetOrganizationTags', $params);
	echo $res;
}

function  GetExamineeProfile ($examinee_id)
{
	$params = array ('examinee_id'=>$examinee_id);
	$res = callService ('GetGeneralProfile', $params);
	echo $res;
}

function load_organization_panel_scripts( $hook ) {

// Load only on ?page=homogenity_page
/*
	if( !(str_ends_with ($hook,'_page_organization_page') or str_ends_with ($hook,'_page_tests_results_page')) ) {
		return;
	}
*/
	wp_enqueue_style( 'psyco_tests_style', plugins_url ('psyco-tests/css/style.css', ''));

// Load style & scripts.
	if (!wp_script_is ('neo240_js', 'enqueued')){
		wp_enqueue_script('highcharts', 'https://code.highcharts.com/highcharts.js', array('jquery'));
		wp_enqueue_script('highcharts-accessibility', 'https://code.highcharts.com/modules/accessibility.js', array('jquery', 'highcharts'));
		wp_enqueue_script('highcharts-more', 'https://code.highcharts.com/highcharts-more.js', array('jquery', 'highcharts'));
		// Enqueue the custom script with highcharts as a dependency.
		wp_enqueue_script('neo240_js', 'https://choicescale.ir/wp-content/plugins/psyco-tests/js/neo240.js', array('highcharts'));
	}

	if (!wp_script_is ('psyco_tools_js', 'enqueued')){
		wp_enqueue_script('psyco_tools_js', 'https://choicescale.ir/wp-content/plugins/psyco-tests/js/psyco-tools.js');
	}
}

add_action( 'admin_enqueue_scripts', 'load_organization_panel_scripts' );
add_action( 'wp_loaded', 'load_organization_panel_scripts' );

