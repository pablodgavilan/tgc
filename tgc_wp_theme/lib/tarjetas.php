<?php

function tarjetas() {
    global $wp_query, $tarjeta;
    $GLOBALS['tgc_tarjeta'] = $wp_query->query_vars['tgc_tarjeta'];
    tgc_guardar_historia();
    if (tgc_es_tarjeta_valida ()) {
        if ($tarjeta->activa) {
            get_template_part('tarjeta_historias');
        } else {
            get_template_part('tarjeta');
        }
    } else {
        header("location: /");
    }
    die;
}

function tgc_es_tarjeta_valida() {
    global $wpdb, $tgc_tarjeta, $tarjetas_tabla;
    $sql = $wpdb->prepare("SELECT cardCode,activa,deseo FROM {$tarjetas_tabla} WHERE cardCode='%s'", $tgc_tarjeta);
    $t = $wpdb->get_row($sql);
    $GLOBALS['tarjeta'] = $t;
    return $tgc_tarjeta == $t->cardCode;
}

function tgc_guardar_historia() {
    global $tgc_tarjeta, $tarjetas_tabla, $wpdb;
    require_once( ABSPATH . WPINC . '/ms-functions.php');
    if ($_POST['tgc_story']) {
        $user_id = get_user_id_from_string('anonimo');
        $post_id = wp_insert_post(array(
                    'post_author' => $user_id,
                    'post_title' => "Historia",
                    'post_content' => $_POST['tgc_story'],
                    'post_category' => array(get_cat_ID('Historias')),
                    'post_status' => 'publish'
                ));
        wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => "Historia ".$post_id));
        add_post_meta($post_id, "tarjeta", $tgc_tarjeta, TRUE);
        add_post_meta($post_id, "lugar", $_POST['tgc_place'], TRUE);
    } else if ($_POST['tgc_cuentanos']) {
        $cuentanos = $_POST['tgc_cuentanos'];
        $date = $_POST['tgc_date'];
        $lugar = $_POST['tgc_place'];
        $deseo = $_POST['tgc_deseo'];
        $sql = $wpdb->prepare("UPDATE {$tarjetas_tabla} SET activa=1,cuentanos='%s',date='%s',lugar='%s',deseo='%s' WHERE cardCode='{$tgc_tarjeta}'", $cuentanos, $date, $lugar, $deseo);
        $wpdb->query($sql);
    }
}

function tgc_historias() {
    global $wpdb, $tgc_tarjeta;
    $t_id = htmlspecialchars($tgc_tarjeta);
    $sql = $wpdb->prepare("SELECT post_id FROM wp_postmeta WHERE meta_key='tarjeta' AND meta_value='%s'", $t_id);
    $historias_ids = $wpdb->get_col($sql);
    query_posts(array('post__in' => $historias_ids));
    if (count($historias_ids) > 0)
        if (have_posts ()) {
            get_template_part('historias');
            $res = TRUE;
        }
    return $res;
}

function tgc_numero_targeta() {
    global $tgc_tarjeta;
    echo htmlspecialchars($tgc_tarjeta);
}

function tgc_tarjeta_deseo() {
    global $tarjeta;
    echo $tarjeta->deseo;
}

$tarjetas_tabla = "wp_tgc_tarjetas";
add_action('init', 'tgc_crear_bbdd');

function tgc_crear_bbdd() {
    global $tarjetas_tabla;
    if (get_option("TGC_BBDD_VERISON") != BBDD_VERISON) {

        $sql = "CREATE TABLE {$tarjetas_tabla} (
                `cardId` int(10) unsigned NOT NULL,
                `cardParentId` int(10) unsigned default NULL,
                `cardCode` varchar(16) NOT NULL,
                `cardType` int(10) unsigned NOT NULL default '0',
                `cardStatus` int(10) unsigned NOT NULL default '1',
                `cardCreatedBy` int(10) unsigned NOT NULL default '0',
                `cardModified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
                activa TINYINT default 0,
                cuentanos VARCHAR(255) NULL,
                date DATE NULL,
                lugar VARCHAR(255) NULL,
                deseo VARCHAR(255) NULL,
                PRIMARY KEY  (`cardId`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta(array($sql));

        update_option("TGC_BBDD_VERISON", BBDD_VERISON);
    }
}

?>
