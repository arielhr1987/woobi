<?php

/**
 * Class Woobi_Pivot_Header_Base
 *
 * Base class that represents a dimension header
 *
 * @since 1.0.0
 */
class Woobi_Pivot_Header_Base extends Woobi_Tree_Node{

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
	 * Woobi_Pivot_Header_Base constructor.
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
			$dimension = $this->get_child_dimension();

			$child->set_pivot( $pivot );
			$child->set_dimension( $dimension );
		}

		return $child;
	}

	/**
	 * Returns the id of this header
	 * An id is unique in the among siblings as we use a SELECT DISTINCT
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
	 * Expand current node
	 *
	 * @since 1.0.0
	 */
	public function expand() {

		$child_dimension = $this->get_child_dimension();
		if ( ! $child_dimension ) {
			/**
			 * Nothing to expand
			 */
			return;
		}

		$query = new Woobi_Pivot_Query_Builder();
		$query->distinct();
		$query->select( $child_dimension->get_column() ); //TODO: include alias
		$query->order_by( $child_dimension->get_column(), $child_dimension->get_sort() );
		$query = $this->recursive_where( $query );
		$sql   = $query->get_compiled_select( 'customer_product_dollarsales' );

		$rows = $this->get_pivot()->_query( $sql );
		echo $sql . '<br>'; //TODO: remove

		foreach ( $rows as $row ) {
			/**
			 * Add child
			 */
			$child = $this->add_child( $row[ $child_dimension->get_column() ] );


			// TODO: remove
			if ( in_array( $child->get_value(), [ 'Boston' ] ) ) {
				$child->expand();
			}
			if ( in_array( $child->get_value(), [ 'Diecast Collectables' ] ) ) {
				$child->expand();
			}
		}

		if ( $this->is_root() ) {
			//TODO: add grand totals row
		}
	}

	public function render() {

		$html = '';
		if ( $this->is_root() ) {
			$html = '<table class="table table-nested">';
		}
		$max     = 3;
		$colspan = $max - $this->level() + 1;

		if ( $this->has_children() ) {
			foreach ( $this->get_children() as $index => $child ) {
				$html .= $child->render();
			}
			$html .= '<tr>';
			$html .= '<td colspan="' . $colspan . '">' . $this->get_value() . ' Total</td>';
			$html .= '</tr>';
		} else {
			$html .= '<tr>';
			$html .= $this->get_td();
			$html .= '</tr>';
		}


		if ( $this->is_root() ) {
			$html .= '</table>';
		}

		return $html;
	}

	protected function get_td() {
		$max     = 3;
		$colspan = $max - $this->level() + 1;

		$td = '';
		if ( $this->is_first_child() && $parent = $this->get_parent() ) {
			$td .= $parent->get_td();
		}

		$rowspan = $this->has_children() ? $this->get_descendants_count() : 1;
		if ( ! $this->is_root() ) {
			$td .= '<td rowspan="' . $rowspan . '" ' . ( $rowspan == 1 ? 'colspan="' . $colspan . '"' : '' ) . '>' . $this->get_value() . '</td>';
		}

		return $td;

	}

	/**
	 * @param Woobi_Pivot_Query_Builder $query
	 *
	 * @return Woobi_Pivot_Query_Builder
	 * @since 1.0.0
	 */
	public function recursive_where( Woobi_Pivot_Query_Builder $query ) {
		if ( $this->is_root() ) {
			return $query;
		}
		$this->get_parent()->recursive_where( $query );
		$query->where( $this->dimension->get_column(), $this->get_value() );

		return $query;
	}

	/**
	 * @return false|Woobi_Pivot_Dimension
	 */
	public function get_child_dimension() {
		$level = $this->level();
		$rows  = $this->get_pivot()->get_rows();

		return isset( $rows[ $level ] ) ? $rows[ $level ] : false;
	}
}