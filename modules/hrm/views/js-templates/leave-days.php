<# if ( data.days ) { #>
    <div class="table-wrap">
        <table class="list-days">
        <# _.each( data.days, function(day, index) { #>
            <tr>
                <td>{{ day.date }}</td>
                <td><input type="text" value="{{ day.count }}" readonly="readonly" size="1"> <?php esc_html_e( 'day', 'erp' ); ?></td>
            </tr>
        <# }) #>
        </table>

        <div class="total"><?php esc_html_e( 'Total: ', 'erp' ); ?> {{ data.total }}</div>
    </div>
<# } #>
