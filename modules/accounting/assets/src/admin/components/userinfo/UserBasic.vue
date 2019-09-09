<template>
    <div class="wperp-panel wperp-panel-default mt-20">
        <div class="wperp-panel-body wperp-customer-panel">
            <people-modal  :people="userData" :title="title" v-if="showModal"></people-modal>
            <!-- edit customers info trigger -->
            <span class="edit-badge" data-toggle="wperp-modal" data-target="wperp-edit-customer-modal">
                <i class="flaticon-edit" @click="showModal = true"></i>
            </span>
            <div class="wperp-row">
                <div class="wperp-col-lg-3 wperp-col-md-4 wperp-col-sm-4 wperp-col-xs-12">
                    <div class="customer-identity">
                        <img :src="img_url" :alt=user.name>
                        <div class="">
                            <h3>{{user.first_name}}  {{ user.last_name }}</h3>
                            <span>{{user.email}}</span>
                        </div>
                    </div>
                </div>
                <div class="wperp-col-lg-9 wperp-col-md-8 wperp-col-sm-8 wperp-col-xs-12">
                    <ul class="customer-meta">
                        <li>
                            <strong>{{ __('Phone', 'erp') }}:</strong>
                            <span>{{ user.phone }}</span>
                        </li>
                        <li>
                            <strong>{{ __('Mobile', 'erp') }}:</strong>
                            <span>{{ user.mobile }}</span>
                        </li>
                        <li>
                            <strong>{{ __('Website', 'erp') }}:</strong>
                            <span>{{ user.website }}</span>
                        </li>
                        <li>
                            <strong>{{ __('Fax', 'erp') }}:</strong>
                            <span>{{ user.fax }}</span>
                        </li>
                        <li>
                            <strong>{{ __('Address', 'erp') }}:</strong>
                            <span v-if="userData.billing">{{ userData.billing.street_1 }}, {{ userData.billing.city }} </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import PeopleModal from 'admin/components/people/PeopleModal.vue';

export default {
    name: 'UserBasicInfo',
    components: {
        PeopleModal
    },

    props: {
        userData: {
            type: Object,
            required: true,
            default: () => {
                return {
                    id: '',
                    name: 'Full Name',
                    email: 'email@mail.com',
                    img_url: erp_acct_var.acct_assets + '/images/dummy-user.png', /* global erp_acct_var */
                    meta: {
                        phone: '+ 88101230123',
                        mobile: '+ 999999999',
                        website: 'www.website.com',
                        fax: '+99898989898',
                        address: 'House#1005, Block#B, Avenue#9, Mirpur DOHS'
                    }
                };
            }
        }
    },

    data() {
        return {
            showModal: false,
            title: '',
            img_url: erp_acct_var.acct_assets + '/images/dummy-user.png'
        };
    },
    computed: {
        user() {
            return this.userData;
        }
    },

    methods: {
        camelCase(str) {
            return str.toLowerCase().replace(/(?:(^.)|(\s+.))/g, function(match) {
                return match.charAt(match.length - 1).toUpperCase();
            });
        }
    },

    created() {
        this.title = (this.$route.name.toLowerCase() === 'customers') ? 'customer' : 'vendor';
        this.$on('modal-close', function() {
            this.showModal = false;
        });
        var self = this;
        this.$root.$on('peopleUpdate', function() {
            self.showModal = false;
            self.$parent.fetchItem(self.$route.params.id);
        });
    }
};

</script>
