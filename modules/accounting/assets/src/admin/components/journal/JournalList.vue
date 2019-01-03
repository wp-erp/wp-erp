<template>

    <table class="wperp-table table-striped table-dark widefat table2">
                <thead>
                    <tr>
                        <td scope="col" class="col--check">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input id="cb-select-all" class="form-check-input" type="checkbox" value="">
                                    <span class="form-check-sign">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                        </td>
                        <th scope="col" class="col--name column-primary">Date</th>
                        <th scope="col" class="col--email">Type</th>
                        <th scope="col" class="col--phone">Ref</th>
                        <th scope="col" class="col--life-stage">Customer</th>
                        <th scope="col" class="col--owner">Due Date</th>
                        <th scope="col" class="col--cd">Due</th>
                        <th scope="col" class="col--total">Total</th>
                        <th scope="col" class="col--status">Status</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row" class="col--check">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox">
                                    <span class="form-check-sign">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                        </th>
                        <td class="col--name column-primary">26-09-18
                            <button type="button" class="wperp-toggle-row">
                                <span class="screen-reader-text">Show more details</span>
                            </button>
                        </td>
                        <td class="col--email" data-colname="Type">Invoice</td>
                        <td class="col--phone" data-colname="Ref">012</td>
                        <td class="col--life-stage subscriber" data-colname="Customer">Rizvi Amin khan</td>
                        <td class="col--owner" data-colname="Due Date">06-09-2018</td>
                        <td class="col--cd" data-colname="Due">$24,000.00</td>
                        <td class="col--cd" data-colname="Total">$24,000.00</td>
                        <td class="col--cd" data-colname="Status">Recieved</td>
                        <td class="col--actions">
                            <div class="wperp-has-dropdown dropdown right--middle">
                                <a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#"><i class="flaticon-printer"></i>Print</a></li>
                                    <li><a href="#"><i class="flaticon-sent-mail"></i>Send</a></li>
                                    <li><a href="#"><i class="flaticon-alarm-clock"></i>Send Reminder</a></li>
                                    <li><a href="#"><i class="flaticon-share"></i>Share Invoice</a></li>
                                    <li><a href="#"><i class="flaticon-link-symbol"></i>Print Packing Link</a></li>
                                    <li><a href="#"><i class="flaticon-edit"></i>View/Edit</a></li>
                                    <li><a href="#"><i class="flaticon-copy-content"></i>Copy</a></li>
                                    <li><a href="#"><i class="flaticon-trash"></i>Delete</a></li>
                                    <li><a href="#"><i class="flaticon-delete"></i>Void</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="col--check">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox">
                                    <span class="form-check-sign">
                                    <span class="check"></span>
                                </span>
                                </label>
                            </div>
                        </th>
                        <td class="col--name column-primary">26-09-18
                            <button type="button" class="wperp-toggle-row">
                                <span class="screen-reader-text">Show more details</span>
                            </button>
                        </td>
                        <td class="col--email" data-colname="Email Address">Invoice</td>
                        <td class="col--phone" data-colname="Phone">012</td>
                        <td class="col--life-stage subscriber" data-colname="Life Stage">Rizvi Amin khan</td>
                        <td class="col--owner" data-colname="Owner">06-09-2018</td>
                        <td class="col--cd" data-colname="Created At">$24,000.00</td>
                        <td class="col--cd" data-colname="Created At">$24,000.00</td>
                        <td class="col--cd" data-colname="Created At">Recieved</td>
                        <td class="col--actions">
                            <div class="wperp-has-dropdown dropdown right--middle">
                                <a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#"><i class="flaticon-printer"></i>Print</a></li>
                                    <li><a href="#"><i class="flaticon-sent-mail"></i>Send</a></li>
                                    <li><a href="#"><i class="flaticon-alarm-clock"></i>Send Reminder</a></li>
                                    <li><a href="#"><i class="flaticon-share"></i>Share Invoice</a></li>
                                    <li><a href="#"><i class="flaticon-link-symbol"></i>Print Packing Link</a></li>
                                    <li><a href="#"><i class="flaticon-edit"></i>View/Edit</a></li>
                                    <li><a href="#"><i class="flaticon-copy-content"></i>Copy</a></li>
                                    <li><a href="#"><i class="flaticon-trash"></i>Delete</a></li>
                                    <li><a href="#"><i class="flaticon-delete"></i>Void</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

</template>

<script>
    export default {
        name: "JournalList"
    }
</script>

<style scoped>

</style>
