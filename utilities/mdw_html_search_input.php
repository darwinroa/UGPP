<?php

/**
 * Retorna el HTML del campo de búsqueda
 * Requiere un ID para el campo de búsqueda
 */
function mdw_html_search_field($atts)
{
  // Definir los atributos aceptados y sus valores predeterminados
  $attributes = shortcode_atts(
    array(
      'post_type'  => 'post',
      'placeholder'  => 'buscar',
    ),
    $atts
  );

  $postType = $attributes['post_type'];
  $placeholder = $attributes['placeholder'];

  return "
    <form id='mdw-search-form-$postType' class='mdw__search_field'>
      <input 
        type='text' 
        id='mdw-search-$postType' 
        aria-label='$placeholder' 
        class='mdw__search_input' 
        name='search' 
        placeholder='$placeholder'>
      <span class='mdw__search_icon'></span>
    </form>
    ";
}

add_shortcode('mdw_search', 'mdw_html_search_field');
