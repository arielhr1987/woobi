<?php

/**
 * Class Woobi_Tree_Node
 *
 * A simple tree implementation.
 *
 * @since 1.0.0
 */
class Woobi_Tree_Node{

	/**
	 * The value of this node.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $value = null;

	/**
	 * The parent node.
	 *
	 * @var static
	 * @since 1.0.0
	 */
	protected $parent = null;

	/**
	 * All child nodes.
	 *
	 * @var static[]
	 * @since 1.0.0
	 */
	protected $children = array();

	/**
	 * Woobi_Tree_Node constructor.
	 *
	 * @param string $value
	 *
	 * @since 1.0.0
	 */
	public function __construct( $value = '' ) {
		$this->value = $value;
	}

	/**
	 * Get this node value
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Set this node value
	 *
	 * @param string $value
	 *
	 * @since 1.0.0
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * Get this node parent
	 *
	 * @return static
	 * @since 1.0.0
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * Set this node parent
	 *
	 * @param static $parent The parent of the node
	 *
	 * @since 1.0.0
	 */
	public function set_parent( $parent ) {
		$this->parent = $parent;
	}

	/**
	 * Determine if current node has a parent
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function has_parent() {
		return boolval( $this->parent );
	}

	/**
	 * Determine if is the root node
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_root() {
		return ! $this->has_parent();
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
	 * Get all children
	 *
	 * @return static[]
	 * @since 1.0.0
	 */
	public function get_children() {
		return $this->children;
	}

	/**
	 * @param $index
	 *
	 * @return false|static
	 * @since 1.0.0
	 */
	public function child_at( $index ) {
		return isset( $this->children[ $index ] ) ? $this->children[ $index ] : false;
	}

	/**
	 * Get the first child of this node
	 *
	 * @return false|static
	 * @since 1.0.0
	 */
	public function first_child() {
		return $this->child_at( 0 );
	}

	/**
	 * Get the last child of this node
	 *
	 * @return false|static
	 * @since 1.0.0
	 */
	public function last_child() {
		return end( $this->children );
	}

	/**
	 * Determine if this node is the first child of the parent
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function is_first_child() {
		if ( ! $this->has_parent() ) {
			return false;
		}

		return $this->get_parent()->first_child() === $this;
	}

	/**
	 * Add child
	 *
	 * @param string $child
	 *
	 * @return false|static
	 * @since 1.0.0
	 */
	public function add_child( $child ) {
		if ( is_string( $child ) ) {
			$child = new static( $child );
		}
		if ( ! $child instanceof static ) {
			return false;
		}

		$child->set_parent( $this );

		$this->children[] = $child;

		return $child;
	}

	/**
	 * Remove a child from this node children and return the deleted element
	 *
	 * @param int $index The index of the element you want to delete
	 *
	 * @return false|static The deleted element, false if doesnt exist
	 * @since 1.0.0
	 */
	public function remove_child( $index ) {
		$node = $this->child_at( $index );
		if ( $node ) {
			//Nothing to remove
			array_splice( $this->children, $index, 1 );
		}

		return $node;
	}

	/**
	 * Determine node level.
	 *
	 * @return integer The node level
	 * @since 1.0.0
	 */
	public function level() {
		return $this->is_root() ? 0 : $this->get_parent()->level() + 1;
	}

	/**
	 * Determine how many descendants does this node has
	 *
	 * @return int The number of descendants
	 * @since 1.0.0
	 */
	protected function get_descendants_count() {
		$count = count( $this->get_children() );

		foreach ( $this->get_children() as $child ) {
			$count += $child->get_descendants_count();
		}

		return $count;
	}
}