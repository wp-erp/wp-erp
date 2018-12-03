<template>
  <div class="dropdown" @click.prevent="toggleDropdown">
    <slot name="button">
      <button class="btn btn-secondary dropdown-toggle"
              type="button"
              data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
        Dropdown
      </button>
    </slot>
    <div ref="menu" :class="['dropdown-menu', dropdownClasses, {'show': visible}]" @click.stop="">
      <!-- <div class="popper__arrow" x-arrow /> -->
      <slot name="dropdown"/>
    </div>
  </div>
</template>

<script>
/* eslint no-underscore-dangle: 0 */

// Vue click outside
// https://jsfiddle.net/Linusborg/Lx49LaL8/
import Popper from 'popper.js';

export default {

  name: 'Dropdown',

  props: {
    dropdownClasses: {
      type: String,
      default: '',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    placement: {
      type: String,
      default: 'bottom',
    },
  },

  data() {
    return {
      visible: false,
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
    },
  },

  created() {
    // Create non-reactive property
    this._popper = null;
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
      this.createPopper(element);
    },

    hideMenu() {
      this.$root.$emit('hidden');
      this.removePopper();
    },

    createPopper(element) {
      this.removePopper();
      this._popper = new Popper(element, this.$refs.menu, {
        placement: this.placement,
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
      if (!this.$el || this.elementContains(this.$el, e.target)
        || !this._popper || this.elementContains(this._popper, e.target)
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
    },
  },
};
</script>

<style lang="less">
.dropdown-menu {
    position: absolute;
    background: #fff;
    color: black;
    border-radius: 3px;
    box-shadow: 0 0 2px rgba(0,0,0,0.5);
    padding: 10px;
    text-align: center;
    opacity: 0;
    z-index: 2;
    left: -9999px;

    &.show {
      left: 0;
      opacity: 1;
    }

    ul {
      margin: 0;
    }

    .popper__arrow {
        width: 0;
        height: 0;
        border-style: solid;
        position: absolute;
        margin: 5px;
        border-color: #fff;
    }
}

.dropdown-menu[x-placement^="top"] {
    margin-bottom: 5px;
}

.dropdown-menu[x-placement^="top"] .tooltip-arrow {
    border-width: 5px 5px 0 5px;
    border-left-color: transparent;
    border-right-color: transparent;
    border-bottom-color: transparent;
    bottom: -5px;
    left: calc(50% - 5px);
    margin-top: 0;
    margin-bottom: 0;
}

.dropdown-menu[x-placement^="bottom"] {
    margin-top: 5px;
}

.dropdown-menu[x-placement^="bottom"] .popper__arrow {
    border-width: 0 5px 5px 5px;
    border-left-color: transparent;
    border-right-color: transparent;
    border-top-color: transparent;
    top: -5px;
    left: calc(50% - 5px);
    margin-top: 0;
    margin-bottom: 0;
}

.dropdown-menu[x-placement^="right"] {
    margin-left: 5px;
}

.dropdown-menu[x-placement^="right"] .popper__arrow {
    border-width: 5px 5px 5px 0;
    border-left-color: transparent;
    border-top-color: transparent;
    border-bottom-color: transparent;
    left: -5px;
    top: calc(50% - 5px);
    margin-left: 0;
    margin-right: 0;
}

.dropdown-menu[x-placement^="left"] {
    margin-right: 5px;
}

.dropdown-menu[x-placement^="left"] .popper__arrow {
    border-width: 5px 0 5px 5px;
    border-top-color: transparent;
    border-right-color: transparent;
    border-bottom-color: transparent;
    right: -5px;
    top: calc(50% - 5px);
    margin-left: 0;
    margin-right: 0;
}
</style>
