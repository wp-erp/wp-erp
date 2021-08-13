<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="email_connect"
        :enable_content="false"
        :enableSubSectionTitle="false"
        >
        <div class="email-connect-area">
            <div class="email-card email-connect-outgoing">
                <h4>{{ __( 'Outgoing Email Setting', 'erp' ) }}</h4>
                <div class="email-icons">
                    <div v-for="connection in outgoingConnections" :class="`email-icon pointer ${connection.slug === activeOutgoingEmail ? 'active-email-icon' : ''}`" :key="connection.slug" @click="toggleActiveConnection(connection, 'outgoing')">
                        <img :src="connection.isEnabled ? connection.enableIcon : connection.disableIcon" alt="" />
                        <span class="checkbox-icon checkbox-active" v-if="connection.isEnabled"><i class="fa fa-check-circle"></i></span>
                        <span class="checkbox-icon checkbox-inactive" v-else @click="toggleActiveConnection(connection, 'outgoing')"></span>
                        <p>{{ connection.name }}</p>
                    </div>
                </div>
                <div v-if="activeOutgoingEmail === 'smtp'"><smtp-email /></div>
                <div v-else><mailgun-email /></div>
            </div>
            <div class="email-card email-connect-incoming">
                <h4>{{ __( 'Incoming Email Setting', 'erp' ) }}</h4>
                <div class="email-icons">
                    <div :class="`email-icon pointer ${connection.slug === activeIncomingEmail ? 'active-email-icon' : ''}`" v-for="connection in incomingConnections" :key="connection.slug" @click="toggleActiveConnection(connection, 'incoming')">
                        <img :src="connection.isEnabled ? connection.enableIcon : connection.disableIcon" alt="" />
                        <span class="checkbox-icon checkbox-active" v-if="connection.isEnabled"><i class="fa fa-check-circle"></i></span>
                        <span class="checkbox-icon checkbox-inactive" v-else @click="toggleActiveConnection(connection, 'incoming')"></span>
                        <p>{{ connection.name }}</p>
                    </div>
                </div>
                <div v-if="activeIncomingEmail === 'imap'"><imap-email /></div>
                <div v-else><google-email /></div>
            </div>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";
import BaseContentLayout from "../../layouts/BaseContentLayout.vue";

// Email components
import SmtpEmail from "./SmtpEmail.vue";
import GoogleEmail from "./GoogleEmail.vue";
import ImapEmail from "./ImapEmail.vue";
import MailgunEmail from "./MailgunEmail.vue";
import { generateFormDataFromObject } from "../../../utils/FormDataHandler";

import { mapState } from 'vuex';
var $ = jQuery;

export default {
    name: 'EmailCConnect',

    components: {
        BaseLayout,
        BaseContentLayout,
        SmtpEmail,
        ImapEmail,
        GoogleEmail,
        MailgunEmail
    },

    data() {
        return {
            mailConnections : [],
        }
    },

    created() {
        this.getMailConnections();
    },

    methods: {
        getMailConnections() {
            const self = this;
            let requestData = window.settings.hooks.applyFilters( "requestData",
                {
                    _wpnonce: erp_settings_var.nonce,
                    action  : 'erp_settings_get_email_providers'
                }
            );

            const postData = generateFormDataFromObject( requestData );
            let providers  = [];

            $.ajax({
                url        : erp_settings_var.ajax_url,
                type       : "POST",
                data       : postData,
                processData: false,
                contentType: false,
                success    : function ( response ) {
                    if ( response.success ) {
                        providers = response.data;

                        const mailConnections = [];
                        Object.keys(providers).forEach(key => {
                            const connection = providers[key];
                            mailConnections.push({
                                type       : connection.type,
                                enableIcon : connection.icon_enable,
                                disableIcon: connection.icon_disable,
                                name       : connection.name,
                                slug       : key,
                                isActive   : connection.is_active,
                                isEnabled  : connection.enabled
                            })
                        });

                        self.mailConnections = mailConnections;
                    }
                }
            });
        },

        toggleActiveConnection(activeConnection, type) {
            this.mailConnections.filter(connection => {
                if (connection.type === type){
                    connection.isActive = false;
                }

                if (activeConnection.slug === connection.slug) {
                    connection.isActive = true;
                }
            });
        }
    },

    computed: {
        outgoingConnections: function() {
            return this.mailConnections.filter( mail => mail.type === 'outgoing' );
        },

        incomingConnections: function() {
            return this.mailConnections.filter( mail => mail.type === 'incoming' );
        },

        activeOutgoingEmail: function() {
            const activeOutgoingMails = this.outgoingConnections.filter( mail => mail.isActive );

            if (activeOutgoingMails.length > 0) {
                return activeOutgoingMails[0].slug;
            } else {
                return 'smtp';
            }
        },

        activeIncomingEmail: function() {
            const activeIncomingMails = this.incomingConnections.filter( mail => mail.isActive );

            if ( activeIncomingMails.length > 0 ) {
                return activeIncomingMails[0].slug;
            } else {
                return 'imap';
            }
        },

        ...mapState({
            formDatas( state ) {
                return state.formdata.data;
            }
        })
    },

    watch: {
        formDatas: function(formData) {
            if ( typeof formData !== 'undefined' && formData !== null ) {
                this.getMailConnections();
            }
        }
    },
}
</script>
