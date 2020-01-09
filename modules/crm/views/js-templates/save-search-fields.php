<div class="or-divider" v-if="index != 0">
    <hr>
    <span><?php esc_attr_e( 'Or', 'erp' ); ?></span>
</div>

<div class="search-fields" v-if="searchFields" v-bind:class="marginClass">
    <table class="search-fields-table">
        <tbody class="search-fields-table-tbody" v-for="( searchKey, searchVal ) in searchFields" track-by="$index">
            <tr v-for="( searchFieldKey, searchField ) in searchVal" track-by="$index">
                <td v-if="searchFieldKey == 0">{{ searchField.title }}</td>
                <td v-if="searchFieldKey == 0">
                    <select name="save_search[{{index}}][{{searchKey}}][condition]" id="" v-bind:value="searchField.condval" v-model="searchField.condval">
                        <option v-for="( conditionKey, condition ) in searchField.condition" value="{{conditionKey}}">{{ condition }}</option>
                    </select>
                </td>
                <td colspan="2" align="right" v-if="searchFieldKey != 0">Or</td>
                <td>
                    <input type="text" v-if="searchField.type == 'text'" name="save_search[{{index}}][{{searchKey}}][value][]" v-bind:value="searchField.text" v-model="searchField.text">

                    <select class="selecttwo select2" data-searchkey="{{{ searchKey }}}" data-searchkeyindex="{{searchFieldKey}}" style="width:240px;" v-if="searchField.type == 'dropdown'" name="save_search[{{index}}][{{searchKey}}][value][]" v-bind:value="searchField.text" v-model="searchField.text" data-placeholder="<?php esc_attr_e( '--Select--', 'erp' ); ?>">
                        {{{ searchField.options }}}
                    </select>
                </td>
                <td><a href="#" v-on:click.prevent="removeSearchField( searchVal, searchField )" class="remove-field"><span class="dashicons dashicons-dismiss"></span></a></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="erp-save-search-filter-actions">
    <div class="and-action erp-left">
        <label for="or-action-add" v-if="!isdisabled"><?php esc_attr_e( 'And', 'erp' ); ?></label>
        <select name="and-action-add" class="and-action-add" style="width: 180px;" id="and-action-add" v-model="andSelection" v-on:change="andAdd(index)" data-placeholder="<?php esc_attr_e( 'Select a field', 'erp' ); ?>">
            <option value=""><?php esc_attr_e( '--Select--', 'erp' ); ?></option>
            <option v-for="( key, searchOption ) in searchOptions" value="{{key}}">{{ searchOption.title }}</option>
        </select>
    </div>
    <div class="or-action erp-right" v-if="!isdisabled && (totalSearchItem-1) == index ">
        <label for="or-action-add"><?php esc_attr_e( 'Or', 'erp' ); ?></label>
        <select name="or-action-add" class="or-action-add" style="width: 180px;" id="or-action-add" v-model="orSelection" v-on:change="orAdd(index)"  data-placeholder="<?php esc_attr_e( 'Select a field', 'erp' ); ?>">
            <option value=""><?php esc_attr_e( '--Select--', 'erp' ); ?></option>
            <option v-for="( key, searchOption ) in searchOptions" value="{{key}}">{{ searchOption.title }}</option>
        </select>
    </div>
    <div class="clearfix"></div>
</div>
