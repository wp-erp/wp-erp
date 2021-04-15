<template>
    <div class="timepicker">
        <p class="title">{{ __('Select Time', 'erp') }}</p>

        <div v-timepicker :id="elm"/>
        <input :value="value" type="text" style="display: none">
    </div>
</template>

<script>
import MtrDatepicker from './mtr-datepicker.min';

export default {

    name: 'Timepicker',

    directives: {
        timepicker: {
            inserted(el, binding, vnode) {
                vnode.context.timepickerObj = new MtrDatepicker({
                    target: el.id,
                    disableAmPm: vnode.context.hideAmPmDisplay,
                    smartHours: true
                });
            }
        }
    },

    props: {
        value: {
            type: String,
            required: true,
            default: () => ''
        },
        elm: {
            type: String,
            required: true,
            default: () => ''
        },
        hideAmPmDisplay: {
            type: Boolean,
            default: () => false
        }
    },

    data() {
        return {
            timepickerObj: null
        };
    },

    mounted() {
        let format = 'hh:mm A';

        if (this.hideAmPmDisplay) {
            format = 'hh:mm';
        }

        //    this.$emit('input', this.timepickerObj.format(format));

        this.timepickerObj.onChange('all', () => {
            if (this.hideAmPmDisplay) {
                this.$emit('input', this.timepickerObj.format(format));
            } else {
                this.$emit('input', this.timepickerObj.format(format));
            }
        });
    }

};
</script>

<style src="./mtr-datepicker.min.css"></style>
<style src="./mtr-datepicker.default-theme.min.css"></style>

<style lang="less">
    .timepicker {

        .title {
            padding: 10px 0 0;
            margin: 0;
            text-align: center;
            color: #000;
        }

        .mtr-datepicker {

            .mtr-content {
                .mtr-values .mtr-default-value,
                .mtr-input, .mtr-datepicker .mtr-content input {
                    background: #f5f8fa;
                    border: 1px solid #1A9ED4;
                    color: #222;
                }
            }

            .mtr-input-radio {
                margin-right: 0 !important;

                form {
                    display: grid;
                }

                label span.value {
                    color: #222;
                }
            }

            .mtr-input-slider .mtr-content {
                height: 46px !important;
            }

            .mtr-arrow:hover {
                background: none;
            }

        }

    }
</style>
