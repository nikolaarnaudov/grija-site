<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $request->get_location() ) :
	?>
	<div class="hp-listing__attribute hp-listing__attribute--location">
		<i class="hp-icon fas fa-map-marker-alt"></i>
		<a href="<?php echo esc_url( hivepress()->router->get_url( 'location_view_page', [ 'latitude'  => $request->get_latitude(), 'longitude' => $request->get_longitude() ] ) ); ?>" target="_blank"><?php echo esc_html( $request->get_location() ); ?></a>
	</div>
	<?php
endif;
