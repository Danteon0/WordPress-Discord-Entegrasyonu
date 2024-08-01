<!-- Sosyal Medya Bağlantıları -->
<p align="center">
  <a href="https://discord.com/invite/eWcNKXmsgw" target="_blank">
    <img src="https://img.shields.io/badge/Discord-%2300b0ff?style=for-the-badge&logo=discord&logoColor=white" alt="Discord" />
  </a>
  <a href="https://emirhankaya.net" target="_blank">
    <img src="https://img.shields.io/badge/Website-%23000000?style=for-the-badge&logo=google-chrome&logoColor=white" alt="Website" />
  </a>
  <a href="https://x.com/EmirhanKaya_41" target="_blank">
    <img src="https://img.shields.io/badge/Twitter-%231DA1F2?style=for-the-badge&logo=twitter&logoColor=white" alt="X" />
  </a>
</p>

[![Discord Presence](https://lanyard.cnrad.dev/api/496393095282294796)](https://discord.com/users/496393095282294796)

# 🤖 WordPress Discord Gönderi Entegrasyonu

Bu proje, WordPress sitenizde yayınlanan yeni blog gönderilerini otomatik olarak bir Discord kanalında paylaşmak için kullanılan bir entegrasyon örneğidir. Bu sayede, her yeni gönderi otomatik olarak belirlediğiniz Discord kanalına iletilir, böylece takipçileriniz güncel içeriklere anında erişebilirler.


## 📜 Ön Gereksinimler

Bu entegrasyonu kullanmak için aşağıdaki gereksinimlere sahip olmanız gerekir:

- **WordPress**: Bu proje, bir WordPress sitesinde çalışacak şekilde tasarlanmıştır. WordPress'in güncel bir sürümüne sahip olmalısınız.
- **Discord Hesabı**: Entegrasyonu gerçekleştirmek için bir Discord hesabınız olmalıdır. Ayrıca, göndereceğiniz mesajların görünmesini istediğiniz bir Discord sunucusuna da ihtiyacınız var.
- **Web Sunucusu**: WordPress sitenizin bir web sunucusunda barındırılması gerekir.

## 📁 Discord Webhook Oluşturma

Discord'da, WordPress gönderilerinizi otomatik olarak paylaşabilmek için bir webhook oluşturmanız gerekir. Webhook, Discord sunucunuza mesaj gönderebilmek için bir URL sağlar. İşte webhook oluşturma adımları:

1. **Discord Sunucusuna Giriş Yapın**: Discord uygulamanızı açın ve ilgili sunucuyu seçin.
2. **Webhook Oluşturun**: Sunucu ayarlarına gidin, "Integrations" (Entegrasyonlar) sekmesine tıklayın ve "Create Webhook" (Webhook Oluştur) seçeneğini seçin.
3. **Webhook URL'sini Kopyalayın**: Oluşturulan webhook için verilen URL'yi kopyalayın. Bu URL, WordPress entegrasyon kodunda kullanılacaktır.

## 📕 WordPress Entegrasyonu

Discord webhook'unuzu oluşturduktan sonra, WordPress sitenizde bu webhook'u kullanarak gönderi paylaşımı yapabilirsiniz. Aşağıdaki adımları takip ederek entegrasyonu gerçekleştirebilirsiniz:

1. **functions.php Dosyasını Güncelleyin**: WordPress tema dosyalarınızda bulunan `functions.php` dosyasına aşağıdaki kodu ekleyin.

## 📗 functions.php için Kod

Aşağıda, WordPress'te yeni gönderileri Discord kanalınıza otomatik olarak gönderecek bir PHP kodu örneği bulunmaktadır. Bu kod, WordPress gönderi gönderimlerini Discord'a entegre etmenizi sağlar.

```php
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
```

2. **Webhook URL’sini Güncelleyin:**
- `YOUR_DISCORD_WEBHOOK_URL` kısmını oluşturduğunuz webhook URL'si ile değiştirin.

## 📕 Mesaj Gönderme
Artık mesaj göndermeye hazırsınız! `send_to_discord` fonksiyonunu kullanarak istediğiniz yerden Discord kanalınıza anlık mesajlar gönderebilirsiniz.

```php
send_to_discord($title, $author, $date, $content, $link, $image_url, $webhook_url, $author_avatar);
```

## 📕 Dikkat Edilmesi Gerekenler
**Güvenlik:** Webhook URL’nizi kimseyle paylaşmayın.
**Hata Ayıklama:** Eğer mesaj gitmiyorsa, curl ayarlarınızı ve webhook URL’nizi kontrol edin. Daha fazla destek için discord sunucumuza gelebilirsiniz.

## ✉️ Yardım ve Destek
Bu entegrasyonla ilgili herhangi bir sorun yaşarsanız veya ek yardıma ihtiyacınız olursa, Discord sunucumuzdan veya GitHub Issues sayfasına giderek ve bir destek talebi oluşturabilirsiniz.

## ✒️ Katkıda Bulunma
Bu projeye katkıda bulunmak isterseniz, lütfen aşağıdaki adımları izleyin:

- Projeyi forklayın.
- Değişikliklerinizi yapın.
- Pull request gönderin.

## 📑 Lisans
Bu proje MIT Lisansı altında lisanslanmıştır. Daha fazla bilgi için Lisans Dosyasına göz atabilirsiniz.
