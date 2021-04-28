import accounting from 'accounting';

const dateFormat = erp_settings_var.date_format;

export default {
    methods: {
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

        formatDate(d) {
            if (! d) {
                return '';
            }

            var date = new Date(d),
                month = date.getMonth() + 1,
                day = date.getDate(),
                year = date.getFullYear();

            if (month.toString().length < 2) {
                month = '0' + month;
            }

            if (day.toString().length < 2) {
                day = '0' + day;
            }

            switch (dateFormat) {
                case 'd/m/Y':  // -- 31/12/2020
                    return [day, month, year].join('/');

                case 'm/d/Y':  // -- 12/31/2020
                    return [month, day, year].join('/');

                case 'm-d-Y':  // -- 12-31-2020
                    return [month, day, year].join('-');

                case 'd-m-Y':  // -- 31-12-2020
                    return [day, month, year].join('-');

                case 'Y-m-d':  // -- 2020-12-31
                    return [year, month, day].join('-');

                case 'd.m.Y':  // -- 31.12.2020
                    return [day, month, year].join('.');

                default:
                    return date.toDateString().replace(/^\S+\s/, '');
            }
        },
    }
};
