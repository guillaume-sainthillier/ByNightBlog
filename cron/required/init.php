<?php

require_once __DIR__ . '/../../wp-load.php';

define('FONT_PATH', __DIR__ . '/../fonts');

function get_container() {
    require_once sprintf('%s/app/autoload.php', WP_SYMFONY_PATH);
    require_once sprintf('%s/app/AppKernel.php', WP_SYMFONY_PATH);

    $kernel = new AppKernel(WP_SYMFONY_ENVIRONMENT, WP_SYMFONY_DEBUG);
    $kernel->boot();

    return $kernel->getContainer();
}

function formatDate(\DateTime $date, $dateFormat, $timeFormat) {
    $formatter = IntlDateFormatter::create(null, $dateFormat, $timeFormat);
    return $formatter->format($date->getTimestamp());
}

function sort_events(array $events) {
    $bestEvents = [];
    foreach ($events as $site => $eventss) {
        $bestEvents = array_merge($bestEvents, $eventss);
    }
    $rawEvents  = $bestEvents;
    $bestEvents = array_map(function (TBN\AgendaBundle\Entity\Agenda $event) {
        return $event->getFbParticipations() + $event->getFbInterets();
    }, $bestEvents);

    arsort($bestEvents);
    $sortedEvents = [];
    foreach ($bestEvents as $i => $event) {
        $event = $rawEvents[$i];
        if (!isset($sortedEvents[$event->getSite()->getNom()])) {
            $sortedEvents[$event->getSite()->getNom()] = [];
        }

        $sortedEvents[$event->getSite()->getNom()][] = $event;
    }

    return $sortedEvents;
}

function replace_post_meta($post_id, $name, $value) {
    if (!add_post_meta($post_id, $name, $value, true)) {
        update_post_meta($post_id, $name, $value);
    }
}

function upload($filename, $post_id, array $params = []) {
    // Check the type of file. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype(basename($filename), null);

    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();

    // Prepare an array of post data for the attachment.
    $attachment = array_merge(array(
        'guid'           => $wp_upload_dir['url'] . '/' . basename($filename),
        'post_mime_type' => $filetype['type'],
        'post_status'    => 'inherit',
    ), $params);
    // Insert the attachment.
    $attach_id = wp_insert_attachment($attachment, $filename, $post_id);

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
    wp_update_attachment_metadata($attach_id, $attach_data);

    set_post_thumbnail($post_id, $attach_id);

    return $attach_id;
}

function create_thumb($filename, \DateTime $from, \DateTime $to, $numeroEdition, array $allEvents) {
    require_once __DIR__ . '/../src/NewsGenerator.php';

    $generator = new NewsGenerator($filename);
    $generator->setDate($from, $to);
    $generator->setNumeroEdition($numeroEdition);

    foreach ($allEvents as $site => $events) {
        $generator->addSection($site, $events);
    }

    // header('Content-Type: image/jpeg');
    return $generator->render();
}