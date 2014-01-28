<?php
/*
Plugin Name: Sample Admin Page
Plugin URI: http://yourls.org/
Description: A example of a plugin administration page to save user defined option
Version: 1.0
Author: Ozh
Author URI: http://ozh.org/
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Register our plugin admin page
yourls_add_action( 'plugins_loaded', 'ozh_yourls_samplepage_add_page' );
function ozh_yourls_samplepage_add_page() {
	yourls_register_plugin_page( 'sample_page', 'Sample Admin Page', 'ozh_yourls_samplepage_do_page' );
	// parameters: page slug, page title, and function that will display the page itself
}

// Display admin page
function ozh_yourls_samplepage_do_page() {

	// Check if a form was submitted
	if( isset( $_POST['test_option'] ) ) {
		// Check nonce
		yourls_verify_nonce( 'sample_page' );
		
		// Process form
		ozh_yourls_samplepage_update_option();
	}

	// Get value from database
	$test_option = yourls_get_option( 'test_option' );
	
	// Create nonce
	$nonce = yourls_create_nonce( 'sample_page' );

	echo <<<HTML
		<h1>Sample Plugin Administration Page</h1>
		<p>This plugin stores an integer in the <code>options</code> database.</p>
		<form method="post">
			<input type="hidden" name="nonce" value="$nonce" />
			<label for="test_option">Enter an integer</label>
			<div class="input-group">
			<input type="text" class="test-option" name="test_option" value="$test_option" />
				<span class="input-group-btn">
					<button class="btn btn-default" type="submit">Update value</button>
				</span>
			</div>
		</form>

HTML;
}

// Update option in database
function ozh_yourls_samplepage_update_option() {
	$in = $_POST['test_option'];
	
	if( $in ) {
		// Validate test_option. ALWAYS validate and sanitize user input.
		// Here, we want an integer
		$in = intval( $in);
		
		// Update value in database
		yourls_update_option( 'test_option', $in );
	}
}