<template>
    <div class="field">
        <input type="checkbox" name="switchRounded" :class="switchType" :checked="checked" />
        <label for="switchRounded" @click="toggle">{{ label }}</label>
    </div>
</template>


<script>

import 'bulma-switch/dist/css/bulma-switch.min.css';

export default {
    name: "RadioSwitch",

    props: {
        label: String,
        id: String,
        value: '',
        type: {
            type: String,
            required: false,
            default: 'default'
        },
    },

    computed: {
        switchType() {
            return `switch is-rounded is-${this.type}`;
        },

        checked() {
            if ( this.value == true || this.value == 'yes' || this.value == 'on' ) {
                return 'checked';    
            }

            return '';
        }
    },

    methods: {
        toggle() {
            this.$emit('toggle');
        }
    },
}

</script>
