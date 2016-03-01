<?php $search_keys = erp_crm_get_serach_key(); ?>

<div class="or-divider" v-if="index != 0">
    <hr>
    <span><?php _e( 'Or', 'wp-erp' ); ?></span>
</div>

<div class="search-fields" v-if="searchFields">
    <table>
        <tbody class="" v-for="( searchKey, searchVal ) in searchFields" track-by="$index">
            <tr v-for="( searchFieldKey, searchField ) in searchVal" track-by="$index">
                <td><a href="#" v-on:click.prevent="removeSearchField( searchVal, searchField )" class="remove-field button">&times;</a></td>
                <td v-if="searchFieldKey == 0">{{ searchField.title }}</td>
                <td v-if="searchFieldKey == 0">
                    <select name="save_search[{{index}}][{{searchKey}}][condition]" id="" v-bind:value="searchField.condval" v-model="searchField.condval">
                        <option v-for="( conditionKey, condition ) in searchField.condition" value="{{conditionKey}}">{{ condition }}</option>
                    </select>
                </td>
                <td colspan="2" align="right" v-if="searchFieldKey != 0">Or</td>
                <td>
                    <input type="text" v-if="searchField.type == 'text'" name="save_search[{{index}}][{{searchKey}}][value][]" v-bind:value="searchField.text" v-model="searchField.text">
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="erp-save-search-action">
    <div class="and-action erp-left">
        <label for="or-action-add"><?php _e( 'And', 'wp-erp' ); ?></label>
        <select name="and-action-add" class="and-action-add" id="and-action-add" v-model="andSelection" v-on:change="andAdd(index)">
            <option value=""><?php _e( '--Select--', 'wp-erp' ); ?></option>
            <?php foreach ( $search_keys as $key => $search_key ) : ?>
                <option value="<?php echo $key ?>"><?php echo $search_key['title']; ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <div class="or-action erp-right" v-if="( totalSearchItem-1 ) == index">
        <label for="or-action-add"><?php _e( 'Or', 'wp-erp' ); ?></label>
        <select name="or-action-add" id="or-action-add" v-model="orSelection" v-on:change="orAdd(index)" :disabled="isdisabled">
            <option value=""><?php _e( '--Select--', 'wp-erp' ); ?></option>
            <?php foreach ( $search_keys as $key => $search_key ) : ?>
                <option value="<?php echo $key ?>"><?php echo $search_key['title']; ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="clearfix"></div>
</div>
