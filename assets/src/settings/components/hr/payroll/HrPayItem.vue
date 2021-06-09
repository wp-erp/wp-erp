<template>
    <hr-payroll>
        <form
            action=""
            class="wperp-form"
            method="post"
            @submit.prevent="onFormSubmit"
            enctype="multipart/form-data"
        >
            <h3 class="sub-sub-title">Pay Items Settings</h3>


            <div class="wperp-form-group">
                <submit-button :text="__('Save Changes', 'erp')" />
            </div>
        </form>
    </hr-payroll>
</template>

<script>
import HrPayroll from "settings/components/hr/payroll/HrPayroll.vue";
import SubmitButton from "settings/components/base/SubmitButton.vue";

export default {
    name: "HrPayItem",

    data() {
        return {
            section_id     : "erp-hr",
            sub_section_id : "payroll",
            subSectionTitle: "",
            options        : []
        };
    },

    components: {
        SubmitButton,
        HrPayroll
    },

    created() {
        const menus          = erp_settings_var.erp_settings_menus;
        const parentMenu     = menus.find(menu => menu.id === this.section_id);

        this.subSectionTitle = parentMenu.sections[ this.sub_section_id ];
        this.options         = parentMenu.fields[ this.sub_section_id ];
    },

    methods: {
        onFormSubmit() {
            //
        },
    },
};
</script>
