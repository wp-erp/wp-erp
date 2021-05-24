<template>
    <div id="people-modal">
    </div>
</template>

<script>

export default {
    name: 'ImportModal',

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
            peopleType: '',
            fieldsHtml: '',
            nonce: '',
            error: '',
            showError: false,
        };
    },

    created() {
        var self = this;

        this.peopleFields = erp_acct_var.erp_fields;
        this.nonce = erp_acct_var.export_import_nonce;
        this.peopleType = 'customers' == this.type ? 'customer' : 'vendor';

        wp.ajax.send({
            data: {
                action: 'erp_acct_get_sample_csv_url',
                type: this.type,
                path: this.$router.currentRoute.path
            },
            success: function(response) {
                self.sampleUrl = response;
            }
        });
    },

    methods: {
        downloadSample() {
            window.location.href = this.sampleUrl;
        },
    },
};
</script>

<style lang="less" scoped>
</style>
