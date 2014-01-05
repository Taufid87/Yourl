<?php
define( 'YOURLS_ADMIN', true );
require_once dirname( dirname( __FILE__ ) ) . '/load-yourls.php';
yourls_maybe_require_auth();

// Handle activation/deactivation of theme
if( isset( $_GET['action'] ) && isset( $_GET['theme'] ) ) {

	// Check nonce
	yourls_verify_nonce( 'manage_themes', $_REQUEST['nonce'] );
	
	// Activate / Deactivate
	switch( $_GET['action'] ) {
		case 'activate':
			$result = yourls_activate_theme( $_GET['theme'] );
			if( $result === true )
				yourls_redirect( yourls_admin_url( 'themes.php?success=activated' ), 302 );

			break;

		default:
			$result = yourls__( 'Unsupported action' );
			break;
	}
	
	yourls_add_notice( $result, 'danger' );
}
	
// Handle message upon succesfull (de)activation
if( isset( $_GET['success'] ) && ( $_GET['success'] == 'activated' ) ) {
	if( $_GET['success'] == 'activated' )
		$message = yourls__( 'Theme has been activated!' );

	yourls_add_notice( $message, 'success' );
}

yourls_html_head( 'themes', yourls__( 'Manage Themes' ) );
yourls_template_content( 'before', 'themes' );

$themes = (array)yourls_get_themes();
uasort( $themes, 'yourls_themes_sort_callback' );
	
$count = count( $themes );
$themes_count = sprintf( yourls_n( '%s theme', '%s themes', $count ), $count );
	
yourls_html_htag( yourls__( 'Themes' ), 1, /* //translators: "'3 themes' installed and '1' activated" */ yourls_s( '<strong>%1$s</strong> installed', $themes_count ) );

echo '<p>';
yourls_add_label( yourls__( 'More themes' ), 'info', 'after' );
yourls_e( 'For more themes, head to the official <a href="http://yourls.org/themelist">Theme list</a>.' );
echo '</p>';
	
echo '<div class="themes-list">';
	
$nonce = yourls_create_nonce( 'manage_themes' );
	
$i = 0;
foreach( $themes as $file => $theme_data ) {
	$i++;
	// default fields to read from the theme header
	$fields = array(
		'name'       => 'Theme Name',
		'uri'        => 'Theme URI',
		'desc'       => 'Description',
		'version'    => 'Version',
		'author'     => 'Author',
		'author_uri' => 'Author URI'
	);
		
	// Loop through all default fields, get value if any and reset it
	foreach( $fields as $field => $value ) {
		if( isset( $theme_data[ $value ] ) ) {
			$data[ $field ] = $theme_data[ $value ];
		} else {
			$data[ $field ] = '(no info)';
		}
		unset( $theme_data[$value] );
	}
		
	$themedir = trim( dirname( $file ), '/' );

	if( $themedir == yourls_get_active_theme() ) {
		$class = 'success';
		$action_url = yourls_nonce_url( 'manage_themes', yourls_add_query_arg( array( 'action' => 'activate', 'theme' => 'default' ) ) );
		$action_anchor = yourls__( 'Reset' );
	} else {
		$class = 'warning';
		$action_url = yourls_nonce_url( 'manage_themes', yourls_add_query_arg( array( 'action' => 'activate', 'theme' => $themedir ) ) );
		$action_anchor = yourls__( 'Activate' );
	}
			
	// Other "Fields: Value" in the header? Get them too
	if( $theme_data ) {
		foreach( $theme_data as $extra_field => $extra_value ) {
			$data['desc'] .= "<br/><em>$extra_field</em>: $extra_value";
			unset( $theme_data[$extra_value] );
		}
	}
		
	$data['desc'] .= '<br/><small>' . yourls_s( 'Theme directory: %s', '<code>themes/' . $themedir . '</code>' ) . '</small>';
		
	// Get theme screenshot, or a default div otherwise
	if( $screenshot = yourls_get_theme_screenshot( $themedir ) ) {
		$screenshot = '<img src="' . $screenshot . '" alt="screenshot"/>';
	} else {
		$screenshot = '<span class="screenshot-missing"><i class="fa fa-question-circle"></i></span>';
	}
		
	// Author link
	$by = sprintf( '<span class="theme-author"><a href="%s">%s</a></span>', $data['author_uri'], $data['author'] );
	$by = /* //translators: "By Johnny" (the author) */ yourls_s( 'Created by %s', $by );
		
	printf( '
	<div class="theme">
		<div class="thumbnail">
			<div class="caption-hover">
				%s
				<p class="theme-desc">%s
				<br />
				<small>
					%s
					<span class="label label-default theme-version">%s</span>
				</small>
				</p>
			</div>
			<div class="caption">
				<h4 class="theme-name"><a href="%s">%s</a></h4>
				<p class="theme-actions actions">
					<a class="btn btn-%s" href="%s">%s</a>
				</p>
			</div>
		</div>
	</div>',
		$screenshot, $data['desc'], $by, $data['version'], $data['uri'], 
		$data['name'], $class, $action_url, $action_anchor
	);
		
	if ($i == 2) {
		echo '<div class="clearfix"></div>';
		$i = 0;
	}
}
echo '</div>';
	
echo '<p class="callout callout-warning">';
yourls_e( 'If something goes wrong after you activate a theme and you cannot use YOURLS or access this page, simply rename or delete its directory.' );
echo '</p>';
	
yourls_template_content( 'after', 'themes' );
?>