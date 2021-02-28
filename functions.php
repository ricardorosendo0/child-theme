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