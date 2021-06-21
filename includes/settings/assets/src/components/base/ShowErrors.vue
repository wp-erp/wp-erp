<template>
    <div v-if="error_msgs.length" class="notify bg-red bigger">
        <div class="message">
            <p><strong>{{ __('Please complete these fields', 'erp') }}:</strong></p>
            <ul :key="idx" v-for="(error, idx) in error_msgs">
                <li>* {{ error }}</li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ShowErrors',

    props: {
        error_msgs: Array
    }
};
</script>

<style scoped>
    .notify {
        margin-bottom: 20px;
        width: 914px;
    }
    div.bg-red {
        border-color: #e70101;
        background-color: #fff;
    }
    div.bg-red em {
        background-position: 0 -132px;
    }
    div.notify {
        position: relative;
        padding: 10px;
        max-width: 906px;
        border-width: 2px;
        border-style: solid;
        margin-bottom: 15px;
        box-shadow: 1px 1px 3px #ddd;
        border-radius: 5px;
    }
    li {
        color: #e70101;
    }
</style>
