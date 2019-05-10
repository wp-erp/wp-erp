<template>
    <div class="tax-section">
        <div class="sectionHeader">Tax Shortcuts</div>
        <ul>
            <li><router-link :to="{ name: 'TaxZones' }">View Tax Zones</router-link></li>
            <li><router-link :to="{ name: 'TaxCategories' }">View Tax Categories</router-link></li>
            <li><router-link :to="{ name: 'TaxAgencies' }">View Tax Agencies</router-link></li>
            <li><a class="link" id="learnMoreLink" href="#">Tax Help</a></li>
        </ul>
    </div>
</template>

<script>
    export default {
        name: "TaxShortcuts"
    }
</script>

<style scoped>
    .sectionHeader {
        font-weight: bold;
        margin-bottom: 10px;
        font-family: "Trebuchet MS";
    }
    .tax-section {
        width: 240px;
        border: solid 1px #d9eaf9;
        background-color: #edf4fb;
        padding: 20px 25px;
        border-radius: 4px;
        -moz-border-radius: 4px;
        -webkit-border-radius: 4px;
        margin-bottom: 30px;
        margin-top: 40px;
    }
</style>
