<?php
defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
    the_post();
    ?>
    <main id="content" class="site-main" style="max-width:800px;margin:2em auto;padding:0 16px">
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>
        <?php
        foreach (EB_DEMO_BUCKETS as $name => $label) {
            $value = get_post_meta(get_the_ID(), $name, true);
            if ($value === '') {
                continue;
            }
            if ($name !== 'einleitung') {
                echo '<h2>' . esc_html($label) . '</h2>';
            }
            echo wp_kses_post(wpautop($value));
        }
        ?>
        <p><a class="elementor-button" href="#bewerben">Jetzt bewerben</a></p>
        <aside style="border-top:1px solid #ccc;margin-top:2em;padding-top:1em">
            <h3>Ihre Kontaktperson</h3>
            <p>Erika Musterfrau, Recruiting<br>E-Mail: personal@example.de</p>
        </aside>
    </main>
    <?php
endwhile;

get_footer();
