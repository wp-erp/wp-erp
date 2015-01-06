<tr class="{{ data.class }}">
    <th scope="row" class="check-column">
        <input id="cb-select-1" type="checkbox" name="employee[]" value="{{ data.id }}">
    </th>
    <td class="username col- column-username">
        {{{ data.avatar.image }}}

        <strong><a href="{{ data.url }}">{{ data.name.full_name }}</a></strong>

        <div class="row-actions">
            <span class="edit"><a href="#" data-id="{{ data.id }}" title="<?php echo esc_attr( 'Edit this item', 'wp-erp' ); ?>"><?php _e( 'Edit', 'wp-erp' ); ?></a> | </span>
            <span class="trash"><a class="submitdelete" data-id="{{ data.id }}" title="<?php echo esc_attr( 'Delete this item', 'wp-erp' ); ?>" href="#"><?php _e( 'Delete', 'wp-erp' ); ?></a></span>
        </div>
    </td>
    <td class="col-">{{ data.work.designation_title }}</td>
    <td class="col-">{{ data.work.department_title }}</td>
    <td class="col-">{{ data.work.type }}</td>
    <td class="col-">{{ data.work.joined }}</td>
</tr>