<?php

defined( 'ABSPATH' ) or die( 'No direct access' );

require_once( dirname( __FILE__ ) . '/wfs_200.php' );

/**
 * Class implementing the WP REST API pieces of WFS integration for all supported WFS versions
 *
 * (you know, when we support more versions)
 */
class WFS_Rest {

	/**
	 * The instance variable
	 *
	 * @var $_instance
	 */
	private static $_instance = null;

	/**
	 * Get the singleton instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Set up routes
	 */
	public function __construct() {

		$this->limit = 1000;

		add_action('rest_api_init', function() {

			// Schemas! Schemas for everyone!
			register_rest_route( 'wfs','/schemas/.*', array(
					'methods' => WP_REST_Server::ALLMETHODS,
					'callback' => array( $this, 'handle_schema_request' ),
				) 
			);

			// Base WFS 2.0.0 URL
			register_rest_route( 'wfs','/2.0.0/', array(
					'methods' => WP_REST_Server::ALLMETHODS,
					'callback' => array( $this, 'wfs_200' ),
				) 
			);
			// WFS 2.0.0 URL with namespace
			register_rest_route( 'wfs','/2.0.0/(?P<namespace>[a-zA-Z0-9_-]+)/', array(
					'methods' => WP_REST_Server::ALLMETHODS,
					'callback' => array( $this, 'wfs_200' ),
				) 
			);
		} );
	}

	/**
	 * Schema files are static. Check if it exists on disk. 
	 */
	public function handle_schema_request( $data ) {
		$route = preg_replace( '|^/wfs|', '', $data->get_route() );

		$file_on_disk = dirname( __FILE__ ) . '/..' . $route;

		if ( file_exists( $file_on_disk ) ) {
			header( 'Content-type: application/xml' );
			readfile( $file_on_disk );
			exit();
		}
	}

	/**
	 * Forward wfs 2.0.0 requests to the wfs_200 class
	 */
	public function wfs_200( $data ) {
		new WFS_200( $data );
	}
}
