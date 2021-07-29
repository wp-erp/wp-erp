<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="gmail"
        :enable_content="true"
        :enableSubSectionTitle="false"
        :disableMenu="true"
        :disableSectionTitle="true"
    >
        <template slot="extended-data">
            <div v-if="gmailConnected !== null && gmailConnected.status" style="margin-bottom: 30px">
                <a class="button" :href="gmailConnected.link" target="_blank">
                    {{ __( 'Click to Authorize your gmail account', 'erp' ) }}
                </a>

                <a class="button" :href="gmailConnected.disconnect_url" target="_blank" style="margin-left: 20px" v-if="gmailConnected.is_connected">
                    {{ __( 'Disconnect', 'erp' ) }}
                </a>
            </div>
        </template>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";
import { generateFormDataFromObject } from "../../../utils/FormDataHandler";

var $ = jQuery;

export default {
    name: "GoogleEmail",

    components: {
        BaseLayout
    },

    data() {
        return {
            gmailConnected: {
                link          : '',
                status        : false,
                is_connected  : false,
                disconnect_url: ''
            }
        }
    },

    created() {
        this.getAuthorizationUrl();
    },

    methods: {
        getAuthorizationUrl() {
            const self = this;
            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    _wpnonce: erp_settings_var.nonce,
                    action  : "erp_check_gmail_connection_established",
                }
            );

            const postData = generateFormDataFromObject(requestData);

            $.ajax({
                url        : erp_settings_var.ajax_url,
                type       : "POST",
                data       : postData,
                processData: false,
                contentType: false,
                success    : function (response) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if (response.success) {
                        self.gmailConnected  = response.data;
                    }
                },
            });
        }
    },
};
</script>
