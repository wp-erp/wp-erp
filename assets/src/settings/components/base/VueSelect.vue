<template>
    <multiselect :value="value"
        id="ajax"
        track-by="id"
        label="name"
        :placeholder="__('Type to search', 'erp')"
        :options="options"
        :searchable="true"
        :loading="isLoading"
        :internal-search="false"
        :hide-selected="true"
        :show-labels="false"
        @search-change="asyncFind"
        @select="onSelect"
    >

        <span slot="noResult" v-if="noResult">{{ __('Oops! No elements found.', 'erp') }}</span>
    </multiselect>
</template>

<script>
    import {HTTP}      from '../http'
    import Multiselect from 'vue-multiselect'
    import debounce    from 'lodash/debounce';

    export default {

        name: 'VueSelect',

        props: ['value', 'url', 'type'],

        components: {
            Multiselect
        },

        data() {
            return {
                noResult: false,
                options: [],
                isLoading: false
            };
        },

        methods: {

            asyncFind: debounce(function ( query ) {
                this.options = [];
                this.isLoading = true;

                this.getSearchedData( query );
            }, 300),

            getSearchedData( query ) {
                HTTP.get( this.url, {
                    params: {
                        s: query
                    }
                } ).then(res => {
                    this.isLoading = false;

                    if ( ! res.data.length ) {
                        this.noResult = true;

                        return;
                    }

                    res.data.forEach( value => {
                        this.options.push( {id: value.id, name: value.title } );
                    } );

                } );
            },

            onSelect(selected) {
                this.$emit('input', selected);
            }
        }

    };
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
