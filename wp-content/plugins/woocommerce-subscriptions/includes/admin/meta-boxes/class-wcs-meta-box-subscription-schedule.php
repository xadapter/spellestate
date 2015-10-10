<?php
/**
 * Subscription Billing Schedule
 *
 * @author   Prospress
 * @category Admin
 * @package  WooCommerce Subscriptions/Admin/Meta Boxes
 * @version  2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WCS_Meta_Box_Schedule
 */
class WCS_Meta_Box_Schedule {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post, $the_subscription;

		if ( empty( $the_subscription ) ) {
			$the_subscription = wcs_get_subscription( $post->ID );
		}

		include( 'views/html-subscription-schedule.php' );
	}

	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {

		if ( 'shop_subscription' == $post->post_type && ! empty( $_POST['woocommerce_meta_nonce'] ) && wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {

			if ( isset( $_POST['_billing_interval'] ) ) {
				update_post_meta( $post_id, '_billing_interval', $_POST['_billing_interval'] );
			}

			if ( ! empty( $_POST['_billing_period'] ) ) {
				update_post_meta( $post_id, '_billing_period', $_POST['_billing_period'] );
			}

			$subscription = wcs_get_subscription( $post_id );

			$dates = array();

			foreach ( wcs_get_subscription_date_types() as $date_key => $date_label ) {

				if ( 'last_payment' == $date_key ) {
					continue;
				}

				$utc_timestamp_key = $date_key . '_timestamp_utc';

				// A subscription needs a start date, even if it wasn't set
				if ( isset( $_POST[ $utc_timestamp_key ] ) ) {
					$datetime = $_POST[ $utc_timestamp_key ];
				} elseif ( 'start' === $date_key ) {
					$datetime = current_time( 'timestamp', true );
				} else { // No date to set
					continue;
				}

				$dates[ $date_key ] = date( 'Y-m-d H:i:s', $datetime );
			}

			try {
				$subscription->update_dates( $dates, 'gmt' );
			} catch ( Exception $e ) {
				wcs_add_admin_notice( $e->getMessage(), 'error' );
			}
		}
	}
}