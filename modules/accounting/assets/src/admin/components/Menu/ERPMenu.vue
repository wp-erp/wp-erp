<template>
    <div class="erp-nav-container">
        <div class="erp-page-header">
            <div class="module-icon">
            </div>
            <h2>Accounting</h2>
        </div>
        <ul class="erp-nav -primary" >
            <template v-for="(menu, index) in menuItems">
                <li v-if="menu.hasOwnProperty('submenu')" class="dropdown-nav" >
                     <a href="#">{{menu.title}}</a>
                    <ul class="erp-nav-dropdown">
                        <li v-for="item in menu.submenu">
                            <a href="#">{{item.title}}</a>
                        </li>
                    </ul>
                </li>
                <template v-else>
                    <li><a href="#">{{menu.title}}</a></li>
                </template>
            </template>
        </ul>
    </div>
</template>

<script>
    export default {
        name: 'ERPMenu',

        props: {

        },
        data() {
            return {
                menuItems: erp_acct_var.erp_acct_menus
            }
        },
        created: function(){
            this.init();
        },

        methods: {
            init : function () {

                const container = document.querySelector('.erp-nav-container');


                if ( container == null ) {
                    return;
                }
                primary = container.querySelector('.-primary');


                primaryItems = container.querySelectorAll('.-primary > li:not(.-more)');
                container.classList.add('--jsfied');

                // insert "more" button and duplicate the list
                primary.insertAdjacentHTML('beforeend', '<li class="-more"><button type="button" aria-haspopup="true" aria-expanded="false">More <span class="dashicons dashicons-arrow-down-alt2"></span></button><ul class="-secondary">' + primary.innerHTML + '</ul></li>');
                secondary = container.querySelector('.-secondary');
                secondaryItems = [].slice.call(secondary.children);
                allItems = container.querySelectorAll('li');
                moreLi = primary.querySelector('.-more');
                moreBtn = moreLi.querySelector('button');
                moreBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    container.classList.toggle('--show-secondary');
                    moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'));
                });

                // adapt tabs
                var doAdapt = function doAdapt() {
                    // reveal all items for the calculation
                    allItems.forEach(function (item) {
                        item.classList.remove('--hidden');
                    });

                    // hide items that won't fit in the Primary
                    stopWidth = moreBtn.offsetWidth;
                    hiddenItems = [];
                    primaryWidth = primary.offsetWidth;
                    primaryItems.forEach(function (item, i) {
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
                        secondaryItems.forEach(function (item, i) {
                            if (!hiddenItems.includes(i)) {
                                item.classList.add('--hidden');
                            }
                        });
                    }
                };

                doAdapt(); // adapt immediately on load
                window.addEventListener('resize', doAdapt); // adapt on window resize

                // hide Secondary on the outside click
                document.addEventListener('click', function (e) {
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
            },
        },
    }
</script>

<style lang="less">

    @border-color: #E5E5E5;
    @wperp-theme-color: #1A9ED4;

    div.erp-nav-container {
        background-color : #fff;
        margin-left : -20px;
        padding-left : 20px;
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
                margin-top: 8px;
                svg {
                    height: 15px;
                }
            }
            h2 {
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
                    color : @wperp-theme-color;
                }
            }
            &:hover {
                > a {
                    color : @wperp-theme-color;
                }
            }
            a {
                position: relative;
                text-decoration: none;
                font-size: 14px;
                line-height: 1.5em;
                font-weight: 400;
                display: block;
                color : #000000;

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
            background: #FFFFFF;
            z-index: 99;
            font-size: 12px;
            border: 1px solid #DDDDDD;
            border-radius: 3px;
            box-shadow: 0 4px 10px 0 rgba(0,0,0,0.09);
            transition: all .2s;
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
            left: 0;
            z-index: 99;
            animation: nav-secondary 0.2s;
            background: #FFFFFF;
            font-size: 12px;
            border: 1px solid #DDDDDD;
            border-radius: 3px;
            box-shadow: 0 4px 10px 0 rgba(0,0,0,0.09);
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
