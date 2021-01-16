<?php

/**
 * Class Woobi_Pivot_Measure_Sum
 *
 * Base class that represents a measure
 *
 * @since 1.0.0
 */
class Woobi_Pivot_Measure_Sum extends Woobi_Pivot_Measure{

	/**
	 * The measure name.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $name = 'dollar_sales';

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
	protected $field = 'dollar_sales';

	/**
	 * Aggregator method
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $aggregator = 'SUM';
}