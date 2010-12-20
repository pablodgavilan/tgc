<?php

define(BBDD_VERISON, "2");
require_once 'lib/tarjetas.php';

function register_my_menus() {
    register_nav_menus(
            array('header-menu' => 'Cabecera', 'footer-menu' => 'Pie')
    );
    register_sidebars();
}

function preparar_menu() {
    wp_enqueue_script("jquery");
    wp_enqueue_script("hoverIntent", get_bloginfo('template_url') . "/superfish-1.4.8/js/hoverIntent.js");
    wp_enqueue_script("superfish", get_bloginfo('template_url') . "/superfish-1.4.8/js/superfish.js");
    wp_enqueue_script("script", get_bloginfo('template_url') . "/js/script.js");
}

add_action('init', 'tgc_activate');

function tgc_activate() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

add_action('generate_rewrite_rules', 'tgc_rewrite_rules');

function tgc_rewrite_rules($wp_rewrite) {
    $br = array('tarjeta/(.*)$' => 'index.php?tgc_tarjeta=$matches[1]');
    $br2 = array('historia/(.*)$' => 'index.php?p=$matches[1]');
    $wp_rewrite->rules = $br + $br2 + $wp_rewrite->rules;
}

add_filter('query_vars', 'tgc_query_vars');

function tgc_query_vars($vars) {
    $vars[] = 'tgc_tarjeta';
    return $vars;
}

add_action('template_redirect', 'tgc_template_redirect');

function tgc_template_redirect() {
    global $wp_query;

    if (isset($wp_query->query_vars['tgc_tarjeta'])) {
        tarjetas();
    }
}

add_action('init', 'register_my_menus');
add_action('init', 'preparar_menu');





?>
