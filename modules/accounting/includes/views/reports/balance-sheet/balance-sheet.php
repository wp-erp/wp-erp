<h1><?php _e( 'Accounting Reports: Balance Sheet', 'erp' ); ?></h1>
<div class="warp erp-ac-balance-sheet-wrap">	
	<div class="metabox-holder">

		<div class="postbox ">
			<h2 class="hndle"><span><?php _e( 'Assets', 'erp' ); ?></span></h2>
			<div class="inside">
				<table class="wp-list-table widefat">
					<thead>
						<tr>
							<th><strong><?php _e( 'Accounts', 'erp' ); ?></strong></th>
							<th><strong><?php _e( 'Balance', 'erp' ); ?></strong></th>
						</tr>
					</thead>
	             	
	             	<tbody>
	             		<tr>
	             			<td><a href="">Accounts Receivable</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             		<tr>
	             			<td><a href="">Less Accumulated Depreciation on Computer Equipment</a></td>
	             			<td>$70000.0000</td>
	             		</tr>
	             		<tr>
	             			<td><a href="">Accounts Receivable</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             		<tr>
	             			<td><a href="">Less Accumulated Depreciation on Computer Equipment</a></td>
	             			<td>$7.0</td>
	             		</tr>

	             			<tr>
	             			<td><a href="">Accounts Receivable</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             		<tr>
	             			<td><a href="">Less Accumulated Depreciation on Computer Equipment</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             		<tr>
	             			<td><a href="">Accounts Receivable</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             		<tr>
	             			<td><a href="">Less Accumulated Depreciation on Computer Equipment</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             	</tbody>
	            </table>
			</div>

			<div class="erp-ac-total-count">
				<table>
		           	<tr>
		           		<td><strong>Total</strong></td>
		           		<td><strong>$100</strong></td>
		           	</tr>
	           	</table>

	        </div>
		</div>
		
		<div class="postbox ">
			<h2 class="hndle"><?php _e( 'Liabilities', 'erp' ); ?></h2>
			<div class="inside">
				<table class="wp-list-table widefat">
					<thead>
						<tr>
							<th><strong><?php _e( 'Accounts', 'erp' ); ?></strong></th>
							<th><strong><?php _e( 'Balance', 'erp' ); ?></strong></th>
						</tr>
					</thead>
	             	
	             	<tbody>
	             		<tr>
	             			<td><a href="">Employee Deductions payable</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             		<tr>
	             			<td><a href="">Revenue Received in Advance</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             	</tbody>
	            </table>
			</div>

			<div class="erp-ac-total-count">
	            <table>
		           	<tr>
		           		<td><strong>Total</strong></td>
		           		<td><strong>$100</strong></td>
		           	</tr>
	           	</table>

	        </div>
		</div>

		<div class="postbox ">
			<h2 class="hndle"><?php _e( 'Equity', 'erp' ); ?></h2>
			<div class="inside">
				<table class="wp-list-table widefat">
					<thead>
						<tr>
							<th><strong><?php _e( 'Accounts', 'erp' ); ?></strong></th>
							<th><strong><?php _e( 'Balance', 'erp' ); ?></strong></th>
						</tr>
					</thead>
	             	
	             	<tbody>
	             		<tr>
	             			<td><a href="">Owners Contribution</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             		<tr>
	             			<td><a href="">Retained Earnings</a></td>
	             			<td>$7.0</td>
	             		</tr>
	             	</tbody>
	            </table>
			</div>

			<div class="erp-ac-total-count">
            	<table>
		           	<tr>
		           		<td><strong>Total</strong></td>
		           		<td><strong>$100</strong></td>
		           	</tr>
	           	</table>

            </div>
		</div>
		<div class="clear"></div>
	</div>	
</div>

<?php


global $wpdb;
$financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
$financial_end   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

$sql = "SELECT led.id, led.code, led.name, led.type_id, types.name as type_name, types.class_id, class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit
FROM wp_erp_ac_ledger as led
LEFT JOIN wp_erp_ac_chart_types as types ON types.id = led.type_id
LEFT JOIN wp_erp_ac_chart_classes as class ON class.id = types.class_id
LEFT JOIN wp_erp_ac_journals as jour ON jour.ledger_id = led.id
LEFT JOIN wp_erp_ac_transactions as tran ON tran.id = jour.transaction_id
WHERE tran.status IS NULL OR tran.status != 'draft' AND ( tran.issue_date >= '$financial_start' AND tran.issue_date <= '$financial_end' )
GROUP BY led.id";

$ledgers = $wpdb->get_results( $sql );
$charts  = [];
pr($ledgers);  die();
if ( $ledgers ) {
    foreach ($ledgers as $ledger) {
    
        if ( ! isset( $charts[ $ledger->class_id ] ) ) {
            $charts[ $ledger->class_id ]['label'] = $ledger->class_name;
            $charts[ $ledger->class_id ]['ledgers'][] = $ledger;
        } else {
            $charts[ $ledger->class_id ]['ledgers'][] = $ledger;
        }
    }
}

echo '<pre>'; print_r( $charts ); echo '</pre>';


		














