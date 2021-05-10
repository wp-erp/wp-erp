<div class="wrap erp-hr-requests erp-hr-request-listing" id="erp-hr-requests" v-cloak>
    <h2><?php esc_attr_e( 'People', 'erp' ); ?></h2>

    <?php do_action( 'erp_hr_people_menu', 'requests' ); ?>

    <div class="list-table-wrap erp-hr-requests">
        <div class="list-table-inner">
            <form method="get">
                <div class="erp-table-wrapper">
                    <table class="wp-list-table widefat fixed striped requests">
                        <thead>
                            <tr>
                                <td v-if="! hideCb" id="cb" class="manage-column column-cb check-column">
                                    <label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'erp' ); ?></label>
                                    
                                    <input id="cb-select-all-1" v-model="checkAllCheckbox" @change="triggerAllCheckBox()" type="checkbox">
                                </td>

                                <td class="actions bulkactions" v-if="hasBulkAction() && checkboxItems.length" :colspan="columnCount">
                                    <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( 'Select bulk action', 'erp' ); ?></label>
                                    
                                    <select name="action" id="bulk-action-selector-top" v-model="bulkaction1">
                                        <option value="-1"><?php _e( 'Bulk Actions', 'erp' ); ?></option>
                                        <option v-for="actions in bulkactions" value="{{ actions.id }}">{{ actions.text }}</option>
                                    </select>

                                    <input type="submit" id="doaction" @click.prevent="handleBulkAction(bulkaction1)" class="button action" value="<?php esc_attr_e( 'Apply', 'erp' ); ?>">
                                </td>
                                
                                <td v-if="!checkboxItems.length" v-for="header in tableHeaders" :class="header.class">{{ header.title }}</td>
                            </tr>
                        </thead>

                        <tbody id="the-list">
                            <tr v-if="requests" v-for="request in requests">
                                <th v-if="!hideCb" scope="row" class="check-column vertical-middle">
                                    <input class="vertical-super" type="checkbox" v-model="checkboxItems" name="checkboxItems[]" :value="request.id">
                                </th>

                                <td class="vertical-middle"><a :href="request.employee.url" target="_blank">{{ request.employee.name }}</a></td>
                                
                                <td class="vertical-middle" v-if="request.reason">{{ request.reason.title }}</td>
                                
                                <td class="vertical-middle" v-if="request.item">{{ request.item.name }}</td>
                                
                                <td class="vertical-middle" v-if="request.category">{{ request.category.name }}</td>
                                
                                <td class="vertical-middle" v-if="request.amount">{{ request.amount }}</td>
                                
                                <td class="vertical-middle" v-if="request.trn_date">{{ request.trn_date }}</td>
                                
                                <td class="vertical-middle" v-if="request.date">{{ request.date }}</td>
                                
                                <td class="vertical-middle" v-if="request.start_date">{{ request.start_date }}</td>
                                
                                <td class="vertical-middle" v-if="request.end_date">{{ request.end_date }}</td>
                                
                                <td class="vertical-middle" v-if="request.created && activeTopNav != 'remote_work'">{{ request.created }}</td>
                                
                                <td v-if="request.duration" class="text-center text-green vertical-middle">{{ request.duration.value }}</td>

                                <td class="text-center vertical-middle">
                                    <span class="req-status status-{{ request.status.id }}">{{ request.status.title }}</span>
                                </td>

                                <td class="text-center vertical-middle">
                                    <div class="erp-row-action-dropdown">
                                        <a href="#" @click.prevent="showRowActions($index)" class="erp-row-actions-btn {{ ! request.actions ? disabled : '' }}">
                                            <span class="dashicons dashicons-ellipsis"></span>
                                        </a>

                                        <div id="request-row-actions-{{ $index }}" class="dropdown-content">
                                            <a v-for="(key, action) in request.actions"
                                                href="#"
                                                @click.prevent="onActionClick(request.id, action.id, request.status.id)">
                                                <span v-if="action.class" :class="action.class"></span> {{ action.text }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!requests.length || !isLoaded">
                                <td :colspan="columnCount">
                                    <span v-if="!isLoaded"><?php _e( 'Loading', 'erp' ); ?>...</span>
                                    <span v-else><?php _e( 'No requests found.', 'erp' ); ?></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
