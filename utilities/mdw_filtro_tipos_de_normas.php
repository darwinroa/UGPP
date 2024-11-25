<?php
function custom_taxonomy_filter_shortcode()
{
  // Obtener todas las categorías de la taxonomía 'tipo_de_norma'
  $terms = get_terms(array(
    'taxonomy' => 'tipo_de_norma',
    'orderby'  => 'name',
    'hide_empty' => false, // Muestra todas las categorías, incluyendo las vacías
  ));

  if (is_wp_error($terms)) {
    return ''; // Si no se encuentran términos, devolver vacío
  }

  // Inicializar el contenido del filtro
  $output = '<div class="mdw__section_taxonomy-filter" id="normas">
              <div class="mdw__container_taxonomy-filter">
              <search class="mdw__taxonomy_filter" role="search" data-base-url="' . esc_url(home_url('/normas/')) . '" data-page-num="1" data-page-x="">
                <input type="hidden" id="mdw__slug_taxonomy">';

  // Recorremos los términos para agruparlos por padres
  $parent_terms = array_filter($terms, function ($term) {
    return $term->parent == 0; // Solo los padres
  });

  // Ahora procesamos cada categoría padre
  foreach ($parent_terms as $parent_term) {
    // Añadir el botón para la categoría padre
    $output .= '<div class="mdw__taxonomy_filter-item parent" data-filter="' . esc_attr($parent_term->slug) . '" aria-pressed="false">' . esc_html($parent_term->name) . '</div>';

    // Obtener los hijos de esta categoría padre
    $child_terms = array_filter($terms, function ($term) use ($parent_term) {
      return $term->parent == $parent_term->term_id; // Solo los hijos de esta categoría
    });

    // Si hay hijos, añadirlos como botones dentro de un contenedor anidado
    if (!empty($child_terms)) {
      foreach ($child_terms as $child_term) {
        $output .= '<div class="mdw__taxonomy_filter-item child" data-filter="' . esc_attr($child_term->slug) . '" aria-pressed="false">' . esc_html($child_term->name) . '</div>';
      }
    }
  }

  // Cerrar el contenedor de búsqueda y el contenedor principal
  $output .= '</search></div></div>';

  return $output;
}

// Registrar el shortcode en WordPress
add_shortcode('normas_filter', 'custom_taxonomy_filter_shortcode');
