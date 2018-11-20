<template>
    <span>
        <span @click="toggleActions">&vellip;</span>

        <ul v-show="showActions" class="actions-box">
            <li v-for="action in actions" :key="action.key" :class="action.key">
                <a href="#" @click.prevent="actionClicked(action.key, row)">{{ action.label }}</a>
            </li>
        </ul>
    </span>
</template>


<script>
    export default {
        name: 'RowActions',

        props: {
            row: {
                type: Object,
                required: true,
            },
            actions: {
                type: Array,
                required: false,
                default: () => [],
            },
        },

        data() {
            return {
                showActions: false,
            };
        },

        methods: {
            toggleActions() {
                this.showActions = !this.showActions;
            },

            actionClicked(actionKey, row) {
                console.log(actionKey, row);
            },
        },
    };
</script>

<style lang="less" scoped>

    .actions-box {
        box-shadow: 1px 2px 3px 0 #ddd;
        border-radius: 3px;
        padding: 10px 0 5px 15px;
        position: absolute;
        min-width: 100px;
        margin-top: -35px;
        margin-left: -125px;
        background: #fff;

        &:after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            top: 30px;
            right: -13px;
            border-color: transparent transparent #fff #fff;
            transform-origin: 0 0;
            transform: rotate(-136deg);
            box-shadow: -2px 1px 1px 0 #ddd;
            border: .5em solid #fff;
        }
    }

</style>
