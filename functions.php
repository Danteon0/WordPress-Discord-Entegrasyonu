<?php

function send_to_discord($title, $author, $date, $content, $link, $image_url, $webhook_url, $author_avatar) {
    $embed = array(
        "title" => $title,
        "url" => $link,
        "description" => $content,
        "color" => hexdec("7289da"), //Embed' de olmasını istediğiniz renk kodunuz
        "footer" => array(
            "text" => "example.com telif hakkında tabidir",
        ),
        "image" => array(
            "url" => $image_url,
        ),
        "author" => array(
            "name" => $author,
            "icon_url" => $author_avatar,
        ),
        "timestamp" => $date,
    );

    $data = array(
        "content" => "Yeni Blog Yazısı Yayınlandı! @everyone",
        "embeds" => array($embed),
    );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($webhook_url, false, $context);

    if ($result === FALSE) {
        error_log('Discord webhook gönderiminde bir hata oluştu.');
    }
}

function discord_webhook_new_post($post_ID, $post) {
    // Daha önce bildirildiyse geri dön
    if (get_post_meta($post_ID, '_discord_notified', true)) {
        return;
    }

    // Yalnızca yayınlanan gönderiler için çalıştır
    if ($post->post_status !== 'publish' || $post->post_type !== 'post') {
        return;
    }

    $webhook_url = 'WEBHOOK_URL';
    $title = get_the_title($post_ID);
    $author = get_the_author_meta('display_name', $post->post_author);
    $author_avatar = get_avatar_url($post->post_author);
    $date = get_the_date('c', $post_ID);
    $content = wp_trim_words(get_the_content(null, false, $post_ID), 40, '...');
    $link = get_permalink($post_ID);
    $image_url = '';

    if (has_post_thumbnail($post_ID)) {
        $image_url = get_the_post_thumbnail_url($post_ID);
    }

    send_to_discord($title, $author, $date, $content, $link, $image_url, $webhook_url, $author_avatar);
    update_post_meta($post_ID, '_discord_notified', true);
}

function on_save_post($post_ID, $post, $update) {
    // Yalnızca yayınlanan gönderiler için çalıştır
    if ($post->post_status === 'publish' && $post->post_type === 'post') {
        // Kısa bir gecikme ekleyerek görselin tam yüklenmesini sağlayın
        wp_schedule_single_event(time() + 5, 'send_to_discord_event', array($post_ID, $post));
    }
}

add_action('send_to_discord_event', 'discord_webhook_new_post', 10, 2);
add_action('save_post', 'on_save_post', 10, 3);
