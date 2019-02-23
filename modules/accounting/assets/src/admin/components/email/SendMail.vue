<template>
    <modal
        @close="closeModal"
        :title="'Send Mail'"
        :footer="true"
        :hasForm="true"
        :header="true"
    >
        <template slot="body">
            <div class="wperp-form-group wperp-row">
                <div class="wperp-col-sm-3 wperp-col-xs-12">
                    <label>To <span class="wperp-required-sign">*</span></label>
                </div>
                <div class="wperp-col-sm-9 wperp-col-xs-12 wperp-email-multiselect">
                    <multiselect
                        v-model="emails"
                        tag-placeholder="Add this Email"
                        placeholder="Click to Add Email Address"
                        label="name"
                        track-by="code"
                        :options="options"
                        :multiple="true"
                        :taggable="true"
                        @tag="addEmail">
                    </multiselect>
                </div>
            </div>
            <div class="wperp-form-group wperp-row">
                <div class="wperp-col-sm-3 wperp-col-xs-12">
                    <label>Subject <span class="wperp-required-sign">*</span></label>
                </div>
                <div class="wperp-col-sm-9 wperp-col-xs-12">
                    <input type="text" v-model="subject" class="wperp-form-field" placeholder="Enter Subject Here" />
                </div>
            </div>
            <div class="wperp-form-group wperp-row">
                <div class="wperp-col-sm-3 wperp-col-xs-12">
                    <label>Message <span class="wperp-required-sign">*</span></label>
                </div>
                <div class="wperp-col-sm-9 wperp-col-xs-12">
                    <textarea v-model="message" class="wperp-form-field" placeholder="Enter Your Message Here" rows="4"></textarea>
                </div>
            </div>
            <div class="wperp-row">
                <div class="wperp-col-sm-3 wperp-col-xs-12">
                    <label>Attachment <span class="wperp-required-sign">*</span></label>
                </div>
                <div class="wperp-col-sm-9 wperp-col-xs-12">
                    <div class="form-check">
                        <label class="form-check-label mb-0">
                            <input class="form-check-input" v-model="attachment" type="checkbox">
                            <span class="form-check-sign"></span> <span class="field-label">Attach as PDF</span>
                        </label>
                    </div>
                </div>
            </div>
        </template>
        <template slot="footer">
            <div class="buttons-wrapper text-right">
                <button class="wperp-btn btn--default" @click="closeModal">Cancel</button>
                <button class="wperp-btn btn--primary" type="submit" @click.prevent="sendAsMail">Send</button>
            </div>
        </template>
    </modal>
</template>

<script>
    import HTTP from 'admin/http';
    import Dropdown from 'admin/components/base/Dropdown.vue';
    import Modal from 'admin/components/modal/Modal.vue';
    import Multiselect from 'vue-multiselect';

    export default {
        name: "SendMail",

        components: {
            HTTP,
            Dropdown,
            Modal,
            Multiselect
        },

        props: {
            data: Object,
            type: String
        },

        data() {
            return {
                options: [],
                emails: [],
                subject: '',
                message: '',
                attachment: ''
            }
        },

        methods: {
            closeModal (){
                this.$root.$emit('close');
            },

            addEmail (newEmail) {
                const email = {
                    name: newEmail,
                    code: newEmail.substring(0, 2) + Math.floor((Math.random() * 10000000))
                };
                this.emails.push(email)
            },

            sendAsMail() {
                HTTP.post(`/transactions/send-pdf/${this.$route.params.id}`, {
                    trn_data: this.data,
                    type: this.type,
                    receiver: this.emails,
                    subject: this.subject,
                    message: this.message,
                    attachment: this.attachment
                }).then(() => {
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Mail Sent!',
                        showConfirmButton: false,
                        timer: 1000
                    });
                });
            }
        }
    }
</script>

<style scoped>

</style>
