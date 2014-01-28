<?php
define( 'YOURLS_ADMIN', true );
define( 'YOURLS_UPGRADING', true );
require_once dirname( dirname( __FILE__ ) ) . '/load-yourls.php';
require_once YOURLS_INC . '/functions-upgrade.php';
require_once YOURLS_INC . '/functions-install.php';
yourls_maybe_require_auth();

yourls_html_head( 'upgrade', yourls__( 'Upgrade YOURLS' ) );
yourls_template_content( 'before' );
yourls_html_htag( 'YOURLS', 1, 'Your Own URL Shortener' );

yourls_html_htag( yourls__( 'Upgrade YOURLS' ), 2 ); 

// Check if upgrade is needed
if ( !yourls_upgrade_is_needed() ) {
	echo yourls_notice_box( yourls__( 'Upgrade not required.' ), 'success' );
	echo '<p>' . yourls_s( 'Go back to <a href="%s">the admin interface</a>.', yourls_admin_url( 'index.php' ) ) . '</p>';

} else {
	/*
	step 1: create new tables and populate them, update old tables structure, 
	step 2: convert each row of outdated tables if needed
	step 3:	- if applicable finish updating outdated tables (indexes etc)
		- update version & db_version in options, this is all done!
	*/
	
	// From what are we upgrading?
	if ( isset( $_GET['oldver'] ) && isset( $_GET['oldsql'] ) ) {
		$oldver = yourls_sanitize_version( $_GET['oldver'] );
		$oldsql = yourls_sanitize_version( $_GET['oldsql'] );
	} else {
		list( $oldver, $oldsql ) = yourls_get_current_version_from_sql();
	}
	
	// To what are we upgrading ?
	$newver = YOURLS_VERSION;
	$newsql = YOURLS_DB_VERSION;
	
	// Verbose & ugly details
	$ydb->show_errors = true;
	
	// Let's go
	$step = ( isset( $_GET['step'] ) ? intval( $_GET['step'] ) : 0 );
	switch( $step ) {

		default:
		case 0:
			echo yourls_notice_box( yourls__( 'Your current installation needs to be upgraded.' ) );
			echo '<p>';
			yourls_e( 'Please, pretty please, it is recommended that you <strong>backup</strong> your database.' );
			echo '<br />';
			yourls_add_label( 'Note', 'info', 'after' );
			yourls_e( 'You should do this regularly anyway.' );
			echo '</p><p>';
			yourls_e( "Nothing awful <em>should</em> happen, but this doesn't mean it <em>won't</em> happen, right? ;)" );
			echo '<br />';
			yourls_e( 'On every step, if <span class="error">something goes wrong</span>, you\'ll see a message and hopefully a way to fix. If everything goes too fast and you cannot read, <span class="success">good for you</span>, let it go :)' );
			echo '</p><p><em>';
			yourls_e( 'Once you are ready, press "Upgrade"!' );
			echo '</em></p>';

			echo '<form action="upgrade.php?" method="get">
				<input type="hidden" name="step" value="1" />
				<input type="hidden" name="oldver" value="' . $oldver . '" />
				<input type="hidden" name="newver" value="' . $newver . '" />
				<input type="hidden" name="oldsql" value="' . $oldsql . '" />
				<input type="hidden" name="newsql" value="' . $newsql . '" />
				<input type="submit" class="btn btn-warning btn-large" value="' . yourls_esc_attr__( 'Upgrade' ) . '" />
			</form>';
			
			break;
			
		case 1:
		case 2:
			$upgrade = yourls_upgrade( $step, $oldver, $newver, $oldsql, $newsql );
			break;
			
		case 3:
			$upgrade = yourls_upgrade( 3, $oldver, $newver, $oldsql, $newsql );
			echo '<p>' . yourls__( 'Your installation is now up to date!' ) . '</p>';
			echo '<p>' . yourls_s( 'Go back to <a href="%s">the admin interface</a>.', yourls_admin_url( 'index.php' ) ) . '</p>';
	}
	
}

yourls_template_content( 'after' ); 
?>