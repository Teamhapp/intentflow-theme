<?php
/**
 * SEO: JSON-LD Schema, Open Graph, Breadcrumbs
 */

/**
 * Output JSON-LD schema for single posts
 */
function intentflow_schema_markup() {
    if (!is_singular('post')) return;

    $post_id   = get_the_ID();
    $title     = get_the_title();
    $excerpt   = get_the_excerpt();
    $permalink = get_permalink();
    $date      = get_the_date('c');
    $modified  = get_the_modified_date('c');
    $author    = get_the_author();
    $author_url = get_author_posts_url(get_the_author_meta('ID'));

    $schema = array(
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        'headline'         => $title,
        'description'      => wp_strip_all_tags($excerpt),
        'url'              => $permalink,
        'datePublished'    => $date,
        'dateModified'     => $modified,
        'author'           => array(
            '@type' => 'Person',
            'name'  => $author,
            'url'   => $author_url,
        ),
        'publisher'        => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url('/'),
        ),
        'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id'   => $permalink,
        ),
    );

    if (has_post_thumbnail($post_id)) {
        $img = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'hero');
        if ($img) {
            $schema['image'] = array(
                '@type'  => 'ImageObject',
                'url'    => $img[0],
                'width'  => $img[1],
                'height' => $img[2],
            );
        }
    }

    // Add content type from taxonomy
    $content_types = wp_get_post_terms($post_id, 'content_type', array('fields' => 'names'));
    if (!empty($content_types) && !is_wp_error($content_types)) {
        $schema['articleSection'] = implode(', ', $content_types);
    }

    $categories = get_the_category($post_id);
    if (!empty($categories)) {
        $schema['keywords'] = implode(', ', wp_list_pluck($categories, 'name'));
    }

    // Word count for reading time estimation
    $content = get_post_field('post_content', $post_id);
    $schema['wordCount'] = str_word_count(strip_tags($content));

    // Sanitize strings for valid JSON
    array_walk_recursive($schema, function (&$value) {
        if (is_string($value)) {
            $value = wp_strip_all_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
        }
    });

    $json = wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    if ($json) {
        printf('<script type="application/ld+json">%s</script>' . "\n", $json);
    }
}
add_action('wp_head', 'intentflow_schema_markup', 5);

/**
 * Output breadcrumb schema
 */
function intentflow_breadcrumb_schema() {
    if (is_front_page()) return;

    $items = array();
    $pos   = 1;

    $items[] = array(
        '@type'    => 'ListItem',
        'position' => $pos++,
        'name'     => __('Home', 'intentflow'),
        'item'     => home_url('/'),
    );

    if (is_singular('post')) {
        $categories = get_the_category();
        if (!empty($categories)) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => $categories[0]->name,
                'item'     => get_category_link($categories[0]->term_id),
            );
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );
    } elseif (is_category()) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos,
            'name'     => single_cat_title('', false),
            'item'     => get_category_link(get_queried_object_id()),
        );
    } elseif (is_search()) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos,
            'name'     => sprintf(__('Search: %s', 'intentflow'), get_search_query()),
        );
    }

    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    );

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}
add_action('wp_head', 'intentflow_breadcrumb_schema', 5);

/**
 * Open Graph meta tags
 */
function intentflow_og_meta() {
    // Skip if Yoast or Rank Math is active
    if (defined('WPSEO_VERSION') || class_exists('RankMath')) return;

    $og = array();

    if (is_singular()) {
        $og['og:type']        = 'article';
        $og['og:title']       = get_the_title();
        $og['og:description'] = wp_strip_all_tags(get_the_excerpt());
        $og['og:url']         = get_permalink();

        if (has_post_thumbnail()) {
            $img = wp_get_attachment_image_src(get_post_thumbnail_id(), 'hero');
            if ($img) {
                $og['og:image']        = $img[0];
                $og['og:image:width']  = $img[1];
                $og['og:image:height'] = $img[2];
            }
        }

        $og['article:published_time'] = get_the_date('c');
        $og['article:modified_time']  = get_the_modified_date('c');
        $og['article:author']         = get_the_author();
    } else {
        $og['og:type']        = 'website';
        $og['og:title']       = get_bloginfo('name');
        $og['og:description'] = get_bloginfo('description');
        $og['og:url']         = home_url('/');
    }

    $og['og:site_name'] = get_bloginfo('name');
    $og['og:locale']    = get_locale();

    // Twitter card
    $og['twitter:card'] = 'summary_large_image';

    foreach ($og as $property => $content) {
        if (empty($content)) continue;
        $attr = strpos($property, 'twitter:') === 0 ? 'name' : 'property';
        printf('<meta %s="%s" content="%s">' . "\n", $attr, esc_attr($property), esc_attr($content));
    }
}
add_action('wp_head', 'intentflow_og_meta', 5);

/**
 * Render visual breadcrumb navigation
 */
function intentflow_breadcrumbs() {
    if (is_front_page()) return;
    ?>
    <nav class="breadcrumbs mb-6" aria-label="<?php esc_attr_e('Breadcrumb', 'intentflow'); ?>">
        <ol class="flex items-center flex-wrap gap-1 text-small text-text-light">
            <li>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-primary no-underline">
                    <?php esc_html_e('Home', 'intentflow'); ?>
                </a>
            </li>

            <?php if (is_singular('post')) :
                $categories = get_the_category();
                if (!empty($categories)) :
            ?>
                <li class="before:content-['/'] before:mx-1 before:text-border">
                    <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"
                       class="hover:text-primary no-underline">
                        <?php echo esc_html($categories[0]->name); ?>
                    </a>
                </li>
            <?php endif; ?>
                <li class="before:content-['/'] before:mx-1 before:text-border text-text-dark font-medium truncate max-w-[200px]">
                    <?php the_title(); ?>
                </li>

            <?php elseif (is_category()) : ?>
                <li class="before:content-['/'] before:mx-1 before:text-border text-text-dark font-medium">
                    <?php single_cat_title(); ?>
                </li>

            <?php elseif (is_search()) : ?>
                <li class="before:content-['/'] before:mx-1 before:text-border text-text-dark font-medium">
                    <?php printf(esc_html__('Search: %s', 'intentflow'), get_search_query()); ?>
                </li>

            <?php elseif (is_tag()) : ?>
                <li class="before:content-['/'] before:mx-1 before:text-border text-text-dark font-medium">
                    <?php single_tag_title(); ?>
                </li>

            <?php elseif (is_404()) : ?>
                <li class="before:content-['/'] before:mx-1 before:text-border text-text-dark font-medium">
                    <?php esc_html_e('404', 'intentflow'); ?>
                </li>
            <?php endif; ?>
        </ol>
    </nav>
    <?php
}
