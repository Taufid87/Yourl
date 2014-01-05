<?php
/**
 * Display HTML head and <body> tag
 *
 * @param string $context Context of the page (stats, index, infos, ...)
 * @param string $title HTML title of the page
 */
function yourls_html_head( $context = 'index', $title = '' ) {

	yourls_do_action( 'pre_html_head', $context, $title );
	
	// Force no cache for all admin pages
	if( yourls_is_admin() && !headers_sent() ) {
		header( 'Expires: Thu, 23 Mar 1972 07:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		yourls_content_type_header( yourls_apply_filters( 'html_head_content-type', 'text/html' ) );
		yourls_do_action( 'admin_headers', $context, $title );
	}
	
	// Store page context in global object
	global $ydb;
	$ydb->context = $context;
	
	// Body class
	$bodyclass = yourls_apply_filter( 'bodyclass', '' );
	
	// Page title
	$_title = 'YOURLS &middot; Your Own URL Shortener | ' . yourls_link();
	$title = $title ? $title . " &mdash; " . $_title : $_title;
	$title = yourls_apply_filter( 'html_title', $title, $context );
	
	?>
<!DOCTYPE html>
<html <?php yourls_html_language_attributes(); ?>>
<head>
	<meta charset="utf-8">
	<title><?php echo $title ?></title>
	<meta name="description" content="YOURLS is Your Own URL Shortener. Get it at http://yourls.org/">
	<meta name="author" content="The YOURLS project - http://yourls.org/">
	<meta name="generator" content="YOURLS <?php echo YOURLS_VERSION ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="canonical" href="<?php yourls_site_url(); ?>/">
	<?php  
	yourls_favicon();
	yourls_output_asset_queue();
	if ( $context == 'infos' ) { 	// Load charts component as needed ?>
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript">
					 google.load('visualization', '1.0', {'packages':['corechart', 'geochart']});
			</script>
	<?php } ?>
	<script type="text/javascript">
	//<![CDATA[
		var ajaxurl    = '<?php echo yourls_admin_url( 'admin-ajax.php' ); ?>';
		var moviepath  = '<?php yourls_site_url( true, YOURLS_ASSETURL . '/js/ZeroClipboard.swf' ); ?>';
		
	//]]>
	</script>
	<?php yourls_do_action( 'html_head', $context ); ?>
</head>
<body class="<?php echo $context . ( $bodyclass ? ' ' . $bodyclass : '' ); ?>">
	<div class="container">
	<?php
}

/**
 * Display YOURLS logo
 *
 * @param bool $linked true if a link is wanted
 */
function yourls_html_logo( $linked = true ) {
	yourls_do_action( 'pre_html_logo' );
	$logo = '<img class="yourls-logo-img" src="' . yourls_site_url( false, YOURLS_ASSETURL . '/img/yourls-logo.png' ) . '" alt="YOURLS" title="YOURLS"/>';
	if ( $linked )
		$logo = yourls_html_link( yourls_admin_url( 'index' ), $logo, 'YOURLS', false, false );
	?>
	<div class="yourls-logo">
		<?php echo $logo; ?>
	</div>
		<?php
	yourls_do_action( 'html_logo' );
}

/**
 * Display HTML heading (h1 .. h6) tag
 *
 * @since 2.0
 * @param string $title     Title to display
 * @param int    $size      Optional size, 1 to 6, defaults to 6
 * @param string $subtitle  Optional subtitle to be echoed after the title
 * @param string $class     Optional html class
 * @param bool   $echo      
 */
function yourls_html_htag( $title, $size = 1, $subtitle = null, $class = null, $echo = true ) {
	$size = intval( $size );
	if( $size < 1 )
		$size = 1;
	elseif( $size > 6 )
		$size = 6;
		
	if( $class ) {
		$class = 'class="' . yourls_esc_attr( $class ) . '"';
	}
	
	$result = "<h$size$class>$title";
	if ( $subtitle ) {
		$result .= " <small>&mdash; $subtitle</small>";
	}
	$result .= "</h$size>";
	if ( $echo )
		echo $result;
	else
		return $result;
}

/**
 * Display the admin menu
 *
 * @param string $current_page Which page is loaded?
 */
function yourls_html_menu( $current_page = null ) {
	// Build menu links
	if( defined( 'YOURLS_USER' ) ) {
		$logout_link = yourls_apply_filter( 'logout_link', '<li class="nav-header">' . sprintf( yourls__( 'Hello <strong>%s</strong>' ), YOURLS_USER ) . '</li><li class="logout"><a href="?action=logout" title="' . yourls_esc_attr__( 'Logout' ) . '"><i class="fa fa-sign-out"></i> ' . yourls__( 'Logout' ) . '</a>' );
	} else {
		$logout_link = yourls_apply_filter( 'logout_link', '' );
	}
	$help_link   = yourls_apply_filter( 'help-link', '<a href="' . yourls_site_url( false ) .'/docs/"><i class="fa fa-question-circle"></i> ' . yourls__( 'Help' ) . '</a>' );
	
	$admin_links    = array();
	$admin_sublinks = array();
	
	$admin_links['admin'] = array(
		'url'    => yourls_admin_url( 'index' ),
		'title'  => yourls__( 'Go to the admin interface' ),
		'anchor' => yourls__( 'Interface' ),
		'icon'   => 'home'
	);
	
	if( ( yourls_is_admin() && yourls_is_public_or_logged() ) || defined( 'YOURLS_USER' ) ) {
		$admin_links['tools'] = array(
			'url'    => yourls_admin_url( 'tools' ),
			'anchor' => yourls__( 'Tools' ),
			'icon'   => 'wrench'
		);
		$admin_links['plugins'] = array(
			'url'    => yourls_admin_url( 'plugins' ),
			'anchor' => yourls__( 'Plugins' ),
			'icon'   => 'cogs'
		);
		$admin_links['themes'] = array(
			'url'    => yourls_admin_url( 'themes' ),
			'anchor' => yourls__( 'Themes' ),
			'icon'   => 'picture-o'
		);
		$admin_sublinks['plugins'] = yourls_list_plugin_admin_pages();
	}
	
	$admin_links    = yourls_apply_filter( 'admin-links',    $admin_links );
	$admin_sublinks = yourls_apply_filter( 'admin-sublinks', $admin_sublinks );
	
	// Build menu HTML
	$menu = yourls_apply_filter( 'admin_menu_start', '<nav class="sidebar-responsive-collapse"><ul class="admin-menu">' );
	if ( yourls_is_private() && !empty( $logout_link ) )
		$menu .= $logout_link;

	$menu .= '<li class="nav-header">' . yourls__( 'Administration' ) . '</li>';

	foreach( (array)$admin_links as $link => $ar ) {
		if( isset( $ar['url'] ) ) {
			$anchor = isset( $ar['anchor'] ) ? $ar['anchor'] : $link;
			$title  = isset( $ar['title'] ) ? 'title="' . $ar['title'] . '"' : '';
			$class_active  = $current_page == $link ? ' active' : '';
			
			$format = '<li id="admin-menu-%link%-link" class="admin-menu-toplevel%class%">
				<a href="%url%" %title%><i class="fa fa-%icon%"></i> %anchor%</a></li>';
			$data   = array( 
				'link'   => $link,
				'class'  => $class_active,
				'url'    => $ar['url'],
				'title'  => $title,
				'icon'   => $ar['icon'],
				'anchor' => $anchor,
			);
			
			$menu .= yourls_apply_filter( 'admin-menu-link-' . $link, yourls_replace_string_tokens( $format, $data ), $format, $data );
		}
		
		// Submenu if any. TODO: clean up, too many code duplicated here
		if( isset( $admin_sublinks[$link] ) ) {
			$menu .= '<ul class="admin-menu submenu" id="admin-submenu-' . $link . '">';
			foreach( $admin_sublinks[$link] as $link => $ar ) {
				if( isset( $ar['url'] ) ) {
					$anchor = isset( $ar['anchor'] ) ? $ar['anchor'] : $link;
					$title  = isset( $ar['title'] ) ? 'title="' . $ar['title'] . '"' : '';
					$class_active  = ( isset( $_GET['page'] ) && $_GET['page'] == $link ) ? ' active' : '';
					
					$format = '<li id="admin-menu-%link%-link" class="admin-menu-sublevel admin-menu-sublevel-%link%%class%">
						<a href="%url%" %itle%>%anchor%</a></li>';
					$data   = array(
						'link'   => $link,
						'class'  => $class_active,
						'url'    => $ar['url'],
						'title'  => $title,
						'anchor' => $anchor,
					);
					
					$menu .= yourls_apply_filter( 'admin_menu_sublink_' . $link, yourls_replace_string_tokens( $format, $data ), $format, $data );
				}
			}
			$menu .=  '</ul>';
		}
	}
	
	if ( isset( $help_link ) )
		$menu .=  '<li id="admin-menu-help-link">' . $help_link .'</li>';
	
	$menu .=  yourls_apply_filter( 'admin_menu_end', '</ul></nav>' );
	
	yourls_do_action( 'pre_admin_menu' );
	echo yourls_apply_filter( 'html_admin_menu', $menu );
	yourls_do_action( 'post_admin_menu' );
}

/**
 * Display global stats in a div
 *
 * @since 2.0
 */
function yourls_html_global_stats() {
	list( $total_urls, $total_clicks ) = array_values( yourls_get_db_stats() );
	// @FIXME: this SQL query is also used in admin/index.php - reduce query count
	$html  = '<div class="global-stats"><div class="global-stats-data">';
	$html .= '<strong class="status-number increment">' . yourls_number_format_i18n( $total_urls ) . '</strong><p>' . yourls__( 'Links' );
	$html .= '</p></div><div class="global-stats-data">';
	$html .= '<strong class="status-number">' . yourls_number_format_i18n( $total_clicks ) . '</strong><p>' . yourls__( 'Clicks' ) . '</p></div></div>';
	echo yourls_apply_filters( 'html_global_stats', $html );
}

/**
 * Wrapper function to display admin notice
 *
 * @param string $message The message showed
 * @param string $style notice / error / info / warning / success
 */
function yourls_add_notice( $message, $style = 'notice' ) {
	// Escape single quotes in $message to avoid breaking the anonymous function 
	$message = yourls_notice_box( strtr( $message, array( "'" => "\'" ) ), $style ); 
	yourls_add_action( 'admin_notice', create_function( '', "echo '$message';" ) );
}

/**
 * Return a formatted notice
 * 
 * @param string $message The message showed
 * @param string $style notice / error / info / warning / success
 */
function yourls_notice_box( $message, $style = 'notice' ) {
	return <<<HTML
	<div class="alert alert-$style"><a class="close" data-dismiss="alert" href="#">&times;</a>$message</div>
HTML;
}

/**
 * Wrapper function to display label
 *
 * @since 2.0
 * @param string $message The message showed
 * @param string $style notice / error / info / warning / success
 */
function yourls_add_label( $message, $style = 'normal', $space = null ) {
	$label = '<span class="label label-' . $style . '">' . $message . '</span>';
	if ( $space )
		$label = $space == 'before' ? ' ' . $label : $label . ' ';
	echo $label;
}

/**
 * Display a page
 *
 */
function yourls_page( $page ) {
	$include = YOURLS_PAGEDIR . "/$page.php";
	if( !file_exists( $include ) ) {
		yourls_die( "Page '$page' not found", 'Not found', 404 );
	}
	yourls_do_action( 'pre_page', $page );
	include_once( $include );
	yourls_do_action( 'post_page', $page );
	die();	
}

/**
 * Display the language attributes for the HTML tag.
 *
 * Builds up a set of html attributes containing the text direction and language
 * information for the page. Stolen from WP.
 *
 * @since 1.6
 */
function yourls_html_language_attributes() {
	$attributes = array();
	$output = '';
	
	$attributes[] = ( yourls_is_rtl() ? 'dir="rtl"' : 'dir="ltr"' );
	
	$doctype = yourls_apply_filters( 'html_language_attributes_doctype', 'html' );
	// Experimental: get HTML lang from locale. Should work. Convert fr_FR -> fr-FR
	if ( $lang = str_replace( '_', '-', yourls_get_locale() ) ) {
		if( $doctype == 'xhtml' ) {
			$attributes[] = "xml:lang=\"$lang\"";
		} else {
			$attributes[] = "lang=\"$lang\"";
		}
	}

	$output = implode( ' ', $attributes );
	$output = yourls_apply_filters( 'html_language_attributes', $output );
	echo $output;
}

/**
 * Display HTML footer (including closing body & html tags)
 *
 */
function yourls_html_footer() {
	echo '<hr /><div class="footer" role="contentinfo"><p>';
	$footer  = yourls_s( 'Powered by %s', yourls_html_link( 'http://yourls.org/', 'YOURLS', 'YOURLS', false, false ) . ' v' . YOURLS_VERSION );
		echo yourls_apply_filters( 'html_footer_text', $footer );
	echo '</p></div>';
}

/**
 * Display HTML debug infos
 *
 */
function yourls_html_debug() {
	global $ydb;
	echo '<pre class="debug-info"><button type="button" class="close" onclick="$(this).parent().fadeOut();return false;" title="Dismiss">&times;</button>';
	echo  'Queries: ' . $ydb->num_queries . "\n";
		echo join( "\n", $ydb->debug_log );
	echo '</pre>';
	yourls_do_action( 'html_debug', $ydb->context );
}

/**
 * Display "Add new URL" box
 *
 * @param string $url URL to prefill the input with
 * @param string $keyword Keyword to prefill the input with
 */
function yourls_html_addnew( $url = '', $keyword = '' ) {
	?>
	<div class="new-url-form">
			<div class="new-url-long">
				<label><?php yourls_e( 'Enter the URL' ); ?></label>
				<input type="text" class="add-url" name="url" placeholder="http://&hellip;" size="80">
			</div>
			<div class="new-url-short">
				<label><?php yourls_e( 'Short URL' ); ?> <span class="label label-info"><?php yourls_e( 'Optional' ); ?></span></label>
				<input type="text" placeholder="<?php yourls_e( 'keyword' ); ?>" name="keyword" value="<?php echo $keyword; ?>" class="add-keyword" size="8">
				<?php yourls_nonce_field( 'add_url', 'nonce-add' ); ?>
		</div>
			<div class="new-url-action">
				<button name="add-button" class="add-button"><?php yourls_e( 'Shorten The URL' ); ?></button>
			</div>
		<div class="feedback"></div>
		<?php yourls_do_action( 'html_addnew' ); ?>
	</div>
	<?php 
}

/**
 * Display main search form
 *
 * The $param array is defined in /admin/index.php
 *
 * @param array $params Array of all required parameters
 * @return string Result
 */
function yourls_html_search( $params = array() ) {
	extract( $params ); // extract $search_text, $search_in ...
	?>
				<form class="search-form" action="" method="get" role="search">
						<?php
						// @TODO: Clean up HTML - CSS
						// First search control: text to search
						$_input = '<input type="text" name="search" class="form-control search-primary" value="' . yourls_esc_attr( $search_text ) . '" />';
						$_options = array(
							'keyword' => yourls__( 'Short URL' ),
							'url'     => yourls__( 'URL' ),
							'title'   => yourls__( 'Title' ),
							'ip'      => yourls__( 'IP' ),
						);							
						$_select_search = yourls_html_select( 'search_in', $_options, $search_in );
						$_button = '<span class="input-group-btn">
						<button type="submit" id="submit-sort" class="btn btn-primary">' . yourls__( 'Search' ) . '</button>
						<button type="button" id="submit-clear-filter" class="btn btn-danger" onclick="window.parent.location.href = \'index\'">' . yourls__( 'Clear' ) . '</button>
						</span>';
						
						// Second search control: order by
						$_options = array(
							'keyword'      => yourls__( 'Short URL' ),
							'url'          => yourls__( 'URL' ),
							'timestamp'    => yourls__( 'Date' ),
							'ip'           => yourls__( 'IP' ),
							'clicks'       => yourls__( 'Clicks' ),
						);
						$_select_order = yourls_html_select( 'sort_by', $_options, $sort_by );
						$sort_order = isset( $sort_order ) ? $sort_order : 'desc' ;
						$_options = array(
							'asc'  => yourls__( 'Ascending' ),
							'desc' => yourls__( 'Descending' ),
						);
						$_select2_order = yourls_html_select( 'sort_order', $_options, $sort_order );
						
						// Fourth search control: Show links with more than XX clicks
						$_options = array(
							'more' => yourls__( 'more' ),
							'less' => yourls__( 'less' ),
						);
						$_select_clicks = yourls_html_select( 'click_filter', $_options, $click_filter );
						$_input_clicks  = '<input type="text" name="click_limit" class="form-control" value="' . $click_limit . '" /> ';

						// Fifth search control: Show links created before/after/between ...
						$_options = array(
							'before'  => yourls__( 'before' ),
							'after'   => yourls__( 'after' ),
							'between' => yourls__( 'between' ),
						);
						$_select_creation = yourls_html_select( 'date_filter', $_options, $date_filter );
						$_input_creation  = '<input type="text" name="date-first" class="form-control date-first" value="' . $date_first . '" />';
						$_input2_creation = '<input type="text" name="date-second" class="form-control date-second" value="' . $date_second . '"' . ( $date_filter === 'between' ? ' style="display:inline"' : '' ) . '/>';

						$advanced_search = array(
							yourls__( 'Search' )   => array( $_input, $_button ),
							yourls__( 'In' )       => array( $_select_search ),
							yourls__( 'Order by' ) => array( $_select_order, $_select2_order ),
							yourls__( 'Clicks' )   => array( $_select_clicks, $_input_clicks ),
							yourls__( 'Created' )  => array( $_select_creation, $_input_creation, $_input2_creation )
						);
						foreach( $advanced_search as $title => $options ) {
							?>
							<div class="control-group">
								<label class="control-label"><?php echo $title; ?></label>
								<div class="controls input-group">
									<?php
									foreach( $options as $option )
										echo $option
									?>
						</div>
					</div>
							<?php
						}
						?>
			
				</form>
			<?php
			// Remove empty keys from the $params array so it doesn't clutter the pagination links
			$params = array_filter( $params, 'yourls_return_if_not_empty_string' ); // remove empty keys

			if( isset( $search_text ) ) {
				$params['search'] = $search_text;
				unset( $params['search_text'] );
			}
			yourls_do_action( 'html_search' );
}
			
/**
 * Wrapper function to display the global pagination on interface
 * 
 * @param array $params
 */
function yourls_html_pagination( $params = array() ) {
	extract( $params ); // extract $page, ...
	if( $total_pages > 1 ) { 
			?>
		<div>
			<ul class="pagination">
					<?php
					// Pagination offsets: min( max ( zomg! ) );
				$p_start = max( min( $total_pages - 4, $page - 2 ), 1 );
					$p_end = min( max( 5, $page + 2 ), $total_pages );
					if( $p_start >= 2 ) {
					$link = yourls_add_query_arg( array( 'page' => 1 ) );
					echo '<li><a href="' . $link . '" title="' . yourls_esc_attr__( 'Go to First Page' ) . '">&laquo;</a></li>';
					echo '<li><a href="'.yourls_add_query_arg( array( 'page' => $page - 1 ) ).'">&lsaquo;</a></li>';
					}
					for( $i = $p_start ; $i <= $p_end; $i++ ) {
						if( $i == $page ) {
						echo '<li class="active"><a href="#">' . $i . '</a></li>';
						} else {
						$link = yourls_add_query_arg( array( 'page' => $i ) );
						echo '<li><a href="' . $link . '" title="' . sprintf( yourls_esc_attr( 'Page %s' ), $i ) .'">'.$i.'</a></li>';
						}
					}
					if( ( $p_end ) < $total_pages ) {
					$link = yourls_add_query_arg( array( 'page' => $total_pages ) );
					echo '<li><a href="' . yourls_add_query_arg( array( 'page' => $page + 1 ) ) . '">&rsaquo;</a></li>';
					echo '<li><a href="' . $link . '" title="' . yourls_esc_attr__( 'Go to First Page' ) . '">&raquo;</a></li>';
					}
					?>
			</ul>
			</div>
		<?php }
		yourls_do_action( 'html_pagination' );
}

/**
 * Wrapper function to display how many items are shown
 * 
 * @since 2.0
 *
 * @param string $item_type Type of the item (e.g. "links")
 * @param int $min_on_page
 * @param int $max_on_page
 * @param int $total_items Total of items in data
 */
function yourls_html_displaying_count( $item_type, $min_on_page, $max_on_page, $total_items ) {
	if( $max_on_page - $min_on_page + 1 >= $total_items )
		printf( yourls__( 'Displaying <strong class="increment">all %1$s</strong> %2$s' ), $max_on_page, $item_type );
	else
		printf( yourls__( 'Displaying %1$s <strong>%2$s</strong> to <strong class="increment">%3$s</strong> of <strong class="increment">%4$s</strong> in total' ), $item_type, $min_on_page, $max_on_page, $total_items );
}

/**
 * Return a select box
 *
 * @since 1.6
 *
 * @param string $name HTML 'name' (also use as the HTML 'id')
 * @param array $options array of 'value' => 'Text displayed'
 * @param string $selected optional 'value' from the $options array that will be highlighted
 * @param boolean $display false (default) to return, true to echo
 * @return string HTML content of the select element
 */
function yourls_html_select( $name, $options, $selected = '', $display = false ) {
	$html = '<select name="' . $name . '" class="input-group-addon">';
	foreach( $options as $value => $text ) {
		$html .= '<option value="' . $value .'"';
		$html .= $selected == $value ? ' selected="selected"' : '';
		$html .= ">$text</option>";
	}
	$html .= "</select>";
	$html  = yourls_apply_filters( 'html_select', $html, $name, $options, $selected, $display );
	if( $display )
		echo $html;
	return $html;
}

/**
 * Display the Quick Share box
 *
 */
function yourls_share_box( $longurl, $shorturl, $title = '', $text='', $shortlink_title = '', $share_title = '', $hidden = false ) {
	// @TODO: HTML Clean up
	if ( $shortlink_title == '' )
		$shortlink_title = '<h2>' . yourls__( 'Your short link' ) . '</h2>';
	if ( $share_title == '' )
		$share_title = '<h2>' . yourls__( 'Quick Share' ) . '</h2>';
	
	// Allow plugins to short-circuit the whole function
	$pre = yourls_apply_filter( 'shunt_share_box', false );
	if ( false !== $pre )
		return $pre;
		
	$text   = ( $text ? '"' . $text . '" ' : '' );
	$title  = ( $title ? "$title " : '' );
	$share  = yourls_esc_textarea( $title.$text.$shorturl );
	$count  = 140 - strlen( $share );
	$hidden = ( $hidden ? 'style="display:none;"' : '' );
	
	// Allow plugins to filter all data
	$data = compact( 'longurl', 'shorturl', 'title', 'text', 'shortlink_title', 'share_title', 'share', 'count', 'hidden' );
	$data = yourls_apply_filter( 'share_box_data', $data );
	extract( $data );
	
	$_share = rawurlencode( $share );
	$_url   = rawurlencode( $shorturl );
	?>
	
	<div id="shareboxes" <?php echo $hidden; ?>>

		<?php yourls_do_action( 'shareboxes_before', $longurl, $shorturl, $title, $text ); ?>

		<div id="copybox" class="share">
		<?php echo $shortlink_title; ?>
			<div class="input-group col col-lg-4"><input id="copylink" type="text" value="<?php echo yourls_esc_url( $shorturl ); ?>"/><span class="input-group-btn">
			<?php yourls_html_zeroclipboard( 'copylink' ); ?>
			</span></div>
			<p><small><?php yourls_e( 'Long link' ); ?>: <a id="origlink" href="<?php echo yourls_esc_url( $longurl ); ?>"><?php echo yourls_esc_url( $longurl ); ?></a></small>
			<?php if( yourls_do_log_redirect() ) { ?>
			<br/><small><?php yourls_e( 'Stats' ); ?>: <a id="statlink" href="<?php echo yourls_esc_url( $shorturl ); ?>+"><?php echo yourls_esc_url( $shorturl ); ?>+</a></small>
			<input type="hidden" id="titlelink" value="<?php echo yourls_esc_attr( $title ); ?>" />
			<?php } ?>
			</p>
		</div>

		<?php yourls_do_action( 'shareboxes_middle', $longurl, $shorturl, $title, $text ); ?>

				<?php
				yourls_do_action( 'share_links', $longurl, $shorturl, $title, $text );
				// Note: on the main admin page, there are no parameters passed to the sharebox when it's drawn.
				?>
		
		<?php yourls_do_action( 'shareboxes_after', $longurl, $shorturl, $title, $text ); ?>
	
	</div>
	
	<?php
}

/**
 * Display or return the ZeroClipboard button, with Tooltip additions
 *
 * @since 1.7
 * @param string $clipboard_target Id of the fetched element to copy value
 * @param bool $echo true to print, false to return
 */
function yourls_html_zeroclipboard( $clipboard_target, $echo = true ) {
	$html = yourls_apply_filter( 'html_zeroclipboard',
	'<button class="btn-clipboard" data-copied-hint="' . yourls__( 'Copied!' ) . '" data-clipboard-target="' . $clipboard_target . '" data-placement="bottom" data-trigger="manual" data-original-title="' . yourls__( 'Copy to clipboard' ) . '"><i class="fa fa-copy"></i></button>',
	$clipboard_target );
	if( $echo )
		echo $html;
	return $html;
}

/**
 * Die die die
 *
 */
function yourls_die( $message = '', $title = '', $header_code = 200 ) {
	yourls_status_header( $header_code );
	
	if( !$head = yourls_did_action( 'html_head' ) ) {
		yourls_html_head( 'die', yourls__( 'Fatal error' ) );
		yourls_template_content( 'before', 'die' );
	}
	
	echo yourls_apply_filter( 'die_title', "<h2>$title</h2>" );
	echo yourls_apply_filter( 'die_message', "<p>$message</p>" );
	yourls_do_action( 'yourls_die' );
	
	if( !$head ) {
		yourls_template_content( 'after', 'die' );
	}
	die();
}

/**
 * Return an "Edit" row for the main table
 *
 * @param string $keyword Keyword to edit
 * @return string HTML of the edit row
 */
function yourls_table_edit_row( $keyword ) {
	$keyword = yourls_sanitize_string( $keyword );
	$id = yourls_string2htmlid( $keyword ); // used as HTML #id
	$url = yourls_get_keyword_longurl( $keyword );
	
	$title = htmlspecialchars( yourls_get_keyword_title( $keyword ) );
	$safe_url = yourls_esc_attr( $url );
	$safe_title = yourls_esc_attr( $title );
	$www = yourls_link();
	
	$nonce = yourls_create_nonce( 'edit-save_'.$id );
	
	// @TODO: HTML Clean up
	if( $url ) {
		$return = '
		<tr id="edit-%id%" class="edit-row">
			<td class="edit-row">
				<strong>%l10n_long_url%</strong>:<input type="text" id="edit-url-%id%" name="edit-url-%id%" value="%safe_url%" class="text" size="70" /><br/>
				<strong>%l10n_short_url%</strong>: %www%<input type="text" id="edit-keyword-%id%" name="edit-keyword-%id%" value="%keyword%" class="text" size="10" /><br/>
				<strong>%l10n_title%</strong>: <input type="text" id="edit-title-%id%" name="edit-title-%id%" value="%safe_title%" class="text" size="60" />
			</td>
			<td colspan="1">
				<input type="button" id="edit-submit-%id%" name="edit-submit-%id%" value="%l10n_save%" title="%l10n_save%" class="button" onclick="edit_link_save(\'%id%\');" />
				&nbsp;<input type="button" id="edit-close-$id" name="edit-close-%id%" value="%l10n_edit%" title="%l10n_edit%" class="button" onclick="edit_link_hide(\'%id%\');" />
				<input type="hidden" id="old_keyword_%id%" value="%keyword%"/><input type="hidden" id="nonce_%id%" value="%nonce%"/>
			</td>
		</tr>
		';
		
		$data = array(
			'id' => $id,
			'keyword' => $keyword,
			'safe_url' => $safe_url,
			'safe_title' => $safe_title,
			'nonce' => $nonce,
			'www' => yourls_link(),
			'l10n_long_url' => yourls__( 'Long URL' ),
			'l10n_short_url' => yourls__( 'Short URL' ),
			'l10n_title' => yourls__( 'Title' ), 
			'l10n_save' => yourls__( 'Save' ),
			'l10n_edit' => yourls__( 'Cancel' ),
		);

		$return = urldecode( yourls_replace_string_tokens( $format, $data ) );
	} else {
		$return = '<tr class="edit-row notfound"><td class="edit-row notfound">' . yourls__( 'Error, URL not found' ) . '</td></tr>';
	}
	
	$return = yourls_apply_filter( 'table_edit_row', $return, $format, $data );
	// Compat note : up to YOURLS 1.6 the values passed to this filter where: $return, $keyword, $url, $title

	return $return;
}

/**
 * Return an "Add" row for the main table
 *
 * @return string HTML of the edit row
 */
function yourls_table_add_row( $keyword, $url, $title = '', $ip, $clicks, $timestamp ) {
	$keyword  = yourls_sanitize_string( $keyword );
	$id       = yourls_string2htmlid( $keyword ); // used as HTML #id
	$shorturl = yourls_link( $keyword );

	$statlink = yourls_statlink( $keyword );
		
	$delete_link = yourls_nonce_url( 'delete-link-'.$id,
		yourls_add_query_arg( array( 'id' => $id, 'action' => 'delete', 'keyword' => $keyword ), yourls_admin_url( 'admin-ajax.php' ) ) 
	);
	
	$edit_link = yourls_nonce_url( 'edit-link-'.$id,
		yourls_add_query_arg( array( 'id' => $id, 'action' => 'edit', 'keyword' => $keyword ), yourls_admin_url( 'admin-ajax.php' ) ) 
	);
	
	// Action link buttons: the array
	$actions = array(
		'stats' => array(
			'href'    => $statlink,
			'id'      => "statlink-$id",
			'title'   => yourls_esc_attr__( 'Stats' ),
			'icon'    => "bar-chart-o",
			'anchor'  => yourls__( 'Stats' ),
		),
		'share' => array(
			'href'    => '',
			'id'      => "share-button-$id",
			'title'   => yourls_esc_attr__( 'Share' ),
			'anchor'  => yourls__( 'Share' ),
			'icon'    => "share-square-o",
			'onclick' => "toggle_share('$id');return false;",
		),
		'edit' => array(
			'href'    => $edit_link,
			'id'      => "edit-button-$id",
			'title'   => yourls_esc_attr__( 'Edit' ),
			'anchor'  => yourls__( 'Edit' ),
			'icon'    => "edit",
			'onclick' => "edit_link_display('$id');return false;",
		),
		'delete' => array(
			'href'    => $delete_link,
			'id'      => "delete-button-$id",
			'title'   => yourls_esc_attr__( 'Delete' ),
			'anchor'  => yourls__( 'Delete' ),
			'icon'    => "trash-o",
			'onclick' => "remove_link('$id');return false;",
		)
	);
	$actions = yourls_apply_filter( 'table_add_row_action_array', $actions );
	
	// @TODO: HTML Clean up
	// Action link buttons: the HTML
	$action_links = '<div class="btn-group">';
	foreach( $actions as $key => $action ) {
		$onclick = isset( $action['onclick'] ) ? 'onclick="' . $action['onclick'] . '"' : '' ;
		$action_links .= sprintf( '<a href="%s" id="%s" title="%s" class="%s" %s><i class="fa fa-%s"></i></a>',
			$action['href'], $action['id'], $action['title'], 'btn btn-'.$key, $onclick, $action['icon']
		);
	}
	$action_links .= '</div>';
	$action_links  = yourls_apply_filter( 'action_links', $action_links, $keyword, $url, $ip, $clicks, $timestamp );

	if( ! $title )
		$title = $url;

	$protocol_warning = '';
	if( ! in_array( yourls_get_protocol( $url ) , array( 'http://', 'https://' ) ) )
		$protocol_warning = yourls_apply_filters( 'add_row_protocol_warning', '<i class="warning protocol_warning fa fa-exclamation-circle" title="' . yourls__( 'Not a common link' ) . '"></i> ' );

	// Row template that you can filter before it's parsed (don't remove HTML classes & id attributes)
	$format = '<tr id="id-%id%">
	<td class="keyword btn-clipboard" id="keyword-%id%" %copy%><a href="%shorturl%">%keyword_html%</a></td>
	<td class="url" id="url-%id%">
		<a href="%long_url%" title="%title_attr%">%title_html%</a><br/>
		<small class="longurl">%warning%<a href="%long_url%">%long_url_html%</a></small><br/>
		<input type="hidden" id="keyword_%id%" value="%keyword%"/>
		<input type="hidden" id="shorturl-%id%" value="%shorturl%"/>
		<input type="hidden" id="longurl-%id%" value="%long_url%"/>
		<input type="hidden" id="title-%id%" value="%title_attr%"/>
		<div class="actions" id="actions-%id%">
			<p><small class="added_on">%added_on_from%</small><p>
			<p>%actions%</p>
		</div>
	</td>
	<td class="clicks" id="clicks-%id%">%clicks%</td>
	</tr>';
	
	// Highlight domain in displayed URL
	$domain = parse_url( $url, PHP_URL_HOST );
	if( $domain ) {
		if( substr( $domain, 0, 4 ) == 'www.' ) {
			$domain = substr( $domain, 4 );
		}
		$display_url = preg_replace( "/$domain/", '<strong class="domain">' . $domain . '</strong>', $url, 1 );
	} else {
		$display_url = $url;
	}

	$data = array(
		'id'            => $id,
			'shorturl'      => yourls_esc_url( $shorturl ),
		'keyword'       => yourls_esc_attr( $keyword ),
			'keyword_html'  => yourls_esc_html( $keyword ),
			'long_url'      => yourls_esc_url( $url ),
		'long_url_html' => yourls_trim_long_string( $display_url, 100 ),
			'title_attr'    => yourls_esc_attr( $title ),
			'title_html'    => yourls_esc_html( yourls_trim_long_string( $title ) ),
			'warning'       => $protocol_warning,
		'added_on_from' => yourls_s( 'Added on <span class="timestamp">%s</span> from <span class="ip">%s</span>', date( 'M d, Y H:i', $timestamp +( YOURLS_HOURS_OFFSET * 3600 ) ), $ip ),
		'clicks'        => yourls_number_format_i18n( $clicks, 0, '', '' ),
		'actions'       => $action_links,
		'copy'          => 'data-clipboard-target="' . 'shorturl-' . $id /*. '" data-copied-hint="' . yourls__( 'Copied!' ) . '" data-placement="top" data-trigger="manual" data-original-title="' . yourls__( 'Copy to clipboard' ) */. '"',
	);
		
	$row = yourls_replace_string_tokens( $format, $data );
	$row = yourls_apply_filter( 'table_add_row', $row, $format, $data );
	// Compat note : up to YOURLS 1.6 the values passed to this filter where: $keyword, $url, $title, $ip, $clicks, $timestamp
	
	return $row;
}

/**
 * Callback class for yourls_table_add_row
 *
 * See comment about PHP 5.3+ in yourls_table_add_row()
 *
 * @since 1.7
 */
class yourls_table_add_row_callback {
    private $elements;
	
    function __construct($elements) {
		$this->elements = $elements;
	}
	
    function callback( $matches ) {
		return $this->elements[ $matches[1] ];
    }
}


/**
 * Echo the main table head
 *
 */
function yourls_table_head( $data = null ) {
	echo yourls_apply_filter( 'table_head_start', '<thead><tr>' );
	
	if( $data === null )  {
		$data = array(
		'shorturl' => yourls__( 'Short URL' ),
		'longurl'  => yourls__( 'Original URL' ),
		'clicks'   => yourls__( 'Clicks' ),
		);
	}
	
	$cells = '';
	foreach( $data as $id => $name ) {
		$cells .= '<th id="table-head-' . $id . '">' . $name . '</th>';
	}
	echo yourls_apply_filter( 'table_head_cells', $cells, $data );
	echo yourls_apply_filter( 'table_head_end', '</tr></thead>' );
}

/**
 * Echo the tbody start tag
 *
 */
function yourls_table_tbody_start() {
	echo yourls_apply_filter( 'table_tbody_start', '<tbody class="list">' );
}

/**
 * Echo the tbody end tag
 *
 */
function yourls_table_tbody_end() {
	echo yourls_apply_filter( 'table_tbody_end', '</tbody>' );
}

/**
 * Echo the table start tag
 *
 */
function yourls_table_start( $div_id = '', $table_class = '' ) {
	echo yourls_apply_filter( 'table_start', '<div id="' . $div_id . '"><table class="' . $table_class . '">', $table_class );
}

/**
 * Echo the table end tag
 *
 */
function yourls_table_end() {
	echo yourls_apply_filter( 'table_end', '</table></div>' );
}

/**
 * Echo the content start tag
 *
 * @since 2.0
 */
function yourls_wrapper_start() {
	yourls_do_action( 'admin_notice' );
	echo yourls_apply_filter( 'wrapper_start', '<div class="content" role="main">' );
}

/**
 * Echo the content end tag
 *
 * @since 2.0
 */
function yourls_wrapper_end() {
	echo yourls_apply_filter( 'wrapper_end', '</div>' );
	if( defined( 'YOURLS_DEBUG' ) && YOURLS_DEBUG == true ) {
		yourls_html_debug();
	}
}

/**
 * Echo the sidebar start tag
 *
 * @since 2.0
 */
function yourls_sidebar_start() {
	echo yourls_apply_filter( 'sidebar_start', '<div class="sidebar">
	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-responsive-collapse">
	  <i class="fa fa-bars"></i>
	</button>' );
}

/**
 * Echo the sidebar end tag
 *
 * @since 2.0
 */
function yourls_sidebar_end() {
	echo yourls_apply_filter( 'sidebar_end', '</div>' );
}

/**
 * Echo HTML tag for a link
 *
 * @param string $href Where the link point
 * @param string $content
 * @param string $title Optionnal "title" attribut
 * @param bool $class Optionnal "class" attribut
 * @param bool $echo 
 * @return HTML tag with all contents
 */
function yourls_html_link( $href, $content = '', $title = '', $class = false, $echo = true ) {
	if( !$content )
		$content = yourls_esc_html( $href );
	if( $title ) {
		$title = sprintf( ' title="%s"', yourls_esc_attr( $title ) );
		if( $class )
			$class = sprintf( ' class="%s"', yourls_esc_attr( $title ) );
	}
	$link = sprintf( '<a href="%s"%s%s>%s</a>', yourls_esc_url( $href ), $class, $title, $content );
	if ( $echo )
		echo yourls_apply_filter( 'html_link', $link );
	else 
		return yourls_apply_filter( 'html_link', $link );
}

/**
 * Display the login screen. Nothing past this point.
 *
 */
function yourls_login_screen( $error_msg = '' ) {
	// Since the user is not authed, we don't disclose any kind of stats
	yourls_remove_from_template( 'yourls_html_global_stats' );

	yourls_html_head( 'login' );
	
	$action = ( isset( $_GET['action'] ) && $_GET['action'] == 'logout' ? '?' : '' );
	
	yourls_template_content( 'before' );
	yourls_html_htag( 'YOURLS', 1, 'Your Own URL Shortener' );
	
	?>
	<div id="login">
		<form method="post" class="login-screen" action="<?php echo $action; // reset any QUERY parameters ?>">
			<?php 
				if( !empty( $error_msg ) ) {
					echo yourls_notice_box( $error_msg[0], $error_msg[1] );
	}
			?>
			<div class="control-group">
				<label class="control-label" for="username"><?php yourls_e( 'Username' ); ?></label>
				<div class="controls">
					<input type="text" id="username" name="username" class="text" autofocus="autofocus">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="password"><?php yourls_e( 'Password' ); ?></label>
				<div class="controls">
					<input type="password" id="password" name="password" class="text">
				</div>
			</div>
			<div class="form-actions">
				<button type="submit" class="submit" name="submit"><?php yourls_e( 'Login' ); ?></button>
			</div>
		</form>
	</div>
	<?php
	
	yourls_template_content( 'after' );

	die();	
}

/**
 * Output translated strings used by the Javascript calendar
 *
 * @since 1.6
 */
function yourls_l10n_calendar_strings() {
	echo "<script>";
	echo "var l10n_cal_month = " . json_encode( array_values( yourls_l10n_months() ) ) . ";";
	echo "var l10n_cal_days = " . json_encode( array_values( yourls_l10n_weekday_initial() ) ) . ";";
	echo "var l10n_cal_today = \"" . yourls_esc_js( yourls__( 'Today' ) ) . "\";";
	echo "var l10n_cal_close = \"" . yourls_esc_js( yourls__( 'Close' ) ) . "\";";
	echo "</script>";
	
	// Dummy returns, to initialize l10n strings used in the calendar
	yourls__( 'Today' );
	yourls__( 'Close' );
}


/**
 * Display a notice if there is a newer version of YOURLS available
 *
 * @since 1.7
 */
function yourls_new_core_version_notice() {

	yourls_debug_log( 'Check for new version: ' . ( yourls_maybe_check_core_version() ? 'yes' : 'no' ) );
	
	$checks = yourls_get_option( 'core_version_checks' );
	
	if( isset( $checks->last_result->latest ) AND version_compare( $checks->last_result->latest, YOURLS_VERSION, '>' ) ) {
		$msg = yourls_s( '<a href="%s">YOURLS version %s</a> is available. Please update!', 'http://yourls.org/download', $checks->last_result->latest );
		yourls_add_notice( $msg );
	}
}

/**
 * Send a filerable content type header
 *
 * @since 1.7
 * @param string $type content type ('text/html', 'application/json', ...)
 * @return bool whether header was sent
 */
function yourls_content_type_header( $type ) {
	if( !headers_sent() ) {
		$charset = yourls_apply_filters( 'content_type_header_charset', 'utf-8' );
		header( "Content-Type: $type; charset=$charset" );
		return true;
	}
	return false;
}

/**
 * Get search text from query string variables search_protocol, search_slashes and search
 *
 * Some servers don't like query strings containing "(ht|f)tp(s)://". A javascript bit
 * explodes the search text into protocol, slashes and the rest (see JS function
 * split_search_text_before_search()) and this function glues pieces back together
 * See issue https://github.com/YOURLS/YOURLS/issues/1576
 *
 * @since 1.7
 * @return string Search string
 */
function yourls_get_search_text() {
	$search = '';
	if( isset( $_GET['search_protocol'] ) )
		$search .= $_GET['search_protocol'];
	if( isset( $_GET['search_slashes'] ) )
		$search .= $_GET['search_slashes'];
	if( isset( $_GET['search'] ) )
		$search .= $_GET['search'];
	
	return htmlspecialchars( trim( $search ) );
}

/**
 * Display custom message based on query string parameter 'login_msg'
 *
 * @since 1.7
 */
function yourls_display_login_message() {
	if( !isset( $_GET['login_msg'] ) )
		return;
	
	switch( $_GET['login_msg'] ) {
		case 'pwdclear':
			$message  = yourls_html_htag( yourls__( 'Warning' ), 4, null, null, false );
			$message .= '<p>' . yourls__( 'Your password is stored as clear text in your <code>config.php</code>' );
			$message .= '<br />' . yourls__( 'Did you know you can easily improve the security of your YOURLS install by <strong>encrypting</strong> your password?' );
			$message .= '<br />' . yourls__( 'See <a href="http://yourls.org/userpassword">UsernamePassword</a> for details.' ) . '</p>';
			yourls_add_notice( $message, 'notice' );
			break;
	}
}

/**
 * Close html page
 *
 * @since 2.0
 */
function yourls_html_ending() {
	yourls_do_action( 'html_ending' );
	echo '</div></body></html>';
}
