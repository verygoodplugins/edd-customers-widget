<?php

/*
 * Plugin Name: EDD Customers Widget
 * Description: Show new customers and manual license renewals for the period on the EDD summary widget.
 * Plugin URI: https://github.com/verygoodplugins/edd-customers-widget
 * Version: 1.2.0
 * Author: Very Good Plugins
 * Author URI: https://verygoodplugins.com/
*/

class EDD_Customers_Widget {

	/**
	 * Get things started
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Add our actions
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function init() {
		add_action( 'edd_sales_summary_widget_after_stats', array( $this, 'widget' ), 20 );
	}

	/**
	 * Display the widget
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function widget() {

		$data = get_transient( 'edd_customers_stats' );

		// No transient.
		if ( false === $data ) {

			$args = array(
				'number'     => 9999999,
				'count'      => true,
				'date_query' => array(
					'year'   => date( 'Y' )
				),
			);

			$this_year_customers = new EDD_Customer_Query( $args );

			$args = array(
				'number'     => 9999999,
				'count'      => true,
				'date_query' => array(
					'inclusive' => true,
					'after'     => array(
						'year'  => date( 'Y' ) - 1,
						'month' => 1,
						'day'   => 1,
					),
					'before'    => array(
						'year'  => date( 'Y' ) - 1,
						'month' => date( 'n' ),
						'day'   => date( 'd' ),
					),
				),
			);

			$last_year_customers = new EDD_Customer_Query( $args );

			if ( ! empty( $last_year_customers->items ) ) {

				$yoy_change = ( $this_year_customers->items - $last_year_customers->items ) / $last_year_customers->items * 100;

				$yoy_change = round( $yoy_change, 1 );

				if ( 0 < $yoy_change ) {
					$yoy_change = '+' . $yoy_change;
				}
			} else {
				$yoy_change = '+∞';
			}

			$args = array(
				'number'     => 9999999,
				'count'      => true,
				'date_query' => array(
					'year'  => date( 'Y' ),
					'month' => date( 'n' ),
				),
			);

			$this_month_customers = new EDD_Customer_Query( $args );

			$args = array(
				'number'     => 9999999,
				'count'      => true,
				'date_query' => array(
					'inclusive' => true,
					'after'     => array(
						'year'  => date( 'Y' ),
						'month' => date( 'n' ) - 1,
						'day'   => 1,
					),
					'before'    => array(
						'year'  => date( 'Y' ),
						'month' => date( 'n' ) - 1,
						'day'   => date( 'd' ),
					),
				),
			);

			$last_month_customers = new EDD_Customer_Query( $args );

			if ( ! empty( $last_month_customers->items ) ) {

				$mtd_change = ( $this_month_customers->items - $last_month_customers->items ) / $last_month_customers->items * 100;

				$mtd_change = round( $mtd_change, 1 );

				if ( 0 < $mtd_change ) {
					$mtd_change = '+' . $mtd_change;
				}

			} else {
				$mtd_change = '+∞';
			}

			$args = array(
				'number'     => 9999999,
				'count'      => true,
				'date_query' => array(
					'inclusive' => true,
					'after'     => array(
						'year'  => date( 'Y' ) - 1,
						'month' => date( 'n' ),
						'day'   => 1,
					),
					'before'    => array(
						'year'  => date( 'Y' ) - 1,
						'month' => date( 'n' ),
						'day'   => date( 'd' ),
					),
				),
			);

			$this_month_last_year_customers = new EDD_Customer_Query( $args );

			if ( ! empty( $this_month_last_year_customers->items ) ) {

				$mtdyoy_change = ( $this_month_customers->items - $this_month_last_year_customers->items ) / $this_month_last_year_customers->items * 100;

				$mtdyoy_change = round( $mtdyoy_change, 1 );

				if ( 0 < $mtdyoy_change ) {
					$mtdyoy_change = '+' . $mtdyoy_change;
				}
			} else {
				$mtdyoy_change = '+∞';
			}

			// Manual renewals data.

			$args = array(
				'status'     => 'publish',
				'meta_query' => array(
					array(
						'key'     => '_edd_sl_is_renewal',
						'value'   => '1',
						'compare' => '=',
					),
					array(
						'key'     => 'subscription_id',
						'compare' => 'NOT EXISTS',
					),
				),
				'date_query'  => array(
					'inclusive' => true,
				),
				'year'        => gmdate( 'Y' ),
			);

			$payments = edd_get_payments( $args );

			$total_revenue_year = 0;

			foreach ( $payments as $payment ) {
				$total_revenue_year += edd_get_payment_amount( $payment->ID );
			}

			$args['month'] = gmdate( 'n' );

			$payments = edd_get_payments( $args );

			$total_revenue_month = 0;

			foreach ( $payments as $payment ) {
				$total_revenue_month += edd_get_payment_amount( $payment->ID );
			}

			$data = array(
				'ytd'           => $this_year_customers->items,
				'mtd'           => $this_month_customers->items,
				'yoy_change'    => $yoy_change,
				'mtd_change'    => $mtd_change,
				'mtdyoy_change' => $mtdyoy_change,
				'renewals_ytd'  => $total_revenue_year,
				'renewals_mtd'  => $total_revenue_month,
			);

			set_transient( 'edd_customers_stats', $data, HOUR_IN_SECONDS );

		}

		?>
		<div class="table table_left table_current_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'New Customers' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t"><?php _e( 'This Year' ); ?></td>
						<td class="b" style="white-space: nowrap;">
							<?php echo $data['ytd'] ?>  <small style="color: <?php echo $this->color( $data['yoy_change'] ); ?>">( <?php echo $data['yoy_change']; ?>% )</small>
						</td>
					</tr>
					<tr>
						<td class="first t"><?php _e( 'This Month (mtd)' ); ?></td>
						<td class="b" style="white-space: nowrap;">
							<?php echo $data['mtd']; ?> <small style="color: <?php echo $this->color( $data['mtd_change'] ); ?>">( <?php echo $data['mtd_change']; ?>% )</small>
						</td>
					</tr>
					<tr>
						<td class="first t"><?php _e( 'This Month (yoy)' ); ?></td>
						<td class="b" style="white-space: nowrap;">
							<?php echo $data['mtd']; ?> <small style="color: <?php echo $this->color( $data['mtdyoy_change'] ); ?>">( <?php echo $data['mtdyoy_change']; ?>% )</small>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="table table_right table_current_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Manual License Renewals' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t"><?php _e( 'This Year' ); ?></td>
						<td class="b" style="white-space: nowrap;">
							<?php echo edd_currency_filter( edd_format_amount( $data['renewals_ytd'] ) ); ?>  
						</td>
					</tr>
					<tr>
						<td class="first t"><?php _e( 'This Month' ); ?></td>
						<td class="b" style="white-space: nowrap;">
						<?php echo edd_currency_filter( edd_format_amount( $data['renewals_mtd'] ) ); ?>  
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both"></div>

		<?php
	}

	/**
	 * Get the text color based on the text value
	 *
	 * @since  1.0.0
	 * @return void
	 */

	private function color( $value ) {

		if ( 0 === strpos( $value, '+' ) ) {
			return 'green';
		} else {
			return '#d63638';
		}

	}

}
new EDD_Customers_Widget;
