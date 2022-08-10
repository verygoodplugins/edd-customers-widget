<?php

/*
 * Plugin Name: EDD Customers Widget
 * Description: Show new customers for the period on the EDD summary widget.
 * Plugin URI: https://verygoodplugins.com/
 * Version: 1.1
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
				'number'     => -1,
				'date_query' => array(
					'year'   => date( 'Y' )
				),
			);

			$this_year_customers = new EDD_Customer_Query( $args );

			$args = array(
				'number'     => -1,
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

			$yoy_change = ( count( $this_year_customers->items ) - count( $last_year_customers->items ) ) / count( $last_year_customers->items ) * 100;

			$yoy_change = round( $yoy_change, 1 );

			if ( 0 < $yoy_change ) {
				$yoy_change = '+' . $yoy_change;
			}

			$args = array(
				'number'     => -1,
				'date_query' => array(
					'year'  => date( 'Y' ),
					'month' => date( 'n' ),
				),
			);

			$this_month_customers = new EDD_Customer_Query( $args );

			$args = array(
				'number'     => -1,
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

			$mtd_change = ( count( $this_month_customers->items ) - count( $last_month_customers->items ) ) / count( $last_month_customers->items ) * 100;

			$mtd_change = round( $mtd_change, 1 );

			if ( 0 < $mtd_change ) {
				$mtd_change = '+' . $mtd_change;
			}

			$args = array(
				'number'     => -1,
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

			$mtdyoy_change = ( count( $this_month_customers->items ) - count( $this_month_last_year_customers->items ) ) / count( $this_month_last_year_customers->items ) * 100;

			$mtdyoy_change = round( $mtdyoy_change, 1 );

			if ( 0 < $mtdyoy_change ) {
				$mtdyoy_change = '+' . $mtdyoy_change;
			}

			$data = array(
				'ytd'           => count( $this_year_customers->items ),
				'mtd'           => count( $this_month_customers->items ),
				'yoy_change'    => $yoy_change,
				'mtd_change'    => $mtd_change,
				'mtdyoy_change' => $mtdyoy_change,
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
$widget = new EDD_Customers_Widget;
unset( $widget );
