<?php
/*
 * This is the child theme for Hello Elementor theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */
add_action('wp_enqueue_scripts', 'ugpp_enqueue_styles');
function ugpp_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style')
    );
}
/*
 * Your code goes below
 */

///////////////////////////////////////////////////////////////////////
////////////////////////////SHORTCODES/////////////////////////////////
///////////////////////////////////////////////////////////////////////
require 'shortcodes/tramites/mdw_tramites.php'; // Trámites
require 'shortcodes/normas/mdw_normas.php'; // Normas


///////////////////////////////////////////////////////////////////////
///////////////////////UTILIDADES GENRALES/////////////////////////////
///////////////////////////////////////////////////////////////////////
require 'utilities/mdw_html_search_input.php'; // Search input filter
require 'utilities/mdw_filtro_tipos_de_normas.php'; // Normas filter