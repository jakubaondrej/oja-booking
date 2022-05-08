<?php
ojabooking_booking_enqueue();
get_header();
ojabooking_get_booking_style();
?>
<div class="container rounded my-md-2 p-sm-3 p-md-5 bg-content shadow rounded">
        <?php if (have_posts()) :
                $ojabooking_price_categories = get_terms(array(
                        'taxonomy' => 'ojabooking_price_categories',
                        'hide_empty' => false,
                ));
                while (have_posts()) : the_post();
                        $id = get_the_ID();
                        $is_periodical = ojabooking_is_event_periodical($id);

                        $languages = wp_get_post_terms($id, 'ojabooking_languages');
                        $language_names = array();
                        if ($languages) {
                                foreach ($languages as $language) {
                                        $language_names[] = $language->name;
                                }
                        }

                        $prices =  get_post_meta($post->ID, 'ojabooking_price_category', true);

                        $max_group = get_post_meta($id, 'ojabooking_group_size', true);

                        if (has_post_thumbnail()) : ?>
                                <div class="wrap-text-around-image-left p-3 pe-sm-5 pb-sm-5">
                                        <div class="oja-image" style="--img: url(<?php the_post_thumbnail_url(); ?>); --width-image:<?php echo get_option('thumbnail_size_w'); ?>px; --height-image:<?php echo get_option('thumbnail_size_h'); ?>px;"></div>

                                        <!--div class="image-blurred-edge" style="background-image: url('<?php the_post_thumbnail_url(); ?>');"></!--div-->
                                        <?php //the_post_thumbnail('thumbnail', array('class' => 'rounded-3 image-shadow')); 
                                        ?>
                                </div>
                        <?php endif; ?>
                        <?php the_title('<h1>', '</h1>'); ?>
                        <table class="table w-auto mb-3">
                                <tbody>
                                        <?php if ($languages) : ?>
                                                <tr>
                                                        <th scope="row"><?php _e('Languages', 'ojabooking'); ?></th>
                                                        <td class="ps-3"><?php echo implode(", ", $language_names); ?></td>
                                                </tr>
                                        <?php endif;
                                        if ($max_group) : ?>
                                                <tr>
                                                        <th scope="row"><?php _e('Maximum group size', 'ojabooking'); ?></th>
                                                        <td class="ps-3"><?php echo sprintf(__('%d people', 'ojabooking'), $max_group); ?></td>
                                                </tr>
                                        <?php endif; ?>
                                        <?php if ($is_periodical) :
                                                $ojabooking_start_term = get_post_meta($id, 'ojabooking_start_term', true);
                                                $ojabooking_end_term = get_post_meta($id, 'ojabooking_end_term', true);
                                                $term_duration = IsNullOrEmptyString( $ojabooking_start_term)?"":  __("From",'ojabooking') . " ". ojabooking_get_local_date_time($ojabooking_start_term) . " ";
                                                $term_duration .= IsNullOrEmptyString( $ojabooking_end_term)?"": __("to",'ojabooking') . " " . ojabooking_get_local_date_time($ojabooking_end_term);
                                        ?>
                                                <tr>
                                                        <th scope="row"><?php _e('Term', 'ojabooking'); ?></th>
                                                        <td class="ps-3"><?php echo $term_duration; ?></td>
                                                </tr>
                                        <?php else :
                                                $event_term = get_post_meta($id, 'ojabooking_the_term', true);
        ?>
                                                <tr>
                                                        <th scope="row"><?php _e('Term', 'ojabooking'); ?></th>
                                                        <td class="ps-3"><?php echo ojabooking_get_local_date_time($event_term); ?></td>
                                                </tr>
                                        <?php endif; ?>

                                </tbody>
                        </table>
                        <?php wp_kses_post(the_content()); ?>
                        <?php $booking_page_id = (int)get_option('ojabooking_booking_page');
                        ?>
                        <h3><?php esc_html_e('Tickets','ojabooking');?></h3>
                        <?php 
                        $next_term = $is_periodical ? ojabooking_get_periodical_event_next_term($id): date("Y-m-d",strtotime($event_term));
                        if($next_term ): ?>
                        <form id="create-event-reservation" action="<?php echo esc_url(get_page_link($booking_page_id)); ?>">
                                <input type="hidden" name="event_id" value="<?php echo get_the_ID(); ?>">
                                <input type="hidden" name="date" value="<?php echo esc_attr($next_term); ?>">
                                <?php
                                $use_languages = get_option('ojabooking_use_booking_languages', 0);
                                if ($use_languages && $languages) :
                                        if (count($languages) > 1) : ?>
                                                <div class="input-group" title="<?php _e('Language', 'ojabooking'); ?>">
                                                        <label class="input-group-text" for="booking-language"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-translate" viewBox="0 0 16 16">
                                                                        <path d="M4.545 6.714 4.11 8H3l1.862-5h1.284L8 8H6.833l-.435-1.286H4.545zm1.634-.736L5.5 3.956h-.049l-.679 2.022H6.18z" />
                                                                        <path d="M0 2a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v3h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3H2a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2zm7.138 9.995c.193.301.402.583.63.846-.748.575-1.673 1.001-2.768 1.292.178.217.451.635.555.867 1.125-.359 2.08-.844 2.886-1.494.777.665 1.739 1.165 2.93 1.472.133-.254.414-.673.629-.89-1.125-.253-2.057-.694-2.82-1.284.681-.747 1.222-1.651 1.621-2.757H14V8h-3v1.047h.765c-.318.844-.74 1.546-1.272 2.13a6.066 6.066 0 0 1-.415-.492 1.988 1.988 0 0 1-.94.31z" />
                                                                </svg></label>
                                                        <select class="form-select" id="booking-language" name="booking-language">
                                                                <?php foreach ($languages as $language) : ?>
                                                                        <option value="<?php echo esc_attr($language->term_id); ?>" <?php selected($book_lang, $language->term_id); ?>><?php echo esc_textarea($language->name); ?></option>
                                                                <?php endforeach; ?>
                                                        </select>
                                                </div>
                                        <?php else : ?>
                                                <input type="hidden" name="booking-language" value="<?php echo esc_attr($languages[0]->term_id); ?>">
                                        <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($prices) : ?>
                                        <table class="table w-auto">
                                                <thead>
                                                        <tr>
                                                                <th scope="col"><?php _e('Category', 'ojabooking'); ?></th>
                                                                <th scope="col"><?php _e('Count', 'ojabooking'); ?></th>
                                                                <th scope="col"><?php _e('Price', 'ojabooking'); ?></th>
                                                        </tr>
                                                </thead>
                                                <tbody>
                                                        <?php foreach ($ojabooking_price_categories as $category) : ?>
                                                                <tr>
                                                                        <th scope="row"><?php echo esc_textarea($category->name); ?></th>
                                                                        <td>
                                                                                <input type="number" class="group-count" name="group[<?php echo esc_attr($category->term_id); ?>]" value="0" price="<?php echo esc_attr($prices[$category->term_id]); ?>" min="0" max="100" size="3">
                                                                        </td>
                                                                        <td class="price-category"></td>
                                                                </tr>
                                                        <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                        <th scope="row"><?php _e("Summary", 'ojabooking'); ?></th>
                                                        <td></td>
                                                        <td class="price-category"></td>
                                                </tfoot>
                                        </table>
                                <?php endif; ?>
                                <button class="m-2 btn btn-primary"><?php _e('Book now', 'ojabooking'); ?></button>

                        </form>

                        <?php
                        endif;
                        wp_link_pages(
                                array(
                                        'before'   => '<nav class="page-links" aria-label="' . esc_attr__('Page') . '">',
                                        'after'    => '</nav>',
                                        /* translators: %: Page number. */
                                        'pagelink' => esc_html__('Page %'),
                                )
                        );
                        ?>
        <?php endwhile;
        else :
                echo '<p>Neboli nájdené žiadne výsledky hľadania!</p>';
        endif;
        ?>
</div>

<?php get_footer(); ?>