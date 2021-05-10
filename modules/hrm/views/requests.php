<div class="wrap erp-hr-requests erp-hr-request-listing" id="erp-hr-requests" v-cloak>
    <h2><?php esc_attr_e( 'People', 'erp' ); ?></h2>

    <?php do_action( 'erp_hr_people_menu', 'requests' ); ?>

    <div class="list-table-wrap erp-hr-requests">
        <div class="list-table-inner">
            <form method="get">
                <div class="erp-table-wrapper">
                    <table class="wp-list-table widefat fixed striped requests">
                        <tbody id="the-list">
                            <tr v-if="requests" v-for="request in requests">
                                <th v-if="!hideCb" scope="row" class="check-column vertical-middle">
                                    <input class="vertical-super" type="checkbox" v-model="checkboxItems" name="checkboxItems[]" :value="request.id">
                                </th>

                                <td class="vertical-middle"><a :href="request.employee.url" target="_blank">{{ request.employee.name }}</a></td>
                                
                                <td class="vertical-middle" v-if="request.reason">{{ request.reason.title }}</td>
                                
                                <td class="vertical-middle" v-if="request.item">{{ request.item.name }}</td>
                                
                                <td class="vertical-middle" v-if="request.date">{{ request.date }}</td>
                                
                                <td class="vertical-middle" v-if="request.created && activeTopNav != 'remote_work'">{{ request.created }}</td>

                                <td class="text-center vertical-middle">
                                    <span class="req-status status-{{ request.status.id }}">{{ request.status.title }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
