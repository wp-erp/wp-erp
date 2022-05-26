<template>
    <div>
        <h2 class="section-title" v-if="!disableSectionTitle">{{ sectionTitle }}</h2>
        <template v-if="Object.keys(subMenus).length > 0 && !disableMenu">
            <settings-sub-menu :menus="subMenus" :parent_id="section_id" />
        </template>

        <div class="settings-box" :id="`erp-settings-box-${section_id}-${sub_section_id}`">
            <h3 class="sub-section-title" v-if="subSectionTitle && enableSubSectionTitle">
                <slot name="subSectionTitle">{{ subSectionTitle }}</slot>
            </h3>
            <p class="sub-section-description" v-if="subSectionDescription && enableSubSectionTitle" v-html="subSectionDescription"></p>

            <slot v-if="!enable_content"></slot>

            <base-content-layout
                v-if="enable_content"
                :section_id="section_id"
                :sub_section_id="sub_section_id"
                :sub_sub_section_id="sub_sub_section_id"
                :inputs="inputFields"
                :single_option="single_option"
                :options="options">

                <!-- Extended-data slot which will be append before Save changes button -->
                <div slot="extended-data">
                    <slot name="extended-data"></slot>
                </div>
            </base-content-layout>

        </div>
    </div>
</template>

<script>
import SettingsSubMenu from "../menu/SettingsSubMenu.vue";
import BaseContentLayout from "../layouts/BaseContentLayout.vue";

export default {
    name: "BaseLayout",

    components: {
        SettingsSubMenu,
        BaseContentLayout,
    },

    data() {
        return {
            sectionTitle         : "",
            subSectionTitle      : "",
            subSectionDescription: "",
            subMenus             : [],
            allFields            : [],
            inputFields          : [],
            single_option        : true,
        };
    },

    props: {
        section_id: {
            type    : String,
            required: true,
        },
        sub_section_id: {
            type    : String,
            required: true,
        },
        sub_sub_section_id: {
            type    : String,
            required: false
        },
        enable_content: {
            type    : Boolean,
            required: false
        },
        options: {
            type    : Object,
            required: false,
        },
        enableSubSectionTitle: {
            type    : Boolean,
            required: false,
            default : true
        },
        disableMenu: {
            type    : Boolean,
            required: false,
            default : false
        },
        disableSectionTitle: {
            type    : Boolean,
            required: false,
            default : false
        }
    },

    created () {
        // process the menus and get the sections data
        const menus       = erp_settings_var.erp_settings_menus;
        const parentMenu  = menus.find((menu) => menu.id === this.section_id);

        this.sectionTitle = this.getSectionTitle( parentMenu );
        this.subMenus     = parentMenu.sections;
        let fields        = [];

        this.single_option = parentMenu.single_option;

        // Check if second level sub_section_id is provided, like email_connect > gmail
        if ( typeof this.sub_sub_section_id !== 'undefined' && this.sub_sub_section_id.length > 0 ) {
            const subSectionFields = parentMenu.fields[ this.sub_section_id ];

            subSectionFields.map( subSectionField => {
                if ( subSectionField.type === 'sub_sections' ) {
                    fields = subSectionField.sub_sections[this.sub_sub_section_id].fields;
                }
            } );

        } else {
            if ( parentMenu.single_option ) {
                fields = parentMenu.fields[ this.sub_section_id ];
            } else {
                if ( this.section_id !== this.sub_section_id ) {
                    fields = parentMenu.fields[ this.sub_section_id ];
                } else {
                    fields = parentMenu.fields;
                }
            }
        }

        this.allFields = this.inputFields = fields;

        if ( typeof fields !== 'undefined' && Object.keys( fields ).length > 0 ) {
            this.subSectionTitle       = fields[0]?.title;
            this.subSectionDescription = fields[0]?.desc;

            if ( this.enable_content ) {
                // Process the fields and get the real input fields
                let inputFields = [];

                fields.forEach( field => {
                    if ( field.type !== "title" && field.type !== "sectionend" ) {
                        field.value = null;
                        inputFields.push(field);
                    }
                });

                this.inputFields = inputFields;
            }
        }
    }
};
</script>
