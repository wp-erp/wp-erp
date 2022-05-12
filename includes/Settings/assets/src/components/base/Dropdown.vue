<template>
    <div class="dropdown wperp-has-dropdown" @click.prevent="toggleDropdown">
        <slot name="button">
            <button class="btn btn-secondary dropdown-toggle"
                    type="button"
                    data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                {{ __('Dropdown', 'erp') }}
            </button>
        </slot>
        <div ref="menu" :class="['dropdown-popper dropdown-menu', dropdownClasses, {'show': visible}]" @click.stop="">
            <div class="popper__arrow" x-arrow/>
            <slot name="dropdown"/>
        </div>
    </div>
</template>

<script>
import { createPopper } from '@popperjs/core';

export default {

    name: 'Dropdown',

    props: {
        dropdownClasses: {
            type: String,
            default: ''
        },
        disabled: {
            type: Boolean,
            default: false
        },
        placement: {
            type: String,
            default: 'bottom'
        }
    },

    data() {
        return {
            visible: false
        };
    },

    watch: {
        visible(newValue, oldValue) {
            if (newValue !== oldValue) {
                if (newValue) {
                    this.showMenu();
                } else {
                    this.hideMenu();
                }
            }
        }
    },

    created() {
        // Create non-reactive property
        this._popper = null;

        this.$parent.$on('action:click', () => {
            this.visible = false;
        });
    },

    mounted() {
        window.addEventListener('click', this.closeDropdown);
    },

    beforeDestroy() {
        this.visible = false;
        this.removePopper();
    },

    destroyed() {
        window.removeEventListener('click', this.closeDropdown);
    },

    methods: {
        toggleDropdown() {
            this.visible = !this.visible;
        },

        showMenu() {
            if (this.disabled) {
                return;
            }

            const element = this.$el;
            this.initPopper(element);
        },

        hideMenu() {
            this.$root.$emit('hidden');
            this.removePopper();
        },

        initPopper(element) {
            this.removePopper();
            this._popper = new createPopper(element, this.$refs.menu, {
                placement: this.placement
            });
        },

        removePopper() {
            if (this._popper) {
                // Ensure popper event listeners are removed cleanly
                this._popper.destroy();
            }
            this._popper = null;
        },

        closeDropdown(e) {
            if (!this.$el || this.elementContains(this.$el, e.target) ||
                    !this._popper || this.elementContains(this._popper, e.target)
            ) {
                return;
            }

            this.visible = false;
        },

        elementContains(elm, otherElm) {
            if (typeof elm.contains === 'function') {
                return elm.contains(otherElm);
            }

            return false;
        }
    }
};
</script>
