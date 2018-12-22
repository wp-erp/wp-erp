<template>
    <div class="wperp-container">
        <div id="wperp-add-customer-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
            <div class="wperp-modal-dialog">
                <div class="wperp-modal-content">
                    <!-- modal body title -->
                    <div class="wperp-modal-header">
                        <h3>Add Customer</h3>
                        <!-- <span class="modal-close">
                            <i class="flaticon-close" @click="$parent.$emit('modal-close')"></i></span> -->
                    </div>
                    <!-- end modal body title -->
                    <form action="" method="post" class="modal-form edit-customer-modal">
                        <div class="wperp-modal-body">
                            <!-- add new product form -->
                            <div class="wperp-row wperp-gutter-20">
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="first_name">First Name <span class="required-sign">*</span></label>
                                    <input type="text" v-model="customerFields.first_name" id="first_name" class="wperp-form-field" placeholder="First Name">
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="last_name">Last Name <span class="required-sign">*</span></label>
                                    <input type="text" v-model="customerFields.last_name" id="last_name" class="wperp-form-field" placeholder="Last Name">
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="email">Email</label>
                                    <input type="email" v-model="customerFields.email" id="email" class="wperp-form-field" placeholder="you@domain.com">
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="mobile">Mobile</label>
                                    <input type="tel" v-model="customerFields.mobile" id="mobile" class="wperp-form-field">
                                </div>
                            </div>

                            <!-- extra fields -->
                            <div class="wperp-more-fields" v-if="showMore">
                                <div class="wperp-row wperp-gutter-20">
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="company">Company</label>
                                        <input type="text" v-model="customerFields.company" id="company" class="wperp-form-field" placeholder="ABC Corporation">
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="phone">Phone</label>
                                        <input type="tel" v-model="customerFields.phone" id="phone" class="wperp-form-field" placeholder="(123) 456-789">
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="website">Website</label>
                                        <input type="tel" v-model="customerFields.website" id="website" class="wperp-form-field" placeholder="www.domain.com">
                                    </div>
                                    <div class="wperp-col-xs-12 wperp-form-group">
                                        <label for="note">Note</label>
                                        <textarea v-model="customerFields.notes" id="note" cols="30" rows="4" class="wperp-form-field" placeholder="Type here"></textarea>
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="fax">Fax</label>
                                        <input type="text" v-model="customerFields.fax" id="fax" class="wperp-form-field" placeholder="Type here">
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="street1">Street 1</label>
                                        <input type="text" v-model="customerFields.street_1" id="street1" class="wperp-form-field" placeholder="Street 1">
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="street2">Street 2</label>
                                        <input type="text" v-model="customerFields.street_2" id="street2" class="wperp-form-field" placeholder="Street 2">
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="city">City</label>
                                        <input type="text" v-model="customerFields.city" id="city" class="wperp-form-field" placeholder="City/Town">
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="country">Country</label>
                                        <div class="with-multiselect">
                                            <multi-select
                                            v-model="customerFields.country"
                                            :options="countries"
                                            :multiple="false" @input="getState( customerFields.country )" />
                                        </div>
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="state">Province/State</label>
                                        <div class="with-multiselect">
                                            <multi-select
                                            v-model="customerFields.state"
                                            :options="states"
                                            :multiple="false" />
                                        </div>
                                    </div>
                                    <div class="wperp-col-sm-6 wperp-col-xs-12 wperp-form-group">
                                        <label for="post_code">Post Code</label>
                                        <input type="text" v-model="customerFields.postal_code" id="post_code" class="wperp-form-field" placeholder="Post Code">
                                    </div>
                                </div>
                            </div>


                            <div class="form-check">
                                <label class="form-check-label mb-0" for="show_more">
                                    <input class="form-check-input" name="show_more" id="show_more" type="checkbox" @click="showDetails">
                                    <span class="form-check-sign"></span> <span class="label-text">Show More</span>
                                </label>
                            </div>

                        </div>

                        <div class="wperp-modal-footer pt-0">
                            <!-- buttons -->
                            <div class="buttons-wrapper text-right">
                                <button v-if="!customer" class="wperp-btn btn--primary" type="submit" @click.prevent="saveCustomer">Add New</button>
                                <button v-else class="wperp-btn btn--primary" type="submit" @click.prevent="saveCustomer">Update</button>
                                <button class="wperp-btn btn--default modal-close" @click="$parent.$emit('modal-close')" type="reset">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from '../../http.js'
    import MultiSelect from 'admin/components/select/MultiSelect.vue';
    export default {
        name: "CustomerModal",
        components: {
            MultiSelect,
        },
        props: {
            customer: {
                type: Object,
                default: {}
            },
            countries: {
                type: Array,
                default: []
            },
            state: {
                type: Array,
                default: [],
            }
        },
        data() {
            return {
                customerFields: {
                    id: null,
                    first_name: '',
                    last_name: '',
                    email: '',
                    mobile: '',
                    company: '',
                    phone: '',
                    website: '',
                    notes: '',
                    fax: '',
                    street_1: '',
                    street_2: '',
                    city: '',
                    country: '',
                    state: '',
                    postal_code: '',
                },
                states: [],
                showMore: false,
                customers:[],
            }
        },
        methods: {
            saveCustomer() {
                if ( !this.customer ) {
                    var url = 'customers';
                    var type = 'post';
                } else {
                    var url = 'customers/' + this.customerFields.id;
                    var type = 'put';
                }
                HTTP[type]( url, this.customerFields ).then( response => {
                    this.$parent.fetchItems();
                    this.$parent.showModal = false;
                    this.resetForm();
                } );
            },
            checkForm() {

            },

            showDetails() {
                this.showMore = !this.showMore;
            },

            getState( country ) {
                let states = this.state;
                this.states = [];
                this.customerFields.state = '';
                for ( let state in country.state ) {
                    this.states.push({ id: state, name: country.state[state] });
                }
            },

            isEmailExist() {
                var customer;
                customer = this.customers.filter( item => {
                    return this.customerFields.email = item.email;
                } );
                if ( customer.length ) {
                   return true;
                }
                return false;
            },

            getCustomers() {
                HTTP.get( 'customers' ).then( response => {
                    this.customers = response.data;
                } );
            },

            setInputField() {
                if ( this.customer) {
                    let customer = this.customer;
                    this.customerFields.id = customer.id;
                    this.customerFields.first_name = customer.first_name;
                    this.customerFields.last_name = customer.last_name;
                    this.customerFields.email = customer.email;
                    this.customerFields.mobile = customer.mobile;
                    this.customerFields.company = customer.company;
                    this.customerFields.phone = customer.phone;
                    this.customerFields.website = customer.website;
                    this.customerFields.notes = customer.notes;
                    this.customerFields.fax = customer.fax;
                    this.customerFields.street_1 = customer.billing.street_1;
                    this.customerFields.street_2 = customer.billing.street_2;
                    this.customerFields.city = customer.billing.city;
                    this.customerFields.country = this.selectedCountry( customer.billing.country );
                    this.customerFields.state = this.selectedState(customer.billing.state );
                    this.customerFields.postal_code = customer.billing.postal_code;
                }
            },

            selectedCountry( id ) {
                return this.countries.find( country => id === country.id );
            },

            selectedState( id ) {
                return this.state.find( item => item.id == id );
            },

            resetForm() {
                this.customerFields.first_name = '';
                this.customerFields.last_name = '';
                this.customerFields.email = '';
                this.customerFields.mobile = '';
                this.customerFields.company = '';
                this.customerFields.phone = '';
                this.customerFields.website = '';
                this.customerFields.note = '';
                this.customerFields.fax = '';
                this.customerFields.street1 = '';
                this.customerFields.street2 = '';
                this.customerFields.city = '';
                this.customerFields.country = '';
                this.customerFields.state = '';
                this.customerFields.post_code = '';
            }
        },
        created() {
            this.selectedCountry();
            this.setInputField();
            this.getCustomers();
        }
    }
</script>