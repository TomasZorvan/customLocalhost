<?php

if ( ! isset( $content_width ) ) $content_width = 613;

add_action( 'after_setup_theme', 'et_setup_theme' );
if ( ! function_exists( 'et_setup_theme' ) ){
	function et_setup_theme(){
		global $themename, $shortname, $et_store_options_in_one_row, $default_colorscheme;

		$themename = 'Harmony';
		$shortname = 'harmony';

		$default_colorscheme = "Default";

		$template_directory = get_template_directory();
		$et_store_options_in_one_row = true;

		require_once( $template_directory . '/epanel/custom_functions.php' );

		require_once( $template_directory . '/includes/functions/comments.php' );

		require_once( $template_directory . '/includes/functions/sidebars.php' );

		load_theme_textdomain( 'Harmony', $template_directory . '/lang' );

		require_once( $template_directory . '/epanel/core_functions.php' );

		require_once( $template_directory . '/epanel/post_thumbnails_harmony.php' );

		include( $template_directory . '/includes/widgets.php' );

		register_nav_menu( 'primary-menu', __( 'Primary Menu', 'Harmony' ) );

		add_theme_support( 'post-formats', array( 'audio' ) );

		add_filter( 'wp_page_menu_args', 'et_add_home_link' );

		add_action( 'wp_enqueue_scripts', 'et_harmony_load_scripts_styles' );

		add_action( 'wp_head', 'et_add_viewport_meta' );

		add_action( 'pre_get_posts', 'et_home_posts_query' );

		add_filter( 'et_get_additional_color_scheme', 'et_remove_additional_stylesheet' );

		add_action( 'wp_enqueue_scripts', 'et_add_responsive_shortcodes_css', 11 );

		add_action( 'init', 'et_harmony_register_posttypes_taxonomies', 0 );

		add_action( 'wp_head', 'et_attach_bg_images' );

		add_action( 'body_class', 'et_add_ios_class' );

		// don't display the empty title bar if the widget title is not set
		remove_filter( 'widget_title', 'et_widget_force_title' );

		add_theme_support( 'woocommerce' );

		add_action( 'body_class', 'et_add_woocommerce_class_to_homepage' );

		// take breadcrumbs out of .container
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
		// woocommerce_breadcrumb function is overwritten in functions.php
		add_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 9, 0 );

		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		add_action( 'woocommerce_before_shop_loop_item_title', 'et_template_loop_product_thumbnail', 10 );

		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		add_action( 'woocommerce_after_shop_loop_item', 'et_after_shop_loop_item', 10 );

		// fixes the theme layout on shop and product pages
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		add_action( 'woocommerce_before_main_content', 'et_theme_wrapper_start', 10 );
		add_action( 'woocommerce_after_main_content', 'et_theme_wrapper_end', 10 );
	}
}

// add Home link to the custom menu WP-Admin page
function et_add_home_link( $args ) {
	$args['show_home'] = true;
	return $args;
}

function et_harmony_load_scripts_styles(){
	$template_dir = get_template_directory_uri();

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );

	if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'Harmony' ) ) {
		$subsets = 'latin,latin-ext';

		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'Harmony' );

		if ( 'cyrillic' == $subset )
			$subsets .= ',cyrillic,cyrillic-ext';
		elseif ( 'greek' == $subset )
			$subsets .= ',greek,greek-ext';
		elseif ( 'vietnamese' == $subset )
			$subsets .= ',vietnamese';

		$protocol = is_ssl() ? 'https' : 'http';
		$query_args = array(
			'family' => 'Open+Sans:300italic,700italic,800italic,400,300,700,800',
			'subset' => $subsets
		);

		wp_enqueue_style( 'harmony-fonts', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null );
	}

	wp_enqueue_script( 'superfish', $template_dir . '/js/superfish.js', array( 'jquery' ), '1.0', true );
	wp_register_script( 'jplayer.playlist', $template_dir . '/js/jplayer.playlist.min.js', array( 'jquery' ), '1.0', true );
	wp_register_script( 'jplayer', $template_dir . '/js/jquery.jplayer.min.js', array( 'jquery', 'jplayer.playlist' ), '1.0', true );
	wp_enqueue_script( 'custom_script', $template_dir . '/js/custom.js', array( 'jquery' ), '1.0', true );
	wp_localize_script( 'custom_script', 'et_custom', array( 'mobile_nav_text' => esc_html__( 'Navigation Menu', 'Harmony' ) ) );

	$et_gf_enqueue_fonts = array();
	$et_gf_heading_font = sanitize_text_field( et_get_option( 'heading_font', 'none' ) );
	$et_gf_body_font = sanitize_text_field( et_get_option( 'body_font', 'none' ) );

	if ( 'none' != $et_gf_heading_font ) $et_gf_enqueue_fonts[] = $et_gf_heading_font;
	if ( 'none' != $et_gf_body_font ) $et_gf_enqueue_fonts[] = $et_gf_body_font;

	if ( ! empty( $et_gf_enqueue_fonts ) ) et_gf_enqueue_fonts( $et_gf_enqueue_fonts );

	/*
	 * Loads the main stylesheet.
	 */
	wp_enqueue_style( 'harmony-style', get_stylesheet_uri() );
}

function et_add_viewport_meta(){
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />';
}

function et_remove_additional_stylesheet( $stylesheet ){
	global $default_colorscheme;
	return $default_colorscheme;
}

/**
 * Filters the main query on homepage
 */
function et_home_posts_query( $query = false ) {
	/* Don't proceed if it's not homepage or the main query */
	if ( ! is_home() || ! is_a( $query, 'WP_Query' ) || ! $query->is_main_query() ) return;

	/* Set the amount of posts per page on homepage */
	$query->set( 'posts_per_page', (int) et_get_option( 'harmony_homepage_posts', '3' ) );

	// Make sure audio posts are excluded from the Recent News section, if Blog style mode is disabled
	if ( 'on' != et_get_option( 'harmony_blog_style', 'false' ) ) {
		$tax_query_args = array(
			array(
				'taxonomy' 	=> 'post_format',
				'field' 	=> 'slug',
				'terms' 	=> array( 'post-format-audio' ),
				'operator'	=> 'NOT IN',
			)
		);
		$query->set( 'tax_query', apply_filters( 'et_home_tax_query_args', $tax_query_args ) );

		// sticky posts don't take posts_per_page option into account, so we display posts in the natural order
		$query->set( 'ignore_sticky_posts', 1 );
	}

	/* Exclude categories set in ePanel */
	$exclude_categories = et_get_option( 'harmony_exlcats_recent', false );
	if ( $exclude_categories ) $query->set( 'category__not_in', array_map( 'intval', et_generate_wpml_ids( $exclude_categories, 'category' ) ) );
}

/**
 * Customize Event Query on archive pages and WP-Admin / Events using ET Start Date / Time value
 */
add_action( 'pre_get_posts', 'et_event_archive_query' );
function et_event_archive_query( $query ) {
	// Don't proceed if it's not the Event archive page or the main query
	if ( ( ! is_post_type_archive( 'event' ) && ! is_tax( 'event_category' ) ) || ! is_a( $query, 'WP_Query' ) || ! $query->is_main_query() ) return;

	// don't show expired events on Event archive page, all events ( from future to past ) show up on Event taxonomy page ( category page ) and on WP-Admin / Events
	$is_event_taxonomy_page = is_tax( 'event_category' );

	if ( is_admin() ) {
		if ( isset( $query->query_vars[ 'post_type' ] ) && 'event' == $query->query_vars[ 'post_type' ] )
			$is_event_taxonomy_page = true;
		else
			return;
	}

	if ( ! $is_event_taxonomy_page ) {
		$et_event_archive_meta_query = array(
			array(
				'key' => '_et_event_date',
				'value' => time(),
				'compare' => '>'
			)
		);

		$query->set( 'meta_query', apply_filters( 'et_event_archive_meta_query', $et_event_archive_meta_query ) );
	}

	$query->set( 'meta_key', '_et_event_date' );
	$query->set( 'orderby', 'meta_value_num' );

	if ( ! $is_event_taxonomy_page ) $query->set( 'order', 'ASC' );
	else $query->set( 'order', 'DESC' );
}

/**
 * Customize Gallery post type Query on archive pages and WP-Admin / Galleries using Gallery Date
 */
add_action( 'pre_get_posts', 'et_gallery_archive_query' );
function et_gallery_archive_query( $query ) {
	// Don't proceed if it's not the Gallery archive page or the main query
	if ( ( ! is_post_type_archive( 'gallery' ) && ! is_tax( 'gallery_category' ) ) || ! is_a( $query, 'WP_Query' ) || ! $query->is_main_query() ) return;

	if ( is_admin() && ! ( isset( $query->query_vars[ 'post_type' ] ) && 'gallery' == $query->query_vars[ 'post_type' ] ) ) return;

	$query->set( 'meta_key', '_et_gallery_date' );
	$query->set( 'orderby', 'meta_value_num' );
	$query->set( 'order', 'DESC' );
}

if ( ! function_exists( 'et_list_pings' ) ){
	function et_list_pings($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; ?>
		<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?> - <?php comment_excerpt(); ?>
	<?php }
}

if ( ! function_exists( 'et_get_the_author_posts_link' ) ){
	function et_get_the_author_posts_link(){
		global $authordata, $themename;

		$link = sprintf(
			'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			esc_url( get_author_posts_url( $authordata->ID, $authordata->user_nicename ) ),
			esc_attr( sprintf( __( 'Posts by %s', $themename ), get_the_author() ) ),
			get_the_author()
		);
		return apply_filters( 'the_author_posts_link', $link );
	}
}

if ( ! function_exists( 'et_get_comments_popup_link' ) ){
	function et_get_comments_popup_link( $zero = false, $one = false, $more = false ){
		global $themename;

		$id = (int) get_the_ID();
		$number = (int) get_comments_number( $id );

		if ( 0 == $number && !comments_open() && !pings_open() ) return;

		if ( $number > 1 )
			$output = str_replace('%', number_format_i18n($number), ( false === $more ) ? __('% Comments', $themename) : $more);
		elseif ( $number == 0 )
			$output = ( false === $zero ) ? __('No Comments',$themename) : $zero;
		else // must be one
			$output = ( false === $one ) ? __('1 Comment', $themename) : $one;

		return '<span class="comments-number">' . '<a href="' . esc_url( get_permalink() . '#respond' ) . '">' . apply_filters('comments_number', $output, $number) . '</a>' . '</span>';
	}
}

if ( ! function_exists( 'et_postinfo_meta' ) ){
	function et_postinfo_meta( $postinfo, $date_format, $comment_zero, $comment_one, $comment_more ){
		global $themename;

		$postinfo_meta = '';

		if ( in_array( 'author', $postinfo ) )
			$postinfo_meta .= ' ' . esc_html__('by',$themename) . ' ' . et_get_the_author_posts_link();

		if ( in_array( 'date', $postinfo ) )
			$postinfo_meta .= ' ' . esc_html__('on',$themename) . ' ' . get_the_time( $date_format );

		if ( in_array( 'categories', $postinfo ) )
			$postinfo_meta .= ' ' . esc_html__('in',$themename) . ' ' . get_the_category_list(', ');

		if ( in_array( 'comments', $postinfo ) )
			$postinfo_meta .= ' | ' . et_get_comments_popup_link( $comment_zero, $comment_one, $comment_more );

		if ( '' != $postinfo_meta ) $postinfo_meta = __('Posted', $themename) . ' ' . $postinfo_meta;

		echo $postinfo_meta;
	}
}

function et_harmony_register_posttypes_taxonomies() {
	$labels = array(
		'name' 					=> _x( 'Events', 'post type general name', 'Harmony' ),
		'singular_name' 		=> _x( 'Event', 'post type singular name', 'Harmony' ),
		'add_new' 				=> _x( 'Add New', 'event item', 'Harmony' ),
		'add_new_item'			=> __( 'Add New Event', 'Harmony' ),
		'edit_item' 			=> __( 'Edit Event', 'Harmony' ),
		'new_item' 				=> __( 'New Event', 'Harmony' ),
		'all_items' 			=> __( 'All Events', 'Harmony' ),
		'view_item' 			=> __( 'View Event', 'Harmony' ),
		'search_items' 			=> __( 'Search Events', 'Harmony' ),
		'not_found' 			=> __( 'Nothing found', 'Harmony' ),
		'not_found_in_trash' 	=> __( 'Nothing found in Trash', 'Harmony' ),
		'parent_item_colon' 	=> ''
	);

	$args = array(
		'labels' 				=> $labels,
		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'show_ui' 				=> true,
		'can_export'			=> true,
		'show_in_nav_menus'		=> true,
		'query_var' 			=> true,
		'has_archive' 			=> true,
		'rewrite' 				=> apply_filters( 'et_event_posttype_rewrite_args', array( 'slug' => 'event', 'with_front' => false, 'feeds' => true ) ),
		'capability_type' 		=> 'post',
		'hierarchical' 			=> false,
		'menu_position' 		=> null,
		'supports' 				=> array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields' )
	);

	register_post_type( 'event' , apply_filters( 'et_event_posttype_args', $args ) );

	$labels = array(
		'name' 					=> _x( 'Galleries', 'post type general name', 'Harmony' ),
		'singular_name' 		=> _x( 'Gallery', 'post type singular name', 'Harmony' ),
		'add_new' 				=> _x( 'Add New', 'gallery item', 'Harmony' ),
		'add_new_item'			=> __( 'Add New Gallery', 'Harmony' ),
		'edit_item' 			=> __( 'Edit Gallery', 'Harmony' ),
		'new_item' 				=> __( 'New Gallery', 'Harmony' ),
		'all_items' 			=> __( 'All Galleries', 'Harmony' ),
		'view_item' 			=> __( 'View Gallery', 'Harmony' ),
		'search_items' 			=> __( 'Search Galleries', 'Harmony' ),
		'not_found' 			=> __( 'Nothing found', 'Harmony' ),
		'not_found_in_trash' 	=> __( 'Nothing found in Trash', 'Harmony' ),
		'parent_item_colon' 	=> ''
	);

	$args = array(
		'labels' 				=> $labels,
		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'show_ui' 				=> true,
		'can_export'			=> true,
		'show_in_nav_menus'		=> true,
		'query_var' 			=> true,
		'has_archive' 			=> true,
		'rewrite' 				=> apply_filters( 'et_gallery_posttype_rewrite_args', array( 'slug' => 'gallery', 'with_front' => false ) ),
		'capability_type' 		=> 'post',
		'hierarchical' 			=> false,
		'menu_position' 		=> null,
		'supports' 				=> array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields' )
	);

	register_post_type( 'gallery' , apply_filters( 'et_gallery_posttype_args', $args ) );

	$labels = array(
		'name' 				=> _x( 'Event Categories', 'taxonomy general name', 'Harmony' ),
		'singular_name' 	=> _x( 'Category', 'taxonomy singular name', 'Harmony' ),
		'search_items' 		=>  __( 'Search Categories', 'Harmony' ),
		'all_items' 		=> __( 'All Categories', 'Harmony' ),
		'parent_item' 		=> __( 'Parent Category', 'Harmony' ),
		'parent_item_colon' => __( 'Parent Category:', 'Harmony' ),
		'edit_item' 		=> __( 'Edit Category', 'Harmony' ),
		'update_item' 		=> __( 'Update Category', 'Harmony' ),
		'add_new_item' 		=> __( 'Add New Category', 'Harmony' ),
		'new_item_name' 	=> __( 'New Category Name', 'Harmony' ),
		'menu_name' 		=> __( 'Categories', 'Harmony' )
	);

	register_taxonomy( 'event_category', array( 'event' ), array(
		'hierarchical' 	=> true,
		'labels' 		=> $labels,
		'show_ui' 		=> true,
		'query_var' 	=> true,
		'rewrite' 		=> apply_filters( 'et_event_category_rewrite_args', array( 'slug' => 'event-category' ) )
	) );

	$labels['name'] = __( 'Gallery Categories', 'Harmony' );

	register_taxonomy( 'gallery_category', array( 'gallery' ), array(
		'hierarchical' 	=> true,
		'labels' 		=> $labels,
		'show_ui' 		=> true,
		'query_var' 	=> true,
		'rewrite' 		=> apply_filters( 'et_gallery_category_rewrite_args', array( 'slug' => 'gallery-category' ) )
	) );
}

// flush permalinks on theme activation
add_action( 'after_switch_theme', 'et_rewrite_flush' );
function et_rewrite_flush() {
    flush_rewrite_rules();
}

//add filter to ensure the text Event / Gallery, or event / gallery, is displayed when user updates an event
add_filter( 'post_updated_messages', 'et_custom_post_type_updated_message' );
function et_custom_post_type_updated_message( $messages ) {
	global $post, $post_id;

	$messages['event'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Event updated. <a href="%s">View event</a>', 'Harmony' ), esc_url( get_permalink( $post_id ) ) ),
		2 => __( 'Custom field updated.', 'Harmony' ),
		3 => __( 'Custom field deleted.', 'Harmony' ),
		4 => __( 'Event updated.', 'Harmony' ),
		/* translators: %s: date and time of the revision */
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s', 'Harmony' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Event published. <a href="%s">View event</a>', 'Harmony' ), esc_url( get_permalink( $post_id ) ) ),
		7 => __( 'Event saved.', 'Harmony' ),
		8 => sprintf( __( 'Event submitted. <a target="_blank" href="%s">Preview event</a>', 'Harmony' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ) ),
		9 => sprintf( __( 'Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'Harmony' ),
		  // translators: Publish box date format, see http://php.net/date
		  date_i18n( __( 'M j, Y @ G:i', 'Harmony' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_id ) ) ),
		10 => sprintf( __( 'Event draft updated. <a target="_blank" href="%s">Preview event</a>', 'Harmony' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ) )
	);

	$messages['gallery'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Gallery updated. <a href="%s">View gallery</a>', 'Harmony' ), esc_url( get_permalink( $post_id ) ) ),
		2 => __( 'Custom field updated.', 'Harmony' ),
		3 => __( 'Custom field deleted.', 'Harmony' ),
		4 => __( 'Gallery updated.', 'Harmony' ),
		/* translators: %s: date and time of the revision */
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Gallery restored to revision from %s', 'Harmony' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Gallery published. <a href="%s">View gallery</a>', 'Harmony' ), esc_url( get_permalink( $post_id ) ) ),
		7 => __( 'Gallery saved.', 'Harmony' ),
		8 => sprintf( __( 'Gallery submitted. <a target="_blank" href="%s">Preview gallery</a>', 'Harmony' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ) ),
		9 => sprintf( __( 'Gallery scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview gallery</a>', 'Harmony' ),
		  // translators: Publish box date format, see http://php.net/date
		  date_i18n( __( 'M j, Y @ G:i', 'Harmony' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_id ) ) ),
		10 => sprintf( __( 'Gallery draft updated. <a target="_blank" href="%s">Preview gallery</a>', 'Harmony' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ) )
	);

	return $messages;
}

add_action( 'add_meta_boxes', 'et_event_posttype_meta_box' );
function et_event_posttype_meta_box() {
	add_meta_box( 'et_settings_meta_box', __( 'ET Event Settings', 'Harmony' ), 'et_event_settings_meta_box', 'event', 'normal', 'high' );
	add_meta_box( 'et_settings_meta_box', __( 'ET Gallery Settings', 'Harmony' ), 'et_gallery_settings_meta_box', 'gallery', 'normal', 'high' );
	add_meta_box( 'et_settings_meta_box', __( 'ET Settings', 'Harmony' ), 'et_post_settings_meta_box', 'post', 'normal', 'high' );
}

function et_event_settings_meta_box() {
	$post_id = (int) get_the_ID();

	$default_time_format = get_option( 'time_format' );

	$event_start_date = ( $start_date = get_post_meta( $post_id, '_et_event_date', true ) ) && '' != $start_date ? $start_date : time();
	$event_start_time = date( $default_time_format, $event_start_date );
	$event_end_date = ( $end_date = get_post_meta( $post_id, '_et_event_enddate', true ) ) && '' != $end_date ? $end_date : $event_start_date;
	$event_end_time = date( $default_time_format, $event_end_date );
	$event_location = get_post_meta( $post_id, '_et_event_location', true );
	$et_event_venue = get_post_meta( $post_id, '_et_event_venue', true );
	$et_event_price = get_post_meta( $post_id, '_et_event_price', true );
	$et_purchase_link = get_post_meta( $post_id, '_et_purchase_link', true );

	$date_format = apply_filters( 'et_event_settings_date_format', 'D, M d, Y' );

	wp_nonce_field( basename( __FILE__ ), 'et_settings_nonce' );
?>
	<p>
		<label for="et_event_startdate"><?php esc_html_e( 'Event Start Date', 'Harmony' ); ?>: </label>
		<input type="text" name="et_event_startdate" id="et_event_startdate" class="et_event_date" value="<?php echo esc_attr( date( $date_format, $event_start_date ) ); ?>" />
	</p>
	<p>
		<label for="et_event_starttime"><?php esc_html_e( 'Event Start Time', 'Harmony' ); ?>: </label>
		<input type="text" name="et_event_starttime" id="et_event_starttime" class="et_event_time" value="<?php echo esc_attr( $event_start_time ); ?>" />
	</p>
	<p>
		<label for="et_event_enddate"><?php esc_html_e( 'Event End Date', 'Harmony' ); ?>: </label>
		<input type="text" name="et_event_enddate" id="et_event_enddate" class="et_event_date" value="<?php echo esc_attr( date( $date_format, $event_end_date ) ); ?>" />
	</p>
	<p>
		<label for="et_event_endtime"><?php esc_html_e( 'Event End Time', 'Harmony' ); ?>: </label>
		<input type="text" name="et_event_endtime" id="et_event_endtime" class="et_event_time" value="<?php echo esc_attr( $event_end_time ); ?>" />
	</p>
	<p>
		<label for="et_event_location"><?php esc_html_e( 'Event Location', 'Harmony' ); ?>: </label>
		<input type="text" name="et_event_location" id="et_event_location" class="regular-text" value="<?php echo esc_attr( $event_location ); ?>" />
	</p>
	<p>
		<label for="et_event_venue"><?php esc_html_e( 'Event Venue', 'Harmony' ); ?>: </label>
		<input type="text" name="et_event_venue" id="et_event_venue" class="regular-text" value="<?php echo esc_attr( $et_event_venue ); ?>" />
	</p>
	<p>
		<label for="et_event_price"><?php esc_html_e( 'Event Price', 'Harmony' ); ?>: </label>
		<input type="text" name="et_event_price" id="et_event_price" class="regular-text" value="<?php echo esc_attr( $et_event_price ); ?>" />
	</p>
	<p>
		<label for="et_purchase_link"><?php esc_html_e( 'Purchase Link', 'Harmony' ); ?>: </label>
		<input type="text" name="et_purchase_link" id="et_purchase_link" class="regular-text" value="<?php echo esc_attr( $et_purchase_link ); ?>" />
	</p>
<?php
}

function et_gallery_settings_meta_box() {
	$post_id = (int) get_the_ID();

	$gallery_date = ( $date = get_post_meta( $post_id, '_et_gallery_date', true ) ) && '' != $date ? $date : time();

	$date_format = apply_filters( 'et_event_settings_date_format', 'D, M d, Y' );

	wp_nonce_field( basename( __FILE__ ), 'et_settings_nonce' );
?>
	<p>
		<label for="et_gallery_date"><?php esc_html_e( 'Gallery Date', 'Harmony' ); ?>: </label>
		<input type="text" name="et_gallery_date" id="et_gallery_date" class="et_event_date" value="<?php echo esc_attr( date( $date_format, $gallery_date ) ); ?>" />
	</p>
<?php
	$et_media_query = new WP_Query(
		array(
			'post_type' 		=> 'attachment',
			'post_status' 		=> 'inherit',
			'post_mime_type' 	=> 'image',
			'posts_per_page'	=> 15,
		)
	);

	$et_used_images = get_post_meta( $post_id, '_et_used_images', true );

	echo '<div class="et_settings_box">';
		echo '<h2>' . __( 'Currently used images', 'Harmony' ) . '</h2>';
		echo '<p id="et_no_images">' . __( 'Please, add some images', 'Harmony' ) . '</p>';
		echo '<ul id="et_used_images" style="overflow: hidden; ">';
			if ( $et_used_images ){
				foreach( $et_used_images as $et_used_media ){
					if ( is_numeric( $et_used_media ) ) {
						$saved_media = wp_get_attachment_image( $et_used_media );
						if ( '' != $saved_media )
							echo	'<li data-attachment_id="' . esc_attr( $et_used_media  ) . '" style="float: left; margin: 0 10px 10px 0;">'
										. $saved_media
										. '<span class="et_delete">x</span> <span class="et_image_edit">' . __('Edit','Harmony') . '</span>'
										. '<div class="et_image_options">'
											. '<input type="hidden" name="et_used_image_id[]" value="' . esc_attr( $et_used_media  ) . '">'
											. '<a href="#" class="et_image_save">' . __( 'Save', 'Harmony' ) . '</a>'
										. '</div>'
									. '</li>';
					}
				}
			}
		echo '</ul>';
	echo '</div>';

	echo '<div class="et_settings_box et_last_box">';
		echo '<h2>' . __( 'Add image(s)', 'Harmony' ) . '</h2>';

		echo '<ul id="et_available_images" style="overflow: hidden; ">';
			foreach ($et_media_query->posts as $et_attachment) {
				$added_class = ( $et_used_images && array_key_exists( $et_attachment->ID, $et_used_images ) ) ? ' class="et_added"' : '';
				echo '<li data-attachment_title="' . esc_attr( $et_attachment->post_title ) . '" data-attachment_description="' . esc_attr( $et_attachment->post_content ) . '" data-attachment_id="' . esc_attr( $et_attachment->ID ) . '"' . $added_class . '>' . wp_get_attachment_image( $et_attachment->ID ) . '<span class="et_delete">x</span> <span class="et_image_edit">' . __( 'Edit', 'Harmony' ) . '</span>' . '</li>';
			}
		echo '</ul>';

		if ( $et_media_query->max_num_pages > 1 ){
			echo '<div id="et_attachments_pagination">';
				for ( $i=1; $i <= $et_media_query->max_num_pages; $i++ ){
					echo '<a href="#"' . ( 1 == $i ? ' class="et_active_page"' : '' ) . '>' . $i . '</a>';
				}
			echo '</div>';
		}
	echo '</div>';

	wp_reset_postdata();
}

function et_post_settings_meta_box() {
	$post_id = (int) get_the_ID();
	wp_nonce_field( basename( __FILE__ ), 'et_settings_nonce' );
?>
	<p>
		<label for="et_full_post" class="selectit"><input name="et_full_post" type="checkbox" id="et_full_post" <?php checked( get_post_meta( $post_id, '_et_full_post', true ), 'on' ); ?>> <?php esc_html_e( 'Hide Sidebar', 'Harmony' ); ?></label>
	</p>
	<div id="et_audio_post_settings">
		<p>
			<label for="et_audio_mp3"><?php esc_html_e( 'Mp3 File URL', 'Harmony' ); ?>: </label>
			<input type="text" name="et_audio_mp3" id="et_audio_mp3" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post_id, '_et_audio_mp3', true ) ); ?>" />
		</p>
		<p>
			<label for="et_audio_ogg"><?php esc_html_e( 'Ogg File Url', 'Harmony' ); ?>: </label>
			<input type="text" name="et_audio_ogg" id="et_audio_ogg" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post_id, '_et_audio_ogg', true ) ); ?>" />
		</p>
		<p>
			<label for="et_audio_album_title"><?php esc_html_e( 'Album Title', 'Harmony' ); ?>: </label>
			<input type="text" name="et_audio_album_title" id="et_audio_album_title" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post_id, '_et_audio_album_title', true ) ); ?>" />
		</p>
	</div>
<?php
}

add_action( 'save_post', 'et_metabox_settings_save_details', 10, 2 );
function et_metabox_settings_save_details( $post_id, $post ){
	global $pagenow;
	$date_format = apply_filters( 'et_event_settings_date_format', 'D, M d, Y' );

	if ( 'post.php' != $pagenow ) return $post_id;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	if ( !isset( $_POST['et_settings_nonce'] ) || ! wp_verify_nonce( $_POST['et_settings_nonce'], basename( __FILE__ ) ) )
        return $post_id;

	switch ( $post->post_type ) {
		case 'post' :
			if ( isset( $_POST['et_audio_mp3'] ) )
				update_post_meta( $post_id, '_et_audio_mp3', sanitize_text_field( $_POST['et_audio_mp3'] ) );
			else
				delete_post_meta( $post_id, '_et_audio_mp3' );

			if ( isset( $_POST['et_audio_ogg'] ) )
				update_post_meta( $post_id, '_et_audio_ogg', sanitize_text_field( $_POST['et_audio_ogg'] ) );
			else
				delete_post_meta( $post_id, '_et_audio_ogg' );

			if ( isset( $_POST['et_audio_album_title'] ) )
				update_post_meta( $post_id, '_et_audio_album_title', sanitize_text_field( $_POST['et_audio_album_title'] ) );
			else
				delete_post_meta( $post_id, '_et_audio_album_title' );

			if ( isset( $_POST['et_full_post'] ) )
				update_post_meta( $post_id, '_et_full_post', 'on' );
			else
				delete_post_meta( $post_id, '_et_full_post' );

			break;
		case 'event' :
			if ( isset( $_POST['et_event_startdate'] ) ) {
				$unix_start_date = strtotime( $_POST['et_event_startdate'] . $_POST['et_event_starttime'] );
				update_post_meta( $post_id, '_et_event_date', sanitize_text_field( $unix_start_date ) );
			} else {
				delete_post_meta( $post_id, '_et_event_date' );
			}

			if ( isset( $_POST['et_event_enddate'] ) ) {
				// make sure the event end date is valid
				$event_enddate = strtotime( $_POST['et_event_enddate'] ) >= strtotime( $_POST['et_event_startdate'] ) ? $_POST['et_event_enddate'] : $_POST['et_event_startdate'];

				$unix_end_date = strtotime( $event_enddate . $_POST['et_event_endtime'] );
				update_post_meta( $post_id, '_et_event_enddate', sanitize_text_field( $unix_end_date ) );
			} else {
				delete_post_meta( $post_id, '_et_event_enddate' );
			}

			if ( isset( $_POST['et_event_location'] ) )
				update_post_meta( $post_id, '_et_event_location', sanitize_text_field( $_POST['et_event_location'] ) );
			else
				delete_post_meta( $post_id, '_et_event_location' );

			if ( isset( $_POST['et_event_venue'] ) )
				update_post_meta( $post_id, '_et_event_venue', sanitize_text_field( $_POST['et_event_venue'] ) );
			else
				delete_post_meta( $post_id, '_et_event_venue' );

			if ( isset( $_POST['et_event_price'] ) )
				update_post_meta( $post_id, '_et_event_price', sanitize_text_field( $_POST['et_event_price'] ) );
			else
				delete_post_meta( $post_id, '_et_event_price' );

			if ( isset( $_POST['et_purchase_link'] ) )
				update_post_meta( $post_id, '_et_purchase_link', sanitize_text_field( $_POST['et_purchase_link'] ) );
			else
				delete_post_meta( $post_id, '_et_purchase_link' );

			break;
		case 'gallery' :
			if ( isset( $_POST['et_used_image_id'] ) )
				update_post_meta( $post_id, '_et_used_images', array_map( 'intval', $_POST['et_used_image_id'] ) );
			else
				delete_post_meta( $post_id, '_et_used_images' );

			if ( isset( $_POST['et_gallery_date'] ) )
				update_post_meta( $post_id, '_et_gallery_date', strtotime( $_POST['et_gallery_date'] ) );
			else
				update_post_meta( $post_id, '_et_gallery_date', strtotime( time() ) );

			break;
	}
}

add_filter( 'manage_edit-event_columns', 'et_event_edit_columns' );
function et_event_edit_columns( $columns ) {
	$columns = array(
		'cb' 				=> '<input type="checkbox" />',
		'title' 			=> __( 'Title', 'Harmony' ),
		'et_event_date' 	=> __( 'Date', 'Harmony' ),
		'et_event_location' => __( 'Location', 'Harmony' ),
		'et_event_category' => __( 'Category', 'Harmony' )
	);

	return $columns;
}

add_action( 'manage_posts_custom_column', 'et_event_custom_columns' );
function et_event_custom_columns( $column ) {
	$date_format = apply_filters( 'et_event_settings_date_format', 'D, M d, Y' );
	$default_time_format = get_option( 'time_format' );
	$custom_fields = get_post_custom();

	switch ( $column ) {
		case 'et_event_date' :
			$event_start_date = $custom_fields['_et_event_date'][0];
			$event_end_date = $custom_fields['_et_event_enddate'][0];

			if ( '' != $event_start_date && '' != $event_end_date ){
				printf( '<span class="et_event_label">%s: </span>%s - %s <br/>',
					__( 'Start Date', 'Harmony' ),
					esc_html( date( $date_format, $event_start_date ) ),
					esc_html( date( $default_time_format, $event_start_date ) )
				);
				printf( '<span class="et_event_end_date"><span class="et_event_label">%s: </span>%s - %s </span>',
					__( 'End Date', 'Harmony' ),
					esc_html( date( $date_format, $event_end_date ) ),
					esc_html( date( $default_time_format, $event_end_date ) )
				);
			}
			break;
		case 'et_event_location' :
			if ( '' != $custom_fields['_et_event_location'][0] )
				echo esc_html( $custom_fields['_et_event_location'][0] );
			else
				_e( 'None', 'Harmony' );
			break;
		case 'et_event_category' :

			$et_event_categories = get_the_terms( get_the_ID(), 'event_category' );

			if ( !empty( $et_event_categories ) ) {
				$out = array();
				foreach ( $et_event_categories as $et_event_category ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => 'event', 'event_category' => $et_event_category->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $et_event_category->name, $et_event_category->term_id, 'event_category', 'display' ) )
					);
				}

				echo join( ', ', $out );
			} else {
				_e( 'None', 'Harmony' );
			}

			break;
	}
}

add_filter( 'manage_edit-gallery_columns', 'et_gallery_edit_columns' );
function et_gallery_edit_columns( $columns ) {
	$columns = array(
		'cb' 					=> '<input type="checkbox" />',
		'title' 				=> __( 'Title', 'Harmony' ),
		'et_gallery_date' 		=> __( 'Date', 'Harmony' ),
		'et_gallery_category' 	=> __( 'Category', 'Harmony' )
	);

	return $columns;
}

add_action( 'manage_posts_custom_column', 'et_gallery_custom_columns' );
function et_gallery_custom_columns( $column ) {
	$date_format = et_get_option( 'harmony_date_format', 'M j, Y' );
	$custom_fields = get_post_custom();

	switch ( $column ) {
		case 'et_gallery_date' :
			$gallery_date = $custom_fields['_et_gallery_date'][0];

			if ( '' != $gallery_date )
				echo esc_html( date( $date_format, $gallery_date ) );

			break;
		case 'et_gallery_category' :
			$et_gallery_categories = get_the_terms( get_the_ID(), 'gallery_category' );

			if ( ! empty( $et_gallery_categories ) ) {
				$out = array();
				foreach ( $et_gallery_categories as $et_gallery_category ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => 'gallery', 'gallery_category' => $et_gallery_category->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $et_gallery_category->name, $et_gallery_category->term_id, 'gallery_category', 'display' ) )
					);
				}

				echo join( ', ', $out );
			} else {
				_e( 'None', 'Harmony' );
			}

			break;
	}
}

add_action( 'wp_ajax_et_show_attachments_page', 'et_gallery_show_attachments_page' );
function et_gallery_show_attachments_page() {
	if ( ! wp_verify_nonce( $_POST['et_settings_nonce'], basename( __FILE__ ) ) ) die(-1);

	$et_page = intval( $_POST['et_page'] );

	$et_media_query = new WP_Query(
		array(
			'post_type' 		=> 'attachment',
			'post_status' 		=> 'inherit',
			'post_mime_type' 	=> 'image',
			'posts_per_page' 	=> 15,
			'offset' 			=> ( 15 * ( $et_page - 1 ) ),
		)
	);

	foreach ( $et_media_query->posts as $et_attachment ) {
		echo '<li data-attachment_title="' . esc_attr( $et_attachment->post_title ) . '" data-attachment_description="' . esc_attr( $et_attachment->post_content ) . '" data-attachment_id="' . esc_attr( $et_attachment->ID ) . '">' . wp_get_attachment_image( $et_attachment->ID ) . '<span class="et_delete">x</span> <span class="et_image_edit">' . __( 'Edit', 'Harmony' ) . '</span>' . '</li>';
	}

	die();
}

add_action( 'admin_enqueue_scripts', 'et_admin_scripts_styles', 10, 1 );
function et_admin_scripts_styles( $hook ) {
	global $typenow;

	$template_dir = get_template_directory_uri();

	if ( isset( $typenow ) && 'event' == $typenow && 'edit.php' == $hook )
		wp_enqueue_style( 'et_event_columns', $template_dir . '/css/et_edit_screen.css' );

	if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) return;

	if ( isset( $typenow ) && in_array( $typenow, array( 'event', 'gallery' ) ) ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'et_datepicker_custom', $template_dir . '/js/admin_custom.js', array( 'jquery-ui-datepicker' ) );
		wp_enqueue_style( 'jquery.ui.theme', $template_dir . '/css/smoothness/jquery-ui-1.9.2.custom.min.css' );
		if ( 'gallery' == $typenow ) {
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'et_upload_images', $template_dir . '/js/et-upload_images.js', array( 'jquery' ) );
			wp_enqueue_style( 'et_upload_images_css', $template_dir . '/css/et-upload_images.css' );
		}
	}

	if ( in_array( $typenow, array( 'post', 'event' ) ) ) {
		wp_enqueue_style( 'et_settings_box', $template_dir . '/css/et_settings.css' );
		if ( 'post' == $typenow ) wp_enqueue_script( 'et_audio_settings', $template_dir . '/js/et_audio_settings.js', array( 'jquery' ) );
	}
}

function et_attach_bg_images() {
	$template_directory = get_template_directory_uri();
?>
	<style>
		#main-header { background-image: url(<?php echo esc_html( et_get_option( 'harmony_header_bg_image', $template_directory .  '/images/bg.jpg' ) ); ?>); }
		#songs { background-image: url(<?php echo esc_html( et_get_option( 'harmony_songs_bg_image', $template_directory .  '/images/bg.jpg' ) ); ?>); }
		#media-gallery { background-image: url(<?php echo esc_html( et_get_option( 'harmony_media_bg_image', $template_directory .  '/images/bg.jpg' ) ); ?>); }
	</style>
<?php
}

if ( function_exists( 'get_custom_header' ) ) {
	// compatibility with versions of WordPress prior to 3.4

	add_action( 'customize_register', 'et_harmony_customize_register' );
	function et_harmony_customize_register( $wp_customize ) {
		$google_fonts = et_get_google_fonts();

		$font_choices = array();
		$font_choices['none'] = 'Default Theme Font';
		foreach ( $google_fonts as $google_font_name => $google_font_properties ) {
			$font_choices[ $google_font_name ] = $google_font_name;
		}

		$wp_customize->add_section( 'et_google_fonts' , array(
			'title'		=> __( 'Fonts', 'Harmony' ),
			'priority'	=> 50,
		) );

		$wp_customize->add_setting( 'et_harmony[link_color]', array(
			'default'		=> '#FFA300',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_harmony[link_color]', array(
			'label'		=> __( 'Link Color', 'Harmony' ),
			'section'	=> 'colors',
			'settings'	=> 'et_harmony[link_color]',
		) ) );

		$wp_customize->add_setting( 'et_harmony[font_color]', array(
			'default'		=> '#3D5054',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_harmony[font_color]', array(
			'label'		=> __( 'Main Font Color', 'Harmony' ),
			'section'	=> 'colors',
			'settings'	=> 'et_harmony[font_color]',
		) ) );

		$wp_customize->add_setting( 'et_harmony[logo_color]', array(
			'default'		=> '#ffffff',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_harmony[logo_color]', array(
			'label'		=> __( 'Logo Color', 'Harmony' ),
			'section'	=> 'colors',
			'settings'	=> 'et_harmony[logo_color]',
		) ) );

		$wp_customize->add_setting( 'et_harmony[heading_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options'
		) );

		$wp_customize->add_control( 'et_harmony[heading_font]', array(
			'label'		=> __( 'Header Font', 'Harmony' ),
			'section'	=> 'et_google_fonts',
			'settings'	=> 'et_harmony[heading_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) );

		$wp_customize->add_setting( 'et_harmony[body_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options'
		) );

		$wp_customize->add_control( 'et_harmony[body_font]', array(
			'label'		=> __( 'Body Font', 'Harmony' ),
			'section'	=> 'et_google_fonts',
			'settings'	=> 'et_harmony[body_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) );
	}

	add_action( 'customize_preview_init', 'et_harmony_customize_preview_js' );
	function et_harmony_customize_preview_js() {
		wp_enqueue_script( 'harmony-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), false, true );
	}

	add_action( 'wp_head', 'et_harmony_add_customizer_css' );
	add_action( 'customize_controls_print_styles', 'et_harmony_add_customizer_css' );
	function et_harmony_add_customizer_css(){ ?>
		<style>
			a { color: <?php echo esc_html( et_get_option( 'link_color', '#FFA300' ) ); ?>; }
			body { color: <?php echo esc_html( et_get_option( 'font_color', '#3D5054' ) ); ?>; }
			#main-header h1, #main-header h2 { color: <?php echo esc_html( et_get_option( 'logo_color', '#ffffff' ) ); ?>; }
		<?php
			$et_gf_heading_font = sanitize_text_field( et_get_option( 'heading_font', 'none' ) );
			$et_gf_body_font = sanitize_text_field( et_get_option( 'body_font', 'none' ) );

			if ( 'none' != $et_gf_heading_font || 'none' != $et_gf_body_font ) :

				if ( 'none' != $et_gf_heading_font )
					et_gf_attach_font( $et_gf_heading_font, 'h1, h2, h3, h4, h5, h6' );

				if ( 'none' != $et_gf_body_font )
					et_gf_attach_font( $et_gf_body_font, 'body' );

			endif;
		?>
		</style>
	<?php }

	add_action( 'customize_controls_print_footer_scripts', 'et_load_google_fonts_scripts' );
	function et_load_google_fonts_scripts() {
		wp_enqueue_script( 'et_google_fonts', get_template_directory_uri() . '/epanel/google-fonts/et_google_fonts.js', array( 'jquery' ), '1.0', true );
	}

	add_action( 'customize_controls_print_styles', 'et_load_google_fonts_styles' );
	function et_load_google_fonts_styles() {
		wp_enqueue_style( 'et_google_fonts_style', get_template_directory_uri() . '/epanel/google-fonts/et_google_fonts.css', array(), null );
	}
}

// overwrite woocommerce_breadcrumb function to change wrap_before and wrap_after arguments
function woocommerce_breadcrumb( $args = array() ) {
	$defaults = array(
		'delimiter'  => ' &rsaquo; ',
		'wrap_before'  => '<div id="breadcrumbs" itemprop="breadcrumb">' . '<div class="container">',
		'wrap_after' => '</div>' . '</div>',
		'before'   => '',
		'after'   => '',
		'home'    => null,
	);

	$args = wp_parse_args( $args, $defaults  );

	if ( function_exists( 'WC' ) ) {
		wc_get_template( 'global/breadcrumb.php', $args );
	} else {
		woocommerce_get_template( 'shop/breadcrumb.php', $args );
	}
}

function woocommerce_get_sidebar() {
	if ( ! et_is_shop_page_fullwidth() ) {
		if ( function_exists( 'WC' ) ) {
			wc_get_template( 'global/sidebar.php' );
		} else {
			woocommerce_get_template( 'shop/sidebar.php' );
		}
	}
}

// detects if shop page or a product page should display as full width
if ( ! function_exists( 'et_is_shop_page_fullwidth' ) ) :
function et_is_shop_page_fullwidth() {
	$shop_pages_fullwidth 		= 'on' == et_get_option( 'harmony_shop_pages_fullwidth', 'on' ) ? true : false;
	$product_pages_fullwidth 	= 'on' == et_get_option( 'harmony_product_pages_fullwidth', 'on' ) ? true : false;

	if ( ( $shop_pages_fullwidth && is_woocommerce() && ! is_product() ) || ( $product_pages_fullwidth && is_product() ) ) return true;

	return false;
}
endif;

// displays 190x190px image on shop pages and homepage
function et_template_loop_product_thumbnail() {
	$thumb = '';
	$width = (int) apply_filters( 'et_product_image_width', 190 );
	$height = (int) apply_filters( 'et_product_image_height', 190 );
	$classtext = '';
	$titletext = get_the_title();
	$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Product' );
	$thumb = $thumbnail["thumb"];

	print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext );
}

// displays Product Info button on the bottom of each post
function et_after_shop_loop_item() { ?>
	<br />
	<a href="<?php the_permalink(); ?>" class="product-info"><?php esc_html_e( 'Viac Info', 'Harmony' ); ?></a>
<?php
}

// these 2 functions fix the theme layout on shop and product pages
function et_theme_wrapper_start() {
	$et_fullwidth = et_is_shop_page_fullwidth();
?>
	<div id="main-area">
		<div class="container">
			<div id="content-area" class="clearfix<?php if ( $et_fullwidth ) echo esc_attr( ' fullwidth' ); ?>">
			<?php if ( ! $et_fullwidth ) : ?>
				<div id="left-area">
			<?php endif; ?>
<?php
}

function et_theme_wrapper_end() {
	if ( ! et_is_shop_page_fullwidth() ) : ?>
				</div> <!-- #left-area -->
				<?php do_action( 'woocommerce_sidebar' ); ?>
	<?php endif; ?>
			</div> <!-- #content-area -->
		</div> <!-- .container -->
	</div> <!-- #main-area -->
<?php
}

function et_add_ios_class( $classes ){
	if ( strstr( $_SERVER['HTTP_USER_AGENT'], 'iPad' ) || strstr( $_SERVER['HTTP_USER_AGENT'], 'iPhone' ) )
		$classes[] = 'et_ios';

	return $classes;
}

function et_epanel_custom_colors_css(){
	global $shortname; ?>

	<style type="text/css">
		body { color: #<?php echo esc_html(et_get_option($shortname.'_color_mainfont')); ?>; }
		#content-area a { color: #<?php echo esc_html(et_get_option($shortname.'_color_mainlink')); ?>; }
		ul.nav li a { color: #<?php echo esc_html(et_get_option($shortname.'_color_pagelink')); ?> !important; }
		ul.nav > li.current_page_item > a, ul#top-menu > li:hover > a, ul.nav > li.current-cat > a { color: #<?php echo esc_html(et_get_option($shortname.'_color_pagelink_active')); ?>; }
		h1, h2, h3, h4, h5, h6, h1 a, h2 a, h3 a, h4 a, h5 a, h6 a { color: #<?php echo esc_html(et_get_option($shortname.'_color_headings')); ?>; }

		#sidebar a { color:#<?php echo esc_html(et_get_option($shortname.'_color_sidebar_links')); ?>; }
		.footer-widget { color:#<?php echo esc_html(et_get_option($shortname.'_footer_text')); ?> }
		#footer a, ul#bottom-menu li a { color:#<?php echo esc_html(et_get_option($shortname.'_color_footerlinks')); ?> }
	</style>

<?php }

function et_add_woocommerce_class_to_homepage( $classes ) {
	if ( is_home() ) $classes[] = 'woocommerce';

	return $classes;
}