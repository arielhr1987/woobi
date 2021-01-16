<?php

/**
 * Class Woobi_Pivot_Dimension
 *
 * Base class that represents a dimension
 *
 * @since 1.0.0
 */
class Woobi_Pivot_Dimension{

	/**
	 * The dimension name.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $name;

	/**
	 * The dimension display name.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $display_name;

	/**
	 * The dimension filters.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $filters = array();

	/**
	 * In which order are we going to show the data
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	protected $sort = 'ASC';

	/**
	 * Database column name
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $column;

	/**
	 * Woobi_Pivot_Dimension constructor.
	 *
	 * @param string $column
	 *
	 * @since 1.0.0
	 */
	public function __construct( $column ) {
		$this->column = $column;
	}

	/**
	 * Get the dimension name
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the dimension display name
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_display_name() {
		return $this->display_name;
	}

	/**
	 * Get all filters
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_filters() {
		return $this->filters;
	}

	/**
	 * Set the sort direction
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_sort() {
		return $this->sort;
	}

	/**
	 * Set the sort direction
	 *
	 * @since 1.0.0
	 */
	public function set_sort( $sort = 'ASC' ) {
		$sort = strtoupper( $sort );
		if ( in_array( $sort, array( 'ASC', 'DESC' ) ) ) {
			$this->sort = $sort;
		}
	}

	/**
	 * Get the db column to fetch
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_column() {
		return $this->column;
	}
}