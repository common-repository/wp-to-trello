<?php
/*
Plugin Name: WP To Trello
Plugin URI: (https://www.tegos.co.jp/wp_to_trello)
Description: This plugin integrates wordpress and Trello.When you post an article to wordpress, this plugin will create a card in Trello.
Version: 0.1
Author: TEGOS.K.K
Author URI: https://www.tegos.co.jp
License: GPL2
*/

// メニューに管理画面を追加
add_action('admin_menu', 'sharingTrello_settings_menu');

function sharingTrello_settings_menu() {

	  // ユーザーが必要な権限を持つか確認する必要がある
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }

  $page_title = 'Setting for WP To Trello';
  $menu_title = 'WP To Trello';
  $capability = 'administrator';//管理者のみ 'manage_options'
  $menu_slug = 'sharingTrello-settings';
  $submenu_slug_api = 'sharingTrello-apiKey-settings';
  $submenu_slug_post = 'sharingTrello-post-settings';
if (empty(get_option('sharingTrello_key'))) {
    $submenu_slug_api = $menu_slug;
    $menu_function = 'sharingTrello_apiKey_settings_page';
  }else{
    $submenu_slug_post = $menu_slug;
    $menu_function = 'sharingTrello_post_settings_page';
  }
  add_menu_page($page_title, $menu_title, $capability, $menu_slug, $menu_function);

  if (!empty(get_option('sharingTrello_key'))) {
    $submenu_page_title = 'WP To Trello POST Settings';
    $submenu_title = 'POST Settings';
    $submenu_slug = $submenu_slug_post;
    $submenu_function = 'sharingTrello_post_settings_page';
    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);  
  }

  $submenu_page_title = 'WP To Trello API KEY';
  $submenu_title = 'API KEY';
  $submenu_slug = $submenu_slug_api;
  $submenu_function = 'sharingTrello_apiKey_settings_page';
  add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);



}
// Viewの設定 Trello連携 API KEY
function sharingTrello_apiKey_settings_page()
{
  include 'views/sharingTrello-ApiKey-setting.php';
}
// Viewの設定 Trello連携 投稿時
function sharingTrello_post_settings_page()
{
  include 'views/sharingTrello-post-setting.php';
}









// フィールドの作成
add_action('admin_init', 'register_sharingTrello_settings');

function register_sharingTrello_settings()
{
  $my_options = array( 'key', 'token');
  foreach ($my_options as $my_option) {
    register_setting('sharingTrello_ApiKey_settings', 'sharingTrello_' . $my_option);
  }
  $my_options = array( 'board_id_sentaku',
											);
  foreach ($my_options as $my_option) {
    register_setting('sharingTrello_bordselect', 'sharingTrello_' . $my_option);
  }

  $my_options = array('board_id',
											'board_list_id',
											'label_list_id',
											);
  foreach ($my_options as $my_option) {
    register_setting('sharingTrello_settings', 'sharingTrello_post_' . $my_option);
  }

}

/**
 * 投稿が保存されたとき投稿メタデータも保存する。
 *
 * @param int  $post_ID  投稿 ID。
 * @param post $post     投稿オブジェクト。
 * @param bool $update   既存投稿の更新か否か。
 */
add_action( 'publish_post', 'save_post_sharingTrello_add_card', 10, 2 );
function save_post_sharingTrello_add_card( $post_ID, $post ) {

  $trello = new SharingTrelloPlugin();

  $trelloinfo = $trello->sendToTrello_addCard( $post->post_title , wp_strip_all_tags($post->post_content,true) , $post->post_type );
  // 投稿メタデータを更新する。
  update_post_meta( $post_ID, 'trello_id', $trelloinfo["id"] );

}

final class SharingTrelloPlugin
{
  public function getBoardList()
  {
    $url = 'https://trello.com/1/members/me/boards';
    $response = wp_remote_post($url, array(
      'method' => 'GET',
      'httpversion' => '1.0',
      'body' => array(
        'key' => get_option('sharingTrello_key')
        , 'token' => get_option('sharingTrello_token')
        , 'fields' => 'name'
      ),
    ));

    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      $error_message = "Something went wrong: $error_message";
      $this->_log('error:' . 'function:' . __FUNCTION__ . 'line:' . __LINE__);
      $this->_log($error_message);
    } else {
      // Response body.
      $body = wp_remote_retrieve_body($response);
      $array_body = json_decode($body, true);
      //$this->_log( $array_body );
      return $array_body;
    }
  }

  public function getListInBoard($board_id)
  {
    if (empty($board_id)) {
      $this->_log('error:' . 'function:' . __FUNCTION__ . 'line:' . __LINE__);
      return false;
    }
    $url = "https://trello.com/1/boards/{$board_id}/lists";
    $response = wp_remote_post($url, array(
      'method' => 'GET',
      'httpversion' => '1.0',
      'body' => array(
        'key' => get_option('sharingTrello_key'), 'token' => get_option('sharingTrello_token'), 'fields' => 'name'
      ),
    ));

    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      $error_message = "Something went wrong: $error_message";
      //$this->_log($error_message);
    } else {
      // Response body.
      $body = wp_remote_retrieve_body($response);
      $array_body = json_decode($body, true);
      //$this->_log( $array_body );
      return $array_body;
    }
  }

  public function getLabelsInBoard($board_id)
  {
    if (empty($board_id)) {
      //$this->_log('error:' . 'function:' . __FUNCTION__ . 'line:' . __LINE__);
      return false;
    }
    $url = "https://trello.com/1/boards/{$board_id}";
    $response = wp_remote_post($url, array(
      'method' => 'GET',
      'httpversion' => '1.0',
      'body' => array(
        'key' => get_option('sharingTrello_key'),
        'token' => get_option('sharingTrello_token'),
        'labels' => 'all',
        'fields' => 'name'
      ),
    ));

    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      $error_message = "Something went wrong: $error_message";
      $this->_log($error_message);
    } else {
      // Response body.
      $body = wp_remote_retrieve_body($response);
      $array_body = json_decode($body, true);
      $this->_log( $array_body['labels'] );
      return $array_body['labels'];
    }
  }

  function _log($message)
  {
    if (WP_DEBUG === true) {
      if (is_array($message) || is_object($message)) {
        error_log(print_r($message, true));
      } else {
        error_log(__function__ . ':' . $message);
      }
    }
  }

  public function sendToTrello_addCard($post_title = null, $post_content = null, $post_type = "post")
  {
    if (empty($post_title) || empty($post_content)) {
      $this->_log('error');
      return false;
    }
    https://codex.wordpress.org/Function_Reference/wp_remote_post
    $url = 'https://trello.com/1/cards';
    $response = wp_remote_post($url, array(
      'method' => 'POST',
      'httpversion' => '1.0',
      'body' => array(
        'key' => get_option('sharingTrello_key') 
        , 'token' => get_option('sharingTrello_token')
        , 'idList' => $this->getListId_Contact($post_type)
        , 'name' => $post_title, 'desc' => $post_content
        , 'pos' => 'top'
        , 'idLabels' => $this->getlabelId_Contact($post_type)
      ),
    ));
    if (is_wp_error($response)) {
      return 1;
      $error_message = $response->get_error_message();
      $error_message = "Something went wrong: {$error_message}";
      $this->_log($error_message);
    } else {
      // Response body.
      $body = wp_remote_retrieve_body($response);
      $array_body = json_decode($body, true);
      // $this->_log($array_body);
      $url = $array_body['shortUrl'];
      $id = $array_body['id'];
      $trelloInfoArray = array('url' => $url, 'id' => $id);

      return $trelloInfoArray;
    }
  }

  //投稿時のリスト取得
  function getListId_Contact($post_type) {
    //return get_option("sharingTrello_{$post_type}_board_list_id"); 
    return get_option("sharingTrello_post_board_list_id"); 
  }
  //投稿時のラベル取得
  function getlabelId_Contact($post_type) {
    // return get_option("sharingTrello_{$post_type}_label_list_id"); 
    return get_option("sharingTrello_post_label_list_id"); 
  }
}


