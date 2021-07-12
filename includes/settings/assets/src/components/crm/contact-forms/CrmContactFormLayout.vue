<template>
    <base-layout
        :section_id="section_id"
        :sub_section_id="sub_section_id"
        :enable_content="false"
        :enableSubSectionTitle="false"
    >
        <div v-if="typeof options.sub_sections === 'undefined' && options.length > 0">
            <h3 class="sub-section-title">
                {{ options[0].title }}
            </h3>
            <div v-html="options[0].desc"></div>
        </div>
        <div v-else>
            <h3 class="sub-section-title" v-if="subSectionTitle">
                {{ subSectionTitle }}
            </h3>
        </div>

        <div>
            <ul class="sub-sub-menu">
                <li v-for="(menu, key) in options.sub_sections" :key="key">
                    <router-link :to="'/' + section_id + '/' + sub_section_id + '/' + key">
                        <span class="menu-name">{{ menu }}</span>
                    </router-link>
                </li>
            </ul>
            <slot></slot>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: "CrmContactForm",

    data() {
        return {
            section_id     : "erp-crm",
            sub_section_id : "contact_forms",
            subSectionTitle: "",
            options        : []
        };
    },

    components: {
        BaseLayout
    },

    created() {
        const menus          = erp_settings_var.erp_settings_menus;
        const parentMenu     = menus.find(menu => menu.id === this.section_id);
        this.subSectionTitle = parentMenu.sections[ this.sub_section_id ];
        this.options         = parentMenu.fields[ this.sub_section_id ];
    }
};
</script>
