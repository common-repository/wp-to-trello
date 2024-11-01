<?php
  $sharingTrello_post_type = "sharingTrello_post";
	$board_id = get_option('sharingTrello_board_id_sentaku');
	delete_option('sharingTrello_board_id_sentaku');
	$trello = new SharingTrelloPlugin();
	$boards = $trello->getBoardList();
if (empty($board_id)){
  $board_id = get_option("{$sharingTrello_post_type}_board_id");
}
if (empty($board_id)){
  $board_id = $boards[0]["id"];
}
$board_lists = $trello->getListInBoard($board_id);
  $label_lists = $trello->getLabelsInBoard($board_id);

	$arr_option = array();
	$arr_option[$sharingTrello_post_type.'_board_list_id']= get_option($sharingTrello_post_type.'_board_list_id'); 
	$arr_option[$sharingTrello_post_type.'_label_list_id']= get_option($sharingTrello_post_type.'_label_list_id'); 
?>


<div class="wrap">
    <h2>システム設定　Trello連携 投稿時</h2>
    <?
    // actionは必ずoption.phpにする
    ?>
    <form method="post" action="options.php">
        <?php settings_fields('sharingTrello_bordselect'); ?>
        <?php do_settings_sections('sharingTrello_bordselect'); ?>


        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="sharingTrello_board_id">カードを作成するボード</label>
                    </th>
                    <td>
                        <select id="sharingTrello_board_id_sentaku" class="sharingTrello_board_id_sentaku" name="sharingTrello_board_id_sentaku">
                            <?php
                            foreach ($boards as $board) {
                              $trello->_log($board);
                              if ($board['id'] == $board_id) {
                                echo "<option value={$board['id']} selected>{$board['name']}</option>";
                              } else {
                                echo "<option value={$board['id']}>{$board['name']}</option>";
                              }
                            }
                            ?>
                        </select>
				<input type="submit" name="Submit" id="Submit" class="button" value="選択"  />
                    </td>
                </tr>
            </tbody>
        </table>
    </form>


    <form method="post" action="options.php">
				<?php settings_fields('sharingTrello_settings'); ?>
				<?php do_settings_sections('sharingTrello_settings'); ?>
				<input type="hidden" id="<?php echo($sharingTrello_post_type);?>_board_id" name="<?php echo($sharingTrello_post_type);?>_board_id" value="<?php echo $board_id; ?>" />
      <table class="form-table">
            <tbody>
                <tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo($sharingTrello_post_type);?>_board_list_id">投稿時にカードを作成するリスト</label>
                    </th>
                    <td>
                        <select id="<?php echo($sharingTrello_post_type);?>_board_list_id" class="<?php echo($sharingTrello_post_type);?>_board_list_id" name="<?php echo($sharingTrello_post_type);?>_board_list_id">
                            <?php
                            foreach ($board_lists as $board_list) {
                              if ($board_list['id'] == $arr_option["{$sharingTrello_post_type}_board_list_id"]) {
                                echo "<option value={$board_list['id']} selected>{$board_list['name']}</option>";
                              } else {
                                echo "<option value={$board_list['id']}>{$board_list['name']}</option>";
                              }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php echo($sharingTrello_post_type);?>_label_list_id">投稿時のラベル</label>
                    </th>
                    <td>
                        <select id="<?php echo($sharingTrello_post_type);?>_label_list_id" class="<?php echo($sharingTrello_post_type);?>_label_list_id" name="<?php echo($sharingTrello_post_type);?>_label_list_id">
                            <?php
                              echo "<option value=''></option>";
                              foreach ($label_lists as $label_list) {
                              if ($label_list['id'] == $arr_option["{$sharingTrello_post_type}_label_list_id"]) {
                                echo "<option value={$label_list['id']} selected>{$label_list['color']}:{$label_list['name']}</option>";
                              } else {
                                echo "<option value={$label_list['id']}>{$label_list['color']}:{$label_list['name']}</option>";
                              }
                            }
                            ?>
                        </select>
                    </td>
                </tr>

            </tbody>
        </table>
        <?php submit_button(); ?>

    </form>


</div>