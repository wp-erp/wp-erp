<template>
    <crm-contact-form-layout>
        <template>
            <crm-contact-form-upsell
                v-if="isProLocked"
                :title="proInfo.title"
                :upgradeUrl="proInfo.upgrade_url"
                :formPluginActive="proInfo.form_plugin_active"
                :i18n="localizedData.i18n || {}"
            />

            <div v-else-if="Object.keys( forms ).length">
                <div class="wperp-form-group mb-20" style="max-width: 600px">
                    <label>{{ __( 'Select a form', 'erp' ) }}</label>
                    <select v-model="selectedFormKey">
                        <option value="">{{ __( 'Select a form', 'erp' ) }}</option>
                        <option
                            v-for="formKey in Object.keys( forms )"
                            :value="formKey"
                            :key="formKey"
                        >
                            {{ forms[ formKey ].title }}{{ isFormMapped( formKey ) ? ' ✔' : '' }}
                        </option>
                    </select>
                </div>

                <crm-contact-form-single
                    v-if="selectedFormKey !== '' && typeof forms[ selectedFormKey ] !== 'undefined'"
                    :key="selectedFormKey"
                    :formData="forms[ selectedFormKey ]"
                    :data="localizedData"
                    :plugin="formName"
                    :formId="parseInt( selectedFormKey )"
                    @reset_contact_form_data="resetContactFormData"
                />
            </div>
        </template>
    </crm-contact-form-layout>
</template>

<script>
import CrmContactFormLayout from './CrmContactFormLayout.vue';
import CrmContactFormSingle from './CrmContactFormSingle.vue';
import CrmContactFormUpsell from './CrmContactFormUpsell.vue';

export default {
    name: "CrmContactForm",

    data() {
        return {
            forms               : {},
            section_id          : "erp-crm",
            sub_section_id      : "contact_forms",
            localizedData       : {},
            defaultLocalizedData: {},
            formName            : 'contact_form_7',
            selectedFormKey     : ""
        };
    },

    components: {
        CrmContactFormLayout,
        CrmContactFormSingle,
        CrmContactFormUpsell
    },

    computed: {
        /**
         * Pro/upsell metadata for the active plugin tab (empty if not pro).
         */
        proInfo() {
            const proPlugins = this.localizedData.proPlugins || {};

            return proPlugins[ this.formName ] || {};
        },

        /**
         * Whether the active tab is a pro integration not unlocked by erp-pro.
         * When erp-pro unlocks it, the slug is absent from proPlugins.
         */
        isProLocked() {
            return !! this.proInfo.is_pro;
        }
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
            const { plugin, formId, map, contactGroup, contactOwner, lifeStage } = data;
            this.localizedData.forms[plugin][formId].map              = map;
            this.localizedData.forms[plugin][formId].contactGroup     = contactGroup;
            this.localizedData.forms[plugin][formId].contactOwner     = contactOwner;
            this.localizedData.forms[plugin][formId].lifeStage        = lifeStage;
        },

        /**
         * Whether a form already has a saved field mapping
         *
         * Used to flag previously-configured forms in the picker so existing
         * users can find and keep editing their old mappings.
         *
         * @param string|number formKey
         *
         * @return boolean
         */
        isFormMapped( formKey ) {
            const mapped = this.localizedData.mappedData;

            return !! ( mapped
                && mapped[ this.formName ]
                && mapped[ this.formName ][ formKey ]
            );
        },

        /**
         * Pick the form to show first
         *
         * Prefer a form that already has a saved mapping (old-user support),
         * otherwise leave the picker on the "Select a form" placeholder so the
         * page isn't flooded with every form at once.
         *
         * @return string
         */
        getDefaultFormKey() {
            const keys = Object.keys( this.forms );

            const mappedKey = keys.find( key => this.isFormMapped( key ) );

            return typeof mappedKey !== 'undefined' ? mappedKey : "";
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

            this.selectedFormKey = this.getDefaultFormKey();
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
