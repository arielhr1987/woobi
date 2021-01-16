<?php

/**
 * Class Woobi_Pivot_Header
 *
 * Base class that represents a dimension header
 *
 * @since 1.0.0
 */
class Woobi_Pivot_Header{

	/**
	 * @var string [row|col]
	 * @since 1.0.0
	 */
	protected $zone = 'row';

	/**
	 * The value of this node.
	 * This value shouldn't be set as its the actual DB value
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $value = null;

	/**
	 * The parent node.
	 *
	 * @var Woobi_Pivot_Header
	 * @since 1.0.0
	 */
	protected $parent = null;

	/**
	 * All child nodes.
	 *
	 * @var Woobi_Pivot_Header[]
	 * @since 1.0.0
	 */
	protected $children = array();

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
		$this->value = $value;
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
		$colspan = $max - $this->get_level() + 1;

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
		$colspan = $max - $this->get_level() + 1;

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
	 * Add child
	 *
	 * @param $child
	 *
	 * @return false|Woobi_Pivot_Header
	 * @since 1.0.0
	 */
	public function add_child( $child ) {
		if ( is_string( $child ) ) {
			$child = new Woobi_Pivot_Header( $child );
		}
		if ( ! $child instanceof Woobi_Pivot_Header ) {
			return false;
		}
		$pivot     = $this->get_pivot();
		$dimension = $this->get_child_dimension();

		$child->set_parent( $this );
		$child->set_pivot( $pivot );
		$child->set_dimension( $dimension );

		$this->children[] = $child;

		return $child;
	}

	/**
	 * Determine if current node has children
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function has_children() {
		return ! empty( $this->children );
	}

	/**
	 * Determine if is the root node
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_root() {
		return ! $this->parent;
	}

	/**
	 * Determine node level
	 *
	 * @return integer
	 * @since 1.0.0
	 */
	public function get_level() {
		return $this->is_root() ? 0 : $this->get_parent()->get_level() + 1;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * @return Woobi_Pivot_Header
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * @param string $parent
	 */
	public function set_parent( $parent ) {
		$this->parent = $parent;
	}

	/**
	 * @return Woobi_Pivot_Header[]
	 */
	public function get_children() {
		return $this->children;
	}

	/**
	 * @return Woobi_Pivot
	 */
	public function get_pivot() {
		return $this->pivot;
	}

	/**
	 * @param Woobi_Pivot $pivot
	 */
	public function set_pivot( $pivot ) {
		$this->pivot = $pivot;
	}

	/**
	 * @return Woobi_Pivot_Dimension
	 */
	public function get_dimension() {
		return $this->dimension;
	}

	/**
	 * @return false|Woobi_Pivot_Dimension
	 */
	public function get_child_dimension() {
		$level = $this->get_level();
		$rows  = $this->get_pivot()->get_rows();

		return isset( $rows[ $level ] ) ? $rows[ $level ] : false;
	}

	/**
	 * @param Woobi_Pivot_Dimension $dimension
	 */
	public function set_dimension( $dimension ) {
		$this->dimension = $dimension;
	}

	protected function get_descendants_count() {
		$count = count( $this->get_children() );

		foreach ( $this->get_children() as $child ) {
			$count += $child->get_descendants_count();
		}

		return $count;
	}

	protected function is_first_child() {
		$parent = $this->get_parent();
		if ( $parent ) {
			$children = $parent->get_children();

			return $children[0] === $this;
		}

		return false;
	}
}