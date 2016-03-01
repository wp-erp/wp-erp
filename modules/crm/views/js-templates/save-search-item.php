<# if ( ! data.or ) { #>
    <tbody class="erp-crm-{{data.id}}">
        <tr>
            <td><a href="#" class="button">X</a></td>
            <td>{{ data.content.title }}</td>
            <td>
                <select name="cond_{{data.id}}[{{data.count}}]" id="">
                    <# _.each( data.content.condition, function( el, i ) { #>
                        <option value="{{ i }}">{{ el }}</option>
                    <# } ) #>
                </select>
            </td>
            <td>
                <input type="text" name="{{data.id}}[{{data.count}}][]">
            </td>
        </tr>
    </tbody>
<# } #>

<# if ( data.or ) { #>
    <?php $search_keys = erp_crm_get_serach_key(); ?>
    <tbody data-count="{{data.count}}">
        <tr>
            <td>
              <div class="">&nbsp;<hr></div>
              <div class="">Or</div>
              <div class="">&nbsp;<hr></div>
            </td>
        </tr>
        <tr>
            <td>
                <table>
                    <tbody class="erp-crm-{{data.id}}">
                        <tr>
                            <td><a href="#" class="button">X</a></td>
                            <td>{{ data.content.title }}</td>
                            <td>
                                <select name="cond_{{data.id}}[{{data.count}}]" id="">
                                    <# _.each( data.content.condition, function( el, i ) { #>
                                        <option value="{{ i }}">{{ el }}</option>
                                    <# } ) #>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="{{data.id}}[{{data.count}}][]">
                            </td>
                        </tr>
                    </tbody>
                    <tbody>
                        <tr class="actions">
                            <td class="and" colspan="2">
                                &nbsp;<label for="add_filter_1">And</label>&nbsp;
                                <select name="add_filter_1" class="add_filter_1">
                                    <option></option>
                                    <?php
                                        foreach ( $search_keys as $key => $search_key ) {
                                            ?>
                                                <option value="<?php echo $key; ?>"><?php echo $search_key['title']; ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>

                            </td>
                            <td class="or" colspan="2">
                                <label for="add_clause">Or</label>&nbsp;
                                <select name="add_clause_2" class="erp-crm-add-or-filter">
                                  <option></option>
                                    <?php
                                        foreach ( $search_keys as $key => $search_key ) {
                                            ?>
                                                <option value="<?php echo $key; ?>"><?php echo $search_key['title']; ?></option>
                                            <?php
                                        }
                                    ?>
                                </select>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>

<# } #>

