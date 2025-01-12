<?php
function custom_taxonomy_filter_shortcode($atts)
{
  // Obtener los atributos del shortcode
  $atts = shortcode_atts(array(
    'parent_slug' => '', // Slug de la categoría padre
    'order' => 'ASC', // Orden de los términos
  ), $atts);

  // Verificar que el slug del padre esté definido
  if (empty($atts['parent_slug'])) {
    return '<p>Por favor, proporciona el slug de una categoría padre.</p>';
  }

  // Obtener la categoría padre a partir del slug
  $parent_term = get_term_by('slug', $atts['parent_slug'], 'tipo_de_norma');
  if (!$parent_term || is_wp_error($parent_term)) {
    return '<p>La categoría especificada no existe.</p>';
  }

  // Obtener las categorías hijas de la categoría padre
  $child_terms = get_terms(array(
    'taxonomy' => 'tipo_de_norma',
    'parent' => $parent_term->term_id,
    'orderby' => 'name',
    'order' => $atts['order'],
    'hide_empty' => false, // Mostrar todas las categorías, incluidas las vacías
  ));

  // Construir la estructura HTML manteniendo el diseño actual
  $output = '<div class="mdw__section_taxonomy-filter" id="normas">
              <div class="mdw__container_taxonomy-filter">
              <div class="mdw__taxonomy_filter" role="search" data-base-url="' . esc_url(home_url('/normas/')) . '" data-page-num="1" data-page-x="">
                <input type="hidden" id="mdw__slug_taxonomy">';

  // Añadir el botón para la categoría padre
  $output .= '<div class="mdw__taxonomy_filter-item parent" data-filter="' . esc_attr($parent_term->slug) . '" aria-pressed="false">' . esc_html($parent_term->name) . '</div>';

  // Añadir las categorías hijas solo si existen
  if (!empty($child_terms)) {
    foreach ($child_terms as $child_term) {
      $output .= '<div class="mdw__taxonomy_filter-item child" data-filter="' . esc_attr($child_term->slug) . '" aria-pressed="false">' . esc_html($child_term->name) . '</div>';
    }
  }

  // Cerrar el contenedor de búsqueda y el contenedor principal
  $output .= '</div></div></div>';

  return $output;
}

// Registrar el shortcode en WordPress
add_shortcode('normas_filter', 'custom_taxonomy_filter_shortcode');

