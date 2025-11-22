<?php
/**
 * Event Details Component
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
 * Event Details Component
 *
 * Displays event or appointment information with calendar links.
 *
 * @since 1.0.0
 */
class EventDetails extends Component {

	/**
	 * Render event details component
	 *
	 * @param array $args         {
	 *
	 * @type string $title        Event title
	 * @type string $date         Event date
	 * @type string $time         Event time
	 * @type string $duration     Duration
	 * @type string $location     Location (physical or virtual)
	 * @type string $description  Event description
	 * @type string $join_url     Virtual meeting URL
	 * @type string $calendar_url Add to calendar URL (.ics)
	 * @type array  $attendees    List of attendees
	 * @type string $organizer    Organizer name
	 *                            }
	 *
	 * @return string Event details HTML
	 * @since 1.0.0
	 */
	public static function render( array $args = [] ): string {
		$title        = $args['title'] ?? '';
		$date         = $args['date'] ?? '';
		$time         = $args['time'] ?? '';
		$duration     = $args['duration'] ?? '';
		$location     = $args['location'] ?? '';
		$description  = $args['description'] ?? '';
		$join_url     = $args['join_url'] ?? '';
		$calendar_url = $args['calendar_url'] ?? '';
		$attendees    = $args['attendees'] ?? [];
		$organizer    = $args['organizer'] ?? '';

		$html = '<div class="component component-event-details" style="background: #ffffff; border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin: 24px 0;">';

		// Date header banner
		if ( $date ) {
			$html .= '<div class="event-header" style="background: #f3f4f6; padding: 16px 24px; text-align: center; border-bottom: 1px solid #e5e7eb;">';
			$html .= sprintf(
				'<div class="event-date" style="font-size: 24px; font-weight: 700; color: #111827;">%s</div>',
				esc_html( $date )
			);
			if ( $time ) {
				$html .= sprintf(
					'<div class="event-time" style="font-size: 16px; color: #6b7280; margin-top: 4px;">%s',
					esc_html( $time )
				);
				if ( $duration ) {
					$html .= sprintf( ' <span class="event-duration">(%s)</span>', esc_html( $duration ) );
				}
				$html .= '</div>';
			}
			$html .= '</div>';
		}

		// Main content
		$html .= '<div class="event-content" style="padding: 24px;">';

		// Title
		if ( $title ) {
			$html .= sprintf(
				'<h2 class="event-title" style="margin: 0 0 16px; font-size: 20px; font-weight: 600; color: #111827;">%s</h2>',
				esc_html( $title )
			);
		}

		// Description
		if ( $description ) {
			$html .= sprintf(
				'<p class="event-description" style="margin: 0 0 20px; color: #374151; line-height: 1.5;">%s</p>',
				esc_html( $description )
			);
		}

		// Details table
		$html .= '<table class="event-details-table" style="width: 100%; margin-bottom: 20px;">';

		// Location row
		if ( $location ) {
			$html .= '<tr class="event-location-row">';
			$html .= '<td class="event-label" style="padding: 8px 0; color: #6b7280; width: 120px; vertical-align: top;">📍 Location:</td>';
			$html .= '<td class="event-value" style="padding: 8px 0; color: #111827; font-weight: 500;">';

			if ( $join_url && stripos( $location, 'zoom' ) !== false || stripos( $location, 'meet' ) !== false || stripos( $location, 'teams' ) !== false ) {
				$html .= sprintf(
					'<a href="%s" class="event-location-link" style="color: #2563eb; text-decoration: none;">%s</a>',
					esc_url( $join_url ),
					esc_html( $location )
				);
			} else {
				$html .= esc_html( $location );
			}

			$html .= '</td></tr>';
		}

		// Organizer row
		if ( $organizer ) {
			$html .= '<tr class="event-organizer-row">';
			$html .= '<td class="event-label" style="padding: 8px 0; color: #6b7280; width: 120px; vertical-align: top;">👤 Organizer:</td>';
			$html .= sprintf(
				'<td class="event-value" style="padding: 8px 0; color: #111827;">%s</td>',
				esc_html( $organizer )
			);
			$html .= '</tr>';
		}

		// Attendees row
		if ( ! empty( $attendees ) ) {
			$html .= '<tr class="event-attendees-row">';
			$html .= '<td class="event-label" style="padding: 8px 0; color: #6b7280; width: 120px; vertical-align: top;">👥 Attendees:</td>';
			$html .= '<td class="event-value" style="padding: 8px 0; color: #111827;">';

			if ( count( $attendees ) <= 3 ) {
				$html .= esc_html( implode( ', ', $attendees ) );
			} else {
				$shown     = array_slice( $attendees, 0, 3 );
				$remaining = count( $attendees ) - 3;
				$html      .= esc_html( implode( ', ', $shown ) );
				$html      .= sprintf( ' <span class="event-attendees-more" style="color: #6b7280;">+%d more</span>', $remaining );
			}

			$html .= '</td></tr>';
		}

		$html .= '</table>';

		// Action buttons
		$html .= '<div class="event-actions" style="display: flex; gap: 12px; flex-wrap: wrap;">';

		// Join button for virtual events
		if ( $join_url ) {
			$html .= sprintf(
				'<a href="%s" class="component-button button-join" style="display: inline-block; background: #10b981; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">Join Meeting</a>',
				esc_url( $join_url )
			);
		}

		// Add to calendar button
		if ( $calendar_url ) {
			$html .= sprintf(
				'<a href="%s" class="component-button button-calendar" style="display: inline-block; background: #2563eb; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">Add to Calendar</a>',
				esc_url( $calendar_url )
			);
		}

		$html .= '</div>';

		$html .= '</div></div>';

		return $html;
	}

}