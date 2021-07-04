<template>
    <div id="erp-wc-sync">
        <base-layout v-if="notice"
            section_id="erp-woocommerce"
            sub_section_id="erp-woocommerce"
        >   
            <div class="wc-sync-logo">
                <img :src="wooSyncLogo" id="wc-sync-bg">
            </div>
            
            <div class="wc-sync-notice">
                <h4>{{ __("Connect with WooCommerce", "erp") }}</h4>
                <p v-html="notice"></p>
            </div>

            <div class="wperp-form-group erp-wc-sync-notice-btn">
                <a class="wperp-btn btn--primary" :href="btnLink" target="_blank" id="erp-wc-sync-notice-btn">
                    {{ btnText }}
                </a>
            </div>
        </base-layout>
    </div>
</template>

<script>
import BaseLayout from '../layouts/BaseLayout.vue';

export default {
    name: 'WooCommerce',

    data() {
        return {
            assetSource: '',
            notice: '',
            proActivated: false,
            wcPurchased: false,
            wcActivated: false,
        }
    },

    components: {
        BaseLayout
    },

    beforeCreate() {
        if (this.$store.state.wcActivated) {
            this.$router.push({name:'WCOrderSync'});
        }
    },

    created() {
        let menu          = erp_settings_var.erp_settings_menus.find((menu) => menu.id === 'erp-woocommerce');
        this.assetSource  = erp_settings_var.erp_assets;
        this.notice       = menu.extra.notice;
        this.proActivated = menu.extra.pro_activated;
        this.wcPurchased  = menu.extra.wc_purchased;
        this.wcActivated  = menu.extra.wc_activated;

        if (this.wcActivated) {
            this.$router.push({ name: "WCSynchronization" })
        }
;    },

    computed: {
        wooSyncLogo() {
            return `${this.assetSource}/images/wperp-settings/wc-sync.png`;
        },

        btnLink() {
            let link     = erp_settings_var.erp_pro_link;
            let adminUrl = erp_settings_var.admin_url;
            
            if (this.proActivated && !this.wcActivated) {
                return `${adminUrl}?page=erp-extensions`;
            }

            return link;
        },

        btnText() {
            if (this.proActivated) {
                if (! this.wcPurchased) {
                    return __('Get WooCommerce Extension', 'erp');
                } else if (!this.wcActivated) {
                    return __('Activate WooCommerce', 'erp');
                }
            }

            return __('Get WP ERP Pro', 'erp');
        }
    }
}
</script>