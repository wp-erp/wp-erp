<template>
    <dropdown>
        <template slot="button">
            <input ref="datePicker" v-model="selectedDate" class="wperp-form-field">
        </template>
        <template slot="dropdown">
            <calendar
                @dayclick="pickerSelect"
                backgroundColor="#fff"
                :attributes="pickerAttrs" />
        </template>
    </dropdown>
</template>

<script>
    import Dropdown from 'admin/components/base/Dropdown.vue'

    import { setupCalendar, Calendar } from 'v-calendar'
    import 'v-calendar/lib/v-calendar.min.css'

    setupCalendar({
        firstDayOfWeek: 2
    });

    export default {
        name: 'Datepicker',

        components: {
            Dropdown,
            Calendar
        },

        props: { defaultDate : String },

        data() {
            return {
                pickerAttrs: [{
                    key: 'today',
                    highlight: { backgroundColor: '#1A9ED4' },
                    contentStyle: { color: '#fff' },
                    dates: new Date()
                }],
                selectedDate: this.getCurrentDate(),
            }
        },

        methods: {
            pickerSelect(day) {
                // add leading zero
                let days = day.day < 10 ? `0${day.day}` : day.day;
                let month = day.month < 10 ? `0${day.month}` : day.month;

                let formattedDate = day.year + '-' + month + '-' + days; //e.g. 2018-07-24
                this.selectedDate = formattedDate;
                this.$refs.datePicker.click();
                this.$emit('input', this.selectedDate);
            },

            getCurrentDate() {
                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth()+1;
                var yyyy = today.getFullYear();

                if(dd < 10) {
                    dd = '0' + dd
                }

                if(mm < 10) {
                    mm = '0' + mm
                }

                today = yyyy + '-' + mm + '-' + dd;

                return today;
            }
        }
    }
</script>

<style lang="less">
#erp-accounting {
    .c-pane-container {
        border: 0 !important;
        box-shadow: 0 0 6px 1px #ddd;
        border-radius: 3px;
        right: 0;
        top: 70px;

        .c-pane {
            z-index: 1;
        }
    }

    .c-header {
        background: #F9F9F9;
        height: 58px;

        .c-title {
            font-weight: bold !important;
            font-size: 16px !important;
        }
    }

    .c-weekdays {
        font-size: 10px !important;
        font-weight: bold !important;
        color: #000 !important;
        background: #fff;
        padding: 15px 5px 2px !important;
    }

    .c-weeks {
        background: #fff;
    }

    .c-weeks-rows-wrapper {
        color: #A5ACB1;

        .c-day-content {
            font-size: 11px;
            width: 2.5rem;
            height: 2.5rem;

            &:hover {
                background: rgb(218,218,218);
                cursor: pointer;
            }
        }
    }
}
</style>
