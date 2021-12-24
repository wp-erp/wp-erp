<template>
    <crm-contact-form-layout>
        <template>
            <div v-for="(formKey, index) in Object.keys( forms )" :key="index">
                <crm-contact-form-single
                    :formData="forms[formKey]"
                    :data="localizedData"
                    :plugin="formName"
                    :formId="parseInt( formKey )"
                    @reset_contact_form_data="resetContactFormData"
                />
                <hr style="border-top: 0px !important; border-bottom: 1px solid #e1e1e1 !important; padding-top: 15px;margin-bottom: 25px;"/>
            </div>
        </template>
    </crm-contact-form-layout>
</template>

<script>
import CrmContactFormLayout from './CrmContactFormLayout.vue';
import CrmContactFormSingle from './CrmContactFormSingle.vue';

export default {
    name: "CrmContactForm",

    data() {
        return {
            forms               : {},
            section_id          : "erp-crm",
            sub_section_id      : "contact_forms",
            localizedData       : {},
            defaultLocalizedData: {},
            formName            : 'contact_form_7'
        };
    },

    components: {
        CrmContactFormLayout,
        CrmContactFormSingle
    },

    created() {
       this.initializeDataByFormName( this.$route.params.id || this.formName );

       if( this.localizedData.length && this.localizedData.plugins.length > 0 && typeof this.$route.params.id === 'undefined' ) {
            this.$router.push(`/erp-crm/contact_forms/${this.localizedData.plugins[0]}`);
       }
    },


    methods: {
        /**
         * Reset Contact forms data based on `plugin` and `formId`
         *
         * @param object data
         *
         * @return void
         */
        resetContactFormData(data) {
            const { plugin, formId, map, contactGroup, contactOwner } = data;
            this.localizedData.forms[plugin][formId].map              = map;
            this.localizedData.forms[plugin][formId].contactGroup     = contactGroup;
            this.localizedData.forms[plugin][formId].contactOwner     = contactOwner;
        },

        /**
         * Initialize Data by formName
         *
         * example formName - `contact_form_7`
         */
        initializeDataByFormName( formName ) {
            const menus                = erp_settings_var.erp_settings_menus;
            const parentMenu           = menus.find(menu => menu.id === this.section_id);
            const localizedData        = parentMenu.fields.contact_forms.localized_data;
            const defaultLocalizedData = parentMenu.fields.contact_forms.localized_data;

            this.formName      = formName;
            this.localizedData = {};

            if ( typeof localizedData !== 'undefined' ) {
                this.defaultLocalizedData = defaultLocalizedData;
                this.localizedData        = localizedData;
                this.forms                = typeof localizedData.forms[ this.formName ] !== 'undefined' ? localizedData.forms[ this.formName ] : {}
            }
        }
    },

    watch: {
        $route(to, from) {
            // Update data if route params changed.
            if ( typeof this.localizedData !== 'undefined' ) {
                this.initializeDataByFormName( to.params.id )
            }
        }
    }
};
</script>
