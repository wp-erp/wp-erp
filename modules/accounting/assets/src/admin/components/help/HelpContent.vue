<template>
    <div>
        <h2 class="view-doc">
            <span>{{ __('Accounting Help', 'erp') }}</span>
            <a href="" class="wperp-btn">{{ __('View all documentation', 'erp') }}</a>
        </h2>

        <div class="acoounting-docs-wrap">
            <div id="dashboard-widgets" class="metabox-holder">

                <div class="erp-help-section postbox-container" :key="index" v-for="(doc, index) in docs">
                    <div class="metabox-holder">
                        <div class="meta-box-sortables">
                            <div class="postbox">
                                <h2 class="hndle">{{ doc.title }}</h2>

                                <div class="erp-help-questions">
                                    <ul :key="queInx" v-for="(que, queInx) in doc.ques">
                                        <li><a :href="que.url" target="_blank">{{ que.label }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- .erp-help-section -->

            </div>
        </div>

    </div>
</template>

<script>

export default {
    name: 'HelpContent',

    data() {
        return {
            docs: [{
                title: window.__('General', 'erp'),
                ques: [
                    {
                        label: window.__('Hell in a cell?', 'erp'),
                        url: 'HTTP://THIS IS A link?'
                    }
                ]
            }]
        };
    }
};
</script>

<style lang="less">
    .view-doc {
        margin-top:15px;
        align-items: center;
        display: flex;

        span {
            font-size: 18px;
            font-weight: bold;
        }

        a {
            background: #1a9ed4;
            border-radius: 3px;
            color: #fff;
            font-size: 12px;
            height: 29px;
            line-height: 29px;
            margin-left: 13px;
            text-align: center;
            text-decoration: none;
            width: 160px;
        }
    }

    .erp-help-questions li {
        margin: 0;
        border-bottom: 1px solid #eee;
    }

    .erp-help-questions li a {
        padding: 10px 15px;
        display: block;
    }

    .erp-help-questions li a:hover {
        background-color: #F5F5F5;
    }

    .erp-help-questions li:last-child {
        border-bottom: none;
    }

    .erp-help-questions li .dashicons {
        float: right;
        color: #ccc;
        margin-top: -3px;
    }

    @media screen and (min-width: 960px) {
        .erp-help-section .postbox-container{
            width: 100% !important;
        }

        .erp-help-section:nth-child(odd){
            clear:both !important;
        }

    }
</style>
