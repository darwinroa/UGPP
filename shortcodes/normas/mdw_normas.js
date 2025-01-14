jQuery(document).ready(function($) {
  var page = 1; // Inicializando el paginado
  var isLoadMore = false;
  var ajaxRequest; // Variable para almacenar la solicitud AJAX activa
  var filterParam = '';

  // Obtener los parámetros de la URL
  const urlParams = new URLSearchParams(window.location.search);
  const taxonomyParam = urlParams.get('taxonomy'); // Obtener el parámetro `taxonomy`
  const searchParam = urlParams.get('search'); // Obtener el parámetro `search`
  const pageParam = urlParams.get('page'); // Obtener el parámetro `page`

  // Si el parámetro `taxonomy` existe, inicializar el filtro seleccionado
  if (taxonomyParam) {
    $('.mdw__taxonomy_filter-item').each(function() {
      if ($(this).data('filter') === taxonomyParam) {
        $(this).addClass('active');
        $('#mdw__slug_taxonomy').val(taxonomyParam); // Establecer el valor en el campo oculto
      }
    });
    filterParam = taxonomyParam; // Establecer el valor global
  }
  
  if (searchParam) {
    $('#mdw-search-normas').val(searchParam); // Establecer el valor en el campo de búsqueda
    $('#mdw__slug_taxonomy').val(''); // Limpiar el valor del campo de taxonomía
  }

  // Si el parámetro `page` existe, actualizar el valor de la página
  page = pageParam ? parseInt(pageParam) : 1;
  
  // Realizar la búsqueda automáticamente al cargar la página
  mdwNormasAjax();

  $(document).on('change', '#mdw-search-form-normas', function() {
    page = 1; // Inicializando el paginado cada vez que se desea filtrar
    isLoadMore = false;
    var search = $('#mdw-search-normas').val();
    // Actualizar la URL con el nuevo parámetro de búsqueda
    $('#mdw__slug_taxonomy').val(''); // Limpiar el valor del campo de taxonomía
    updateURL(search, page, '');
    mdwNormasAjax();
  });

  // Evento para hacer búsqueda en tiempo real
  $('#mdw-search-normas').on('keyup', function(event) {
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

    $('#mdw__slug_taxonomy').val(''); // Limpiar el valor del campo de taxonomía
    // Obtener el valor de búsqueda y actualizar la URL
    var search = $('#mdw-search-normas').val();
    updateURL(search, page, '');

    // Realizar la nueva búsqueda al escribir
    ajaxRequest = mdwNormasAjax();
  });
  
  // Se ejecuta al momento de presionar sobre el botón de reset
  $('#mdw__button-reset').on('click', function() {
    page = 1; // Inicializando el paginado cada vez que se desea filtrar
    isLoadMore = false;
    // Reiniciar el formulario de filtros
    $('#mdw__form-filter-normas')[0].reset();
    updateURL('', page, ''); // Limpiar el parámetro de búsqueda en la URL
    mdwNormasAjax();
  });

  // Manejar el evento click sobre los elementos con la clase .mdw__taxonomy_filter-item
  $('.mdw__taxonomy_filter-item').on('click', function() {
    // Limpiar el valor del input con ID mdw-search-normas
    $('#mdw-search-normas').val('');
    var search = $('#mdw-search-normas').val();
    updateURL(search, page, '');
    // Eliminar la clase 'active' de todos los elementos
    $('.mdw__taxonomy_filter-item').removeClass('active');
    
    // Añadir la clase 'active' solo al elemento que fue clickeado
    $(this).addClass('active');

    // Obtener el slug de la taxonomía seleccionada
    filterParam = $(this).data('filter');
    $('#mdw__slug_taxonomy').val(filterParam); // Establecer el valor en el campo de taxonomía

    // Actualizar la URL usando la función unificada
    updateURL('', 1, filterParam); // Reiniciar búsqueda y página, y establecer la taxonomía seleccionada

    page = 1; // Inicializando el paginado cada vez que se desea filtrar
    isLoadMore = false;
    mdwNormasAjax();
  });

  // Evento para manejar los botones de paginación
  $(document).on('click', '.pagination-button', function () {
    const newPage = $(this).data('page'); // Obtener el número de página del botón
    if (newPage !== page) {
      page = newPage;

      // Actualizar el parámetro 'page' en la URL
      updateURL($('#mdw-search-normas').val(), page, '');

      // Realizar la solicitud AJAX para la nueva página
      isLoadMore = false;
      mdwNormasAjax(page);
    }
  });

  // Función para actualizar la URL
  function updateURL(search, page, taxonomy) {
    const url = new URL(window.location.href);

    // Actualizar los parámetros según los valores pasados
    if (search !== undefined) {
      url.searchParams.set('search', search); // Actualiza o establece el parámetro `search`
    }

    if (page !== undefined) {
      url.searchParams.set('page', page); // Actualiza o establece el parámetro `page`
    }

    if (taxonomy !== undefined) {
      url.searchParams.set('taxonomy', taxonomy); // Actualiza o establece el parámetro `taxonomy`
    } else {
      url.searchParams.delete('taxonomy'); // Elimina el parámetro `taxonomy` si no se pasa
    }

    window.history.pushState({}, '', url); // Actualiza la barra de direcciones sin recargar
  }

  // Función Ajax para la petición del filtro y el cargar más
  function mdwNormasAjax() {
    const search = $('#mdw-search-normas').val(); // Nuevo campo de búsqueda 
    const  taxonomy = $('#mdw__slug_taxonomy').val(); // Compo de slug taxonomy 

    // Retornar la solicitud AJAX para poder cancelarla si es necesario
    return $.ajax({
      url: wp_ajax.ajax_url,
      type: 'post',
      data: {
        action: 'mdw_norma_ajax_filter',
        nonce: wp_ajax.nonce,
        post_per_page: wp_ajax.post_per_page,
        search,
        taxonomy,
        page,
      },
      beforeSend: function() {
        const loaderUrl = wp_ajax.theme_directory_uri + '/assets/img/ri-preloader.svg';
        const loaderIcon = `<div class='mdw-loader-ajax'><img id='mdw__loadmore-icon' height='20' width='20' decoding='async' alt='Loading' data-src='${loaderUrl}' class='ls-is-cached lazyloaded e-font-icon-svg e-fas-spinner eicon-animation-spin' src='${loaderUrl}'></div>`;
        isLoadMore || $('#mdw__normas_section .mdw__content_loop-grid').empty();
        $('#mdw__normas_section .mdw__content_button-loadmore').html(loaderIcon);
        $('#mdw__normas_section .mdw__button_loadmore').hide();
      },
      success: function(response) {
        if (response.success) {
          $('#mdw__normas_section .mdw__button-loadmore').show();
          $('.mdw-loader-ajax').remove();
          if (isLoadMore) {
            $('#mdw__normas_section .mdw__content_loop-grid').append(response.data);
          } else {
            $('#mdw__normas_section .mdw__content_loop-grid').html(response.data);
          }
        } else {
          $('#mdw__normas_section .mdw__content_loop-grid').html('<p>Hubo un error en la solicitud.</p>');
        }
      }
    });
  }
});