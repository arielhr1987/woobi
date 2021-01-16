<?php

/**
 * Class Woobi_Pivot
 *
 * Core class to handle multi dimensional queries
 *
 * @since 1.0.0
 */
class Woobi_Pivot{

	/**
	 * Data Source provider
	 *
	 * @var Woobi_Pivot_Data_Source
	 * @since 1.0.0
	 */
	protected $ds = null;

	/**
	 * Filters applied to the current pivot
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $filters = array();

	/**
	 * All registered measures
	 *
	 * @var Woobi_Pivot_Measure[]
	 * @since 1.0.0
	 */
	protected $measures = array();

	/**
	 * Left axis dimensions
	 *
	 * @var Woobi_Pivot_Dimension[]
	 * @since 1.0.0
	 */
	protected $rows = array();

	/**
	 * Top axis dimensions
	 *
	 * @var Woobi_Pivot_Dimension[]
	 * @since 1.0.0
	 */
	protected $columns = array();

	/**
	 * Tree with all row headers
	 *
	 * @var Woobi_Pivot_Header
	 * @since 1.0.0
	 */
	protected $row_header = null;

	/**
	 * Tree with all row headers
	 *
	 * @var Woobi_Pivot_Header
	 * @since 1.0.0
	 */
	protected $column_header = null;

	/**
	 * All data processed
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $data = array();

	/**
	 * Woobi_Pivot constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

	}

	public function add_measure( $measure ) {
		//We need to check if measure was not already added
		$this->measures[] = $measure;
	}

	public function add_row( $dimension ) {
		if ( ! $dimension instanceof Woobi_Pivot_Dimension ) {
			return;
		}
		//TODO: We need to check if the dimension is not already in filters, columns or rows
		$this->rows[] = $dimension;
	}

	public function add_column( $dimension ) {
		if ( ! $dimension instanceof Woobi_Pivot_Dimension ) {
			return;
		}
		//TODO: We need to check if the dimension is not already in filters, columns or rows
		$this->columns[] = $dimension;
	}

	/**
	 * @return array
	 */
	public function get_filters(): array {
		return $this->filters;
	}

	/**
	 * @return Woobi_Pivot_Measure[]
	 */
	public function get_measures(): array {
		return $this->measures;
	}

	/**
	 * @return Woobi_Pivot_Dimension[]
	 */
	public function get_rows(): array {
		return $this->rows;
	}

	/**
	 * @return Woobi_Pivot_Dimension[]
	 */
	public function get_columns(): array {
		return $this->columns;
	}

	/**
	 * Execute all DB queries and get the results
	 *
	 * @since 1.0.0
	 */
	public function process() {
		/**
		 * Assign an alias to each dimension
		 */


		/**
		 * Calculate all headers
		 */
		$this->row_header = new Woobi_Pivot_Header_Base();
		$this->row_header->set_pivot( $this );
		$this->row_header->expand();


	}

	public function render() {
		//return $this->row_header->render();

		?>
        <div class="woobi-pivot-container">
<!--      wp-list-table widefat      -->
            <table class="woobi-pivot woobi-table woobi-table-bordered">
                <tbody>
                <tr>
                    <td colspan="3">
                        Filter Zone
                    </td>
                </tr>
                <tr>
                    <td colspan="1">
                        Measures Zone
                    </td>
                    <td colspan="3">
                        Top Axis Dimensions
                    </td>
                </tr>
                <tr>
                    <td colspan="1">
                        Left Axis Dimensions
                    </td>
                    <td colspan="1" class="woo-bi-top-axis-header">

                        Top Axis
                    </td>
                    <td colspan="1" rowspan="2" class="vertical-scroll">
                        <!--Vertical Scroll-->
                    </td>
                </tr>
                <tr>
                    <td colspan="1" class="woo-bi-left-axis-header woobi-table-container">
						<?php echo $this->row_header->render(); ?>
                    </td>
                    <td colspan="1" class="p-0">
                        <div class="woo-bi-data-container" style="overflow: hidden">
                            Data Zone
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="horizontal-scroll">
                        <!--Horizontal Scroll-->
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="">
                        Pager
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="">
                        Statusbar
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
		<?php
	}

	public function _query( $sql ) {
		$mysqli = new mysqli( "localhost", "root", "root", "koolphp" );
		if ( mysqli_connect_errno() ) {
			echo 'Error connecting to DB';
		}
		$result = $mysqli->query( $sql );
		$rows   = array();
		while( $row = $result->fetch_assoc() ){
			$rows[] = $row;
		}
		$result->close();
		$mysqli->close();

		return $rows;
	}

}