<?php

/**
 * Export all (Gutenberg) blocks' metadata from a WordPress post
 */
function get_block_data($content) 
{
  $ret = [];
  $blocks = parse_blocks($content);
  recursively_add_blocks($ret, $blocks);
  return $ret;
}

function recursively_add_blocks(&$ret, $blocks) 
{  
  foreach ($blocks as $block) {
    if ($block['attrs']['ref']) {
      $ret = array_merge(
      	$ret,
      	recursively_render_block_core_block($block['attrs'])
      );
    }
    else {
      $ret[] = $block;
    }
    if ($block['innerBlocks']) {
      recursively_add_blocks($ret, $block['innerBlocks']);
    }
  }
}

function recursively_render_block_core_block($attributes) 
{
  if (empty($attributes['ref'])) {
    return [];
  }

  $reusable_block = get_post($attributes['ref']);
  if (!$reusable_block || 'wp_block' !== $reusable_block->post_type) {
    return [];
  }

  if ('publish' !== $reusable_block->post_status || ! empty($reusable_block->post_password)) {
    return [];
  }

  return get_block_data($reusable_block->post_content);
}