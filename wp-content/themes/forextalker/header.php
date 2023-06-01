<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Newsphere
 */

?>
    <!doctype html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
		<script data-ad-client="ca-pub-9027026925759613" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-1HLSZ3K91T"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-1HLSZ3K91T');
        </script>


         <!-- Yandex.Metrika counter -->
         <script type="text/javascript" >
           var yaParams = {ipaddress: "<? echo $_SERVER['REMOTE_ADDR']; ?>"};
           (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
           m[i].l=1*new Date();
           for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
           k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
           (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
        
           ym(92799078, "init", {
                params:window.yaParams,
                clickmap:true,
                trackLinks:true,
                accurateTrackBounce:true,
                webvisor:true
           });
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/92799078" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
        
        <!-- Hotjar Tracking Code for https://forextalker.com -->
        <script>
            (function(h,o,t,j,a,r){
                h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
                h._hjSettings={hjid:3413657,hjsv:6};
                a=o.getElementsByTagName('head')[0];
                r=o.createElement('script');r.async=1;
                r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
                a.appendChild(r);
            })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
        </script>
        <?php wp_head(); ?>
    </head>

<body <?php body_class(); ?>>

<?php
$enable_preloader = newsphere_get_option('enable_site_preloader');
if (1 == $enable_preloader):
    ?>
    <div id="af-preloader">
        <div class="af-preloader-wrap">
            <div class="af-sp af-sp-wave">
            </div>
        </div>
    </div>
<?php endif; ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'newsphere'); ?></a>

<?php

do_action('newsphere_action_header_section');

do_action('newsphere_action_front_page_main_section');

do_action('newsphere_action_banner_featured_section');


if (is_singular('post')) {
    $single_post_featured_image_view = newsphere_get_option('single_post_featured_image_view');
    if ($single_post_featured_image_view == 'full') {
        do_action('newsphere_action_single_header');
    }
}
?>


    <div id="content" class="container-wrapper">
<?php

do_action('newsphere_action_get_breadcrumb');