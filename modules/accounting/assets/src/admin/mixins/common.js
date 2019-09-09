import accounting from 'accounting';

/* global erp_acct_var */
const currencyOptions = {
    symbol  : erp_acct_var.symbol,
    decimal : erp_acct_var.decimal_separator,
    thousand: erp_acct_var.thousand_separator,
    format  : erp_acct_var.currency_format
};

export default {
    methods: {
        formatAmount(val, prefix = false) {
            if (val < 0) {
                return prefix ? `Cr. ${this.moneyFormat(Math.abs(val))}` : `${this.moneyFormat(Math.abs(val))}`;
            }

            return prefix ? `Dr. ${this.moneyFormat(val)}` : `${this.moneyFormat(Math.abs(val))}`;
        },

        formatDBAmount(val, prefix = false) {
            if (val < 0) {
                return `(-) ${this.moneyFormat(Math.abs(val))}`;
            }

            return this.moneyFormat(val);
        },

        showAlert(type, message) {
            this.$swal({
                position: 'center',
                type: type,
                title: message,
                showConfirmButton: false,
                timer: 1500
            });
        },

        getFileName(path) {
            // eslint-disable-next-line no-useless-escape
            return path.replace(/^.*[\\\/]/, '');
        },

        decodeHtml(str) {
            const regex = /^[A-Za-z0-9 ]+$/;

            if (regex.test(str)) {
                return str;
            }

            const txt = document.createElement('textarea');
            txt.innerHTML = str;

            return txt.value;
        },

        moneyFormat(number) {
            return accounting.formatMoney(number, currencyOptions);
        },

        moneyFormatwithDrCr(value) {
            var DrCr = null;

            if (value.indexOf('Dr') > 0) {
                DrCr = 'Dr ';
            } else if (value.indexOf('Dr') === -1) {
                DrCr = 'Cr ';
            }

            const money = accounting.formatMoney(value, currencyOptions);

            return DrCr + money;
        },

        noFulfillLines(lines, selected) {
            let nofillLines = false;

            for (const item of lines) {
                if (!Object.prototype.hasOwnProperty.call(item, selected)) {
                    nofillLines = true;
                } else {
                    nofillLines = false;
                    break;
                }
            }

            return nofillLines;
        }
    }
};
