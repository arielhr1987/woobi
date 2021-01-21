<?php

/**
 * Class Woobi_Pivot_Header
 *
 * Base class that represents a dimension header
 *
 * @since 1.0.0
 */
class Woobi_Pivot_Header extends Woobi_Tree_Node{

	/**
	 * The pivot this header belongs to
	 *
	 * @var Woobi_Pivot
	 * @since 1.0.0
	 */
	protected $pivot = null;

	/**
	 * The dimension associated with this header
	 *
	 * @var Woobi_Pivot_Dimension
	 * @since 1.0.0
	 */
	protected $dimension = null;

	/**
	 * Woobi_Pivot_Header constructor.
	 *
	 * @param string $value
	 *
	 * @since 1.0.0
	 */
	public function __construct( $value = '' ) {
		parent::__construct( $value );
	}

	/**
	 * Get the pivot associated with this header
	 *
	 * @return Woobi_Pivot
	 * @since 1.0.0
	 */
	public function get_pivot() {
		return $this->pivot;
	}

	/**
	 * Sets the pivot associated with this header
	 *
	 * @param Woobi_Pivot $pivot
	 *
	 * @since 1.0.0
	 */
	public function set_pivot( $pivot ) {
		$this->pivot = $pivot;
	}

	/**
	 * Get the dimension associated with this header
	 *
	 * @return Woobi_Pivot_Dimension
	 * @since 1.0.0
	 */
	public function get_dimension() {
		return $this->dimension;
	}

	/**
	 * Sets the dimension associated with this header
	 *
	 * @param Woobi_Pivot_Dimension $dimension
	 *
	 * @since 1.0.0
	 */
	public function set_dimension( $dimension ) {
		$this->dimension = $dimension;
	}

	/**
	 * Add child
	 *
	 * @param string|self $child
	 *
	 * @return false|self
	 * @since 1.0.0
	 */
	public function add_child( $child ) {
		$child = parent::add_child( $child );

		if ( $child ) {
			$pivot     = $this->get_pivot();
			$dimension = $this->child_dimension();

			$child->set_pivot( $pivot );
			$child->set_dimension( $dimension );
		}

		return $child;
	}

	/**
	 * Returns the id of this header
	 * An id is unique among siblings as we use a SELECT DISTINCT
	 * i.e. fn89a7nd8an08d78f7ad
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function id() {
		return md5( $this->get_value() );
	}

	/**
	 * Returns the unique id of this node.
	 * A unique id (uid) identifies a node uniquely in the tree
	 * Is calculated by concatenating the parent uid, a dot "." and the current id
	 * i.e. fn89a7nd8an08d78f7ad-98as7da89sda8sd9as7d
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function uid() {
		if ( $parent = $this->get_parent() ) {
			$parent_uid = $parent->uid();

			return empty( $parent_uid ) ? $this->id() : $parent->uid() . '.' . $this->id();
		}

		return '';
	}

	/**
	 * Recursive method to traverse node hierarchy and generate sql SELECT clause.
	 * i.e. `country` AS 'country', 'city` AS 'city'
	 *
	 * @param Woobi_Pivot_Query_Builder $query
	 *
	 * @return Woobi_Pivot_Query_Builder
	 * @since 1.0.0
	 */
	public function select( $query ) {
		if ( $this->has_parent() ) {
			$this->get_parent()->select( $query );
		}
		$dimension = $this->child_dimension();
		$query->select( $dimension->get_column() );

		return $query;
	}

	/**
	 * Recursive method to traverse node hierarchy and generate sql WHERE clause
	 * i.e. (`country` = 'USA' AND 'city` = 'Boston')
	 * where country and city are dimensions
	 *
	 * @param Woobi_Pivot_Query_Builder $query
	 *
	 * @return Woobi_Pivot_Query_Builder
	 * @since 1.0.0
	 */
	public function where( $query ) {
		if ( $this->is_root() ) {
			return $query;
		}
		$this->get_parent()->where( $query );
		$query->where( $this->get_dimension()->get_column(), $this->get_value() );

		return $query;
	}

	/**
	 * @param Woobi_Pivot_Query_Builder $query
	 *
	 * @return Woobi_Pivot_Query_Builder
	 */
	public function group_by( $query ) {
		if ( $this->has_parent() ) {
			$this->get_parent()->group_by( $query );
		}

		$dimension = $this->child_dimension();
		$query->group_by( $dimension->get_column() . ' ' . $dimension->get_sort() , false);

		return $query;
	}

	/**
	 * Get the next dimension that will be assigned to this node children
	 *
	 * @return false|Woobi_Pivot_Dimension
	 * @since 1.0.0
	 */
	public function child_dimension() {
		$level = $this->level();

		if ( $this instanceof Woobi_Pivot_Header_Row ) {
			//its a row
			$rows = $this->get_pivot()->get_rows();
		} else {
			//its a column
			$rows = $this->get_pivot()->get_columns();
		}

		return isset( $rows[ $level ] ) ? $rows[ $level ] : false;
	}

	/**
	 * Process current node recursively.
	 *
	 * @since 1.0.0
	 */
	public function process() {

		$child_dimension = $this->child_dimension();
		if ( ! $child_dimension ) {
			/**
			 * Nothing to expand
			 */
			return;
		}

		/**
		 * Calculate all DISTINCT values
		 */
		$query = new Woobi_Pivot_Query_Builder();
		$query->distinct();
		$query->select( $child_dimension->get_column() ); //TODO: include alias
		$query->order_by( $child_dimension->get_column(), $child_dimension->get_sort() );
		$query = $this->where( $query );
		$sql   = $query->get_compiled_select( 'customer_product_dollarsales' );

		$rows = $this->get_pivot()->_query( $sql );
		echo $sql . '<br>'; //TODO: remove

		foreach ( $rows as $row ) {
			/**
			 * Add child
			 */
			$child = $this->add_child( $row[ $child_dimension->get_column() ] );

			// TODO: Check if node is expanded
			if ( in_array( $child->get_value(), [ 'USA', 'Boston' , 'NYC'] ) ) {
				$child->process();
			}
			if ( in_array( $child->get_value(), [ 'Trains' ] ) ) {
				$child->process();
			}
		}

		if ( $this->is_root() ) {
			//TODO: add grand totals row
		}
	}

	/**
	 * Method to render current processed node
	 *
	 * @since 1.0.0
	 */
	public function render() {
		/**
		 * Implement in child class
		 */
		//TODO: check if is processed. Should I throw an Exception?
	}

	/**
	 * @param array $nodes
	 *
	 * @return static[][]
	 */
	public function get_expanded_nodes_by_level( &$nodes = null ) {
		if ( $nodes === null ) {
			$nodes = array();
		}

		if ( $this->has_children() ) {
			//is expanded

			$level = $this->level();
			if ( ! isset( $nodes[ $level ] ) || ! is_array( $nodes[ $level ] ) ) {
				$nodes[ $level ] = array();
			}

			$nodes[ $level ][] = $this;

			foreach ( $this->get_children() as $child ) {
				$child->get_expanded_nodes_by_level( $nodes );
			}
		}

		return $nodes;

	}
}