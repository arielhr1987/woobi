<?php

/**
 * Class Woobi_Pivot_Header_Column
 *
 * Class that represents the rows dimension header
 *
 * @since 1.0.0
 */
class Woobi_Pivot_Header_Column extends Woobi_Pivot_Header_Base{


	/**
	 * Method to render top axis header table
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function render() {

		$html = '';
		if ( $this->is_root() ) {
			$html = '<table class="woobi-table">';
		}
		$max     = 3; //TODO: calculate tree max level
		$colspan = $max - $this->level() + 1;

		$this->get_td( $rows );

		foreach ( $rows as $row ) {
			$html .= '<tr>';
			$html .= implode( '', $row );
			$html .= '</tr>';
		}


		if ( $this->is_root() ) {
			$html .= '</table>';
		}

		return $html;
	}

	/**
	 * Utility method to traverse node hierarchy and get each level elements
	 * This method also takes core of adding corresponding colspan and rowspan to the element
	 *
	 * @since 1.0.0
	 */
	protected function get_td( &$tds ) {
		$max = 3;

		if ( $this->has_children() ) {
			if ( ! isset( $tds[ $this->level() ] ) ) {
				$tds[ $this->level() ] = array();
			}
			$tds[ $this->level() ][] = '<td colspan="' . $this->get_descendants_count() . '" rowspan="1">' . $this->get_value() . '</td>';;
			foreach ( $this->get_children() as $child ) {
				$child->get_td( $tds );
			}
		}

		$measures_count = count( $this->get_pivot()->get_measures() );
		$measures_count = $measures_count ? $measures_count : 1;
		$colspan        = $measures_count;

		$rowspan = $max - $this->level() + 1 ;

		if ( ! $this->is_root() ) {
			$td = '<td colspan="' . $colspan . '" rowspan="' . $rowspan . '">' . $this->get_value() . '</td>';

			if ( ! $this->has_children() ) {
				//TODO: Add measures column headers if there are more than 1 measure
			}

			if ( ! isset( $tds[ $this->level() ] ) ) {
				$tds[ $this->level() ] = array();
			}
			$tds[ $this->level() ][] = $td;

		}
	}
}