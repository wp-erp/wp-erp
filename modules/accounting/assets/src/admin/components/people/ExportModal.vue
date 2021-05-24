<template>
    <div id="people-modal">
    </div>
</template>

<script>

export default {
    name: 'ExportModal',

    props: {
        type: {
            type: String
        },
        title: {
            required: true
        },
    },

    data() {
        return {
            sampleUrl: '',
            peopleFields: [],
            fieldsHtml: '',
            nonce: '',
            description: '',
            peopleType: '',
        };
    },

    created() {
        this.peopleType   = 'customers' == this.type ? 'customer' : 'vendor';
        this.peopleFields = erp_acct_var.erp_fields ? erp_acct_var.erp_fields[this.peopleType].fields : [];
        this.nonce        = erp_acct_var.export_import_nonce;
        this.description  = 'customer' === this.peopleType
                          ? __('Select customer fields to export', 'erp')
                          : __('Select vendor fields to export', 'erp');
    },

    methods: {
        selectFields(e) {
            jQuery("#export_form #fields input[type=checkbox]").prop('checked', jQuery(e.target).prop("checked"));
        },

        strTitleCase(string) {
            var str = string.toString().replace(/_/g, ' ');

            return str.toLowerCase().split(' ').map(function (word) {
                return (word.charAt(0).toUpperCase() + word.slice(1));
            }).join(' ');
        },
    },
};
</script>

<style lang="less" scoped>
</style>
