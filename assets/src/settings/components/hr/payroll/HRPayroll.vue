<template>
    <base-layout
        :section_id="section_id"
        :sub_section_id="sub_section_id"
        :enable_content="false"
    >
        <h3 class="sub-section-title" v-if="subSectionTitle">
            {{ subSectionTitle }}
        </h3>

        <div>
            <ul class="sub-sub-menu">
                <li v-for="(menu, key, index) in options.sub_sections" :key="key">
                    <router-link :to="'/' + section_id + '/' + sub_section_id + '/' + key"
                    :class="$route.name === 'HrPayment' && index === 0 ? 'router-link-active': ''">
                        <span class="menu-name">{{ menu }}</span>
                    </router-link>
                </li>
            </ul>
            <slot></slot>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from "settings/components/layouts/BaseLayout.vue";
import SubmitButton from "settings/components/base/SubmitButton.vue";

export default {
    name: "HrPayroll",

    data() {
        return {
            section_id     : "erp-hr",
            sub_section_id : "payroll",
            subSectionTitle: "",
            options        : []
        };
    },

    components: {
        BaseLayout,
        SubmitButton,
    },

    created() {
        const menus          = erp_settings_var.erp_settings_menus;
        const parentMenu     = menus.find(menu => menu.id === this.section_id);

        this.subSectionTitle = parentMenu.sections[ this.sub_section_id ];
        this.options         = parentMenu.fields[ this.sub_section_id ];
    }
};
</script>
