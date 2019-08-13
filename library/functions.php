<?php

/**
 * Export all (Gutenberg) blocks' data from a WordPress post
 */
function get_block_data($content, $remove_divider_block = true)
{
  // Parse the blocks, and convert them into a single-level array
  $ret = [];
  $blocks = parse_blocks($content);
  recursively_add_blocks($ret, $blocks);

  // Maybe remove blocks without name
  if ($remove_divider_block) {
    $ret = remove_blocks_without_name($ret);
  }

  // Remove 'innerBlocks' property if it exists (since that code was copied to the first level, it is currently duplicated)
  foreach ($ret as &$block) {
    unset($block['innerBlocks']);
  }

  return $ret;
}

/**
 * Remove the blocks without name, such as the empty block divider
 */
function remove_blocks_without_name($blocks)
{
  return array_values(array_filter(
    $blocks,
    function($block) {
      return $block['blockName'];
    }
  ));
}

/**
 * Add block data (including global and nested blocks) into the first level of the array
 */
function recursively_add_blocks(&$ret, $blocks)
{
  foreach ($blocks as $block) {
    // Global block: add the referenced block instead of this one
    if ($block['attrs']['ref']) {
      $ret = array_merge(
        $ret,
        recursively_render_block_core_block($block['attrs'])
      );
    }
    // Normal block: add it directly
    else {
      $ret[] = $block;
    }
    // If it contains nested or grouped blocks, add them too
    if ($block['innerBlocks']) {
      recursively_add_blocks($ret, $block['innerBlocks']);
    }
  }
}

/**
 * Function based on `render_block_core_block`
 */
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
