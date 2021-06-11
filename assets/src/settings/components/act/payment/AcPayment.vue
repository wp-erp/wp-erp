<template>
    <base-layout
        :section_id="section_id"
        :sub_section_id="sub_section_id"
        :enable_content="false"
        :single_option="single_option"
    >
        <h3 class="sub-section-title" v-if="subSectionTitle">{{ subSectionTitle }}</h3>

        <div>
            <ul class="sub-sub-menu">
                <li v-for="(menu, key, index) in options.sub_sections" :key="key">
                    <router-link :to="'/' + section_id + '/' + sub_section_id + '/' + key"
                    :class="$route.name === 'AcPaymentGeneral' && index === 0 ? 'router-link-active': ''">
                        <span class="menu-name">{{ menu }}</span>
                    </router-link>
                </li>
            </ul>

            <base-content-layout
                :inputs="inputs"
                :sub_sub_section_title="subSubSectionTitle"
                :section_id="section_id"
                :sub_section_id="sub_section_id"
                :sub_sub_section_id="sub_sub_section"
                :single_option="true"
            />
        </div>
    </base-layout>
</template>

<script>
import BaseLayout        from "settings/components/layouts/BaseLayout.vue";
import BaseContentLayout from "settings/components/layouts/BaseContentLayout.vue";
import SubmitButton      from "settings/components/base/SubmitButton.vue";

export default {
    name: "AcPayment",

    data() {
        return {
            section_id        : 'erp-ac',
            sub_section_id    : 'payment',
            subSectionTitle   : '',
            subSubSectionTitle: '',
            options           : [],
            inputs            : []
        };
    },

    components: {
        BaseLayout,
        BaseContentLayout,
        SubmitButton
    },

    props: {
        sub_sub_section: {
            type: String,
            required: true
        }
    },

    created() {
        const menus             = erp_settings_var.erp_settings_menus;
        const parentMenu        = menus.find(menu => menu.id === this.section_id);

        this.subSectionTitle    = parentMenu.sections[ this.sub_section_id ];
        this.options            = parentMenu.fields[ this.sub_section_id ];
        this.inputs             = parentMenu.fields[ this.sub_section_id ][ this.sub_sub_section ];
        this.subSubSectionTitle = this.inputs.length > 0 ? this.inputs[0].title : '';
    }
};
</script>
