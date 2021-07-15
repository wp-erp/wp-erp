<template>
    <div class="erp-nav-container">
        <div class="erp-page-header">
            <div class="module-icon">
                <svg
                    id="Group_235"
                    data-name="Group 235"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 239 341.4"
                >
                    <path id="Path_281" data-name="Path 281" class="cls-1" :d="svgData"></path>
                </svg>
            </div>
            <p class="page-title">{{ module_name }}</p>
        </div>
        <ul :class="primaryNav">
            <template v-for="(menu, index) in menuItems">
                <li :key="index" v-if="menu.hasOwnProperty('submenu')" :class="dropdownNav" :id="`erp-act-menu-${menu.slug}`">
                    <router-link :to="'/' + menu.slug">{{ menu.title }}</router-link>

                    <ul :class="dropDownClass">
                        <li :key="index" v-for="(item, index) in menu.submenu">
                            <router-link :to="'/' + item.slug">{{ item.title }}</router-link>
                        </li>
                    </ul>
                </li>
                <li :key="index" v-else>
                    <router-link :to="'/' + menu.slug">{{ menu.title }}</router-link>
                </li>
            </template>
        </ul>
    </div>
</template>

<script>
export default {
    name: 'AccountingMenu',

    props: {},
    data() {
        /* global __ */
        return {
            menuItems: erp_acct_var.erp_acct_menus, /* global erp_acct_var */
            dropDownClass: 'erp-nav-dropdown',
            primaryNav: 'erp-nav -primary',
            dropdownNav: 'dropdown-nav',
            module_name: __('Accounting', 'erp'),
            svgData: 'M221.9,0H17.1C6.8,0,0,6.8,0,17.1V324.3c0,10.2,6.8,17.1,17.1,17.1H221.9c10.2,0,17.1-6.8,17.1-17.1V17.1C238.9,6.8,232.1,0,221.9,0ZM68.3,307.2H34.1V273.1H68.2v34.1Zm0-68.3H34.1V204.8H68.2v34.1Zm0-68.2H34.1V136.6H68.2v34.1Zm68.2,136.5H102.4V273.1h34.1Zm0-68.3H102.4V204.8h34.1Zm0-68.2H102.4V136.6h34.1Zm68.3,136.5H170.7V273.1h34.1v34.1Zm0-68.3H170.7V204.8h34.1v34.1Zm0-68.2H170.7V136.6h34.1v34.1Zm0-68.3H34.1V34.1H204.8v68.3Zm0,0',
            current_url: erp_acct_var.erp_acct_url,
            activeClass:[]
        };
    },

    created() {
        this.init();
    },

    methods: {
        init() {
            const container = document.querySelector('.erp-nav-container');
            if (container == null) {
                return;
            }
            const primary = container.querySelector('.-primary');

            const primaryItems = container.querySelectorAll('.-primary > li:not(.-more)');
            container.classList.add('--jsfied');

            // insert "more" button and duplicate the list
            primary.insertAdjacentHTML(
                'beforeend',
                '<li class="-more"><button type="button" aria-haspopup="true" aria-expanded="false">More <span class="dashicons dashicons-arrow-down-alt2"></span></button><ul class="-secondary">' +
                    primary.innerHTML +
                    '</ul></li>'
            );
            const secondary = container.querySelector('.-secondary');
            const secondaryItems = [].slice.call(secondary.children);
            const allItems = container.querySelectorAll('li');
            const moreLi = primary.querySelector('.-more');
            const moreBtn = moreLi.querySelector('button');
            moreBtn.addEventListener('click', function(e) {
                e.preventDefault();
                container.classList.toggle('--show-secondary');
                moreBtn.setAttribute(
                    'aria-expanded',
                    container.classList.contains('--show-secondary')
                );
            });

            // adapt tabs
            var doAdapt = function doAdapt() {
                // reveal all items for the calculation
                allItems.forEach(function(item) {
                    item.classList.remove('--hidden');
                });

                // hide items that won't fit in the Primary
                var stopWidth = moreBtn.offsetWidth;
                const hiddenItems = [];
                const primaryWidth = primary.offsetWidth;
                primaryItems.forEach(function(item, i) {
                    if (primaryWidth >= stopWidth + item.offsetWidth) {
                        stopWidth += item.offsetWidth;
                    } else {
                        item.classList.add('--hidden');
                        hiddenItems.push(i);
                    }
                });

                // toggle the visibility of More button and items in Secondary
                if (!hiddenItems.length) {
                    moreLi.classList.add('--hidden');
                    container.classList.remove('--show-secondary');
                    moreBtn.setAttribute('aria-expanded', false);
                } else {
                    secondaryItems.forEach(function(item, i) {
                        if (!hiddenItems.includes(i)) {
                            item.classList.add('--hidden');
                        }
                    });
                }
            };

            doAdapt(); // adapt immediately on load
            window.addEventListener('resize', doAdapt); // adapt on window resize

            // hide Secondary on the outside click
            document.addEventListener('click', function(e) {
                var el = e.target;
                while (el) {
                    if (el === secondary || el === moreBtn) {
                        return;
                    }
                    el = el.parentNode;
                }
                container.classList.remove('--show-secondary');
                moreBtn.setAttribute('aria-expanded', false);
            });
        }
    }
};
</script>

<style lang="less">
    @border-color: #e5e5e5;
    @wperp-theme-color: #1a9ed4;

    .cls-1 {
        fill: #9ca1a6;
        height: 15px;
    }

    div.erp-nav-container {
        background-color: #fff;
        margin: 0 -20px;
        padding-left: 20px;
        border: 1px solid @border-color;
        color: #000;
        position: relative;

        ul {
            padding: 0;
            margin: 0;
        }

        .erp-page-header {
            float: left;
            display: flex;
            align-items: center;
            //margin-right: 20px;
            .module-icon {
                margin-top: 0px;
                svg {
                    height: 15px;
                }
            }

            .page-title {
                font-size: 20px;
                line-height: 1.4em;
                font-weight: 700;
                margin: 0;
                padding: 9px;
            }
        }

        .erp-nav {
            > li {
                display: inline-block;
                margin: 0;
                //padding: 0 15px;
                border-bottom: 2px solid transparent;

                a {
                    line-height: 45px;
                    padding: 0 10px;
                }

                &.active {
                    border-bottom-color: @wperp-theme-color;
                }
            }
        }

        li {
            padding: 0;
            position: relative;

            &.dropdown-nav {
                @media screen and (max-width: 768px) {
                    & > a {
                        pointer-events: none;
                    }
                }

                > a:after {
                    content: "\f347";
                    display: inline-block;
                    font: 400 13px/1 dashicons;
                    padding-left: 5px;
                }

                &:hover,
                &.active {
                    i {
                        color: @wperp-theme-color;
                    }
                }

                &:hover {
                    .erp-nav-dropdown {
                        display: block;
                    }
                }

                i {
                    position: absolute;
                    right: -2px;
                    top: 50%;
                    color: inherit;
                    font-size: 13px;
                    line-height: 1.5em;
                    transform: translateY(-50%);
                }
            }
            &.active {
                > a {
                    color: @wperp-theme-color;
                }
            }
            &:hover {
                > a {
                    color: @wperp-theme-color;
                }
            }
            a {
                position: relative;
                text-decoration: none;
                font-size: 14px;
                line-height: 1.5em;
                font-weight: 400;
                display: block;
                color: #000000;

                &:focus {
                    outline: none;
                    box-shadow: none;
                }
            }
        }

        .erp-nav-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0px;
            background: #ffffff;
            z-index: 99;
            font-size: 12px;
            border: 1px solid #dddddd;
            border-radius: 3px;
            box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.09);
            transition: all 0.2s;
            padding: 5px 0;
            margin-top: 2px;
            min-width: 160px;

            li a:empty {
                display: none;
            }

            li {
                //padding: 3px 7px;
                margin: 0;

                a {
                    padding: 5px 10px;
                }
            }

            a {
                line-height: 1.5em !important;
            }
        }

        &:not(.--jsfied) {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        // shared
        .--hidden {
            display: none !important;
        }
        button {
            width: 100%;
            height: 100%;
            display: block;
            font-size: 1em;
            line-height: 1.2;
            background-color: transparent;
            border: none;
            color: @wperp-theme-color;
            cursor: pointer;

            &:focus {
                outline: none;
            }
        }
        // primary
        .-primary {
            display: flex;
            > li {
                //flex-grow: 1;
                > a,
                > button {
                    white-space: nowrap;
                }
            }
            .-more {
                margin: 13px 0;

                > button span {
                    display: inline-block;
                    transition: transform 0.2s;
                    width: auto;
                    height: auto;
                    font: 400 13px/1 dashicons;
                }
            }
        }
        &.--show-secondary .-primary {
            .-more > button span {
                transform: rotate(180deg);
            }
        }
        // secondary
        .-secondary {
            max-width: 100%;
            display: none;
            position: absolute;
            top: 100%;
            left: -70px;
            z-index: 99;
            animation: nav-secondary 0.2s;
            background: #ffffff;
            font-size: 12px;
            border: 1px solid #dddddd;
            border-radius: 3px;
            box-shadow: 0 4px 10px 0 rgba(0, 0, 0, 0.09);
            min-width: 140px;

            > li {
                //padding: 3px 15px;

                &.--hidden + li {
                    padding-top: 10px;
                }

                &:last-child {
                    padding-bottom: 8px;
                }

                &.dropdown-nav {
                    margin-right: 10px;
                }

                a {
                    line-height: 1.5em !important;
                }
            }

            .erp-nav-dropdown {
                top: 0;
                right: 100%;
                left: auto;

                li a:empty {
                    display: none;
                }
            }
        }
        &.--show-secondary .-secondary {
            display: block;
        }
    }

    // keyframes
    @keyframes nav-secondary {
        0% {
            opacity: 0;
            transform: translateY(-1em);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
