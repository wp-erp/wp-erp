<template>
    <div class="wperp-select-container select-primary combo-btns" v-click-outside="outside">
        <div class="wperp-selected-option">
              <div class="left-part" @click="optionSelected(options[0])">
                <button type="submit" class="btn-fake">{{ options[0].text }}</button>
            </div>

              <div class="right-part" @click="toggleButtons">
                <span class="btn-caret"></span>
            </div>
        </div>

        <ul class="wperp-options" v-show="showMenu">
            <li :key="index" @click="optionSelected(option)" v-for="(option, index) in options.slice(1)">
                <button type="submit" class="btn-fake">{{ option.text }}</button>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'ComboButton',

    data() {
        return {
            showMenu: false
        };
    },

    props: {
        options: {
            type: Array
        }
    },

    methods: {
        outside() {
            this.showMenu = false;
            this.$root.$emit('combo-btn-close');
        },

        optionSelected(option) {
            this.showMenu = false;

            this.$store.dispatch('combo/setBtnID', option.id);
        },

        toggleButtons() {
            this.showMenu = !this.showMenu;
        }
    }
};
</script>

<style lang="less">
    .combo-btns {
        .wperp-selected-option {
            padding: 0 !important;
        }

         .btn-fake {
             border: 0;
             box-shadow: none;
             background: none;
             cursor: pointer;
         }

        .btn-caret {
            border-top: 4px solid #fff;
            border-right: 4px solid transparent;
            border-left: 4px solid transparent;
        }

        .left-part,
        .right-part {
            float: left;

        }

        .left-part {
            width: 80%;
            border-top-left-radius: 3px;
            border-bottom-left-radius: 3px;
            line-height: 2;
            text-align: center;

            .btn-fake {
                color: #fff;
                padding: 2px 10px;

                &:focus {
                    outline: 0;
                }
            }
        }

        .right-part {
            width: 20%;
            background: #03A9F4;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top-right-radius: 3px;
            border-bottom-right-radius: 3px;
        }

        .wperp-options {
            .btn-fake {
                padding: 5px 20px;
                display: block;
                clear: both;
                font-weight: 400;
                line-height: 1.6;
                color: #333;
                white-space: nowrap;
                text-decoration: none;
                width: 100%;

                &:hover {
                    background: #ececec;
                    color: #1a9ed4;
                }
            }
        }
    }

</style>
