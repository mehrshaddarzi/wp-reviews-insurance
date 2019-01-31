<?php

namespace WP_REVIEWS_INSURANCE\WP_List_Table;

use WP_REVIEWS_INSURANCE;
use WP_REVIEWS_INSURANCE\Admin_Page;
use WP_REVIEWS_INSURANCE\Gravity_Form;
use WP_REVIEWS_INSURANCE\Helper;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Reviews extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'factor',
			'plural'   => 'factors',
			'ajax'     => false
		) );
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		//Column Option
		$this->_column_headers = $this->get_column_info();

		//Process Bulk and Row Action
		$this->process_bulk_action();

		//Prepare Data
		$per_page     = $this->get_items_per_page( 'factor_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		//Create Pagination
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

		//return items
		$this->items = self::get_actions( $per_page, $current_page );
	}

	/**
	 * Retrieve Items data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_actions( $per_page = 10, $page_number = 1 ) {
		global $wpdb;

		//$tbl = $wpdb->prefix . WP_Statistics_Actions::table;
		$tbl = 'z_factor';
		$sql = "SELECT * FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		//Check Order By
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		} else {
			$sql .= ' ORDER BY `id`';
		}

		//Check Order Fields
		$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' DESC';
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Conditional sql
	 */
	public static function conditional_sql() {
		//Where conditional
		$where = false;

		//Check Search
		if ( isset( $_GET['s'] ) and ! empty( $_GET['s'] ) ) {
			$search  = sanitize_text_field( $_GET['s'] );
			$where[] = "`id` = $search";
		}

		//Check Factor For Order
		if ( isset( $_GET['order'] ) and ! empty( $_GET['order'] ) ) {
			$where[] = '`order_id` =' . $_GET['order'];
		}

		//Check filter Creator User
		if ( isset( $_GET['user'] ) and ! empty( $_GET['user'] ) ) {
			$where[] = '`user_id` =' . $_GET['user'];
		}

		return $where;
	}

	/**
	 * Delete a action record.
	 *
	 * @param int $id action ID
	 */
	public static function delete_action( $id ) {
		global $wpdb;
		Helper::remove_factor( $id );
		//$tbl = $wpdb->prefix . WP_Statistics_Actions::table;
		//$wpdb->delete( $tbl, array( 'ID' => $id ), array( '%d' ) );
	}


	/**
	 * Returns the count of records in the database.
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		//$tbl = $wpdb->prefix . WP_Statistics_Actions::table;
		$tbl = 'z_factor';
		$sql = "SELECT COUNT(*) FROM `$tbl`";

		//Where conditional
		$conditional = self::conditional_sql();
		if ( ! empty( $conditional ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $conditional );
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Not Found Item Text
	 */
	public function no_items() {
		_e( 'هیچ فاکتوری یافت نشد.', 'wp-statistics-actions' );
	}

	/**
	 *  Associative array of columns
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'factor_id'  => __( 'شناسه فاکتور', 'wp-statistics-actions' ),
			'date'       => __( 'تاریخ فاکتور', 'wp-statistics-actions' ),
			'user'       => __( 'برای کاربر', 'wp-statistics-actions' ),
			'order'      => __( 'متعلق به سفارش', 'wp-statistics-actions' ),
			'type'       => __( 'نوع فاکتور', 'wp-statistics-actions' ),
			'price'      => __( 'مبلغ فاکتور ' . '(' . Helper::currency() . ')', 'wp-statistics-actions' ),
			'pay_status' => __( 'وضعیت پرداخت', 'wp-statistics-actions' )
		);

		return $columns;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		global $WP_Statistics;

		//Default unknown Column Value
		$unknown = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __( "Unknown", 'wp-statistics-actions' ) . '</span>';

		switch ( $column_name ) {
			case 'factor_id' :

				// row actions to ID
				//$actions['id'] = '<span class="text-muted">#' . $item['ID'] . '</span>';

				//row actions to edit
				if ( $item['payment_status'] == 1 ) {
					$actions['edit'] = '<a href="' . add_query_arg( array( 'page' => 'factor', 'method' => 'edit', 'factor_id' => $item['id'], 'order_id' => $item['order_id'] ), admin_url( "admin.php" ) ) . '">' . __( 'ویرایش', 'wp-statistics-actions' ) . '</a>';
				}

				//Row Action to Clone
				$actions['view'] = '<a target="_blank" href="' . add_query_arg( array( 'view_factor' => $item['id'], 'redirect' => 'xx-admin', '_security_code' => wp_create_nonce( 'view_factor_access' ) ), home_url() ) . '" class="text-success">' . __( 'نمایش فاکتور', 'wp-statistics-actions' ) . '</a>';

				// row actions to Delete
				if ( $item['payment_status'] == 1 ) {
					$actions['trash'] = '<a onclick="return confirm(\'آیا مطمئن هستید ؟\')"  href="' . add_query_arg( array( 'page' => 'factor', 'action' => 'delete', '_wpnonce' => wp_create_nonce( 'delete_action_nonce' ), 'del' => $item['id'] ), admin_url( "admin.php" ) ) . '">' . __( 'حذف', 'wp-statistics-actions' ) . '</a>';
				}

				return $item['id'] . $this->row_actions( $actions );
				break;

			case 'date' :
				$date                   = date_i18n( "j F Y", strtotime( $item['date'] ) );
				$actions['create_time'] = date_i18n( "H:i:s", strtotime( $item['date'] ) );

				return $date . $this->row_actions( $actions );
				break;
			case 'user' :

				return '<div>' . Helper::get_user_full_name( $item['user_id'] ) . ' <br /> ' . Helper::get_user_mobile( $item['user_id'] ) . '<br />' . Helper::get_user_email( $item['user_id'] ) . '</div>';
				break;
			case 'order' :
				$order = Helper::get_order( $item['order_id'] );

				return '<span class="text-danger">' . $item['order_id'] . '</span> <br/><a target="_blank" href="' . admin_url() . 'admin.php?page=gf_entries&view=entry&id=' . Gravity_Form::$order_form_id . '&lid=' . $order['entry_id'] . '&order=ASC&filter&paged=1&pos=0&field_id&operator">جزئیات سفارش</a>';
				break;
			case 'type' :

				return '<span class="text-success">' . Helper::get_type_factor( $item['type'] ) . '</span>';
				break;
			case 'price' :

				return number_format_i18n( $item['price'] ) . ' ' . Helper::currency();
				break;
			case 'pay_status' :
				$order = Helper::get_order( $item['order_id'] );
				$t     = Helper::get_status_factor( $item['payment_status'] ) . '<br>';
				if ( $item['payment_status'] == 1 ) {
					$t .= '<a href="' . Admin_Page::admin_link( 'factor', array( 'top' => 'change-payment-status', 'order_id' => $item['order_id'], 'order_status' => $order['status'], 'status' => $item['payment_status'], 'factor_id' => $item['id'] ) ) . '">تغییر وضعیت</a>';
				}
				return $t;
				break;
		}
	}

	/**
	 * Columns to make sortable.
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'date' => array( 'date', false ),
			'user' => array( 'user_id', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'حذف', 'wp-statistics-actions' ),
		);

		return $actions;
	}

	/**
	 * Search Box
	 *
	 * @param $text
	 * @param $input_id
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" placeholder="<?php echo __( "شماره فاکتور", 'wp-statistics-actions' ); ?>" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" autocomplete="off"/>
			<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
        </p>
		<?php
	}

	/**
	 * Bulk and Row Actions
	 */
	public function process_bulk_action() {
		global $wpdb;


		//Content Action : Edit Factor
		if ( isset( $_REQUEST['content-action'] ) and $_REQUEST['content-action'] == "edit-factor" ) {


			//Get Before Price
			$this_factor  = Helper::get_factor( $_POST['factor_id'] );
			$before_price = $this_factor['price'];

			//Set Factor item
			$Sum_Factor = 0;
			$z          = 0;
			foreach ( $_POST['item'] as $item ) {
				if ( trim( $_POST['item'][ $z ] ) != "" and trim( $_POST['price'][ $z ] ) != "" ) {
					$Sum_Factor = $Sum_Factor + $_POST['price'][ $z ];
				}
				$z ++;
			}

			//Get Main Price
			$price            = $price_main = $Sum_Factor;
			$discount_percent = 0;

			//if Pish Factor
			if ( $_POST['type'] == 1 ) {
				//Check Ghabel Pardakht
				if ( $_POST['payable_price'] == 1 ) {
					$price = round( ( $Sum_Factor * 50 ) / 100 );
				}
			}

			//if main Factor
			if ( $_POST['type'] == 2 ) {

				//Calculate Discount
				if ( ! empty( $_POST['discount_percent'] ) and $_POST['discount_percent'] != 0 ) {
					$discount_percent = $_POST['discount_percent'];
					$price            = $price - round( ( $price * $discount_percent ) / 100 );
				}

				//if last pish factor
				$sum_pish_factor = 0;
				if ( $_POST['is_calculate_price_main'] == 1 ) {
					$sum_pish_factor = $wpdb->get_var( "SELECT SUM(price) FROM `z_factor` WHERE `order_id` = {$_POST['order_id']} AND `payment_status` = 2 AND `type` = 1" );
				}
				$price = $price - $sum_pish_factor;

			}


			//Save To database
			$order = Helper::get_order( $_POST['order_id'] );
			$wpdb->update(
				'z_factor',
				array(
					'user_id'          => $order['user_id'],
					'order_id'         => $_POST['order_id'],
					'type'             => $_POST['type'],
					'price'            => $price,
					'discount_percent' => $discount_percent,
					'price_main'       => $price_main,
				),
				array( 'id' => $_POST['factor_id'] )
			);
			$factor_id = $_POST['factor_id'];

			//Remove All Factor item
			Helper::remove_factor_items( $_POST['factor_id'] );

			//Set Factor item
			$sum = 0;
			$z   = 0;
			foreach ( $_POST['item'] as $item ) {
				if ( trim( $_POST['item'][ $z ] ) != "" and trim( $_POST['price'][ $z ] ) != "" ) {
					$wpdb->insert(
						'z_factor_item',
						array(
							'factor_id' => $factor_id,
							'item'      => $_POST['item'][ $z ],
							'price'     => $_POST['price'][ $z ]
						)
					);
					$sum = $sum + $_POST['price'][ $z ];
				}
				$z ++;
			}

			//Set Sum Price
			$sum = $price;

			//Push Notification
			if ( $before_price != $sum ) {

				//Send Sms
				$arg         = array( "factor_id" => $_POST['factor_id'], "factor_price" => $sum, "factor_type" => $_POST['type'], "order_id" => $_POST['order_id'], "user_name" => Helper::get_user_full_name( $order['user_id'] ) );
				$user_mobile = Helper::get_user_mobile( $order['user_id'] );
				if ( $user_mobile != "" ) {
					WP_REVIEWS_INSURANCE::send_sms( $user_mobile, '', 'send_to_user_at_edit_factor', $arg );
				}

				//Send Email
				$user_mail = Helper::get_user_email( $order['user_id'] );
				if ( $user_mail != "" ) {
					$subject = "تغییر مبلغ فاکتور به شناسه  " . $_POST['factor_id'];

					$content = '<p>';
					$content .= 'کاربر گرامی ';
					$content .= Helper::get_user_full_name( $order['user_id'] );
					$content .= '</p><p>';
					$content .= " مبلغ فاکتور به شناسه ";
					$content .= $_POST['order_id'];
					$content .= " به ";
					$content .= number_format( $arg['factor_price'] ) . ' ' . \WP_REVIEWS_INSURANCE\Helper::currency() . ' ';
					$content .= ' تغییر پیدا کرد .';
					$content .= '</p><br />';
					$content .= '<p>با تشکر</p>';
					$content .= '<p><a href="' . get_bloginfo( "url" ) . '">' . get_bloginfo( "name" ) . '</a></p>';

					WP_REVIEWS_INSURANCE::send_mail( $user_mail, $subject, $content );
				}

			}

			wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'factor', 'alert' => 'edit-factor' ), admin_url( "admin.php" ) ) ) );
			exit;
		}


		//Content Action : New Factor
		if ( isset( $_REQUEST['content-action'] ) and $_REQUEST['content-action'] == "add-factor" ) {

			//Set Factor item
			$Sum_Factor = 0;
			$z          = 0;
			foreach ( $_POST['item'] as $item ) {
				if ( trim( $_POST['item'][ $z ] ) != "" and trim( $_POST['price'][ $z ] ) != "" ) {
					$Sum_Factor = $Sum_Factor + $_POST['price'][ $z ];
				}
				$z ++;
			}

			//Get Main Price
			$price            = $price_main = $Sum_Factor;
			$discount_percent = 0;

			//if Pish Factor
			if ( $_POST['type'] == 1 ) {
				//Check Ghabel Pardakht
				if ( $_POST['payable_price'] == 1 ) {
					$price = round( ( $Sum_Factor * 50 ) / 100 );
				}
			}

			//if main Factor
			if ( $_POST['type'] == 2 ) {

				//Calculate Discount
				if ( ! empty( $_POST['discount_percent'] ) and $_POST['discount_percent'] != 0 ) {
					$discount_percent = $_POST['discount_percent'];
					$price            = $price - round( ( $price * $discount_percent ) / 100 );
				}

				//if last pish factor
				$sum_pish_factor = 0;
				if ( $_POST['is_calculate_price_main'] == 1 ) {
					$sum_pish_factor = $wpdb->get_var( "SELECT SUM(price) FROM `z_factor` WHERE `order_id` = {$_POST['order_id']} AND `payment_status` = 2 AND `type` = 1" );
				}
				$price = $price - $sum_pish_factor;

			}


			//Save To database
			$order = Helper::get_order( $_POST['order_id'] );
			$wpdb->insert(
				'z_factor',
				array(
					'user_id'          => $order['user_id'],
					'order_id'         => $_POST['order_id'],
					'date'             => current_time( 'mysql' ),
					'type'             => $_POST['type'],
					'price'            => $price,
					'discount_percent' => $discount_percent,
					'price_main'       => $price_main,
					'payment_status'   => 1
				)
			);
			$factor_id = $wpdb->insert_id;

			//Set Factor item
			$sum = 0;
			$z   = 0;
			foreach ( $_POST['item'] as $item ) {
				if ( trim( $_POST['item'][ $z ] ) != "" and trim( $_POST['price'][ $z ] ) != "" ) {
					$wpdb->insert(
						'z_factor_item',
						array(
							'factor_id' => $factor_id,
							'item'      => $_POST['item'][ $z ],
							'price'     => $_POST['price'][ $z ]
						)
					);
					$sum = $sum + $_POST['price'][ $z ];
				}
				$z ++;
			}

			//Set Sum Price
			$sum = $price;

			//Set Factor Price
//			$wpdb->update(
//				'z_factor',
//				array(
//					'price' => $sum
//				),
//				array( 'id' => $factor_id )
//			);

			//change Order Status
			Helper::change_status_order( $_POST['order_id'], $_POST['new-status-order'], false );

			//Push Notification
			if ( $_POST['is-notification'] == "yes" ) {

				//Send Sms
				$arg         = array( "factor_id" => $factor_id, "factor_price" => $sum, "factor_type" => $_POST['type'], "order_id" => $_POST['order_id'], "new_status" => Helper::show_status( $_POST['new-status-order'] ), "user_name" => Helper::get_user_full_name( $order['user_id'] ) );
				$user_mobile = Helper::get_user_mobile( $order['user_id'] );
				if ( $user_mobile != "" ) {
					WP_REVIEWS_INSURANCE::send_sms( $user_mobile, '', 'send_to_user_at_create_factor', $arg );
				}

				//Send Email
				$user_mail = Helper::get_user_email( $order['user_id'] );
				if ( $user_mail != "" ) {
					$subject = "فاکتور به شناسه  " . $factor_id;

					$content = '<p>';
					$content .= 'کاربر گرامی ';
					$content .= Helper::get_user_full_name( $order['user_id'] );
					$content .= '</p><p>';
					if ( $arg['factor_type'] == 1 ) {
						$content .= 'پیش فاکتور ';
					} else {
						$content .= 'فاکتور ';
					}
					$content .= "به مبلغ ";
					$content .= number_format( $arg['factor_price'] ) . ' ' . \WP_REVIEWS_INSURANCE\Helper::currency() . ' ';
					$content .= 'برای سفارش به شناسه ';
					$content .= $arg['order_id'];
					$content .= ' ایجاد شده است.لطفا نسبت به پرداخت آن اقدام نمایید.';
					$content .= '</p><br /><p>';
					$content .= '
					<table>
					    <thead>
                        <tr>
                        <th> #</th>
                        <th> توضیحات</th>
                        <th> مبلغ ' . \WP_REVIEWS_INSURANCE\Helper::currency() . '</th>
                        </tr>
                        </thead>
                        <tbody>
                        ';

					$c          = 1;
					$list_items = Helper::get_factor_items( $factor_id );
					foreach ( $list_items as $f_k => $f_v ) {

						$content .= '
						 <tr>
						 <td>' . $c . '</td>
						 <td>' . $f_v['name'] . '</td>
						 <td>' . number_format_i18n( $f_v['price'] ) . ' ' . Helper::currency() . '</td>
						    </tr>
						    ';
						$c ++;
					}

					$content .= '
                    <tr>
                    <td colspan="2" >جمع کل فاکتور</td>
                    <td>' . number_format_i18n( $price_main ) . ' ' . Helper::currency() . '</td>
                    </tr>';

					if ( $discount_percent != 0 ) {
						$content .= '
                        <tr>
                        <td colspan="2" >تخفیف (' . $discount_percent . '%)</td>
                        <td>' . number_format_i18n( round( ( $price_main * $discount_percent ) / 100 ) ) . ' ' . Helper::currency() . '</td>
                        </tr>';
					}

					if ( $price_main != $price and $_POST['type'] == 2 ) {
						$content .= '
                            <tr>
                            <td colspan="2" >مبلغ پرداخت شده</td>
                            <td>' . number_format_i18n( $wpdb->get_var( "SELECT SUM(price) FROM `z_factor` WHERE `order_id` = {$_POST['order_id']} AND `payment_status` = 2 AND `type` = 1" ) ) . ' ' . Helper::currency() . '</td>
                            </tr>';
					}

					$content .= '
                    <tr>
                    <td colspan="2" >قابل پرداخت</td>
                    <td>' . number_format_i18n( $sum ) . ' ' . Helper::currency() . '</td>
                    </tr>
                    </tbody>
					</table>
					';
					$content .= '</p><br />';
					$content .= '<p>با تشکر</p>';
					$content .= '<p><a href="' . get_bloginfo( "url" ) . '">' . get_bloginfo( "name" ) . '</a></p>';

					WP_REVIEWS_INSURANCE::send_mail( $user_mail, $subject, $content );
				}

			}


			wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'factor', 'alert' => 'create-factor' ), admin_url( "admin.php" ) ) ) );
			exit;
		}


		//Content Action : Change Status Factor
		if ( isset( $_POST['new-status-factor'] ) ) {

			//push notification
			$is_push_notification = false;
			if ( $_POST['is-notification'] == "yes" ) {
				$is_push_notification = true;
			}

			//Change Status Factor
			Helper::change_factor_status( $_POST['factor_id'], $_POST['new-status-factor'] );

			//change Status Payment
			if ( isset( $_POST['payment_id'] ) ) {
				Helper::change_payment_status( $_POST['payment_id'], $_POST['new-status-factor'] );
			}

			//change status Order
			Helper::change_status_order( $_POST['order_id'], $_POST['new-status-order'], $is_push_notification );
			sleep( 1 );

			wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'factor', 'alert' => 'change-status' ), admin_url( "admin.php" ) ) ) );
			exit;
		}


		// Row Action Delete
		if ( 'delete' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'delete_action_nonce' ) ) {
				die( __( "You are not Permission for this action.", 'wp-statistics-actions' ) );
			} else {
				self::delete_action( absint( $_GET['del'] ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'factor', 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}


		//Bulk Action Delete
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) ) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			if ( is_array( $delete_ids ) and count( $delete_ids ) > 0 ) {
				foreach ( $delete_ids as $id ) {
					self::delete_action( $id );
				}

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => 'factor', 'alert' => 'delete' ), admin_url( "admin.php" ) ) ) );
				exit;
			}
		}

	}

}