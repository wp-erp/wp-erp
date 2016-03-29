<tr class="{{ data.cls }}" id="erp-dept-{{ data.id }}">
    <th scope="row" class="check-column">
        <input id="cb-select-1" type="checkbox" name="dept[]" value="{{ data.id }}">
    </th>
    <td class="col-">

        <strong><a href="#">{{ data.title }}</a></strong>

        <div class="row-actions">
            <span class="edit"><a href="#" title="Edit this item" data-id="{{ data.id }}"><?php _e( 'Edit', 'erp' ); ?></a> | </span>
            <span class="trash"><a class="submitdelete" data-id="{{ data.id }}" title="<?php esc_attr_e( 'Delete this item', 'erp' ); ?>" href="#"><?php _e( 'Delete', 'erp' ); ?></a></span>
        </div>
    </td>
    <td class="col-">{{ data.lead }}</td>
    <td class="col-">{{ data.employee }}</td>
</tr>