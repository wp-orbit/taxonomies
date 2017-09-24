<?php

namespace WPOrbit\Taxonomies;

/**
 * Provides an extensible class for registering custom taxonomies to post type(s).
 *
 * Class Taxonomy
 *
 * @package WPOrbit\Taxonomies
 */
abstract class AbstractTaxonomy {
	/**
	 * @var string The taxonomy key.
	 */
	protected $key;

	/**
	 * @var string The taxonomy slug.
	 */
	protected $slug;

	/**
	 * @var string Singular taxonomy label.
	 */
	protected $singular;

	/**
	 * @var string Plural taxonomy label.
	 */
	protected $plural;

	/**
	 * @var string Menu name label.
	 */
	protected $menu_name;

	/**
	 * @var bool Is hierarchical (like categories) or false like tags.
	 */
	protected $hierarchical = false;

	/**
	 * @var bool
	 */
	protected $show_ui = true;

	/**
	 * @var bool
	 */
	protected $show_admin_column = true;

	/**
	 * @var bool
	 */
	protected $query_var = true;

	/**
	 * @var array An array of post types to inject this taxonomy.
	 */
	protected $post_types = [];

	/**
	 * The taxonomy key.
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Get the taxonomy key -- alias of getKey().
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return $this->get_key();
	}

	/**
	 * The taxonomy slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * The taxonomy singular label.
	 *
	 * @return string
	 */
	public function get_singular() {
		return $this->singular;
	}

	/**
	 * The taxonomy plural label.
	 *
	 * @return string
	 */
	public function get_plural() {
		return $this->plural;
	}

	/**
	 * An array of post types to which this taxonomy is registered.
	 *
	 * @return array
	 */
	public function get_post_types() {
		return $this->post_types;
	}

	protected function validate_taxonomy() {
		// Class name.
		$className = static::class;

		// Required keys.
		$keys = [
			'key',
			'slug',
			'singular',
			'plural'
		];

		// Loop through required keys.
		foreach ( $keys as $key ) {
			// Verify that the current iteration is specified.
			if ( null === $this->{$key} ) {
				throw new \Exception( "No \${$key} specified in class {$className}." );
			}
		}
	}

	/**
	 * @return array Taxonomy labels.
	 */
	protected function get_labels() {
		// Return hierarchical labels.
		if ( $this->hierarchical ) {
			return [
				'name'              => _x( $this->menu_name ?: $this->plural, 'taxonomy general name' ),
				'singular_name'     => _x( $this->singular, 'taxonomy singular name' ),
				'search_items'      => __( 'Search ' . $this->plural ),
				'all_items'         => __( 'All ' . $this->plural ),
				'parent_item'       => __( 'Parent ' . $this->plural ),
				'parent_item_colon' => __( 'Parent ' . $this->plural . ':' ),
				'edit_item'         => __( 'Edit ' . $this->singular ),
				'update_item'       => __( 'Update ' . $this->singular ),
				'add_new_item'      => __( 'Add New ' . $this->singular ),
				'new_item_name'     => __( 'New ' . $this->singular . ' Name' ),
				'menu_name'         => __( $this->menu_name ?: $this->plural ),
			];
		}

		// Return non-hierarchical labels.
		$labels = [
			'name'                       => _x( $this->menu_name ?: $this->plural, 'taxonomy general name' ),
			'singular_name'              => _x( $this->singular, 'taxonomy singular name' ),
			'search_items'               => __( 'Search ' . $this->plural ),
			'popular_items'              => __( 'Popular ' . $this->plural ),
			'all_items'                  => __( 'All ' . $this->plural ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit ' . $this->singular ),
			'update_item'                => __( 'Update ' . $this->singular ),
			'add_new_item'               => __( 'Add New ' . $this->singular ),
			'new_item_name'              => __( 'New ' . $this->singular . ' Name' ),
			'separate_items_with_commas' => __( 'Separate ' . $this->plural . ' with commas' ),
			'add_or_remove_items'        => __( 'Add or remove ' . $this->plural ),
			'choose_from_most_used'      => __( 'Choose from the most used ' . $this->plural ),
			'not_found'                  => __( 'No ' . strtolower( $this->plural ) . ' found.' ),
			'menu_name'                  => __( $this->menu_name ?: $this->plural ),
		];

		return apply_filters( 'wp-orbit-taxonomy-labels', $labels, $this->get_key() );
	}

	/**
	 * Hook WordPress.
	 */
	public function register_taxonomy() {

		// Validate taxonomy configuration.
		$this->validate_taxonomy();

		// Define arguments.
		$args = [
			'hierarchical'      => $this->hierarchical,
			'labels'            => $this->get_labels(),
			'show_ui'           => $this->show_ui,
			'show_admin_column' => $this->show_admin_column,
			'query_var'         => $this->query_var,
			'rewrite'           => [ 'slug' => $this->slug ],
		];

		$args = apply_filters( 'wp-orbit-taxonomy-args', $args, $this->get_key() );

		// Register the taxonomy.
		register_taxonomy( $this->key, $this->post_types, $args );
	}

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * @return static
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static;
		}
		return static::$instance;
	}

	/**
	 * Taxonomy constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = [] ) {

		$args = wp_parse_args( $args, [
			'key' => '',
			'slug' => '',
			'singular' => '',
			'plural' => '',
			'menu_name' => '',
			'hierarchical' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'post_types' => [],
		]);

		foreach( $args as $method => $arg ) {
			if ( property_exists( static::class, $method ) ) {
				$this->{$method} = $arg;
			}
		}

		add_action( 'init', [$this, 'register_taxonomy'] );
	}
}