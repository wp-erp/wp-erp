export default {
    methods: {
        formatAmount( val ) {
            let currency = '$';
            if ( val < 0 ){
                return `Cr. ${currency} ${Math.abs(val)}`;
            }

            return `Dr. ${currency} ${val}`;
        }
    }
}
