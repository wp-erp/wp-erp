<template>
    <form action="" class="wperp-form" method="post" @submit.prevent="onFormSubmit" enctype="multipart/form-data">

        <h3 class="sub-sub-title" v-html="sub_sub_section_title" v-if="typeof sub_sub_section_title !== 'undefined' && sub_sub_section_title.length > 0"></h3>

        <div v-for="(input, index) in fields" :key="index">
            <div class="wperp-form-group">

                <label :for="'erp-'+fields[index]['id']">
                    <span v-html="input.title"></span>
                    <tooltip :input="input"  />
                </label>

                <template v-if="input.type === 'select'">
                    <multi-select v-model="fields[index]['value']" :options="input.options" :id="'erp-'+fields[index]['id']" :placeholder="'Please select ' + fields[index]['title']" />
                    <input-desc :input="input" />
                </template>


                <div class="form-check" v-if="input.type === 'checkbox'">
                    <label class="form-check-label">
                        <input v-model="fields[index]['value']" type="checkbox" class="form-check-input" :id="'erp-'+fields[index]['id']" />
                        <span class="form-check-sign"> <span class="check"></span> </span>
                        <span class="form-check-label-light" v-html="input.desc"></span>
                    </label>

                    <input-desc :input="input" />
                </div>

                <div class="form-check" v-if="input.type === 'multicheck'">
                    <label class="form-check-label" v-for="(checkOption, checkKey, index2) in input.options" :key="index2">
                        <input v-model="fields[index]['value'][checkKey]" type="checkbox" class="form-check-input" :id="'erp-'+fields[index]['id'][checkKey]" />
                        <span class="form-check-sign"> <span class="check"></span> </span>
                        <span class="form-check-label-light" v-html="checkOption"></span>
                    </label>

                    <input-desc :input="input" />
                </div>

                <div v-if="input.type === 'text' || input.type === 'textarea'">
                    <input v-if="input.type === 'text' && input.class !== 'erp-date-field'" v-model="fields[index]['value']" class="wperp-form-field" :id="'erp-'+fields[index]['id']" />
                    <date-picker v-if="input.type === 'text' && input.class === 'erp-date-field'" class="wperp-form-field" :placeholder="__( 'Select date', 'erp' )" v-model="fields[index]['value']" :id="'erp-'+fields[index]['id']" />
                    <textarea v-if="input.type === 'textarea'" cols="45" rows="4" v-model="fields[index]['value']" class="wperp-form-field" :id="'erp-'+fields[index]['id']" />

                    <input-desc :input="input" />
                </div>

                <div v-if="input.type === 'image'">
                    <image-picker
                        v-model="fields[index]['value']"
                        @changeImage="(value) => changeImage(value, index)"
                        :value="fields[index]['value']"
                        :id="'erp-'+fields[index]['id']"
                    />
                </div>
            </div>
        </div>

        <div class="wperp-form-group">
            <submit-button :text="__('Save Changes', 'erp')" />
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
import { generateFormDataFromObject } from "../../utils/FormDataHandler";

var $ = jQuery;

export default {
    name: "BaseContentLayout",

    components: {
        SubmitButton,
        ImagePicker,
        Tooltip,
        InputDesc,
        DatePicker,
        MultiSelect
    },

    data() {
        return {
            fields: [],
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
            required: false
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
                    single_option : ! self.single_option ? self.section_id: null,
                    sub_section_id: self.sub_section_id,
                    _wpnonce      : erp_settings_var.nonce,
                    action        : 'erp-settings-get-data'
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
                    requestDataPost[ item.id ] = item.value.id;
                }

            } );

            if ( typeof self.sub_sub_section_id !== 'undefined' && self.sub_sub_section_id !== '' ) {
                requestDataPost['sub_sub_section'] = self.sub_sub_section_id;
            }

            let requestData = {
                ...requestDataPost,
                _wpnonce: erp_settings_var.nonce,
                action  : "erp-settings-save",
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
                    } else {
                        self.showAlert("error", response.data);
                    }
                },
            });

        },

        /**
         * Change Image Type Inputs
         */
        changeImage( value, index ) {
            this.fields[ index ]['value'] = value;
        }
    },
};
</script>
