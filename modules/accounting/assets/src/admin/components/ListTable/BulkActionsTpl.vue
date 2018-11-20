<template>
    <tr>
        <td v-if="showCb" class="manage-column column-cb check-column">
            <input v-model="bulkSelectAll" type="checkbox" @change="changeBulkCheckbox">
        </td>
        <th v-if="hasBulkActions" :colspan="columnsCount">
            <ul>
                <li v-for="bulkAction in bulkActions" :key="bulkAction.key">
                    <img :src="bulkAction.img" :alt="bulkAction.label">
                    <span>{{ bulkAction.label }}</span>
                </li>
            </ul>
        </th>
    </tr>
</template>

<script>
    export default {
        name: 'BulkActionsTpl',

        props: {
            bulkActions: {
                type: Array,
                required: false,
                default: () => [],
            },

            showCb: {
                type: Boolean,
                default: true,
            },

            columnsCount: {
                type: Number,
                default: 0,
            },

            selectAll: {
                type: Boolean,
                default: false,
            },
        },

        data() {
            return {
                bulkSelectAll: this.selectAll,
            };
        },

        computed: {
            hasBulkActions() {
                return this.bulkActions.length > 0;
            },
        },

        methods: {
            changeBulkCheckbox() {
                this.$parent.$emit('bulk-chkbx', this.bulkSelectAll);
            },
        },
    };
</script>
