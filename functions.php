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

//Limita el carrito de WooCommerce a un único producto
	add_filter( 'woocommerce_add_cart_item_data', 'mk_only_one_item_in_cart', 10, 1 );

	function mk_only_one_item_in_cart( $cartItemData ) {
		wc_empty_cart();

		return $cartItemData;
	}

// Salir si se accede directamente// 

	add_filter( 'woocommerce_cart_item_quantity', 'wc_cart_item_quantity', 10, 3 );
	function wc_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ){
	    if( is_cart() ){
	        $product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] );
	    }
	    return $product_quantity;
	}

	if ( !defined( 'ABSPATH' ) ) exit;



// Cambia el rol de usuario del cliente a "Cliente premium" cuando compra uno o varios productos determinados usando los siguentes estados:
// woocommerce_order_status_pending
// woocommerce_order_status_failed
// woocommerce_order_status_on-hold
// woocommerce_order_status_processing
// woocommerce_order_status_completed
// woocommerce_order_status_refunded
// woocommerce_order_status_cancelled 

	add_action( 'init', 'add_premium_customer_role' );
	function add_premium_customer_role(){

	    	$customer_role = get_role( 'customer' );
	    	add_role( 'premium_customer', __( 'Cliente Premium', 'woocommerce' ), $customer_role->capabilities );
	}

	add_action( 'woocommerce_order_status_completed', 'change_user_role_by_product_purchased', 10, 2 );
	function change_user_role_by_product_purchased( $order_id, $posted ){
		$order = new WC_Order( $order_id );
		$new_role = 'premium_customer';
		$products_list = array( 177, 180, 191 ); // Aquí defines la lista de productos para cambiar del rol cuando cumplle el estado solicitado

		// Obtiene el email del pedido
		if( '3.0.0' <= WC()->version ){

			$billing_email = $order->get_billing_email();
		}else{

			// Mantiene compatibilidad con WooCommerce <= 2.6.x
			$order_meta = get_post_meta( $order_id );
			$billing_email = $order_meta[ '_billing_email' ][0];
		}

		// Comprueba si el usuario ya está registrado con ese email
		if( email_exists( $billing_email ) ) {
			foreach ( $order->get_items() as $product ) {
				if ( in_array( $product[ 'product_id' ], $products_list ) ) {
	      				$user = get_user_by( 'email', $billing_email );
	      				$user->set_role( $new_role );
				}
			}
	   	}	
	}


	// Mostrar campos personalizados fecha en Woocomerce
	function mostrar_campo_fecha_woo() {
		global $product;
		// reemplaza el nombre del campo personalizado por el tuyo
		$fecha = get_post_meta( $product->id, 'fecha', true );
		$fecha = str_replace( '_', ' ', $fecha );
		if ( ! empty( $fecha ) ) {
			echo '<div class="meta-cf"><i class="far fa-clock"></i>' . ucwords( $fecha ) . '</div>';
		}
	}
	add_action( 'woocommerce_after_shop_loop_item_title', 'mostrar_campo_fecha_woo', 9 );

//Fin del evento//

// Actualiza automáticamente el estado de los pedidos a COMPLETADO
add_action( 'woocommerce_order_status_processing', 'actualiza_estado_pedidos_a_completado' );
add_action( 'woocommerce_order_status_on-hold', 'actualiza_estado_pedidos_a_completado' );
function actualiza_estado_pedidos_a_completado( $order_id ) {
    global $woocommerce;
    
    //ID's de las pasarelas de pago a las que afecta: bacs: transferencias, cod: pago contra entrega.
    $paymentMethods = array( 'bacs', 'cheque', 'cod', 'paypal' );
    
    if ( !$order_id ) return;
    $order = new WC_Order( $order_id );

    if ( !in_array( $order->payment_method, $paymentMethods ) ) return;
    $order->update_status( 'completed' );
}


// cambiar la url del login, Nota renombrar el wp-login.php por system.php
function security_wp_login_filter( $url, $path, $orig_scheme ) {
	//se cambia el nombre system por el que quieras
    return preg_replace( '/wp-login\.php/', 'system\.php?/', $url, 1 );
}
function security_wp_login_redirect() {
	//se cambia el nombre system por el que quieras
    if ( strpos( $_SERVER['REQUEST_URI'], 'system\.php?/' ) === true ) {
        wp_redirect( site_url() );
        exit();
    }
}
add_filter( 'site_url', 'security_wp_login_filter', 10, 3 );
add_action( 'login_init', 'security_wp_login_redirect' );
