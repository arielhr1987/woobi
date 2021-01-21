<?php

/**
 * Class Woobi_Pivot_Header_Row
 *
 * Class that represents the rows dimension header
 *
 * @since 1.0.0
 */
class Woobi_Pivot_Header_Row extends Woobi_Pivot_Header{

	/**
	 * Method to render left axis header table
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function render() {

		$html = '';
		if ( $this->is_root() ) {
			$html = '<table class="woobi-table" style="table-layout: auto;">';
		}
		$max     = 3; //TODO: calculate max level of the tree
		$colspan = $max - $this->level() + 1;

		if ( $this->has_children() ) {
			foreach ( $this->get_children() as $index => $child ) {
				$html .= $child->render();
			}
			/**
			 * If we render the children, we most show totals
			 */
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

	/**
	 * Utility method to traverse node hierarchy and generate all <td> elements
	 * This method also takes core of adding corresponding colspan and rowspan to the element
	 *
	 * @return string
	 * @since 1.0.0
	 */
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
}