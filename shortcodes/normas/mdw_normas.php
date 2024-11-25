<?php
if (!function_exists('mdw_normas_function')) {
  add_shortcode('mdw_normas', 'mdw_normas_function');

  function mdw_normas_function()
  {
    $post_per_page = 6;

    wp_enqueue_script('mdw-norma-script', get_stylesheet_directory_uri() . '/shortcodes/normas/mdw_normas.js', array('jquery'), null, true);
    wp_localize_script('mdw-norma-script', 'wp_ajax', array(
      'ajax_url'            => admin_url('admin-ajax.php'),
      'nonce'               => wp_create_nonce('load_more_nonce'),
      'theme_directory_uri' => get_stylesheet_directory_uri(),
      'post_per_page'       => $post_per_page,
    ));

    // Obtiene la página actual para la paginación
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $args = array(
      'post_type'       => 'normas',
      'posts_per_page'  => $post_per_page,
      'paged'           => $paged,
    );

    // Llamada a la función combinada
    $query_loop = mdw_query_normas_loop_with_pagination($args);

    ob_start();
    $html = '';
    $html .= "
      <div id='mdw__normas_section' class='mdw__normas_section'>
        <div class='mdw__content_loop'>
          <div class='mdw__content_loop-grid'>
            $query_loop
          </div>
        </div>
      </div>
    ";
    $html .= ob_get_clean();
    return $html;
  }
}

function mdw_query_normas_loop_with_pagination($args)
{
  $query = new WP_Query($args);
  $html = "";

  // Verificar si hay posts
  if ($query->have_posts()) {
    ob_start();

    // Recorrer los posts
    while ($query->have_posts()) : $query->the_post();
      $html .= do_shortcode('[elementor-template id="149"]');
    endwhile;

    // Mostrar los posts
    $html .= ob_get_clean();

    // Agregar la paginación si es necesario
    if ($query->max_num_pages > 1) {
      $big = 999999999; // Número que nunca será válido para la página
      $pagination = paginate_links(array(
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => max(1, get_query_var('paged')),
        'total'     => $query->max_num_pages,
        'prev_text' => __('« Anterior'),
        'next_text' => __('Siguiente »'),
      ));
      $html .= "<div class='mdw__pagination'>$pagination</div>"; // Incluir los enlaces de paginación
    }
  } else {
    $html .= ""; // Si no hay posts, no mostrar nada
  }

  wp_reset_postdata(); // Resetea los datos del post
  return $html;
}



/**
 * Función para la respuesta del Ajax
 */
if (!function_exists('mdw_norma_ajax_filter')) {
  add_action('wp_ajax_nopriv_mdw_norma_ajax_filter', 'mdw_norma_ajax_filter');
  add_action('wp_ajax_mdw_norma_ajax_filter', 'mdw_norma_ajax_filter');

  function mdw_norma_ajax_filter()
  {
    check_ajax_referer('load_more_nonce', 'nonce');

    // Obtener la página y otros parámetros del POST
    $page = $_POST['page'];
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
    $post_per_page = $_POST['post_per_page'];

    // Configurar los argumentos de la consulta, incluyendo la paginación
    // Iniciar el array de argumentos para la consulta
    $args = array(
      'post_type'       => 'normas',
      'posts_per_page'  => $post_per_page,
      'paged'           => $page, // Usar la página actual
    );

    // Si existe el parámetro de búsqueda (search)
    if ($search) {
      $args['s'] = $search; // Filtrar por búsqueda
    }

    // Si existe el parámetro de taxonomía (taxonomy)
    if ($taxonomy) {
      $args['tax_query'] = array(
        array(
          'taxonomy' => 'tipo_de_norma', // Nombre de la taxonomía
          'field'    => 'slug',          // Puede ser 'term_id', 'name' o 'slug'
          'terms'    => $taxonomy,       // Término o array de términos a los que se filtra
        ),
      );
    }

    // Obtener el loop de los posts y la paginación
    $query_loop = mdw_query_normas_loop_with_pagination($args);

    // Enviar la respuesta AJAX con el HTML del loop y la paginación
    wp_send_json_success($query_loop);
    wp_die();
  }
}
