<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://leira.dev
 * @since      1.0.0
 *
 * @package    Woobi
 * @subpackage Woobi/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woobi
 * @subpackage Woobi/admin
 * @author     Ariel <arielhr1987@gmail.com>
 */
class Woobi_Admin{

	/**
	 * The capability user most have to be able to access admin page
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @access   public
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woobi_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woobi_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woobi-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @access   public
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woobi_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woobi_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woobi-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add the admin menu item
	 *
	 * @access   public
	 * @since    1.0.0
	 */
	public function admin_menu() {
		$hook = add_menu_page(
			__( 'Woobi', 'woobi' ),
			__( 'Woobi', 'woobi' ),
			$this->capability,
			'woobi',
			array( $this, 'render_admin_page' ),
			'dashicons-lightbulb',
			58.5 //Marketing uses 58
		);

		if ( ! empty( $hook ) ) {
			add_action( "load-$hook", array( $this, 'admin_page_load' ) );
		}
	}


	/**
	 * Render the admin page
	 *
	 * @access   public
	 * @since    1.0.0
	 */
	public function render_admin_page() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woobi' ) );
		}

		require_once __DIR__ . '/../includes/class-woobi-pivot.php';
		require_once __DIR__ . '/../includes/class-woobi-pivot-dimension.php';
		require_once __DIR__ . '/../includes/class-woobi-pivot-measure.php';
		require_once __DIR__ . '/../includes/class-woobi-pivot-measure-sum.php';
		require_once __DIR__ . '/../includes/class-woobi-pivot-data-source.php';
		require_once __DIR__ . '/../includes/class-woobi-pivot-query-builder.php';
		require_once __DIR__ . '/../includes/class-woobi-tree-node.php';
		require_once __DIR__ . '/../includes/class-woobi-pivot-header.php';
		require_once __DIR__ . '/../includes/class-woobi-pivot-header-row.php';
		require_once __DIR__ . '/../includes/class-woobi-pivot-header-column.php';

		$pivot = new Woobi_Pivot();

		$sales = new Woobi_Pivot_Measure_Sum();
		$pivot->add_measure( $sales );

		$dimension = new Woobi_Pivot_Dimension( 'country' );
		$pivot->add_row( $dimension );

		$dimension = new Woobi_Pivot_Dimension( 'city' );
		$pivot->add_row( $dimension );

		$dimension = new Woobi_Pivot_Dimension( 'customerName' );
		//$dimension->set_sort('DESC');
		$pivot->add_row( $dimension );

		//==================

		$dimension = new Woobi_Pivot_Dimension( 'productLine' );
		//$dimension->set_sort('DESC');
		$pivot->add_column( $dimension );

		$dimension = new Woobi_Pivot_Dimension( 'productName' );
		//$dimension->set_sort('DESC');
		$pivot->add_column( $dimension );

		//===================

		$pivot->process();


		?>

        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo __( 'WooBI', 'woobi' ) ?></h1>
            <hr class="wp-header-end">
            <h2 class="screen-reader-text"><?php _e( 'Filter cron jobs list', 'woobi' ) ?></h2>
			<?php echo $pivot->render() ?>
        </div>

		<?php
	}

	/**
	 * On admin page load. Add content to the page
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function admin_page_load() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woobi' ) );
		}

//		//enqueue styles
//		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woobi-admin.css', array(), $this->version, 'all' );
//
//		//enqueue scripts
//		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woobi-admin.min.js', array(
//			'jquery',
//			'wp-a11y'
//		), $this->version, false );
//
//		//initialize table here to be able to register default WP_List_Table screen options
//		$this->get_list_table();
//
//		//handle bulk and simple actions
//		$this->handle_actions();
//
//		//add modal thickbox js
//		add_thickbox();

		//Add screen options
		add_screen_option( 'per_page', array( 'default' => 999 ) );

		//Add screen help
		get_current_screen()->add_help_tab(
			array(
				'id'      => 'overview',
				'title'   => __( 'Overview', 'woobi' ),
				'content' =>
					'<p>' . __( 'Write an overview about the plugin functionality.', 'woobi' ) . '</p>' .
					'',
			)
		);
		get_current_screen()->add_help_tab(
			array(
				'id'      => 'screen-content',
				'title'   => __( 'Screen Content', 'woobi' ),
				'content' =>
					'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:', 'woobi' ) . '</p>' .
					'<ul>' .
					'<li>' . __( 'Some text describing how to customize a feature.', 'woobi' ) . '</li>' .
					'</ul>'
			)
		);

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'woobi' ) . '</strong></p>' .
			'<p>' . __( '<a href="https://developer.wordpress.org/plugins/woobi/">Documentation on Crons</a>', 'woobi' ) . '</p>' .
			'<p>' . __( '<a href="https://wordpress.org/support/plugin/woobi/">Support</a>', 'woobi' ) . '</p>' .
			'<p>' . __( '<a href="https://wordpress.org/support/plugin/woobi/">Report an issue</a>', 'woobi' ) . '</p>'
		);

//		get_current_screen()->set_screen_reader_content(
//			array(
//				'heading_views'      => __( 'Filter Cron Job list', 'woobi' ),
//				'heading_pagination' => __( 'Cron Job list navigation', 'woobi' ),
//				'heading_list'       => __( 'Cron Job list', 'woobi' ),
//			)
//		);
	}
}
