<template>
    <div v-if="checkOptions" class="vue-select" :style="'width:' + width + 'px'">
        <select v-model="select_val" @change="onChange">
            <option :key="option.id" v-for="option in options" :value="option.id">{{ option.name }}</option>
        </select>
        <i class="select--icon" />
    </div>
</template>

<script>
export default {
    name: 'SimpleSelect',
    props: {
        selected: {
            type: Number,
            default: null
        },
        width: {
            type: Number,
            default: null
        },
        options: {
            type: Array,
            default: () => []
        }

    },
    data() {
        return {
            select_val: null
        };
    },
    computed: {
        checkOptions() {
            if (this.options) {
                return this.options;
            } else {
                window.console.error(this.name + " couldn't render without options");
                return false;
            }
        }
    },
    methods: {
        onChange() {
            this.$root.$emit('SimpleSelectChange', {
                selected: this.select_val
            });
        }
    }
};
</script>

<style scoped>
    .vue-select {
        border-bottom: 1px solid #ccc;
        display: inline-block;
        font-family: inherit;
        white-space: nowrap;
        position: relative;
        overflow: hidden;
        width: auto;
    }
    .vue-select select {
        -webkit-appearance: none;
        box-shadow: none;
        font-size: 15px;
        width: 130%;
        border: 0;
    }
    .vue-select select:focus {
        outline: none;
    }
    .vue-select .select--icon {
        border-color: #333 transparent transparent transparent;
        border-width: 6px 5px 0 5px;
        transition: opacity 0.5s;
        border-style: solid;
        position: absolute;
        opacity: 0.5;
        right: 5px;
        top: 10px;
        height: 0;
        width: 0;
    }
    .vue-select:hover .select--icon {
        opacity: 1;
    }
</style>
