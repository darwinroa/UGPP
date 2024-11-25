jQuery(document).ready(function($) {
  var page = 1; // Inicializando el paginado
  var isLoadMore = false;
  var ajaxRequest; // Variable para almacenar la solicitud AJAX activa

  // Verificar si hay un parámetro de búsqueda en la URL
  var urlParams = new URLSearchParams(window.location.search);
  var searchParam = urlParams.get('search');
  
  if (searchParam) {
    $('#mdw-search-tramites').val(searchParam); // Establecer el valor en el campo de búsqueda
    page = 1;
    isLoadMore = false;

    // Realizar la búsqueda automáticamente al cargar la página
    mdwTramitesAjax(page);
  }

  $(document).on('change', '#mdw-search-form-tramites', function() {
    page = 1; // Inicializando el paginado cada vez que se desea filtrar
    isLoadMore = false;
    mdwTramitesAjax(page);
  });

  // Evento para hacer búsqueda en tiempo real
  $('#mdw-search-tramites').on('keyup', function(event) {
    if (event.which === 13) { // Verifica si se presionó la tecla Enter
      event.preventDefault(); // Previene el envío del formulario
    }
    // Reiniciar la paginación cuando se empieza a escribir
    page = 1;
    isLoadMore = false;

    // Si ya hay una solicitud en curso, cancelarla
    if (ajaxRequest) {
      ajaxRequest.abort();
    }

    // Realizar la nueva búsqueda al escribir
    ajaxRequest = mdwTramitesAjax(page);
  });
  
  // Se ejecuta al momento de presionar sobre el botón de reset
  $('#mdw__button-reset').on('click', function() {
    page = 1; // Inicializando el paginado cada vez que se desea filtrar
    isLoadMore = false;
    // Reiniciar el formulario de filtros
    $('#mdw__form-filter-tramites')[0].reset();
    mdwTramitesAjax(page);
  });

  // Función Ajax para la petición del filtro y el cargar más
  function mdwTramitesAjax() {
    const search = $('#mdw-search-tramites').val(); // Nuevo campo de búsqueda 

    // Retornar la solicitud AJAX para poder cancelarla si es necesario
    return $.ajax({
      url: wp_ajax.ajax_url,
      type: 'post',
      data: {
        action: 'mdw_tramite_ajax_filter',
        nonce: wp_ajax.nonce,
        post_per_page: wp_ajax.post_per_page,
        search,
      },
      beforeSend: function() {
        const loaderUrl = wp_ajax.theme_directory_uri + '/assets/img/ri-preloader.svg';
        const loaderIcon = `<div class='mdw-loader-ajax'><img id='mdw__loadmore-icon' height='20' width='20' decoding='async' alt='Loading' data-src='${loaderUrl}' class='ls-is-cached lazyloaded e-font-icon-svg e-fas-spinner eicon-animation-spin' src='${loaderUrl}'></div>`;
        isLoadMore || $('#mdw__tramites_section .mdw__content_loop-grid').empty();
        $('#mdw__tramites_section .mdw__content_button-loadmore').html(loaderIcon);
        $('#mdw__tramites_section .mdw__button_loadmore').hide();
      },
      success: function(response) {
        if (response.success) {
          $('#mdw__tramites_section .mdw__button-loadmore').show();
          $('.mdw-loader-ajax').remove();
          if (isLoadMore) {
            $('#mdw__tramites_section .mdw__content_loop-grid').append(response.data);
          } else {
            $('#mdw__tramites_section .mdw__content_loop-grid').html(response.data);
          }
        } else {
          $('#mdw__tramites_section .mdw__content_loop-grid').html('<p>Hubo un error en la solicitud.</p>');
        }
      }
    });
  }
});