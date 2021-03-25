<template>
    <div class="wperp-select-container select-primary combo-box" v-click-outside="outside">
        <div @click="toggleMenu()" class="wperp-selected-option" v-if="selectedOption.name !== undefined">
              {{ selectedOption.name }}
              <span class="caret"></span>
        </div>

        <div @click="toggleMenu()" class="wperp-selected-option" v-if="selectedOption.name === undefined">
              {{placeholderText}}
              <span class="caret"></span>
        </div>

        <ul class="wperp-options" v-if="showMenu">
            <li :key="index" v-for="(option, index) in options">
                <router-link v-if="hasUrl" :to="{name: option.namedRoute}">{{ option.name }}</router-link>
                <a v-else href="#" @click.prevent="updateOption(option)">
                    {{ option.name }}
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'ComboBox',

    data() {
        return {
            selectedOption: {
                name: ''
            },
            showMenu: false,
            placeholderText: __('-Select-', 'erp')
        };
    },
    props: {
        selected: {},
        options: {
            type: [Array, Object]
        },
        placeholder: {
            type: String
        },
        hasUrl: {
            type: Boolean,
            default: false
        }
    },

    mounted() {
        // this.selectedOption.id = this.selected;
        if (this.placeholder) {
            this.selectedOption.name = this.placeholder;
            // this.placeholderText = this.placeholder;
        }
    },

    methods: {
        outside() {
            this.showMenu = false;
            this.$root.$emit('combo-box-close');
        },

        updateOption(option) {
            this.selectedOption = option;
            this.showMenu = false;
            this.$root.$emit('comboSelected', this.selectedOption);
        },

        toggleMenu() {
            this.showMenu = !this.showMenu;
        }
    }
};
</script>

<style lang="less">
    @theme-color: #1A9ED4;
    @theme-border-color: #ECECEC;

    .wperp-select-container {
        display: inline-flex;
        width: auto;
        position: relative;
        .wperp-selected-option {
            display: flex;
            justify-content: space-between;
            background: @theme-color;
            color: #fff;
            padding: 6px 20px;
            border-radius: 3px;
            white-space: nowrap;
            cursor: pointer;
            min-width: 150px;
        }
        a:hover {
            text-decoration: none;
        }
    }

    .wperp-options {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        min-width: 100%;
        white-space: nowrap;
        list-style: none;
        text-align: left;
        background-color: #fff;
        border: 1px solid @theme-border-color;
        border-radius: 3px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
        background-clip: padding-box;
        margin: 6px 0 0;
        padding: 5px 0;
        font-size: 14px;
        display: block;
        &:after,
        &:before {
            content: '';
            position: absolute;
            top: -6px;
            right: 20px;
            border-bottom: 5px solid @theme-border-color;
            border-right: 5px solid transparent;
            border-left: 5px solid transparent;
        }
        &:after {
            top: -4px;
            right: 20px;
            border-bottom-color: #fff;
        }
        li {
            overflow: hidden;
            width: 100%;
            position: relative;
            margin: 0;
            a {
                padding: 5px 20px;
                display: block;
                clear: both;
                font-weight: normal;
                line-height: 1.6;
                color: #333333;
                white-space: nowrap;
                text-decoration: none;
                &:hover {
                    background: @theme-border-color;
                    color: @theme-color;
                }
            }
        }
    }
    .caret {
        position: relative;
        top: 10px;
        margin-left: 5px;
        border-top: 4px solid #fff;
        border-right: 4px solid transparent;
        border-left: 4px solid transparent;
    }

</style>
