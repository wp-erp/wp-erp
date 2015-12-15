Vue.directive('datepicker', {
    bind: function () {
        var vm = this.vm;
        var key = this.expression;

        jQuery(this.el).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: '-100:+0',
            onSelect: function (date) {
                vm.$set(key, date);
            }
        });
    },
    update: function (val) {
        jQuery(this.el).datepicker('setDate', val);
    }
});

new Vue({
    el: '#erp-customer-feeds',
    data: {
        tabShow: 'new_note',
    },

    methods: {
        showTab: function( id ){
            this.tabShow = id;
        },

        getTodayDate: function() {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();

            if(dd<10) {
                dd='0'+dd
            }

            if(mm<10) {
                mm='0'+mm
            }

            today = yyyy+'-'+mm+'-'+dd;
            return today;
        }
    },

    computed: {
        dt : function() {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();

            if(dd<10) {
                dd='0'+dd
            }

            if(mm<10) {
                mm='0'+mm
            }

            today = yyyy+'-'+mm+'-'+dd;
            return today;
        }
    }

});