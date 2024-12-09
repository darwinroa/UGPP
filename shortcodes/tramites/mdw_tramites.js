jQuery(document).ready(function ($) {
  var page = 1; // Inicializando el paginado
  var isLoadMore = false;
  var ajaxRequest; // Variable para almacenar la solicitud AJAX activa

  // Verificar si hay un parámetro de búsqueda en la URL
  var urlParams = new URLSearchParams(window.location.search);
  var searchParam = urlParams.get('search');
  var pageParam = urlParams.get('page');

  if (searchParam) {
    $('#mdw-search-tramites').val(searchParam); // Establecer el valor en el campo de búsqueda
  }

  // Si el parámetro `page` existe, actualizar el valor de la página
  if (pageParam) {
    page = parseInt(pageParam);
  }

  // Realizar la búsqueda automáticamente al cargar la página
  mdwTramitesAjax(page);

  $(document).on('change', '#mdw-search-form-tramites', function () {
    page = 1; // Reiniciar el paginado cada vez que se desea filtrar
    isLoadMore = false;
    mdwTramitesAjax(page);
  });

  // Evento para hacer búsqueda en tiempo real
  $('#mdw-search-tramites').on('keyup', function (event) {
    if (event.which === 13) {
      event.preventDefault(); // Previene el envío del formulario
    }
    page = 1; // Reiniciar la paginación al escribir
    isLoadMore = false;

    // Cancelar solicitud anterior si existe
    if (ajaxRequest) {
      ajaxRequest.abort();
    }

    // Realizar la nueva búsqueda
    ajaxRequest = mdwTramitesAjax(page);
  });

  // Resetear filtros
  $('#mdw__button-reset').on('click', function () {
    page = 1; // Reiniciar el paginado
    isLoadMore = false;
    $('#mdw__form-filter-tramites')[0].reset(); // Resetear el formulario
    mdwTramitesAjax(page);
  });

  // Evento para manejar los botones de paginación
  $(document).on('click', '.pagination-button', function () {
    const newPage = $(this).data('page'); // Obtener el número de página del botón
    if (newPage !== page) {
      page = newPage;

      // Actualizar el parámetro 'page' en la URL
      const url = new URL(window.location.href);
      url.searchParams.set('page', page);
      window.history.pushState({}, '', url); // Actualiza la barra de direcciones sin recargar

      // Realizar la solicitud AJAX para la nueva página
      isLoadMore = false;
      mdwTramitesAjax(page);
    }
  });

  // Función Ajax para la petición del filtro y el cargar más
  function mdwTramitesAjax(page) {
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
        page, // Incluir el número de página en la solicitud
      },
      beforeSend: function () {
        const loaderUrl =
          wp_ajax.theme_directory_uri + '/assets/img/ri-preloader.svg';
        const loaderIcon = `<div class='mdw-loader-ajax'><img id='mdw__loadmore-icon' height='20' width='20' decoding='async' alt='Loading' data-src='${loaderUrl}' class='ls-is-cached lazyloaded e-font-icon-svg e-fas-spinner eicon-animation-spin' src='${loaderUrl}'></div>`;
        isLoadMore || $('#mdw__tramites_section .mdw__content_loop-grid').empty();
        $('#mdw__tramites_section .mdw__content_button-loadmore').html(loaderIcon);
        $('#mdw__tramites_section .mdw__button_loadmore').hide();
      },
      success: function (response) {
        if (response.success) {
          $('#mdw__tramites_section .mdw__button-loadmore').show();
          $('.mdw-loader-ajax').remove();
          if (isLoadMore) {
            $('#mdw__tramites_section .mdw__content_loop-grid').append(
              response.data
            );
          } else {
            $('#mdw__tramites_section .mdw__content_loop-grid').html(
              response.data
            );
          }

          // Actualizar la paginación activa visualmente
          updatePaginationButtons(page);
        } else {
          $('#mdw__tramites_section .mdw__content_loop-grid').html(
            '<p>Hubo un error en la solicitud.</p>'
          );
        }
      },
    });
  }

  // Actualizar la clase activa en los botones de paginación
  function updatePaginationButtons(activePage) {
    $('.pagination-button').removeClass('active');
    $(`.pagination-button[data-page="${activePage}"]`).addClass('active');
  }

  $(document).on('click', '.pagination-button', function () {
    const newPage = $(this).data('page');
    if (newPage) {
        if (newPage !== page) {
            page = newPage;

            // Actualizar el parámetro 'page' en la URL
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.history.pushState({}, '', url);

            // Realizar la solicitud AJAX para la nueva página
            isLoadMore = false;
            mdwTramitesAjax(page);
        }
    }
  });

  // Ignorar clics en puntos suspensivos
  $(document).on('click', '.pagination-dots', function (e) {
      e.preventDefault();
  });
});

