<?php
get_header();

$excerpt_symbols_count = get_option(THEMEMAKERS_THEME_PREFIX . "excerpt_symbols_count");
if (!$excerpt_symbols_count) {
    $excerpt_symbols_count = 140;
}
$show_full_content = get_option(THEMEMAKERS_THEME_PREFIX . 'show_full_content');
?>
<!-- ************ - BEGIN Content Wrapper - ************** -->	
<div class="content-wrapper sbl">
    <header class="page-header">
        <h1 class="page-title"><?php
printf(__('Tag Archives: %s', THEMEMAKERS_THEME_FOLDER_NAME), '<span>' . single_tag_title('', false) . '</span>');
?></h1>

        <?php
        $tag_description = tag_description();
        if (!empty($tag_description))
            echo apply_filters('tag_archive_meta', '<div class="tag-archive-meta">' . $tag_description . '</div>');
        ?>
    </header>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article class="post-item">

                <?php if (has_post_thumbnail()) { ?>
                    <div class="post-thumb">
                        <a href="<?php the_permalink(); ?>"><img src="<?php echo ThemeMakersHelper::get_post_featured_image($post->ID, 721); ?>" alt="" class="add-border"></a>
                    </div><!--/ post-thumb-->
                <?php } ?>

                <div class="post-meta clearfix">
                    <div class="post-date"><?php _e('Date', THEMEMAKERS_THEME_FOLDER_NAME); ?>: <a href="<?php bloginfo('url'); ?>/?m=<?php the_time('Ym'); ?>"><?php the_date(); ?></a></div>
                    <div class="post-author"><?php _e('Author', THEMEMAKERS_THEME_FOLDER_NAME); ?>: <?php the_author_link(); ?></div>
                    <div class="post-tags"><?php the_tags(); ?></div>
                    <?php if (!$_REQUEST['disable_blog_comments']): ?>
                    <div class="post-comments-icon"><a href="<?php the_permalink(); ?>/#comments"><?php comments_number('0', '1', '%'); ?></a></div><!--/ post-comments-icon-->
                    <?php endif; ?>
                </div><!--/ post-meta-->

                <div class="entry">
                    <div class="post-title">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    </div><!--/ post-title-->
                    <?php if ($show_full_content) : ?>
                        <?php the_content(); ?>
                    <?php else: ?>

                        <?php
                        if ($excerpt_symbols_count) {
                            echo substr(get_the_excerpt(), 0, $excerpt_symbols_count) . " ...";
                        } else {
                            the_excerpt();
                        }
                        ?>
                    <?php endif; ?>
                </div><!--/ .entry-->
                <div class="clear"></div>

            </article><!--/ .post-item-->
        <?php endwhile; ?>

    <?php else : ?>

        <article id="post-0" class="post no-results not-found">
            <header class="entry-header">
                <h1 class="entry-title"><?php _e('Nothing Found', THEMEMAKERS_THEME_FOLDER_NAME); ?></h1>
            </header><!-- .entry-header -->

            <div class="entry-content">
                <p><?php _e('Sorry, but nothing matched your search criteria. Please try again with some different keywords.', THEMEMAKERS_THEME_FOLDER_NAME); ?></p>
                <?php get_search_form(); ?>
            </div><!-- .entry-content -->
        </article><!-- #post-0 -->
    <?php endif; ?>	
</div><!--/ content-wrapper-->
<!-- ************ - END Content Wrapper - ************** -->
<div class="pagenavi">
    <?php
    ThemeMakersHelper::pagenavi();
    wp_reset_query();
    ?>
</div><!--/ pagenavi -->
<!-- ************ - END Page navigation - ************** -->
<?php get_footer(); ?>

