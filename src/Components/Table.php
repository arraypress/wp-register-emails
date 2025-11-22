<?php
/**
 * Table Component
 *
 * @package     ArrayPress\RegisterEmails
 * @copyright   Copyright (c) 2025, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterEmails\Components;

use ArrayPress\RegisterEmails\Abstracts\Component;

/**
 * Table Component
 *
 * Flexible data table with optional header and alignment control.
 *
 * @since 1.0.0
 */
class Table extends Component {

	/**
	 * Render table component
	 *
	 * @param array $args {
	 *     @type array  $data        Table data array (required)
	 *     @type bool   $has_header  First row is header
	 *     @type array  $alignments  Column alignments: 'left', 'center', 'right'
	 *     @type bool   $striped     Alternate row backgrounds
	 *     @type bool   $bordered    Add borders between cells
	 * }
	 *
	 * @return string Table HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$data       = $args['data'] ?? [];
		$has_header = $args['has_header'] ?? true;
		$alignments = $args['alignments'] ?? [];
		$striped    = $args['striped'] ?? false;
		$bordered   = $args['bordered'] ?? true;

		if ( empty( $data ) ) {
			return '';
		}

		$table_classes = 'component component-table';
		if ( $striped ) {
			$table_classes .= ' table-striped';
		}
		if ( $bordered ) {
			$table_classes .= ' table-bordered';
		}

		$html = sprintf( '<table class="%s" style="width: 100%%; border-collapse: collapse; margin: 24px 0;">', esc_attr( $table_classes ) );

		$row_index = 0;
		foreach ( $data as $row ) {
			$is_header = $has_header && $row_index === 0;

			// Determine row classes and background
			$row_classes = $is_header ? 'table-header-row' : 'table-body-row';
			$row_bg = '';

			if ( $striped && ! $is_header && $row_index % 2 === 0 ) {
				$row_bg = 'background: #f9fafb;';
				$row_classes .= ' row-striped';
			}

			$html .= sprintf( '<tr class="%s" style="%s">', esc_attr( $row_classes ), $row_bg );

			$col_index = 0;
			foreach ( $row as $cell ) {
				$tag = $is_header ? 'th' : 'td';

				$cell_classes = $is_header ? 'table-header-cell' : 'table-body-cell';

				// Base styles
				$style = 'padding: 12px;';

				// Alignment
				$align = $alignments[$col_index] ?? 'left';
				$style .= ' text-align: ' . $align . ';';
				$cell_classes .= ' align-' . $align;

				// Borders
				if ( $bordered ) {
					if ( $is_header ) {
						$style .= ' border-bottom: 2px solid #e5e7eb;';
					} else {
						$style .= ' border-bottom: 1px solid #f3f4f6;';
					}
				}

				// Header specific styles
				if ( $is_header ) {
					$style .= ' font-weight: 600; color: #374151;';
					if ( $striped ) {
						$style .= ' background: #f3f4f6;';
					}
				} else {
					$style .= ' color: #111827;';
				}

				$html .= sprintf(
					'<%s class="%s" style="%s">%s</%s>',
					$tag,
					esc_attr( $cell_classes ),
					$style,
					wp_kses_post( (string) $cell ),
					$tag
				);

				$col_index++;
			}

			$html .= '</tr>';
			$row_index++;
		}

		$html .= '</table>';

		return $html;
	}

}