<# var total = 0; #>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php _e( 'Component Name', 'erp' ); ?></th>
            <th><?php _e( 'Agency Name', 'erp' ); ?></th>
            <th><?php _e( 'Rate(%)', 'erp' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <#

        if ( ! data.data.length ) {
            #>
            <tr>
                <td colspan="3"><?php _e( 'No data found!', 'erp' ); ?></td>
            </tr>
            <#
        }
        _.each( data.data, function( element, index ) {
            total = parseFloat( element.tax_rate ) + parseFloat( total );
            #>
            <tr>
                <td> {{element.component_name}} </td>
                <td> {{element.agency_name}} </td>
                <td> {{element.tax_rate}} </td>
            </tr>
            <#
        });

        #>
        <tr>
            <td colspan="2"><strong><?php _e( 'Total', 'erp' ); ?></strong></td>
            <td>{{total}}</td>
        </tr>
    </tbody>
</table>