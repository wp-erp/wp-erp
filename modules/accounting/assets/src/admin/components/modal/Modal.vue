<template>
    <div class="modal-dialog">
        <div class="modal">
            <div class="modal-content">
                <section :class="['modal-main', { 'has-footer': footer }]">
                    <header class="modal-header">
                        <slot name="header">
                            <h1>{{ title }}</h1>
                        </slot>

                        <button class="modal-close modal-close-link dashicons dashicons-no-alt"
                                @click="$emit('close')">
                            <span class="screen-reader-text">{{ 'Close modal panel' }}</span>
                        </button>
                    </header>
                    <div class="modal-body">
                        <slot name="body"/>
                    </div>
                    <footer v-if="footer" class="modal-footer">
                        <div class="inner">
                            <slot name="footer"/>
                        </div>
                    </footer>
                </section>
            </div>
        </div>
        <div class="modal-backdrop"/>
    </div>
</template>

<script>
export default {

    name: 'Modal',

    props: {
        footer: {
            type: Boolean,
            required: false,
            default: true,
        },

        title: {
            type: String,
            required: true,
            default: '',
        },
    },

    data() {
        return {};
    },
};
</script>

<style lang="less">

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    min-height: 360px;
    background: #000;
    opacity: .7;
    z-index: 99900;
}

.modal {

    * {
        box-sizing: border-box;
    }

    .modal-content {
        position: fixed;
        background: #fff;
        z-index: 100000;
        left: 50%;
        top: 50%;
        -webkit-transform: translate(-50%,-50%);
        -ms-transform: translate(-50%,-50%);
        transform: translate(-50%,-50%);
        width: 600px;
        border: 1px solid #707070;
        border-radius: 3px;
    }

    .modal-main.has-footer {
        padding-bottom: 55px;
    }

    header.modal-header {
        height: auto;
        padding: 14px 40px;

        h1 {
            padding: 0;
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.5em
        }

        .modal-close-link {
            cursor: pointer;
            color: #777;
            height: 54px;
            width: 54px;
            padding: 0;
            position: absolute;
            top: 0;
            right: 0;
            text-align: center;
            border: 0;
            background-color: transparent;
            -webkit-transition: color .1s ease-in-out,background .1s ease-in-out;
            transition: color .1s ease-in-out,background .1s ease-in-out;

            &::before {
                font: normal 22px/50px dashicons!important;
                color: #666;
                display: block;
                content: '\f335';
                font-weight: 300;
            }

            &:hover {
                background: #ddd;
                border-color: #ccc;
                color: #000;
            }
        }
    }

    .modal-body {
        min-height: 100px;
        padding: 10px 40px;
        overflow-y: scroll;
    }

    footer {
        position: absolute;
        left: 0;
        margin-left: 20px;
        z-index: 100;
        padding: 1em 1.5em;

        .inner {
            text-align: right;
            line-height: 23px;
        }
    }
}

@media only screen and (max-width: 500px) {
    .modal-content {
        width: 400px !important;
        top: 300px !important;
    }
}

@media only screen and (max-width: 376px) {
    .modal-content {
        width: 350px !important;
        top: 300px !important;
    }
}

@media only screen and (max-width: 320px) {
    .modal-content {
        width: 300px !important;
        top: 300px !important;
    }
}

</style>
