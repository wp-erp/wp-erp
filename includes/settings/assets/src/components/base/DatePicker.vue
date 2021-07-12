<template>
    <input type="text" :value="value" autocomplete="off" @input="changeDateInput" />
</template>

<script>
    export default {
        props: ['value', 'dependency'],

        mounted: function () {
            var self = this,
                limit_date = ( self.dependency == 'datepickter-from' ) ? 'maxDate' : 'minDate';

            jQuery( self.$el ).datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                numberOfMonths: 1,
                yearRange: "-100:+5",

                onClose: function( selectedDate ) {
                    jQuery( '.' + self.dependency ).datepicker( 'option', limit_date, selectedDate );
                },

                onSelect: function( dateText ) {
                    self.$emit('input', dateText);
                }
            });
        },

        methods: {
            changeDateInput( e ) {
                this.$emit('input', e.target.value);
            }
        },
    };
</script>
