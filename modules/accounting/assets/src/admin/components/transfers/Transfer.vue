<template>
    <div class="wperp-container">
        <div id="wperp-transfer-money-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
            <div class="wperp-modal-dialog">
                <div class="wperp-modal-content">
                    <!-- modal body title -->
                    <div class="wperp-modal-header">
                        <h3>Transfer Money</h3>
                        <span class="modal-close"><i class="flaticon-close"></i></span>
                    </div>
                    <!-- end modal body title -->
                    <form action="" method="post" class="modal-form edit-customer-modal">
                        <div class="wperp-modal-body">
                            <!-- add new product form -->
                            <div class="wperp-row wperp-gutter-20">
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="transfer_funds_from">Transfer Funds From</label>
                                    <div class="wperp-custom-select">
                                        <select name="transfer_funds_from" id="transfer_funds_from" class="wperp-form-field wperp-is-select2">
                                            <option value="0">Select</option>
                                            <option value="1">Option1</option>
                                            <option value="2">Option2</option>
                                            <option value="3">Option3</option>
                                        </select>
                                        <i class="flaticon-arrow-down-sign-to-navigate"></i>
                                    </div>
                                    <span class="balance mt-10 display-inline-block">Balance: $30,000.0</span>
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="transfer_funds_to">Transfer Funds To</label>
                                    <div class="wperp-custom-select">
                                        <select name="transfer_funds_to" id="transfer_funds_to" class="wperp-form-field wperp-is-select2">
                                            <option value="0">Select</option>
                                            <option value="1">Option1</option>
                                            <option value="2">Option2</option>
                                            <option value="3">Option3</option>
                                        </select>
                                        <i class="flaticon-arrow-down-sign-to-navigate"></i>
                                    </div>
                                    <span class="balance mt-10 display-inline-block">Balance: $30,000.0</span>
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="transfer_amount">Transfer Amount</label>
                                    <input type="text" name="transfer_amount" id="transfer_amount" class="wperp-form-field" placeholder="$100.00">
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="transfer_date">Transfer Date</label>
                                    <div class="wperp-has-datepicker">
                                        <input type="date" name="transfer_date" id="transfer_date" class="wperp-form-field" placeholder="$100.00">
                                        <span class="datepicker-icon">
                                        <i class="flaticon-calendar"></i>
                                    </span>
                                    </div>
                                </div>
                                <div class="wperp-col-xs-12 wperp-form-group">
                                    <label for="transfer_memo">Memo</label>
                                    <textarea name="transfer_memo" id="transfer_memo" rows="3" class="wperp-form-field" placeholder="Type Here"></textarea>
                                </div>
                                <div class="wperp-col-xs-12">
                                    <div class="attachment-container">
                                        <label class="col--attachement">Attachment</label>
                                        <div class="attachment-preview">
                                            <img src="assets/images/img-thumb.png" alt="attachment image">
                                            <i class="flaticon-close remove-attachment"></i>
                                        </div>
                                        <div class="attachment-placeholder">
                                            To attach <input type="file" id="attachment" name="attachment" class="display-none"> <label class="mt-0" for="attachment">Select files</label> from your computer
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="wperp-modal-footer pt-0">
                            <button class="wperp-btn btn--primary" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        name: "Transfer"
    }
</script>

<style scoped>

</style>
