export default {
    methods: {
        formatAmount( val, prefix = false ) {
            let currency = '$';
            if ( val < 0 ) {
                return prefix ? `Cr. ${currency}${Math.abs(val)}` : `${currency}${Math.abs(val)}`;
            }

            return prefix ? `Dr. ${currency}${val}` : `${currency}${Math.abs(val)}`;
        },

        getCurrencySign() {
            return '$';
        },

        showAlert(type, message) {
            this.$swal({
                position         : 'center',
                type             : type,
                title            : message,
                showConfirmButton: false,
                timer            : 1500
            });
        },

        getFileName(path) {
            return path.replace(/^.*[\\\/]/, '');
        },
    }
}
