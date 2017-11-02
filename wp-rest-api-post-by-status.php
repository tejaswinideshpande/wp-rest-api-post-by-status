<?php /*********************************************************
Plugin Name: REST API endpoints for Posts by Status
Plugin URI: localhost/test
Description: REST API endpoints for Posts by Status
Version: 1.0.0
Author: Tejaswini Deshpande
Author URI: http://tejaswinideshpande.com/
Text Domain: wppbs
Wordpress version supported: 4.5 and above
*----------------------------------------------------------------*
* Copyright 2017 Staenz Media  (email : tedeshpa@gmail.com)
*****************************************************************/
// This is a wrong way to separate me buddy...I am so dependent on WordPress! Shoo away!
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
 
class Wppbs_Draft_Route extends WP_REST_Controller {
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    $version = '1';
    $namespace = 'wppbs/v' . $version;
    $base = 'draft';
    register_rest_route( $namespace, '/' . $base, array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args'            => array(
 
        ),
      ),
      array(
        'methods'         => WP_REST_Server::CREATABLE,
        'callback'        => array( $this, 'create_item' ),
        'permission_callback' => array( $this, 'create_item_permissions_check' ),
        //'args'            => $this->get_endpoint_args_for_item_schema( true ),
      ),
    ) );
  }
 
  /**
   * Get a collection of items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items( $request ) {
	$args = array( 
				'posts_per_page' => -1,
				'post_status' => 'draft' 
			);
    $items = get_posts( $args ); //do a query, call another class, etc
   //$items = wp_get_current_user();
    $data = array();
    foreach( $items as $item ) {
      //$itemdata = $this->prepare_item_for_response( $item, $request );
     // $data[] = $this->prepare_response_for_collection( $itemdata );
	 $post['ID'] = $item->ID;
	 $post['post_title'] = $item->post_title;
	 $author = get_userdata($item->post_author);
	 $post['post_author_login'] = $author->user_login;
	 $post['post_author_name'] = $author->display_name;
	 $data[] = (object) $post;
    }
 
    return new WP_REST_Response( $data, 200 );
  }
 
  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function create_item( $request ) {
    $item = $this->prepare_item_for_database( $request );
 
    if ( function_exists( 'wp_insert_post')  ) {
      $data['ID'] = wp_insert_post( $item );
	  $data['post_title'] = $item['post_title'];
	  $author = get_userdata($item['post_author']);
	  $data['author_name'] = $author->display_name;
      if ( is_array( $data ) ) {
        return new WP_REST_Response( $data, 200 );
      }
    }
 
    return new WP_Error( 'cant-create', __( 'message', 'text-domain'), array( 'status' => 500 ) );
  }
  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check( $request ) {
	  
    //return true; <--use to make readable by all
    return current_user_can( 'edit_others_posts' );
    //return true;
  }
 
  /**
   * Check if a given request has access to get a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check( $request ) {
    return $this->get_items_permissions_check( $request );
  }
 
  /**
   * Check if a given request has access to create items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check( $request ) {
    return current_user_can( 'edit_others_posts' );
  }
 
 
  /**
   * Prepare the item for create or update operation
   *
   * @param WP_REST_Request $request Request object
   * @return WP_Error|object $prepared_item
   */
  protected function prepare_item_for_database( $request ) {
    // Or via the helper method:
    $title = $request->get_param( 'title' );
    $content = $request->get_param( 'content' );
    $author = $request->get_param( 'author' );
	$post_arr=array(
			'post_title'=> $title,
			'post_content'=> $content,
			'post_author'=> $author,
		);
	$post = (object)$post_arr;
	
	return $post_arr;
  }
 
  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response( $item, $request ) {
    return array();
  }
 
  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params() {
    return array(
      'page'     => array(
        'description'        => 'Current page of the collection.',
        'type'               => 'integer',
        'default'            => 1,
        'sanitize_callback'  => 'absint',
      ),
      'per_page' => array(
        'description'        => 'Maximum number of items to be returned in result set.',
        'type'               => 'integer',
        'default'            => 10,
        'sanitize_callback'  => 'absint',
      ),
      'search'   => array(
        'description'        => 'Limit results to those matching a string.',
        'type'               => 'string',
        'sanitize_callback'  => 'sanitize_text_field',
      ),
    );
  }
}

//For pending posts
class Wppbs_Pending_Route extends WP_REST_Controller {
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    $version = '1';
    $namespace = 'wppbs/v' . $version;
    $base = 'pending';
    register_rest_route( $namespace, '/' . $base, array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args'            => array(
 
        ),
      ),
    ) );
  }
 
  /**
   * Get a collection of items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items( $request ) {
	$args = array( 
				'posts_per_page' => -1,
				'post_status' => 'pending' 
			);
    $items = get_posts( $args ); //do a query, call another class, etc
   //$items = wp_get_current_user();
    $data = array();
    foreach( $items as $item ) {
      //$itemdata = $this->prepare_item_for_response( $item, $request );
     // $data[] = $this->prepare_response_for_collection( $itemdata );
	 $post['ID'] = $item->ID;
	 $post['post_title'] = $item->post_title;
	 $post['preview'] = get_permalink($item->ID);
	 
	 $author = get_userdata($item->post_author);
	 $post['post_author_login'] = $author->user_login;
	 $post['post_author_name'] = $author->display_name;
	 $data[] = (object) $post;
    }
 
    return new WP_REST_Response( $data, 200 );
  }
  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check( $request ) {
	  
    //return true; <--use to make readable by all
    return current_user_can( 'edit_others_posts' );
    //return true;
  }
 
  /**
   * Check if a given request has access to get a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check( $request ) {
    return $this->get_items_permissions_check( $request );
  }
 
  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response( $item, $request ) {
    return array();
  }
 
  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params() {
    return array(
      'page'     => array(
        'description'        => 'Current page of the collection.',
        'type'               => 'integer',
        'default'            => 1,
        'sanitize_callback'  => 'absint',
      ),
      'per_page' => array(
        'description'        => 'Maximum number of items to be returned in result set.',
        'type'               => 'integer',
        'default'            => 10,
        'sanitize_callback'  => 'absint',
      ),
      'search'   => array(
        'description'        => 'Limit results to those matching a string.',
        'type'               => 'string',
        'sanitize_callback'  => 'sanitize_text_field',
      ),
    );
  }
}

//For Scheduled
class Wppbs_Scheduled_Route extends WP_REST_Controller {
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    $version = '1';
    $namespace = 'wppbs/v' . $version;
    $base = 'scheduled';
    register_rest_route( $namespace, '/' . $base, array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args'            => array(
 
        ),
      ),
    ) );
  }
 
  /**
   * Get a collection of items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items( $request ) {
	$args = array( 
				'posts_per_page' => -1,
				'post_status' => 'future' 
			);
    $items = get_posts( $args ); //do a query, call another class, etc
   //$items = wp_get_current_user();
    $data = array();
    foreach( $items as $item ) {
	 $post['ID'] = $item->ID;
	 $post['post_title'] = $item->post_title;
	 $post['publish_at'] = date('l - jS M Y @ h a', strtotime($item->post_date));
	 $post['preview'] = get_permalink($item->ID);
	 
	 $author = get_userdata($item->post_author);
	 $post['post_author_login'] = $author->user_login;
	 $post['post_author_name'] = $author->display_name;
	 
	 $data[] = (object) $post;
    }
 
    return new WP_REST_Response( $data, 200 );
  }
  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check( $request ) {
	  
    //return true; <--use to make readable by all
    return current_user_can( 'edit_others_posts' );
    //return true;
  }
 
  /**
   * Check if a given request has access to get a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check( $request ) {
    return $this->get_items_permissions_check( $request );
  }
 
  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response( $item, $request ) {
    return array();
  }
 
  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params() {
    return array(
      'page'     => array(
        'description'        => 'Current page of the collection.',
        'type'               => 'integer',
        'default'            => 1,
        'sanitize_callback'  => 'absint',
      ),
      'per_page' => array(
        'description'        => 'Maximum number of items to be returned in result set.',
        'type'               => 'integer',
        'default'            => 10,
        'sanitize_callback'  => 'absint',
      ),
      'search'   => array(
        'description'        => 'Limit results to those matching a string.',
        'type'               => 'string',
        'sanitize_callback'  => 'sanitize_text_field',
      ),
    );
  }
}

//For authors
class Wppbs_Authors_Route extends WP_REST_Controller {
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    $version = '1';
    $namespace = 'wppbs/v' . $version;
    $base = 'authors';
    register_rest_route( $namespace, '/' . $base, array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args'            => array(
 
        ),
      ),
    ) );
  }
 
  /**
   * Get a collection of items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items( $request ) {
	$args = array( 
				'role__not_in' => array('subscriber')
			);
    $items = get_users( $args ); //do a query, call another class, etc
    $data = array();
    foreach( $items as $item ) {
	 $author = array( 
		'ID' => $item->ID,
		'user_login' => $item->user_login,
		'user_nicename' => $item->user_nicename,
		'user_email' => $item->user_email,
		'display_name' => $item->display_name,
	 );
	 $data[] = (object) $author;
    }
 
    return new WP_REST_Response( $data, 200 );
  }
 
  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check( $request ) {
	  
    //return true; <--use to make readable by all
    return current_user_can( 'edit_others_posts' );
    //return true;
  }
 
  /**
   * Check if a given request has access to get a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check( $request ) {
    return $this->get_items_permissions_check( $request );
  }
 
  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response( $item, $request ) {
    return array();
  }
 
  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params() {
    return array(
      'page'     => array(
        'description'        => 'Current page of the collection.',
        'type'               => 'integer',
        'default'            => 1,
        'sanitize_callback'  => 'absint',
      ),
      'per_page' => array(
        'description'        => 'Maximum number of items to be returned in result set.',
        'type'               => 'integer',
        'default'            => 10,
        'sanitize_callback'  => 'absint',
      ),
      'search'   => array(
        'description'        => 'Limit results to those matching a string.',
        'type'               => 'string',
        'sanitize_callback'  => 'sanitize_text_field',
      ),
    );
  }
}

// Create an instance of all the custom rest controllers and call register_routes() methods
add_action('rest_api_init', function () {
    $controller = new Wppbs_Draft_Route();
    $controller->register_routes();
	
	$controller = new Wppbs_Pending_Route();
    $controller->register_routes();
	
	$controller = new Wppbs_Scheduled_Route();
    $controller->register_routes();
	
	$controller = new Wppbs_Authors_Route();
    $controller->register_routes();
});
?>