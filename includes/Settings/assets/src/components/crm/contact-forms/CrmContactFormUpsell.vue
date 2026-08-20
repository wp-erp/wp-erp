<template>
    <div class="cfi-pro-upsell">
        <section class="notice notice-info cfi-hide-submit mt-20" style="max-width: 600px; padding: 20px;">
            <h3 style="margin-top: 0;">
                <span class="dashicons dashicons-lock" style="color: #d98500;"></span>
                {{ i18n.proUpsellHeading }}
            </h3>

            <p>{{ upsellText }}</p>

            <p v-if="! formPluginActive" style="color: #b32d2e;">
                {{ installHint }}
            </p>

            <p>
                <a
                    :href="upgradeUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="wperp-btn btn--primary"
                >
                    {{ i18n.proUpgradeButton }}
                </a>
            </p>
        </section>
    </div>
</template>

<script>
export default {
    name: "CrmContactFormUpsell",

    props: {
        title: {
            type    : String,
            required: true
        },
        upgradeUrl: {
            type   : String,
            default: ''
        },
        formPluginActive: {
            type   : Boolean,
            default: false
        },
        i18n: {
            type    : Object,
            required: true
        }
    },

    computed: {
        upsellText() {
            // i18n.proUpsellText contains a single %s for the form plugin name
            return ( this.i18n.proUpsellText || '' ).replace( '%s', this.title );
        },

        installHint() {
            return ( this.i18n.proInstallHint || '' ).replace( '%s', this.title );
        }
    }
};
</script>
