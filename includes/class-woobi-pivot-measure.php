<?php

/**
 * Class Woobi_Pivot_Measure
 *
 * Base class that represents a measure
 *
 * @since 1.0.0
 */
class Woobi_Pivot_Measure{

	/**
	 * The measure name.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $name;

	/**
	 * The measure display name.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $text;

	/**
	 * The field to to use in queries
	 *
	 * @var string
	 */
	protected $field;

	/**
	 * Aggregator method
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $aggregator = 'SUM';

	/**
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function get_text() {
		return $this->text;
	}

	/**
	 * @return string
	 */
	public function get_field() {
		return $this->field;
	}

	/**
	 * @return string
	 */
	public function get_aggregator() {
		return $this->aggregator;
	}

	public function get_total_expression() {
		return sprintf( '%(%s) AS %s', $this->aggregator, $this->field, $this->name );
	}

	public function format( $value ) {
		return $value;
	}
}