<template>
    <div class="settings-submenu-navbar" v-if="Object.keys(menus).length > 0">
        <ul class="settings-sub-menu">
            <template v-for="(menu, key, index) in menus">
                <li :key="key" v-if="index < dropdownMenuStartPos">
                    <router-link tag="li" :to="`/${parent_id}/${key}`" :class="activeRouteClass(index)">
                        <a href="#"><span class="menu-name">{{ menu }}</span></a>
                    </router-link>
                </li>
            </template>

            <dropdown v-if="dropdownMenuStartPos > 0 && Object.keys(menus).length > 5">
                <template slot="button">
                    <a href="#"> {{ __("More", "erp") }} &nbsp; <i class="fa fa-chevron-down"></i></a>
                </template>

                <template slot="dropdown">
                    <ul role="menu">
                        <template v-for="(menu, key, index) in menus">
                            <li v-if="index >= dropdownMenuStartPos" :key="index" class="dropdown-list-item">
                                <router-link :to="`/${parent_id}/${key}`">
                                    <span class="menu-name">{{ menu }}</span>
                                </router-link>
                            </li>
                        </template>
                    </ul>
                </template>

            </dropdown>
        </ul>
    </div>
</template>

<script>
import Dropdown from "../base/Dropdown.vue";

export default {
    name: "SettingsSubMenu",

    props: {
        parent_id: {
            type    : String,
            required: true
        },
        menus: {
            type    : Object,
            required: false
        }
    },

    data() {
        return {
            dropdownMenuStartPos: 5,
            dropdownMenuEndPos  : 5,
        }
    },

    created() {
        if ( this.menus.length > 5 ) {
            this.dropdownMenuStartPos = 5;
            this.dropdownMenuEndPos   = this.menus.length;
        }
    },

    components: {
        Dropdown,
    },

    methods: {
        activeRouteClass( index ) {
            const currentRouteName = this.$route.name;
            let routeClassName     = '';

            switch ( currentRouteName ) {

                case 'HRWorkDays' :
                case 'AcCustomer' :
                case 'CrmContacts':
                case 'GeneralEmail':
                case 'WCSynchronization':
                case 'WCOrderSync':
                    routeClassName = 'router-link-active';
                    break;

                default:
                    break;
            }

            if ( routeClassName.length === 0 || index !== 0 ) {
                routeClassName = '';
            }

            return routeClassName;
        }
    }
};
</script>
