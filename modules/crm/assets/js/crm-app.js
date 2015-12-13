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
        dt: '2015-12-09'
    },

    methods: {
        showTab: function( id ){
            this.tabShow = id;
        },

        getCurrentDate: function() {
            var today = new Date();
            return today.toISOString().substring(0, 10);
        }
    }
});