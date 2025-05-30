<?php
/*
Template Name: 读者墙
*/

$year = 2;
$sql2 ="SELECT friend_link_url, friend_link_name, friend_link_contact FROM friend_link ORDER BY friend_link_name DESC LIMIT 0, 100";

$friend_links = $wpdb->get_results($sql2);
get_header();
?>

<div id="page" class="container mt20">
    <?php get_template_part('templates/box', 'global-top') ?>
    <?php echo pk_breadcrumbs(); while (have_posts()):the_post();?>
        <div id="page-reads">
            <div id="page-<?php the_ID() ?>" class="row row-cols-1">
                <div id="posts" class="col-lg-<?php pk_hide_sidebar_out('12','8') ?> col-md-12 <?php pk_open_box_animated('animated fadeInLeft') ?> ">
                    <div class="p-block puock-text">
                        <h2 class="t-lg"><?php the_title() ?></h2>
                        <?php if(!empty(get_the_content())): ?>
                            <div class="mt20 <?php get_entry_content_class() ?>">
                                <?php the_content() ?>
                            </div>
                        <?php endif; ?>
                        <div class="mt20 row pd-links">
                            <?php foreach ($friend_links as $link): ?>
                                <div class="col col-6 col-md-4 col-lg-3 pl-0">
                                    <div class="p-2 text-truncate text-nowrap">
                                        <a href="<?php echo $link->friend_link_url; ?>"
                                            <?php echo empty($link->friend_link_url) ? '':'target="_blank"' ?> rel="nofollow">
                                            <img data-bs-toggle="tooltip" <?php echo pk_get_lazy_img_info(get_avatar_url($link->friend_link_contact),'md-avatar') ?>
                                                 title="<?php echo $link->friend_link_name?>" alt="<?php echo $link->friend_link_name?>">
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach;wp_reset_postdata() ?>
                        </div>
                    </div>
                    <?php comments_template() ?>
                </div>
                <?php get_sidebar() ?>
            </div>
        </div>
    <?php endwhile; ?>
    <?php get_template_part('templates/box', 'global-bottom') ?>
</div>

<?php get_footer() ?>
