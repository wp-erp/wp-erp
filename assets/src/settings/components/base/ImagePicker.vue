<template>
    <vue-dropify
        v-model="imageValue"
        accept="image/*"
        uploadIcon="fa fa-cloud-upload"
        :message="__('Upload Image', 'erp')"
        @change="changeImage"
        :src="typeof this.imageValue === 'string' ? this.imageValue : null"
    />
</template>

<script>
import VueDropify from "vue-dropify";

export default {
    name: "ImagePicker",

    props: {
        value: {
            required: true,
        }
    },

    components: {
        VueDropify,
    },

    data() {
        return {
            imageValue: this.value,
        }
    },

    methods: {
        changeImage() {
            if (this.imageValue !== null && this.imageValue.length > 0) {
                this.$emit("changeImage", this.imageValue[0]);
            } else {
                this.$emit("changeImage", '');
            }
        },
    },
};
</script>
