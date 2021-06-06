<template>
    <form
        action=""
        class="wperp-form"
        method="post"
        @submit.prevent="onFormSubmit"
    >
        <div v-for="(input, index) in fields" :key="index">
            <div class="wperp-form-group" v-if="input.type === 'select'">
                <label>{{ input.title }}</label>
                <select
                    v-model="fields[index]['value']"
                    class="wperp-form-field"
                >
                    <option
                        v-for="(item, key, indexOption) in input.options"
                        :value="key"
                        :key="indexOption"
                    >
                        {{ item }}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group" v-if="input.type === 'checkbox'">
                <label>{{ input.title }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input
                            v-model="fields[index]['value']"
                            type="checkbox"
                            class="form-check-input"
                            :id="fields[index]['id']"
                        />
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ input.desc }}
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div class="wperp-form-group">
            <submit-button :text="__('Save Changes', 'erp')" />
        </div>
    </form>
</template>

<script>
import SubmitButton from "settings/components/base/SubmitButton.vue";
import { generateFormDataFromObject } from "settings/utils/FormDataHandler";
var $ = jQuery;

export default {
    name: "BaseContentLayout",

    components: {
        SubmitButton
    },

    data() {
        return {
            fields: [],
        };
    },

    props: {
        inputs: {
            type: Array,
            required: true,
        },
        section_id: {
            type: String,
            required: true,
        },
        sub_section_id: {
            type: String,
            required: true,
        },
    },

    created() {
        this.getSettingsData();
    },

    methods: {
        getSettingsData() {
            const self = this;

            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    ...self.inputs,
                    _wpnonce: erp_settings_var.nonce,
                    action: "erp-settings-get-data",
                }
            );

            const postData = generateFormDataFromObject(requestData);

            $.ajax({
                url: erp_settings_var.ajax_url,
                type: "POST",
                data: postData,
                processData: false,
                contentType: false,
                success: function (response) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if (response.success) {
                        self.fields = response.data;
                    }
                },
            });
        },

        onFormSubmit() {
            const self = this;
            self.$store.dispatch("spinner/setSpinner", true);

            let requestDataPost = {};

            self.fields.forEach((item) => {
                requestDataPost[item.id] = item.value;

                if (item.value === false || item.value === 'no' || item.value === "") {
                    requestDataPost[item.id] = null;
                }
            });

            let requestData = {
                ...requestDataPost,
                _wpnonce: erp_settings_var.nonce,
                action: "erp-settings-save",
                module: self.section_id,
                section: self.sub_section_id,
            };

            requestData = window.settings.hooks.applyFilters(
                "requestData",
                requestData
            );

            const postData = generateFormDataFromObject(requestData);

            $.ajax({
                url: erp_settings_var.ajax_url,
                type: "POST",
                data: postData,
                processData: false,
                contentType: false,
                success: function (response) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if (response.success) {
                        self.showAlert("success", response.data.message);
                    } else {
                        self.showAlert("error", response.data);
                    }
                },
            });
        }
    },
};
</script>
