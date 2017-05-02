<?php

require_once __DIR__ . '/required/init.php';

$arguments   = isset($argv) ? $argv : (isset($_REQUEST) ? $_REQUEST : []);
$forceUpdate = in_array("--force", $arguments) || in_array('force', $arguments);
$container   = get_container();
$manager     = $container->get('tbn.news_manager');
$lundi       = new \DateTime('monday this week');
$dimanche    = new \DateTime('sunday this week');

$datas        = $manager->getNewsDatas($lundi, $dimanche);
$content      = str_replace("\n", "", $datas['content']);
$sortedEvents = sort_events(array_filter($datas['events']));

$news    = $datas['news'];
$post_id = $news->getWordpressPostId();
$edition = $news->getNumeroEdition();

$headline = sprintf(
    'By Night Magazine #%d du %s au %s. On a sélectionné rien que pour vous le top 3 des meilleurs événements dans votre ville !',
    $edition,
    formatDate($lundi, IntlDateFormatter::LONG, IntlDateFormatter::NONE),
    formatDate($dimanche, IntlDateFormatter::LONG, IntlDateFormatter::NONE)
);

$title = sprintf(
    'Top 3 des événements classés par ville (du %s au %s)',
    formatDate($lundi, IntlDateFormatter::LONG, IntlDateFormatter::NONE),
    formatDate($dimanche, IntlDateFormatter::LONG, IntlDateFormatter::NONE)
);

$old_post_id = $post_id;
$post_id     = wp_insert_post([
    'ID'            => $post_id,
    'post_title'    => $title,
    'post_content'  => $content,
    'post_status'   => 'publish',
    'post_excerpt'  => $headline,
    'post_type'     => 'post',
    'post_category' => array(2), //Cat : Actualités
    'meta_input'    => [
        '_yoast_wpseo_metadesc' => $headline, //Meta description
        '_yoast_wpseo_focuskw'  => "événements", //Focus keyword
    ],
]);

//Traitement de l'image principale
$title = sprintf(
    "By Night Magazine édition #%d du %s au %s: Le top 3 des événements classés par ville",
    $edition,
    formatDate($lundi, IntlDateFormatter::LONG, IntlDateFormatter::NONE),
    formatDate($dimanche, IntlDateFormatter::LONG, IntlDateFormatter::NONE)
);

$headline = sprintf(
    'By Night Magazine édition #%d du %s au %s',
    $edition,
    formatDate($lundi, IntlDateFormatter::LONG, IntlDateFormatter::NONE),
    formatDate($dimanche, IntlDateFormatter::LONG, IntlDateFormatter::NONE)
);

$content = sprintf(
    "By Night Magazine édition #%d du %s au %s. Retrouvez chaque semaine les 3 meilleurs événements dans votre ville en une image !",
    $edition,
    formatDate($lundi, IntlDateFormatter::LONG, IntlDateFormatter::NONE),
    formatDate($dimanche, IntlDateFormatter::LONG, IntlDateFormatter::NONE)
);

if ($forceUpdate || $old_post_id !== $post_id) {
    // Création de la miniature
    $image = create_thumb(
        __DIR__ . '/template/template.jpg',
        $lundi,
        $dimanche,
        $edition,
        $sortedEvents
    );

    //Upload de la miniature dans le répertoire WP
    $infos = wp_upload_bits(
        sprintf('by-night-magazine-edition-%s.jpg', $edition),
        null,
        $image
    );

    //Association de la PJ avec l'article
    $attachment_id = upload(
        $infos['file'],
        $post_id, [
            'post_title'   => $title,
            'post_content' => $content,
            'post_excerpt' => $headline,
        ]
    );

    replace_post_meta($attachment_id, '_wp_attachment_image_alt', $title);
}

$datas = get_post($post_id);
$metas = get_post_meta($post_id);
$thumb = get_post($metas['_thumbnail_id'][0]);

//Update en base pour historique de la news
$success = $manager->postNews($news, $post_id, $headline, $content, mashsb_get_bitly_link($datas->guid), $thumb->guid);

if ($success) {
    echo "OK";
} else {
    echo "NOK";
}