<template>
    <tr>
        <td v-if="showCb" class="manage-column column-cb check-column">
            <div class="form-check">
                <label class="form-check-label">
                    <input v-model="bulkSelectAll" type="checkbox" @change="changeBulkCheckbox" ref="removeBulkAction" class="form-check-input" >
                    <span class="form-check-sign">
                        <span class="check"></span>
                    </span>
                </label>
            </div>
        </td>
        <th v-if="hasBulkActions" :colspan="columnsCount">
            <ul class="wp-erp-bulk-actions">
                <li v-for="bulkAction in bulkActions" :key="bulkAction.key" @click="bulkActionSelect(bulkAction.key)">
                    <!-- <img :src="bulkAction.img" :alt="bulkAction.label"> -->
                    <a href="#">
                        <i :class="bulkAction.iconClass"></i>
                        <span>{{ bulkAction.label }}</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="close-div" @click.prevent="removeBulkActions"><i class="flaticon-close"></i></a>
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
            default: () => []
        },

        showCb: {
            type: Boolean,
            default: true
        },

        columnsCount: {
            type: Number,
            default: 0
        },

        selectAll: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            bulkSelectAll: this.selectAll
        };
    },

    computed: {
        hasBulkActions() {
            return this.bulkActions.length > 0;
        }
    },

    methods: {
        removeBulkActions() {
            this.$refs.removeBulkAction.click();
        },
        changeBulkCheckbox() {
            this.$parent.$emit('bulk-checkbox', this.bulkSelectAll);
        },

        bulkActionSelect(key) {
            this.$parent.$emit('bulk-action-click', key);
        }
    }
};
</script>

<style lang="less" scoped>
    .wp-erp-bulk-actions {
        display: flex;
        margin: 0;
        li {
            margin: 0;
            cursor: pointer;
            &:first-child {
                a, i {
                    margin-left: 0;
                }
            }
            a {
                 display: inline-flex;
                color: #23282d;
                font-size: 12px;
                margin: 0 10px;
                i {
                    margin: 0 5px;
                }
                &:hover {
                    color: #f96332;
                    i:before {
                        color: #f96332;
                    }
                }
            }
        }
    }
</style>
