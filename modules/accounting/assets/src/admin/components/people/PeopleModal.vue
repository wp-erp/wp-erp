<template>
    <div id="people-modal">
        <div class="wperp-container">
            <div id="wperp-add-customer-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
                <div class="wperp-modal-dialog">
                    <div class="wperp-modal-content">
                        <!-- modal body title -->
                        <div class="wperp-modal-header">
                            <h3 v-if="!people">{{ title }}</h3>
                            <h3 v-else>{{ __('Update', 'erp') }} {{ title }}</h3>
                            <span class="modal-close">
                                <i class="flaticon-close" @click="$parent.$emit('modal-close')"></i></span>
                        </div>
                        <ul class="errors" v-if="error_message.length">
                            <li v-for="(error, index) in error_message" :key="index">* {{ error }}</li>
                        </ul>
                        <!-- end modal body title -->
                        <form action="" method="post" class="modal-form edit-customer-modal" @submit.prevent="saveCustomer">
                            <div class="wperp-modal-body">
                                <!-- add new product form -->
                                <div class="wperp-row wperp-gutter-20">
                                    <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                        <label for="first_name">{{ __('First Name', 'erp') }} <span class="wperp-required-sign">*</span></label>
                                        <input type="text" v-model="peopleFields.first_name" id="first_name" class="wperp-form-field" :placeholder="__('First Name', 'erp')" required>
                                    </div>
                                    <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                        <label for="last_name">{{ __('Last Name', 'erp') }} <span class="wperp-required-sign">*</span></label>
                                        <input type="text" v-model="peopleFields.last_name" id="last_name" class="wperp-form-field" :placeholder="__('Last Name', 'erp')" required>
                                    </div>
                                    <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                        <label for="email">{{ __('Email', 'erp') }} <span class="wperp-required-sign">*</span></label>
                                        <input type="email" @blur="checkEmailExistence" v-model="peopleFields.email" id="email" class="wperp-form-field" placeholder="you@domain.com" required>
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="phone">{{ __('Phone', 'erp') }}</label>
                                        <input type="tel" v-model="peopleFields.phone" id="phone" class="wperp-form-field" placeholder="(123) 456-789">
                                    </div>
                                </div>

                                <!-- extra fields -->
                                <div class="wperp-more-fields" v-if="showMore">
                                    <div class="wperp-row wperp-gutter-20">
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label for="company">{{ __('Company', 'erp') }}</label>
                                            <input type="text" v-model="peopleFields.company" id="company" class="wperp-form-field" :placeholder="__('ABC Corporation', 'erp')">
                                        </div>
                                        <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                            <label for="mobile">{{ __('Mobile', 'erp') }}</label>
                                            <input type="tel" v-model="peopleFields.mobile" id="mobile" class="wperp-form-field">
                                        </div>
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label for="website">{{ __('Website', 'erp') }}</label>
                                            <input type="text" v-model="peopleFields.website" id="website" class="wperp-form-field" placeholder="www.domain.com">
                                        </div>
                                        <div class="wperp-col-xs-12 wperp-form-group">
                                            <label for="note">{{ __('Note', 'erp') }}</label>
                                            <textarea v-model="peopleFields.notes" id="note" cols="30" rows="4" class="wperp-form-field" :placeholder="__('Type here', 'erp')"></textarea>
                                        </div>
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label for="fax">{{ __('Fax', 'erp') }}</label>
                                            <input type="text" v-model="peopleFields.fax" id="fax" class="wperp-form-field" :placeholder="__('Type here', 'erp')">
                                        </div>
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label for="street1">{{ __('Street 1', 'erp') }}</label>
                                            <input type="text" v-model="peopleFields.street_1" id="street1" class="wperp-form-field" :placeholder="__('Street 1', 'erp')">
                                        </div>
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label for="street2">{{ __('Street 2', 'erp') }}</label>
                                            <input type="text" v-model="peopleFields.street_2" id="street2" class="wperp-form-field" :placeholder="__('Street 2', 'erp')">
                                        </div>
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label for="city">{{ __('City', 'erp') }}</label>
                                            <input type="text" v-model="peopleFields.city" id="city" class="wperp-form-field" :placeholder="__('City/Town', 'erp')">
                                        </div>
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label>{{ __('Country', 'erp') }}</label>
                                            <div class="with-multiselect">
                                                <multi-select
                                                v-model="peopleFields.country"
                                                :options="countries"
                                                :multiple="false" @input="getState( peopleFields.country )" />
                                            </div>
                                        </div>
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label>{{ __('Province/State', 'erp') }}</label>
                                            <div class="with-multiselect">
                                                <multi-select
                                                v-model="peopleFields.state"
                                                :options="states"
                                                :multiple="false" />
                                            </div>
                                        </div>
                                        <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                            <label for="post_code">{{ __('Post Code', 'erp') }}</label>
                                            <input type="text" v-model="peopleFields.postal_code" id="post_code" class="wperp-form-field" :placeholder="__('Post Code', 'erp')">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check">
                                    <label class="form-check-label mb-0" for="show_more">
                                        <input class="form-check-input" name="show_more" id="show_more" type="checkbox" @click="showDetails">
                                        <span class="form-check-sign"></span>
                                        <span class="field-label">{{ __('Show More', 'erp') }}</span>
                                    </label>
                                </div>

                            </div>

                            <div class="wperp-modal-footer pt-0">
                                <!-- buttons -->
                                <div class="buttons-wrapper text-right">
                                    <button class="wperp-btn btn--default modal-close" @click="$parent.$emit('modal-close')" type="reset">{{ __('Cancel', 'erp') }}</button>
                                    <button v-if="!people" class="wperp-btn btn--primary" type="submit">{{ __('Add New', 'erp') }}</button>
                                    <button v-else class="wperp-btn btn--primary" type="submit">{{ __('Update', 'erp') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import MultiSelect from 'admin/components/select/MultiSelect.vue';

export default {
    name: 'CustomerModal',
    components: {
        MultiSelect
    },
    props: {
        people: {
            type: Object
        },
        title: {
            required: true
        },
        type: [String]
    },
    data() {
        return {
            peopleFields: {
                id         : null,
                first_name : '',
                last_name  : '',
                email      : '',
                mobile     : '',
                company    : '',
                phone      : '',
                website    : '',
                notes      : '',
                fax        : '',
                street_1   : '',
                street_2   : '',
                city       : '',
                country    : '',
                state      : '',
                postal_code: ''
            },
            states       : [],
            emailExists  : false,
            showMore     : false,
            customers    : [],
            url          : '',
            error_message: [],
            countries    : [],
            get_states   : []
        };
    },
    methods: {
        saveCustomer() {
            if (!this.checkForm()) {
                return false;
            }

            this.$store.dispatch('spinner/setSpinner', true);

            var type = '';
            var url = '';

            if (!this.people) {
                url = this.url;
                type = 'post';
            } else {
                url = this.url + '/' + this.peopleFields.id;
                type = 'put';
            }

            var message = (type === 'post') ? 'Created' : 'Updated';

            HTTP[type](url, this.peopleFields).then(response => {
                this.$root.$emit('peopleUpdate');
                this.resetForm();
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', message);
            });
        },

        checkForm() {
            this.error_message = [];

            if (this.emailExists) {
                this.error_message.push('Email already exists as customer/vendor');
                this.emailExists = false;

                return false;
            }

            if (this.peopleFields.first_name && this.peopleFields.last_name && this.peopleFields.email) {
                return true;
            }

            if (!this.peopleFields.first_name) {
                this.error_message.push('First name is required');
            }

            if (!this.peopleFields.last_name) {
                this.error_message.push('Last name is required');
            }

            if (!this.peopleFields.email) {
                this.error_message.push('Email is required');
            }

            return false;
        },

        showDetails() {
            this.showMore = !this.showMore;
        },

        getCountries() {
            HTTP.get('customers/country').then(response => {
                const country = response.data.country;
                const states   = response.data.state;
                for (const x in country) {
                    if (states[x] === undefined) {
                        states[x] = [];
                    }

                    this.countries.push({ id: x, name: this.decodeHtml(country[x]), state: states[x] });
                }
                for (const state in states) {
                    for (const x in states[state]) {
                        this.get_states.push({ id: x, name: states[state][x] });
                    }
                }
            });
        },

        getState(country) {
            this.states = [];
            this.peopleFields.state = '';
            for (const state in country.state) {
                this.states.push({ id: state, name: country.state[state] });
            }
        },

        checkEmailExistence() {
            if (this.peopleFields.email) {
                if (!this.people) {
                    HTTP.get('/people/check-email', {
                        params: {
                            email: this.peopleFields.email
                        }
                    }).then((res) => {
                        this.emailExists = res.data;
                    });
                }
            }
        },

        getCustomers() {
            HTTP.get('/customers').then(response => {
                this.customers = response.data;
            });
        },

        setInputField() {
            if (this.people) {
                const people                  = this.people;
                this.peopleFields.id          = people.id;
                this.peopleFields.first_name  = people.first_name;
                this.peopleFields.last_name   = people.last_name;
                this.peopleFields.email       = people.email;
                this.peopleFields.mobile      = people.mobile;
                this.peopleFields.company     = people.company;
                this.peopleFields.phone       = people.phone;
                this.peopleFields.website     = people.website;
                this.peopleFields.notes       = people.notes;
                this.peopleFields.fax         = people.fax;
                this.peopleFields.street_1    = people.billing.street_1;
                this.peopleFields.street_2    = people.billing.street_2;
                this.peopleFields.city        = people.billing.city;
                this.peopleFields.country     = this.selectedCountry(people.billing.country);
                this.peopleFields.state       = this.selectedState(people.billing.state);
                this.peopleFields.postal_code = people.billing.postal_code;
            }
        },

        selectedCountry(id) {
            return this.countries.find(country => id === country.id);
        },

        selectedState(id) {
            return this.get_states.find(item => item.id === id);
        },

        generateUrl() {
            var url;
            if (this.type) {
                if (this.type === 'customer') {
                    url = 'customers';
                } else {
                    url = 'vendors';
                }
            } else if (this.$route.name.toLowerCase() === 'customerdetails') {
                url = 'customers';
            } else if (this.$route.name.toLowerCase() === 'vendordetails') {
                url = 'vendors';
            } else {
                url = this.$route.name.toLowerCase();
            }

            return url;
        },

        resetForm() {
            this.peopleFields.first_name = '';
            this.peopleFields.last_name  = '';
            this.peopleFields.email      = '';
            this.peopleFields.mobile     = '';
            this.peopleFields.company    = '';
            this.peopleFields.phone      = '';
            this.peopleFields.website    = '';
            this.peopleFields.note       = '';
            this.peopleFields.fax        = '';
            this.peopleFields.street1    = '';
            this.peopleFields.street2    = '';
            this.peopleFields.city       = '';
            this.peopleFields.country    = '';
            this.peopleFields.state      = '';
            this.peopleFields.post_code  = '';
        }
    },

    created() {
        this.url = this.generateUrl();
        this.selectedCountry();
        this.setInputField();
        this.getCustomers();
        this.getCountries();
    }
};
</script>

<style lang="less">
    #people-modal {
        .errors {
            margin: 0 20px;
            color: #f44336;
            li {
                background: #f3f3f3;
                padding: 2px 10px;
            }
        }
    }
</style>
