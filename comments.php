<?php
/**
 * Comments Template
 */

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area my-12">

    <?php if (have_comments()) : ?>
        <h2 class="text-h2 mb-6">
            <?php
            $count = get_comments_number();
            printf(
                esc_html(_n('%d Comment', '%d Comments', $count, 'intentflow')),
                $count
            );
            ?>
        </h2>

        <ol class="comment-list space-y-6 list-none pl-0">
            <?php
            wp_list_comments(array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 48,
                'callback'    => 'intentflow_comment_callback',
            ));
            ?>
        </ol>

        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
            <nav class="comment-navigation flex justify-between mt-8 text-small">
                <div><?php previous_comments_link(esc_html__('&larr; Older Comments', 'intentflow')); ?></div>
                <div><?php next_comments_link(esc_html__('Newer Comments &rarr;', 'intentflow')); ?></div>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments text-text-light text-body">
            <?php esc_html_e('Comments are closed.', 'intentflow'); ?>
        </p>
    <?php endif; ?>

    <?php
    $input_class = 'w-full px-4 py-3 rounded-lg border border-border bg-white text-body text-text-dark focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all';
    comment_form(array(
        'class_form'         => 'comment-form mt-8 space-y-4',
        'title_reply'        => __('Leave a Comment', 'intentflow'),
        'title_reply_before' => '<h3 class="text-h3 mb-4">',
        'title_reply_after'  => '</h3>',
        'fields'             => array(
            'author' => '<div class="comment-form-author"><label for="author" class="block text-small font-medium text-text-dark mb-1">' . __('Name', 'intentflow') . ' <span class="text-red-500">*</span></label><input id="author" name="author" type="text" required placeholder="' . esc_attr__('Your name', 'intentflow') . '" class="' . $input_class . '"></div>',
            'email'  => '<div class="comment-form-email"><label for="email" class="block text-small font-medium text-text-dark mb-1">' . __('Email', 'intentflow') . ' <span class="text-red-500">*</span></label><input id="email" name="email" type="email" required placeholder="' . esc_attr__('you@example.com', 'intentflow') . '" class="' . $input_class . '"></div>',
            'url'    => '<div class="comment-form-url"><label for="url" class="block text-small font-medium text-text-dark mb-1">' . __('Website', 'intentflow') . '</label><input id="url" name="url" type="url" placeholder="https://" class="' . $input_class . '"></div>',
        ),
        'comment_field'      => '<div class="comment-form-comment"><label for="comment" class="block text-small font-medium text-text-dark mb-1">' . __('Comment', 'intentflow') . ' <span class="text-red-500">*</span></label><textarea id="comment" name="comment" rows="5" required placeholder="' . esc_attr__('Share your thoughts...', 'intentflow') . '" class="' . $input_class . '"></textarea></div>',
        'submit_button'      => '<button type="submit" name="%1$s" id="%2$s" class="btn-primary">%4$s</button>',
        'submit_field'       => '<div class="form-submit mt-4">%1$s %2$s</div>',
    ));
    ?>

</div>

<?php
/**
 * Custom comment callback
 */
function intentflow_comment_callback($comment, $args, $depth) {
    $tag = $args['style'] === 'div' ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class('bg-surface rounded-xl p-5'); ?>>
        <article class="comment-body">
            <header class="comment-meta flex items-center gap-3 mb-3">
                <?php echo get_avatar($comment, 40, '', '', array('class' => 'rounded-full')); ?>
                <div>
                    <span class="comment-author text-small font-semibold text-text-dark">
                        <?php comment_author_link(); ?>
                    </span>
                    <time class="comment-date block text-xs text-text-light" datetime="<?php comment_time('c'); ?>">
                        <?php printf(esc_html__('%1$s at %2$s', 'intentflow'), get_comment_date(), get_comment_time()); ?>
                    </time>
                </div>
            </header>

            <?php if ($comment->comment_approved === '0') : ?>
                <p class="comment-awaiting text-small text-text-light italic mb-2">
                    <?php esc_html_e('Your comment is awaiting moderation.', 'intentflow'); ?>
                </p>
            <?php endif; ?>

            <div class="comment-content text-body text-text-dark">
                <?php comment_text(); ?>
            </div>

            <div class="comment-actions mt-3 text-small">
                <?php
                comment_reply_link(array_merge($args, array(
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                    'before'    => '<span class="text-primary hover:underline">',
                    'after'     => '</span>',
                )));
                ?>
            </div>
        </article>
    <?php
}
