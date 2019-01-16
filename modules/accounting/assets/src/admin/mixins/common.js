export default {
    methods: {
        formatAmount( val, prefix = true ) {
            let currency = '$';
            if ( val < 0 ){
                return prefix ? `Cr. ${currency} ${Math.abs(val)}` : `${currency} ${Math.abs(val)}`;
            }

            return prefix ? `Dr. ${currency} ${val}` : `${currency} ${Math.abs(val)}`;
        }
    }
}
