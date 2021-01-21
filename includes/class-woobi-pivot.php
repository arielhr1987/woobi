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
	 * @var Woobi_Pivot_Header_Row
	 * @since 1.0.0
	 */
	protected $row_header = null;

	/**
	 * Tree with all row headers
	 *
	 * @var Woobi_Pivot_Header_Column
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
		 * Process all row headers
		 */
		$this->row_header = new Woobi_Pivot_Header_Row();
		$this->row_header->set_pivot( $this );
		$this->row_header->process();

		/**
		 * Process all column headers
		 */
		$this->column_header = new Woobi_Pivot_Header_Column();
		$this->column_header->set_pivot( $this );
		$this->column_header->process();

		/**
		 * Calculate all values for each column/row combination
		 * Only if we have measures we can calculate totals and values
		 */
		if ( count( $this->get_measures() ) ) {

			/**
			 * Calculate all column totals
			 */
			$column_levels = $this->column_header->get_expanded_nodes_by_level();
			foreach ( $column_levels as $column_level => $column_nodes ) {
				$this->calculate_totals( $column_nodes );
			}

			/**
			 * Calculate all row totals
			 */
			$row_levels = $this->row_header->get_expanded_nodes_by_level();
			foreach ( $row_levels as $row_level => $row_nodes ) {
				$this->calculate_totals( $row_nodes );
			}

			/**
			 * Calculate all
			 */
			foreach ( $column_levels as $column_level => $column_nodes ) {

				foreach ( $row_levels as $row_level => $row_nodes ) {

					$query = new Woobi_Pivot_Query_Builder();

					foreach ( $column_nodes as $column_node ) {
						$query = $column_node->select( $query );
						$query = $column_node->group_by( $query );
					}

					foreach ( $row_nodes as $row_node ) {
						$query = $row_node->select( $query );
						$query = $row_node->group_by( $query );

						if ( ! $row_node->is_root() ) {
							$query->or_group_start();
							$query = $column_node->where( $query );
							$query = $row_node->where( $query );
							$query->group_end();
						}
					}

					foreach ( $this->get_measures() as $measure ) {
						$query->select( $measure->get_total_expression() );
					}

					$sql = $query->get_compiled_select( 'customer_product_dollarsales' );

					echo '<code>' . $sql . '</code><br><br>';
				}
			}
		}


		$t = 0;


	}

	protected function calculate_totals( $nodes ) {
		$query = new Woobi_Pivot_Query_Builder();

		foreach ( $nodes as $index => $node ) {

			$query = $node->select( $query );
			$query = $node->group_by( $query );


			if ( ! $node->is_root() ) {

				$query->or_group_start();
				$query = $node->where( $query );
				$query->group_end();
			}
		}

		foreach ( $this->get_measures() as $measure ) {
			$query->select( $measure->get_total_expression() );
		}

		$sql = $query->get_compiled_select( 'customer_product_dollarsales' );

		echo '<code>' . $sql . '</code><br><br>';
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
                    <td colspan="1" class="woobi-top-axis-header woobi-table-container">
						<?php echo $this->column_header->render(); ?>
                    </td>
                    <td colspan="1" rowspan="2" class="vertical-scroll">
                        <!--Vertical Scroll-->
                    </td>
                </tr>
                <tr>
                    <td colspan="1" class="woobi-left-axis-header woobi-table-container">
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