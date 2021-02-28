<?php
function divichild_enqueue_scripts() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'divichild_enqueue_scripts' );

//Login Perosnalizado//
function my_loginlogo() {
    echo '<style type="text/css">
      body.login.login-action-login.wp-core-ui.locale-es-es {
    background-image: url(/wp-content/fondo.png) !important;
    background-size: cover;
    background-repeat: no-repeat;
}
	  h1 a {
        background-image: url(/wp-content/logo.png) !important;
		background-size: 240px !important;
		width: 240px !important;
      }
    </style>';
  }
  add_action('login_head', 'my_loginlogo');
  
  //direcionamiento del login 
  function my_loginURL() {
      return '/';
  }
  add_filter('login_headerurl', 'my_loginURL');

// END ENQUEUE PARENT ACTION

 // Quitar intervalo de precios en productos variables de WooCommerce
function quitar_intervalo( $price, $product ) {
    // Precio normal
    $prices = array( $product->get_variation_price( 'min', true ), $product->get_variation_price( 'max', true ) );
    $price = $prices[0] !== $prices[1] ? sprintf( __( 'Desde: %1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );
 
    // Precio rebajado
    $prices = array( $product->get_variation_regular_price( 'min', true ), $product->get_variation_regular_price( 'max', true ) );
    sort( $prices );
    $saleprice = $prices[0] !== $prices[1] ? sprintf( __( 'Desde: %1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );
 
    if ( $price !== $saleprice ) {
        $price = '<del>' . $saleprice . '</del> <ins>' . $price . '</ins>';
    }
     
    return $price;
}
 
add_filter( 'woocommerce_variable_sale_price_html', 'quitar_intervalo', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'quitar_intervalo', 10, 2 );
//Fin de quitar precios


/* Botón de añadir al carrito en tienda */
add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20 );
