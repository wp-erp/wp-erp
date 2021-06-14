<template>
    <div>
        <h2 class="section-title">{{ sectionTitle }}</h2>
        <template v-if="Object.keys(subMenus).length > 0">
            <settings-sub-menu :menus="subMenus" :parent_id="section_id" />
        </template>

        <div class="settings-box">
            <h3 class="sub-section-title" v-if="subSectionTitle">{{ subSectionTitle }}</h3>
            <p class="sub-section-description" v-if="subSectionDescription">{{ subSectionDescription }}</p>

            <slot v-if="!enable_content"></slot>

            <base-content-layout
                v-if="enable_content"
                :section_id="section_id"
                :sub_section_id="sub_section_id"
                :inputs="inputFields"
                :single_option="single_option"
            />
        </div>
    </div>
</template>

<script>
import SettingsSubMenu from "settings/components/menu/SettingsSubMenu.vue";
import BaseContentLayout from "settings/components/layouts/BaseContentLayout.vue";

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
            single_option        : true
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
        enable_content: {
            type    : Boolean,
            required: false
        }
    },

    created () {
        // process the menus and get the sections data
        const menus       = erp_settings_var.erp_settings_menus;
        const parentMenu  = menus.find((menu) => menu.id === this.section_id);

        this.sectionTitle = parentMenu.label + " Management";
        this.subMenus     = parentMenu.sections;
        let fields        = [];

        this.single_option = parentMenu.single_option;

        if ( parentMenu.single_option ) {
            fields = parentMenu.fields[ this.sub_section_id ];
        } else {
            if ( this.section_id !== this.sub_section_id ) {
                fields = parentMenu.fields[ this.sub_section_id ];
            } else {
                fields = parentMenu.fields;
            }
        }

        this.allFields = fields;

        if ( typeof fields !== 'undefined' && fields.length > 0 ) {
            this.subSectionTitle       = fields[0].title;
            this.subSectionDescription = fields[0].desc;

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
};
</script>
