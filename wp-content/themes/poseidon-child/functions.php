<?php

function my_poseidon_footer_text_before() {
	ob_start();
}

function my_poseidon_footer_text_after() {
	ob_clean();
}

function my_poseidon_footer_text() {
	?>

	<?php dynamic_sidebar( 'home_right_1' ); ?>
	<span class="credit-link">
		Actualités By Night
		<p>Où sortir partout en France</p>
	</span>

	<?php
}

add_action( 'poseidon_footer_text', 'my_poseidon_footer_text_before');
add_action( 'poseidon_footer_text', 'my_poseidon_footer_text_after', 15);
add_action( 'poseidon_footer_text', 'my_poseidon_footer_text', 16);

// Load translation files from your child theme instead of the parent theme
load_theme_textdomain( 'poseidon', get_stylesheet_directory() . '/languages' );

/**
 * Register our sidebars and widgetized areas.
 *
 */
function arphabet_widgets_init() {

	register_sidebar( array(
		'name'          => 'Home right sidebar',
		'id'            => 'home_right_1',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );

}
add_action( 'widgets_init', 'arphabet_widgets_init' );