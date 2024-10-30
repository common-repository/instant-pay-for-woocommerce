<tr valign="top">
    <th scope="row" class="titledesc"> 
        <label for="woocommerce_auspost_debug_mode"><?php _e('HTTP Headers', $this->domain); ?></label>
    </th>
    <td class="forminp">
        <fieldset>
            <table id="customized_payment_api_request_headers" class="wp-list-table widefat fixed striped posts" style="width:60%">
                <thead>
                    <tr>
                        <th class="column-key"  style="width: 40px; padding: 8px 0 8px 10px;"><?php _e('Key', $this->domain); ?></th>
                        <th  style="width: 100px; padding: 8px 0 8px 10px;"><?php _e('Value', $this->domain); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(empty($this->api_request_headers)): ?>
                        <tr>
                            <td><input name="header_keys[]" class="widefat" type="text" style="width: 100%;"></td>
                            <td><input name="header_values[]" class="widefat" type="text" style="width: 100%;"></td>
                        </tr>
                    <?php endif;  ?>
                    <?php 
                    if(isset($this->api_request_headers) and is_array($this->api_request_headers)): 
                        foreach($this->api_request_headers as $key => $value): ?>
                            <tr>
                                <td><input name="header_keys[]" class="widefat" value="<?php echo esc_attr($key); ?>" type="text"  style="width: 100%;"></td>
                                <td><input name="header_values[]" class="widefat" value="<?php echo esc_attr($value); ?>" type="text" style="width: 100%;"></td>
                            </tr>
                            <?php 
                        endforeach; 
                    endif; 
                    ?>
                </tbody>
            </table>
        </fieldset>
    </td>
</tr>
