<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Asset Report.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=asset-report" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h2><?php esc_html_e( 'Asset Report', 'erp' ); ?></h2>

        <!-- Filters — right-aligned -->
        <div style="display: flex; gap: 10px; align-items: center; margin: 15px 0; justify-content: flex-end; flex-wrap: wrap;">
            <select disabled style="min-width: 160px;">
                <option>&mdash; <?php esc_html_e( 'All Time', 'erp' ); ?> &mdash;</option>
            </select>
            <select disabled style="min-width: 160px;">
                <option>&mdash; <?php esc_html_e( 'All Categories', 'erp' ); ?> &mdash;</option>
            </select>
            <button class="button erp-pro-preview-action"><?php esc_html_e( 'Filter', 'erp' ); ?></button>
        </div>

        <!-- Asset Expenditure — horizontal bar chart (matches Flot.js style) -->
        <div class="postbox">
            <div class="erp-handlediv" title="Click to toggle"><br></div>
            <h3 class="erp-hndle" style="padding: 8px 12px; display: flex; align-items: center;"><span class="dashicons dashicons-chart-bar" style="margin-right: 5px;"></span> <?php esc_html_e( 'Asset Expenditure', 'erp' ); ?></h3>
            <div class="inside">
                <div style="padding-left: 20px; display: flex; min-height: 300px;">
                    <!-- Chart area -->
                    <div style="align-self: flex-end; width: 80%; display: inline-block;">
                        <?php
                        $max_amount = 18500;
                        $categories = [
                            [ 'name' => 'Laptop',    'amount' => 18500 ],
                            [ 'name' => 'Monitor',   'amount' => 5400 ],
                            [ 'name' => 'Furniture', 'amount' => 3890 ],
                            [ 'name' => 'Mobile',    'amount' => 2999 ],
                            [ 'name' => 'Software',  'amount' => 1200 ],
                        ];
                        ?>
                        <!-- Chart grid with bars -->
                        <div style="position: relative; border-bottom: 2px solid #000; padding: 10px 0;">
                            <!-- Vertical grid lines -->
                            <div style="position: absolute; top: 0; bottom: 0; left: 20%; border-left: 1px solid #e5e7eb;"></div>
                            <div style="position: absolute; top: 0; bottom: 0; left: 40%; border-left: 1px solid #e5e7eb;"></div>
                            <div style="position: absolute; top: 0; bottom: 0; left: 60%; border-left: 1px solid #e5e7eb;"></div>
                            <div style="position: absolute; top: 0; bottom: 0; left: 80%; border-left: 1px solid #e5e7eb;"></div>

                            <?php foreach ( $categories as $cat ) :
                                $percent = round( ( $cat['amount'] / $max_amount ) * 100 );
                            ?>
                            <div style="display: flex; align-items: center; height: 50px;">
                                <span style="min-width: 80px; font-size: 12px; color: #000; text-align: right; padding-right: 10px;"><?php echo esc_html( $cat['name'] ); ?></span>
                                <div style="flex: 1; display: flex; align-items: center; height: 100%;">
                                    <div style="width: <?php echo esc_attr( $percent ); ?>%; background: rgba(35, 191, 170, 0.6); border: 1px solid #23bfaa; height: 60%; min-width: 2px;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- X-axis ticks -->
                        <div style="display: flex; margin-left: 90px; padding-top: 6px;">
                            <div style="flex: 1; text-align: left;"><span style="font-size: 11px; color: #555;">$0</span></div>
                            <div style="flex: 1; text-align: center;"><span style="font-size: 11px; color: #555;">$5,000</span></div>
                            <div style="flex: 1; text-align: center;"><span style="font-size: 11px; color: #555;">$10,000</span></div>
                            <div style="flex: 1; text-align: center;"><span style="font-size: 11px; color: #555;">$15,000</span></div>
                            <div style="flex: 0; text-align: right;"><span style="font-size: 11px; color: #555;">$20,000</span></div>
                        </div>
                        <p style="text-align: center; font-size: 12px; color: #333; margin: 8px 0 5px; padding-left: 90px;"><strong><?php esc_html_e( 'Expenditure', 'erp' ); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Current Status Count — pie chart -->
        <div class="postbox" style="margin-top: 20px;">
            <div class="erp-handlediv" title="Click to toggle"><br></div>
            <h3 class="erp-hndle" style="padding: 8px 12px; display: flex; align-items: center;"><span class="dashicons dashicons-chart-pie" style="margin-right: 5px;"></span> <?php esc_html_e( 'Asset Current Status Count', 'erp' ); ?></h3>
            <div class="inside" style="position: relative; min-height: 400px; padding: 20px;">
                <!-- Pie Chart -->
                <div style="width: 40%; height: 400px; display: flex; align-items: center; justify-content: center;">
                    <div style="position: relative; width: 280px; height: 280px; border-radius: 50%; background: conic-gradient(#ae82bd 0% 45%, #67c4cc 45% 82%, #fcc1b6 82% 100%);">
                        <!-- Labels on pie -->
                        <div style="position: absolute; top: 25%; left: 20%; background: rgba(0,0,0,0.8); color: #fff; font-size: 11px; padding: 2px 6px; border-radius: 2px;">
                            <?php esc_html_e( 'In Stock', 'erp' ); ?><br>45%
                        </div>
                        <div style="position: absolute; top: 55%; left: 55%; background: rgba(0,0,0,0.8); color: #fff; font-size: 11px; padding: 2px 6px; border-radius: 2px;">
                            <?php esc_html_e( 'Allotted', 'erp' ); ?><br>37%
                        </div>
                        <div style="position: absolute; top: 78%; left: 15%; background: rgba(0,0,0,0.8); color: #fff; font-size: 11px; padding: 2px 6px; border-radius: 2px;">
                            <?php esc_html_e( 'Dismissed', 'erp' ); ?><br>18%
                        </div>
                    </div>
                </div>

                <!-- Legend — positioned to the right of pie -->
                <div style="position: absolute; left: 45%; top: 150px; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 16px; height: 12px; background: #ae82bd;"></span>
                        <span style="font-size: 13px; color: #4b5563;"><?php esc_html_e( 'In Stock', 'erp' ); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 16px; height: 12px; background: #67c4cc;"></span>
                        <span style="font-size: 13px; color: #4b5563;"><?php esc_html_e( 'Allotted', 'erp' ); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 16px; height: 12px; background: #fcc1b6;"></span>
                        <span style="font-size: 13px; color: #4b5563;"><?php esc_html_e( 'Dismissed', 'erp' ); ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
