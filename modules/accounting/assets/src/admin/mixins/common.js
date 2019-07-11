import accounting from 'accounting'

export default {
    methods: {
        formatAmount( val, prefix = false ) {
            if ( val < 0 ) {
                return prefix ? `Cr. ${this.moneyFormat( Math.abs(val) )}` : `${this.moneyFormat( Math.abs(val) )}`;
            }

            return prefix ? `Dr. ${this.moneyFormat(val)}` : `${this.moneyFormat(Math.abs(val))}`;
        },

        formatDBAmount( val, prefix = false ) {
            if ( val < 0 ) {
                return `(-) ${this.moneyFormat( Math.abs(val) )}`;
            }

            return this.moneyFormat(val);
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

        decodeHtml(str) {
            let regex = /^[A-Za-z0-9 ]+$/;

            if ( regex.test( str ) ) {
                return str;
            }

            let txt = document.createElement('textarea');
            txt.innerHTML = str;

            return txt.value;
        },

        moneyFormat( number ) {
            let options = {
                symbol  : erp_acct_var.symbol,
                decimal : erp_acct_var.decimal_separator,
                thousand: erp_acct_var.thousand_separator,
                format  : erp_acct_var.currency_format
            };

            return accounting.formatMoney( number, options );
        }
    }
}
