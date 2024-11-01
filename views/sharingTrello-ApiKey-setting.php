<div class="wrap">
    <h2>システム設定　Trello連携 API KEY</h2>

    <form method="post" action="options.php">

        <?php settings_fields('sharingTrello_ApiKey_settings'); ?>
        <?php do_settings_sections('sharingTrello_ApiKey_settings'); ?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="sharingTrello_key">Trello Key</label>
                    </th>
                    <td><input type="text" id="sharingTrello_key" class="regular-text" name="sharingTrello_key" value="<?= get_option('sharingTrello_key'); ?>"></td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sharingTrello_token">Trello Token</label>
                    </th>
                    <td><input type="text" id="sharingTrello_token" class="regular-text" name="sharingTrello_token" value="<?= get_option('sharingTrello_token'); ?>"></td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>

    </form>


</div>