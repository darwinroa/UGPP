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

    ob_start();
    $html = '';
    $html .= "
      <div id='mdw__normas_section' class='mdw__normas_section'>
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

function mdw_query_normas_loop_with_pagination($args)
{
  $query = new WP_Query($args);
  $html = "";

  // Verificar si hay posts
  if ($query->have_posts()) {
    ob_start();

    // Recorrer los posts
    while ($query->have_posts()) : $query->the_post();
      $html .= do_shortcode('[elementor-template id="1042"]');
    endwhile;

    // Mostrar los posts
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

if (!function_exists('mdw_type_file_function')) {
  add_shortcode('mdw_type_file', 'mdw_type_file_function');

  function mdw_type_file_function()
  {
    $file = get_field('cargar_documento');
    $isTypeSheet = false;
    $html = '';
    if ($file) {
      // Obtener la extensión del archivo y el tipo MIME
      $mime_type = $file['mime_type'];
      $filename = $file['filename'];
      $fileSize = $file['filesize'];
      $file_extension = pathinfo($filename, PATHINFO_EXTENSION);

      // Definir los tipos MIME y extensiones de los archivos de Excel
      $excel_mime_types = array(
        'application/vnd.ms-excel', // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel.sheet.macroenabled.12', // .xlsb
        'text/csv' // .csv
      );

      $excel_extensions = array(
        'xls',
        'xlsx',
        'xlsb',
        'csv'
      );

      // Verificar si el tipo MIME o la extensión coincide con los tipos de archivo Excel
      if (in_array($mime_type, $excel_mime_types) || in_array(strtolower($file_extension), $excel_extensions)) {
        // Si es un archivo de Excel
        $isTypeSheet = true;
      } else {
        // Si no es un archivo de Excel
        $isTypeSheet = false;
      }
    } else {
      // No hay archivo cargado
      $isTypeSheet = false;
      $filename = '';
      $fileSize = '';
    }

    $urlImage = $isTypeSheet ?
      get_stylesheet_directory_uri() . '/assets/img/excel_icon.svg' :
      get_stylesheet_directory_uri() . '/assets/img/icono_PDF.png';

    $html .= "
      <div class='mdw__type_file'>
        <img src='$urlImage' alt='$filename' class='mdw__type_file-type'>
        <span class='mdw__type_file-size'>$fileSize KB</span>
      </div>";

    return $html;
  }
}
