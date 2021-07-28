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
                    <div class="email-icon" v-for="connection in outgoingConnections" :key="connection.slug">
                        <img :src="connection.isActive ? connection.enableIcon :  connection.disableIcon" alt="" />
                        <span class="checkbox-icon checkbox-active" v-if="connection.isActive"><i class="fa fa-check-circle"></i></span>
                        <span class="checkbox-icon checkbox-inactive" v-else @click="toggleActiveConnection(connection, 'outgoing')"></span>
                        <p>{{ connection.name }}</p>
                    </div>
                </div>
                <div v-if="activeOutgoingEmail === 'smtp'"><smtp-email /></div>
            </div>
            <div class="email-card email-connect-incoming">
                <h4>{{ __( 'Incoming Email Setting', 'erp' ) }}</h4>
                <div class="email-icons">
                    <div class="email-icon" v-for="connection in incomingConnections" :key="connection.slug">
                        <img :src="connection.isActive ? connection.enableIcon :  connection.disableIcon" alt="" />
                        <span class="checkbox-icon checkbox-active" v-if="connection.isActive"><i class="fa fa-check-circle"></i></span>
                        <span class="checkbox-icon checkbox-inactive" v-else @click="toggleActiveConnection(connection, 'incoming')"></span>
                        <p>{{ connection.name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";
import BaseContentLayout from "../../layouts/BaseContentLayout.vue";
import SmtpEmail from "./SmtpEmail.vue";

export default {
    name: 'EmailCConnect',

    components: {
        BaseLayout,
        BaseContentLayout,
        SmtpEmail
    },

    data() {
        return {
            mailConnections    : [],
            activeOutgoingEmail: 'smtp',
            activeIncomingEmail: 'imap',
        }
    },

    created() {
        this.getMailConnections();
    },

    methods: {
        getMailConnections() {
            const mailConnections = [
                {
                    type       : 'outgoing',
                    enableIcon : `${erp_settings_var.erp_assets}/images/wperp-settings/email-smtp-enable.png`,
                    disableIcon: `${erp_settings_var.erp_assets}/images/wperp-settings/email-smtp-disable.png`,
                    name       : __( 'SMTP', 'erp' ),
                    slug       : 'smtp',
                    isActive   : true
                },
                {
                    type       : 'outgoing',
                    enableIcon : `${erp_settings_var.erp_assets}/images/wperp-settings/email-mailgun-enable.png`,
                    disableIcon: `${erp_settings_var.erp_assets}/images/wperp-settings/email-mailgun-disable.png`,
                    name       : __( 'Mailgun', 'erp' ),
                    slug       : 'mailgun',
                    isActive   : false
                },
                {
                    type       : 'incoming',
                    enableIcon : `${erp_settings_var.erp_assets}/images/wperp-settings/email-imap-enable.png`,
                    disableIcon: `${erp_settings_var.erp_assets}/images/wperp-settings/email-imap-disable.png`,
                    name       : __( 'IMAP', 'erp' ),
                    slug       : 'imap',
                    isActive   : true
                },
                {
                    type       : 'incoming',
                    enableIcon : `${erp_settings_var.erp_assets}/images/wperp-settings/email-google-enable.png`,
                    disableIcon: `${erp_settings_var.erp_assets}/images/wperp-settings/email-google-disable.png`,
                    name       : __( 'Google Connect', 'erp' ),
                    slug       : 'google',
                    isActive   : false
                }
            ];
            this.mailConnections = mailConnections;
        },

        toggleActiveConnection(activeConnection, type) {
            if(type === 'outgoing') {
                this.activeOutgoingEmail = activeConnection.slug;

                this.mailConnections.filter(connection => {
                    connection.isActive = false;
                    if(activeConnection.slug === connection.slug) {
                        connection.isActive = true;
                    }
                })
            } else {
                this.activeIncomingEmail = activeConnection.slug;

                this.mailConnections.filter(connection => {
                    connection.isActive = false;
                    if(activeConnection.slug === connection.slug) {
                        connection.isActive = true;
                    }
                })
            }
        }
    },

    computed: {
        outgoingConnections: function() {
            return this.mailConnections.filter( mail => mail.type === 'outgoing' );
        },

        incomingConnections: function() {
            return this.mailConnections.filter( mail => mail.type === 'incoming' );
        }
    }
}
</script>
