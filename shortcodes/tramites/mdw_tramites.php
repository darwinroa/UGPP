<?php
if (!function_exists('mdw_tramites_function')) {
  add_shortcode('mdw_tramites', 'mdw_tramites_function');

  function mdw_tramites_function($atts)
  {
    // Definir los atributos aceptados y sus valores predeterminados
    $attributes = shortcode_atts(
      array(
        'post_type'     => 'tramites',
        'post_per_page' => 6
      ),
      $atts
    );

    $postType = $attributes['post_type'];
    $post_per_page = $attributes['post_per_page'];

    wp_enqueue_script('mdw-tramite-script', get_stylesheet_directory_uri() . '/shortcodes/tramites/mdw_tramites.js', array('jquery'), null, true);
    wp_localize_script('mdw-tramite-script', 'wp_ajax', array(
      'ajax_url'            => admin_url('admin-ajax.php'),
      'nonce'               => wp_create_nonce('load_more_nonce'),
      'theme_directory_uri' => get_stylesheet_directory_uri(),
      'post_type'           => $postType,
      'post_per_page'       => $post_per_page,
    ));

    ob_start();
    $html = '';
    $html .= "
      <div id='mdw__tramites_section' class='mdw__tramites_section'>
        <div class='mdw__content_loop'>
          <div class='mdw__content_loop-grid'>
          </div>
        </div>
      </div>
    ";
    $html .= ob_get_clean();
    return $html;
  }
}

function mdw_query_tramites_loop_with_pagination($args)
{
  $query = new WP_Query($args);
  $html = "";

  // Verificar si hay posts
  if ($query->have_posts()) {
    ob_start();

    // Recorrer los posts
    while ($query->have_posts()) : $query->the_post();
      $html .= do_shortcode('[elementor-template id="1052"]');
    endwhile;

    $html .= ob_get_clean();

    // Agregar la paginación con data-page
    $html .= mdw_pagination($query, $args['paged']);
  } else {
    $html .= ""; // Si no hay posts, no mostrar nada
  }

  wp_reset_postdata(); // Resetea los datos del post
  return $html;
}


/**
 * Función para la respuesta del Ajax
 */
if (!function_exists('mdw_tramite_ajax_filter')) {
  add_action('wp_ajax_nopriv_mdw_tramite_ajax_filter', 'mdw_tramite_ajax_filter');
  add_action('wp_ajax_mdw_tramite_ajax_filter', 'mdw_tramite_ajax_filter');

  function mdw_tramite_ajax_filter()
  {
    check_ajax_referer('load_more_nonce', 'nonce');

    // Obtener la página y otros parámetros del POST
    $page = $_POST['page'];
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $post_type = $_POST['post_type'];
    $post_per_page = $_POST['post_per_page'];

    // Configurar los argumentos de la consulta, incluyendo la paginación
    $args = array(
      'post_type'       => $post_type,
      'post_status'     => 'publish',
      'orderby'         => 'menu_order',
      'order'           => 'ASC',
      'posts_per_page'  => $post_per_page,
      'paged'           => $page, // Usar la página actual
      's'               => $search, // Filtrar por búsqueda si hay
    );

    // Obtener el loop de los posts y la paginación
    $query_loop = mdw_query_tramites_loop_with_pagination($args);

    // Enviar la respuesta AJAX con el HTML del loop y la paginación
    wp_send_json_success($query_loop);
    wp_die();
  }
}

/**
 * Shortcode para la url
 */
add_shortcode('mdw_tramites_url', 'mdw_tramites_url_function');

function mdw_tramites_url_function() {
  $same_tab = get_field('nueva_pestana');
  $url = get_field('link_del_tramite');
  if ($same_tab) {
    $html = '
              <a class="elementor-button elementor-button-link elementor-size-sm" href="' . $url . '">
                <span class="elementor-button-content-wrapper">
                  <span class="elementor-button-text">Más en Gov.co</span>
                </span>
              </a>';
  } else {
    $html = '
              <a class="elementor-button elementor-button-link elementor-size-sm" href="' . $url . '" target="_blank">
                <span class="elementor-button-content-wrapper">
                  <span class="elementor-button-text">Conozca más</span>
                </span>
              </a>';
  }

  return $html;
}