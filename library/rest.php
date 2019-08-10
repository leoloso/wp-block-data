<?php
/**
 * Define REST endpoints
 */
add_action('rest_api_init', function () {
	// /wp-json/wp/v2/post-block-data/{POST_ID}
  register_rest_route('wp/v2', 'post-block-data/(?P<post_id>\d+)', [
    'methods'  => 'GET',
    'callback' => 'get_post_blocks'
  ]);
});
function get_post_blocks($request) 
{
  $post = get_post($request['post_id']);
  if (!$post) {
    return new WP_Error('empty_post', 'There is no post with this ID', array('status' => 404));
  }

  $block_data = get_block_data($post->post_content);
  $response = new WP_REST_Response($block_data);
  $response->set_status(200);
  return $response;
}
