<template>
    <div :class="classes" :style="styles">

        <button type="button" class="handlediv" v-if="closable" @click="handleToggle">
            <span class="dashicons dashicons-arrow-down" v-if="closed"></span>
            <span class="dashicons dashicons-arrow-up" v-else></span>
        </button>

        <h3 class="hndle ui-sortable-handle">
            <span class="wp-metabox-title">{{title}}</span>
        </h3>

        <div class="inside">
            <div class="main">
                <slot name="metabox-content"></slot>

                <div class="wp-metabox-footer">
                    <slot name="metabox-footer"></slot>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
    export default{
        name: 'MetaBox',

        props: {
            title: {
                type: String,
            },
            closable: {
                type: Boolean
            }
        },
        data () {
            return {
                closed: false,
            };
        },
        computed: {
            classes () {
                return [
                    'postbox',
                    this.closed ? 'closed' : ''
                ];
            },
            styles () {
                return 'display: block;';
            }
        },
        methods: {
            handleToggle(event) {
                this.closed = !this.closed;
                this.$emit('metaboxToggle', event);
            }
        },
    }
</script>

<style scoped>

</style>
