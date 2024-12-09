<?php

/**
 * Imprime el paginado de un query si es necesario
 */
function mdw_pagination($query, $current_page)
{
  $html = '';
  if ($query->max_num_pages > 1) {
    $html .= "<div class='mdw__pagination'>";

    // Si el total de páginas es mayor a 10
    if ($query->max_num_pages > 10) {
      // Mostrar las primeras 4 páginas
      for ($i = 1; $i <= 4; $i++) {
        $active_class = ($i === $current_page) ? 'active' : '';
        $html .= "<button class='pagination-button $active_class' data-page='$i'>$i</button>";
      }

      // Si la página actual está lejos de las últimas páginas
      if ($current_page >= 4 && $current_page <= ($query->max_num_pages - 3)) {
        if ($current_page - 2 > 5) {
          $html .= "<span class='pagination-dots'>...</span>";
        }
        for ($i = $current_page - 2; $i <= $current_page + 2; $i++) {
          if ($i <= 4 || $i >= $query->max_num_pages - 3) continue;
          $active_class = ($i === $current_page) ? 'active' : '';
          $html .= "<button class='pagination-button $active_class' data-page='$i'>$i</button>";
        }
        if ($current_page + 2 < ($query->max_num_pages - 4)) {
          $html .= "<span class='pagination-dots'>...</span>";
        }
      } else if ($current_page <= 4 || $current_page > ($query->max_num_pages - 3)) {
        $html .= "<span class='pagination-dots'>...</span>";
      }

      // Mostrar las últimas 4 páginas
      for ($i = max($query->max_num_pages - 3, 5); $i <= $query->max_num_pages; $i++) {
        if ($current_page > 4 && $current_page < max($query->max_num_pages - 3, 5)) {
          // continue;
        }
        $active_class = ($i === $current_page) ? 'active' : '';
        $html .= "<button class='pagination-button $active_class' data-page='$i'>$i</button>";
      }
    } else {
      // Si el total de páginas es menor o igual a 10, mostrar todas las páginas
      for ($i = 1; $i <= $query->max_num_pages; $i++) {
        $active_class = ($i === $current_page) ? 'active' : '';
        $html .= "<button class='pagination-button $active_class' data-page='$i'>$i</button>";
      }
    }

    $html .= "</div>"; // Cerrar el contenedor de paginación
  }

  return $html;
}
