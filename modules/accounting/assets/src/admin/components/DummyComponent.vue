<template>
    <h1>Page: {{ title }}</h1>
</template>
<script>
    export default {
        name: 'DummyComponent',

        data() {
            return {
                title: this.$route.name
            }
        }
    }
</script>


<style>

</style>
