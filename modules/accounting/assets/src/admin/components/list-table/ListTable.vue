<template>
  <div :class="{ 'table-loading': loading }">

    <div v-if="loading" class="table-loader-wrap">
      <div class="table-loader-center">
        <div class="table-loader">Loading</div>
      </div>
    </div>

    <div class="tablenav top">

      <div class="alignleft actions">
        <slot name="filters" />
      </div>

      <div class="tablenav-pages">
        <span class="displaying-num">{{ itemsTotal }} items</span>

        <span v-if="hasPagination" class="pagination-links">
          <span v-if="disableFirst" class="tablenav-pages-navspan"
                aria-hidden="true">&laquo;</span>
          <a v-else href="#"
             class="first-page" @click.prevent="goToPage(1);"><span aria-hidden="true">&laquo;</span></a>

          <span v-if="disablePrev" class="tablenav-pages-navspan"
                aria-hidden="true">&lsaquo;</span>
          <a v-else href="#"
             class="prev-page" @click.prevent="goToPage(currentPage - 1);"><span aria-hidden="true">&lsaquo;</span></a>

          <span class="paging-input">
            <span class="tablenav-paging-text">
              <input :value="currentPage" class="current-page"
                     type="text" name="paged" aria-describedby="table-paging" size="1" @keyup.enter="goToCustomPage" > of
              <span class="total-pages">{{ totalPages }}</span>
            </span>
          </span>

          <span v-if="disableNext" class="tablenav-pages-navspan"
                aria-hidden="true">&rsaquo;</span>
          <a v-else href="#"
             class="next-page" @click.prevent="goToPage(currentPage + 1);"><span aria-hidden="true">&rsaquo;</span></a>

          <span v-if="disableLast" class="tablenav-pages-navspan"
                aria-hidden="true">&raquo;</span>
          <a v-else href="#"
             class="last-page" @click.prevent="goToPage(totalPages)"><span aria-hidden="true">&raquo;</span></a>
        </span>
      </div>
    </div>

    <table :class="tableClass">
      <thead>
        <bulk-actions-tpl v-if="checkedItems.length"
                          :select-all="selectAll"
                          :bulk-actions="bulkActions"
                          :show-cb="showCb"
                          :columns-count="columnsCount" />

        <tr v-else>
          <td v-if="showCb" class="manage-column column-cb check-column">
            <input v-model="selectAll" type="checkbox">
          </td>
          <th v-for="(value, key) in columns" :key="key" :class="[
            'column',
            key,
            { 'sortable': isSortable(value) },
            { 'sorted': isSorted(key) },
            { 'asc': isSorted(key) && sortOrder === 'asc' },
            { 'desc': isSorted(key) && sortOrder === 'desc' }
          ]">
            <template v-if="!isSortable(value)">
              {{ value.label }}
            </template>
            <a v-else href="#"
               @click.prevent="handleSortBy(key)">
              <span>{{ value.label }}</span>
              <span class="sorting-indicator"/>
            </a>
          </th>
        </tr>
      </thead>
      <tfoot>
        <bulk-actions-tpl v-if="checkedItems.length"
                          :select-all="selectAll"
                          :bulk-actions="bulkActions"
                          :show-cb="showCb"
                          :columns-count="columnsCount" />

        <tr v-else>
          <td v-if="showCb" class="manage-column column-cb check-column"><input v-model="selectAll" type="checkbox"></td>
          <th v-for="(value, key) in columns" :key="key"
              :class="['column', key]">{{ value.label }}</th>
        </tr>
      </tfoot>
      <tbody>
        <template v-if="rows.length">
          <tr v-for="(row, i) in rows" :key="row[index]">
            <th v-if="showCb" scope="row" class="check-column">
              <input :value="row[index]" v-model="checkedItems" type="checkbox" name="item[]">
            </th>
            <td v-for="(value, key) in columns" :key="key"
                :class="['column', key, { 'selected': checkedItems.includes(row[index]) }]">
              <slot :name="key" :row="row">
                {{ row[key] }}
              </slot>

              <div v-if="actionColumn === key && hasActions" class="row-actions">
                <slot :row="row" name="row-actions">
                  <dropdown placement="left">
                    <template slot="button">
                      <span>&vellip;</span>
                    </template>
                    <template slot="dropdown">
                      <ul slot="action-items">
                        <li v-for="action in actions" :key="action.key" :class="action.key">
                          <a href="#" @click.prevent="actionClicked(action.key, row, i)">{{ action.label }}</a>
                        </li>
                      </ul>
                    </template>
                  </dropdown>
                </slot>
              </div>

            </td>
          </tr>
        </template>
        <tr v-else>
          <td :colspan="colspan">{{ notFound }}</td>
        </tr>
      </tbody>
    </table>
    <div class="tablenav bottom">

      <div class="tablenav-pages">
        <span class="displaying-num">{{ itemsTotal }} items</span>

        <span v-if="hasPagination" class="pagination-links">
          <span v-if="disableFirst" class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>
          <a v-else href="#"
             class="first-page" @click.prevent="goToPage(1);"><span aria-hidden="true">&laquo;</span></a>

          <span v-if="disablePrev" class="tablenav-pages-navspan"
                aria-hidden="true">&lsaquo;</span>
          <a v-else href="#"
             class="prev-page" @click.prevent="goToPage(currentPage - 1);"><span aria-hidden="true">&lsaquo;</span></a>

          <span class="paging-input">
            <span class="tablenav-paging-text">
              {{ currentPage }} of
              <span class="total-pages">{{ totalPages }}</span>
            </span>
          </span>

          <span v-if="disableNext" class="tablenav-pages-navspan"
                aria-hidden="true">&rsaquo;</span>
          <a v-else href="#"
             class="next-page" @click.prevent="goToPage(currentPage + 1);"><span aria-hidden="true">&rsaquo;</span></a>

          <span v-if="disableLast" class="tablenav-pages-navspan"
                aria-hidden="true">&raquo;</span>
          <a v-else href="#"
             class="last-page" @click.prevent="goToPage(totalPages)"><span aria-hidden="true">&raquo;</span></a>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
/* eslint-disable */

import BulkActionsTpl from 'admin/components/list-table/BulkActionsTpl.vue'
import Dropdown from 'admin/components/base/Dropdown.vue'

export default {

  name: 'ListTable',

  components: {
    BulkActionsTpl,
    Dropdown,
  },

  props: {
    columns: {
      type: Object,
      required: true,
      default: () => {},
    },
    rows: {
      type: Array, // String, Number, Boolean, Function, Object, Array
      required: true,
      default: () => [],
    },
    index: {
      type: String,
      default: 'id',
    },
    showCb: {
      type: Boolean,
      default: true,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    actionColumn: {
      type: String,
      default: '',
    },
    actions: {
      type: Array,
      required: false,
      default: () => [],
    },
    bulkActions: {
      type: Array,
      required: false,
      default: () => [],
    },
    tableClass: {
      type: String,
      default: 'wp-list-table widefat fixed striped',
    },
    notFound: {
      type: String,
      default: 'No items found.',
    },
    totalItems: {
      type: Number,
      default: 0,
    },
    totalPages: {
      type: Number,
      default: 1,
    },
    perPage: {
      type: Number,
      default: 20,
    },
    currentPage: {
      type: Number,
      default: 1,
    },
    sortBy: {
      type: String,
      default: null,
    },
    sortOrder: {
      type: String,
      default: 'asc',
    },
  },

  data() {
    return {
      bulkLocal: '-1',
      checkedItems: [],
    };
  },

  computed: {

    hasActions() {
      return this.actions.length > 0;
    },

    itemsTotal() {
      return this.totalItems || this.rows.length;
    },

    hasPagination() {
      return this.itemsTotal > this.perPage;
    },

    disableFirst() {
      if (this.currentPage === 1 || this.currentPage === 2) {
        return true;
      }

      return false;
    },

    disablePrev() {
      if (this.currentPage === 1) {
        return true;
      }

      return false;
    },

    disableNext() {
      if (this.currentPage === this.totalPages) {
        return true;
      }

      return false;
    },

    disableLast() {
      if (this.currentPage === this.totalPages || this.currentPage == (this.totalPages - 1)) {
        return true;
      }

      return false;
    },

    columnsCount() {
      return Object.keys(this.columns).length;
    },

    colspan() {
      let columns = Object.keys(this.columns).length;

      if (this.showCb) {
        columns += 1;
      }

      return columns;
    },

    selectAll: {

      get() {
        if (!this.rows.length) {
          return false;
        }

        return this.rows ? this.checkedItems.length == this.rows.length : false;
      },

      set(value) {
        const selected = [];
        const self = this;

        if (value) {
          this.rows.forEach((item) => {
            if (item[self.index] !== undefined) {
              selected.push(item[self.index]);
            } else {
              selected.push(item.id);
            }
          });
        }

        this.checkedItems = selected;
      },
    },
  },

  created() {
    this.$on('bulk-checkbox', e => {
      if ( ! e ) {
        this.checkedItems = [];
      }
    });

    this.$on('bulk-action-click', key => {
      this.bulkLocal = key;
      this.handleBulkAction();
    });
  },

  methods: {

    hideActionSeparator(action) {
      return action === this.actions[this.actions.length - 1].key;
    },

    actionClicked(action, row, index) {
      this.$emit('action:click', action, row, index);
    },

    goToPage(page) {
      this.$emit('pagination', page);
    },

    goToCustomPage(event) {
      const page = parseInt(event.target.value, 10);

      if (!isNaN(page) && (page > 0 && page <= this.totalPages)) {
        this.$emit('pagination', page);
      }
    },

    handleBulkAction() {
      if ( this.bulkLocal === '-1' ) {
        return;
      }

      this.$emit('bulk:click', this.bulkLocal, this.checkedItems);
    },

    isSortable(column) {
      if (column.hasOwnProperty('sortable') && column.sortable === true) {
        return true;
      }

      return false;
    },

    isSorted(column) {
      return column === this.sortBy;
    },

    handleSortBy(column) {
      const order = this.sortOrder === 'asc' ? 'desc' : 'asc';

      this.$emit('sort', column, order);
    },
  },
};
</script>

<style lang="less">

.row-actions {
  color: #D7DEE2;
  position: static;

  span {
    font-size: 25px;
    font-weight: bold;
    cursor: pointer;
  }
}

.table-loading {
  position: relative;

  .table-loader-wrap {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 9;

    .table-loader-center {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 100%;
    }
  }

  .wp-list-table,
  .tablenav {
    opacity: 0.4;
  }
}

.table-loader {
  font-size: 10px;
  margin: 50px auto;
  text-indent: -9999em;
  width: 11em;
  height: 11em;
  border-radius: 50%;
  background: #ffffff;
  background: -moz-linear-gradient(left, #ffffff 10%, rgba(255, 255, 255, 0) 42%);
  background: -webkit-linear-gradient(left, #ffffff 10%, rgba(255, 255, 255, 0) 42%);
  background: -o-linear-gradient(left, #ffffff 10%, rgba(255, 255, 255, 0) 42%);
  background: -ms-linear-gradient(left, #ffffff 10%, rgba(255, 255, 255, 0) 42%);
  background: linear-gradient(to right, #ffffff 10%, rgba(255, 255, 255, 0) 42%);
  position: relative;
  -webkit-animation: tableLoading 1s infinite linear;
  animation: tableLoading 1s infinite linear;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);

  &:before {
  width: 50%;
  height: 50%;
  background: #ffffff;
  border-radius: 100% 0 0 0;
  position: absolute;
  top: 0;
  left: 0;
  content: '';
  }

  &:after {
  background: #f4f4f4;
  width: 75%;
  height: 75%;
  border-radius: 50%;
  content: '';
  margin: auto;
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  }
}

@-webkit-keyframes tableLoading {
  0% {
  -webkit-transform: rotate(0deg);
  transform: rotate(0deg);
  }
  100% {
  -webkit-transform: rotate(360deg);
  transform: rotate(360deg);
  }
}
@keyframes tableLoading {
  0% {
  -webkit-transform: rotate(0deg);
  transform: rotate(0deg);
  }
  100% {
  -webkit-transform: rotate(360deg);
  transform: rotate(360deg);
  }
}

</style>
