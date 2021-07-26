<template>
    <div :class="['wperp-modal', 'wperp-modal-open', hasForm ? 'wperp-has-form' : ''] ">
        <div :class="modalContentClass">
            <div class="wperp-modal-content">
                <div class="wperp-modal-header">
                    <slot v-if="header" name="header">
                        <h3>{{ title }}</h3>
                    </slot>
                    <span @click="$emit('close')" class="modal-close"><i class="flaticon-close"></i></span>
                </div>
                <div class="wperp-modal-body">
                    <slot name="body"/>
                </div>
                <div v-if="footer" class="wperp-modal-footer">
                    <slot name="footer"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {

    name: 'Modal',

    props: {
        header: {
            type    : Boolean,
            required: false,
            default : false
        },

        footer: {
            type    : Boolean,
            required: false,
            default : false
        },

        title: {
            type    : String,
            required: false,
            default : ''
        },

        hasForm: {
            type    : Boolean,
            required: false,
            default : false
        },

        size: {
            type    : String,
            required: false,
            default : 'md'
        }
    },

    computed: {
        modalContentClass : function() {
            return `wperp-modal-dialog wperp-modal-dialog-${this.size}`;
        }
    },
};
</script>
