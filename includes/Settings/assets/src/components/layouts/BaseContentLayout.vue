<template>
    <form action="" class="wperp-form" method="post" @submit.prevent="onFormSubmit" enctype="multipart/form-data">

        <h3 class="sub-sub-title" v-html="sub_sub_section_title" v-if="typeof sub_sub_section_title !== 'undefined' && sub_sub_section_title.length > 0"></h3>

        <slot name="extended-data-before"></slot>

        <div v-for="(input, index) in fields" :key="index">
            <div class="wperp-form-group">

                <label :for="'erp-'+fields[index]['id']">
                    <span v-html="input.title"></span>
                    <tooltip v-if="input" :input="input"  />
                </label>

                <template v-if="input.type === 'select'">
                    <multi-select v-model="fields[index]['value']" :options="input.options" :id="'erp-'+fields[index]['id']" :placeholder="'Please select ' + fields[index]['title']" />
                    <input-desc :input="input" />
                </template>

                <template v-else-if="input.type === 'label'">
                    <div v-html="input.value" :id="'erp-'+fields[index]['id']" ></div>
                    <input-desc :input="input" />
                </template>

                <template v-else-if="input.type === 'hidden'">
                    <input type="hidden" :value="fields[index]['value']" :id="'erp-'+fields[index]['id']" />
                </template>

                <template v-else-if="input.type === 'hidden-fixed'">
                    <input type="hidden" :value="fields[index]['value']" :id="'erp-'+fields[index]['id']" />
                </template>

                <div class="form-check" v-else-if="input.type === 'checkbox'">
                    <label class="form-check-label">
                        <input v-model="fields[index]['value']" type="checkbox" class="form-check-input" :id="'erp-'+fields[index]['id']" />
                        <span class="form-check-sign"> <span class="check"></span> </span>
                        <span class="form-check-label-light" v-html="input.desc"></span>
                    </label>

                    <input-desc :input="input" />
                </div>

                <div v-else-if="input.type === 'radio'">
                    <radio-switch
                        :value="fields[index]['value']"
                        @toggle="toggleSwitch(index)"
                        :id="'erp-'+fields[index]['id']"
                    ></radio-switch>

                    <input-desc :input="input" />
                </div>

                <div class="form-check" v-else-if="input.type === 'multicheck'">
                    <label class="form-check-label" v-for="(checkOption, checkKey, index2) in input.options" :key="index2">
                        <input v-model="fields[index]['value'][checkKey]" type="checkbox" class="form-check-input" :id="'erp-'+fields[index]['id'][checkKey]" />
                        <span class="form-check-sign"> <span class="check"></span> </span>
                        <span class="form-check-label-light" v-html="checkOption"></span>
                    </label>

                    <input-desc :input="input" />
                </div>

                <div v-else-if="input.type === 'text' || input.type === 'textarea' || input.type === 'password' || input.type === 'email'">
                    <input v-if="input.type === 'text' && input.class !== 'erp-date-field'"
                        v-model="fields[index]['value']"
                        class="wperp-form-field"
                        :placeholder="fields[index]['placeholder'] ? fields[index]['placeholder'] : ''"
                        :id="'erp-'+fields[index]['id']"
                        :disabled="fields[index]['disabled'] ? true : false" />

                    <date-picker
                        v-else-if="input.type === 'text' && input.class === 'erp-date-field'"
                        v-model="fields[index]['value']"
                        class="wperp-form-field"
                        :placeholder="__( 'Select date', 'erp' )"
                        :id="'erp-'+fields[index]['id']" />

                    <textarea
                        v-else-if="input.type === 'textarea'"
                        cols="45" rows="4"
                        v-model="fields[index]['value']"
                        class="wperp-form-field"
                        :id="'erp-'+fields[index]['id']"
                        :disabled="fields[index]['disabled'] ? true : false" />

                    <input v-else-if="input.type === 'password'"
                        v-model="fields[index]['value']"
                        type="password"
                        class="wperp-form-field"
                        :id="'erp-'+fields[index]['id']"
                        :disabled="fields[index]['disabled'] ? true : false" />

                    <input v-else-if="input.type === 'email'"
                        v-model="fields[index]['value']"
                        type="email"
                        class="wperp-form-field"
                        :id="'erp-'+fields[index]['id']"
                        :disabled="fields[index]['disabled'] ? true : false" />

                    <input-desc :input="input" />
                </div>

                <div v-else-if="input.type === 'image'">
                    <image-picker
                        v-model="fields[index]['value']"
                        @changeImage="(value) => changeImage(value, index)"
                        :value="fields[index]['value']"
                        :id="'erp-'+fields[index]['id']"
                    />
                </div>

                <div v-else-if="input.type === 'html'" v-html="input.value"></div>

            </div>
        </div>

        <slot name="extended-data"></slot>

        <div class="wperp-form-group" v-if="! hide_submit">
            <submit-button :text="__('Save Changes', 'erp')" />
            <div class="clearfix"></div>
        </div>
    </form>
</template>

<script>

import DatePicker from '../base/DatePicker.vue';
import SubmitButton from "../base/SubmitButton.vue";
import ImagePicker from "../base/ImagePicker.vue";
import Tooltip from '../base/Tooltip.vue';
import MultiSelect from '../select/MultiSelect.vue';
import InputDesc from '../layouts/partials/InputDesc.vue';
import RadioSwitch from './partials/Switch.vue';
import { generateFormDataFromObject } from "../../utils/FormDataHandler";
var $ = jQuery;

export default {
    name: "BaseContentLayout",

    components: {
        SubmitButton,
        ImagePicker,
        Tooltip,
        InputDesc,
        RadioSwitch,
        DatePicker,
        MultiSelect
    },

    data() {
        return {
            fields: [],
            optionsMutable: this.options,
        };
    },

    props: {
        inputs: {
            type    : Array|Object,
            required: true,
        },
        section_id: {
            type    : String,
            required: true,
        },
        sub_section_id: {
            type    : String,
            required: true,
        },
        single_option: {
            type    : Boolean,
            required: true,
        },
        sub_sub_section_title: {
            type    : String,
            required: false
        },
        sub_sub_section_id: {
            type    : String,
            required: false,
            default : '',
        },
        hide_submit: {
            type    : Boolean,
            required: false,
            default : false
        },
        options: {
            type: Object,
            required: false,
            default: () => (
                {
                    action   : '',
                    recurrent: false,
                    fields   : []
                }
            )
        }
    },

    created() {
        this.getSettingsData();
    },

    methods: {

        /**
         * Get Settings Data
         */
        getSettingsData() {
            const self = this;
            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = window.settings.hooks.applyFilters( "requestData",
                {
                    ...self.inputs,
                    single_option     : ! self.single_option ? self.section_id: null,
                    section_id        : self.section_id,
                    sub_section_id    : self.sub_section_id,
                    sub_sub_section_id: self.sub_sub_section_id,
                    _wpnonce          : erp_settings_var.nonce,
                    action            : 'erp-settings-get-data'
                }
            );

            const postData = generateFormDataFromObject( requestData );

            $.ajax({
                url        : erp_settings_var.ajax_url,
                type       : "POST",
                data       : postData,
                processData: false,
                contentType: false,
                success    : function ( response ) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if ( response.success ) {
                        self.fields = response.data;

                        // Process returned data to show for vue
                        response.data.forEach(( item, index ) => {
                            if ( 'multicheck' === item.type ) {
                                let initialCheckedData = [];

                                // First assign to false or uncheck if nothing found from response
                                Object.keys( item.options ).forEach( optionKey => {
                                    initialCheckedData[ optionKey ] = false;
                                } );

                                if ( item.value !== null && item.value !== false ) {
                                    Object.keys( item.options ).forEach( optionKey => {
                                        initialCheckedData[optionKey] = typeof item.value[ optionKey ] !== 'undefined' ? true : false;
                                    } );
                                }

                                self.fields[ index ]['value'] = initialCheckedData;
                            } else if ( 'select' === item.type ) {
                                Object.keys( item.options ).forEach( optionKey  => {
                                    if ( optionKey === item.value ) {
                                        self.fields[ index ]['value'] = {
                                            id  : optionKey,
                                            name: item.options[ optionKey ]
                                        }
                                    }
                                });
                            } else if ( 'hidden-fixed' === item.type ) {
                                self.fields[ index ]['value'] = self.inputs.find(input => input.id === item.id)['value'];
                            } else if ( 'html' === item.type ) {
                                self.fields[ index ]['value'] = self.inputs.find(input => input.id === item.id)['value'];
                            }
                        } );
                    }
                },
            });
        },

        /**
         * Submit settings global form data
         */
        onFormSubmit() {
            const self = this;
            self.$store.dispatch("spinner/setSpinner", true);

            let requestDataPost = {};

            // Process fields and send to post data
            self.fields.forEach( item => {
                if ( item !== null && typeof item.id !== 'undefined' ) {
                    requestDataPost[ item.id ] = item.value;
                    let initialCheckedData     = [];

                    if ( item.type === 'multicheck' ) {
                        Object.keys( item.options ).forEach( optionKey => {
                            if ( item.value[ optionKey ] !== false )  initialCheckedData[ optionKey ] = optionKey;
                        });
                        requestDataPost[ item.id ] = initialCheckedData;
                    }

                    if ( item.type === 'checkbox' && ( item.value === false || item.value === 'no' ) ) {
                        requestDataPost[ item.id ] = null;
                    }

                    if ( item.type === 'select' && ( item.value !== "" || item.value !== null ) ) {
                        requestDataPost[ item.id ] =  item.value ? item.value.id : '';
                    }
                }
            } );

            if ( typeof self.sub_sub_section_id !== 'undefined' && self.sub_sub_section_id !== '' ) {
                requestDataPost['sub_sub_section'] = self.sub_sub_section_id;
            }

            if ( typeof self.optionsMutable.fields !== 'undefined' && Array.isArray( self.optionsMutable.fields ) ) {
                self.optionsMutable.fields.forEach( field => {
                    requestDataPost[ field.key ] = field.value;
                } );
            }

            let requestData = {
                ...requestDataPost,
                _wpnonce: erp_settings_var.nonce,
                action  : ! self.optionsMutable.action ? "erp-settings-save" : self.optionsMutable.action,
                module  : self.section_id,
                section : self.sub_section_id,
            };

            requestData    = window.settings.hooks.applyFilters( "requestData", requestData );
            const postData = generateFormDataFromObject(requestData);

            $.ajax({
                url        : erp_settings_var.ajax_url,
                type       : "POST",
                data       : postData,
                processData: false,
                contentType: false,
                success: function (response) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if (response.success) {
                        self.showAlert("success", response.data.message);
                        self.$store.dispatch("formdata/setFormData", requestData);
                    } else {
                        self.showAlert("error", response.data);
                    }
                },
            });

            if (! self.optionsMutable.recurrent) {
                self.optionsMutable = {
                    action   : '',
                    recurrent: false,
                    fields   : []
                };
            }
        },

        /**
         * Change Image Type Inputs
         */
        changeImage( value, index ) {
            this.fields[ index ]['value'] = value;
        },

        /**
         * Toggle switch
         */
        toggleSwitch( index ) {
            this.fields[index]['value'] = this.fields[index]['value'] == 'yes' ? 'no' : 'yes';
        },

        /**
         * Change Radio Type Inputs
         */
        changeRadioInput( index, key ) {
            this.fields[index]['value'] = key;
        },
    },

    watch: {
        options: {
            handler( newVal, oldValue) {
                this.optionsMutable = newVal
            },
            deep: true
        }
    },
};
</script>
