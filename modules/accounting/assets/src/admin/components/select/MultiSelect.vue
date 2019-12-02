<template>
  <multiselect
    :value="value"
    :options="options"
    :multiple="multiple"
    :close-on-select="!multiple"
    :loading="isLoading"
    :placeholder="placeholder"
    :disabled="disabled"
    label="name"
    track-by="id"
    @open="onDropdownOpen"
    @remove="onRemove"
    @select="onSelect"
    @search-change="asyncFind">

    <span slot="noResult">Oops! No elements found.</span>
  </multiselect>
</template>

<script>
/* eslint func-names: ["error", "never"] */
import Multiselect from 'vue-multiselect';
import debounce from 'admin/components/select/debounce';
import 'vue-multiselect/dist/vue-multiselect.min.css';

export default {
    name: 'MultiSelect',

    components: {
        Multiselect
    },

    props: {
        value: {
            type: null,
            required: true
        },

        options: {
            type: Array,
            default: () => []
        },

        multiple: {
            type: Boolean,
            default: false
        },

        disabled: {
            type: Boolean,
            default: false
        },

        placeholder: {
            type: String,
            default: 'Please search'
        }
    },

    data() {
        return {
            noResult: false,
            isLoading: false,
            results: []
        };
    },

    watch: {
        options() {
            this.results = [];
            this.isLoading = false;
        }
    },

    methods: {
        onSelect(selected) {
            if (this.multiple) {
                this.results.push(selected);
                this.$emit('input', this.results);
            } else {
                this.$emit('input', selected);
            }
        },

        onRemove(removed) {
            this.results = this.results.filter(element => element.id !== removed.id);

            this.$emit('input', this.results);
        },

        onDropdownOpen(id) {
            this.$root.$emit('dropdown-open');
        },

        asyncFind: debounce(function(query) {
            // this.isLoading = true;
            this.$root.$emit('options-query', query);
        }, 1)
    }
};
</script>

<style lang="less">

    .multiselect {
        input.multiselect__input {
            display: none;
        }
        &.multiselect--active input.multiselect__input {
            display: block;
            width: 97% !important;
        }
    }

.with-multiselect {
    .multiselect__input {
        &::placeholder {
            font-size: 15px;
            font-weight: normal;
            color: #DBDBDB;
            opacity: 1;
        }

        &:-ms-input-placeholder {
            font-size: 15px;
            font-weight: normal;
            color: #DBDBDB;
        }

        &::-ms-input-placeholder {
            font-size: 15px;
            font-weight: normal;
            color: #DBDBDB;
        }
    }

    .custom__tag {
        background: #f7f7f7;
        padding: 4px 8px;
        border-radius: 3px;
        display: inline-block;
        margin: 1px 5px 2px 0;;
        border: 1px solid #e8eaec;

        span {
            color: #72777c;
        }

        .custom__remove {
            color: #999;
            font-weight: bold;

            &:hover {
                color: #333;
                cursor: pointer;
            }
        }
    }

    .multiselect__element {
        margin: 0;

        .multiselect__option {
            min-height: 32px;
            line-height: 12px;

            &:after {
                line-height: 35px;
            }

            span {
                font-size: 15px;
            }
        }
    }

    .multiselect__single {
        font-size: 15px;
        color: #555;
        line-height: 2;
        margin: 0;
    }

    .multiselect__tags {
        height: auto;
        min-height: 35px;
        border-radius: 3px;
        padding: 2px;

        .multiselect__spinner {
            height: 32px;
        }

        .multiselect__input {
            border: 0;
            box-shadow: none;
        }
    }

    .multiselect__select {
        height: 33px;
    }

    .multiselect__placeholder {
        padding-top: 0;
        margin: 6px 0 0 4px;
    }

    .multiselect__option--selected {
        color: rgba(45,140,240,.9)!important;
        font-weight: normal;
    }

    .multiselect__option--highlight {
        background: #f3f3f3 !important;
        color: #515a6e !important;

        &:after {
            content: '';
        }
    }

    .multiselect__option--selected {
        &.multiselect__option--highlight:after {
            background: #f3f3f3 !important;
            content: "\f158" !important;
            color: #ff6a6a !important;
            font-family: dashicons !important;
        }

        &:after {
            content: "\f147" !important;
            font-size: 20px !important;
            color: rgba(45,140,240,.9) !important;
            font-family: dashicons !important;
        }
    }

}

</style>
