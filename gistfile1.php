add_shortcode( 'dwqa-list-questions-with-taxonomy', 'dwqa_archive_question_shortcode' );
function dwqa_archive_question_shortcode( $atts ) {
    global $script_version, $dwqa_sript_vars;
    
    extract( shortcode_atts( array(
        'taxonomy_category' => '',//Use slug
        'taxonomy_tag' => '',//Use slug
    ), $atts, 'bartag' ) );
    
    ob_start( array( $this, 'sanitize_output' ) );
    ?>
        <div class="dwqa-container" >
            <div id="archive-question" class="dw-question">
                <div class="dwqa-list-question">
                    <div class="loading"></div>
                    <div class="dwqa-search">
                        <form action="" class="dwqa-search-form">
                            <input class="dwqa-search-input" placeholder="<?php _e('Search','dwqa') ?>">
                            <span class="dwqa-search-submit fa fa-search show"></span>
                            <span class="dwqa-search-loading dwqa-hide"></span>
                            <span class="dwqa-search-clear fa fa-times dwqa-hide"></span>
                        </form>
                    </div>
                    <div class="filter-bar">
                        <?php wp_nonce_field( '_dwqa_filter_nonce', '_filter_wpnonce', false ); ?>
                        <?php  
                            global $dwqa_options;
                            $submit_question_link = get_permalink( $dwqa_options['pages']['submit-question'] );
                        ?>
                        <?php if( $dwqa_options['pages']['submit-question'] && $submit_question_link ) { ?>
                        <a href="<?php echo $submit_question_link ?>" class="dwqa-btn dwqa-btn-success"><?php _e('Ask a question','dwqa') ?></a>
                        <?php } ?>
                        <div class="filter">
                            <li class="status">
                                <?php  
                                    $selected = isset($_GET['status']) ? $_GET['status'] : 'all';
                                ?>
                                <ul>
                                    <li><?php _e('Status:') ?></li>
                                    <li class="<?php echo $selected == 'all' ? 'active' : ''; ?> status-all" data-type="all">
                                        <a href="#"><?php _e( 'All','dwqa' ); ?></a>
                                    </li>
 
                                    <li class="<?php echo $selected == 'open' ? 'active' : ''; ?> status-open" data-type="open">
                                        <a href="#"><?php echo current_user_can( 'edit_posts' ) ? __( 'Need Answer','dwqa' ) : __( 'Open','dwqa' ); ?></a>
                                    </li>
                                    <li class="<?php echo $selected == 'replied' ? 'active' : ''; ?> status-replied" data-type="replied">
                                        <a href="#"><?php _e( 'Answered','dwqa' ); ?></a>
                                    </li>
                                    <li class="<?php echo $selected == 'resolved' ? 'active' : ''; ?> status-resolved" data-type="resolved">
                                        <a href="#"><?php _e( 'Resolved','dwqa' ); ?></a>
                                    </li>
                                    <li class="<?php echo $selected == 'closed' ? 'active' : ''; ?> status-closed" data-type="closed">
                                        <a href="#"><?php _e( 'Closed','dwqa' ); ?></a>
                                    </li>
                                    <?php if( dwqa_current_user_can( 'edit_question' ) ) : ?>
                                    <li class="<?php echo $selected == 'overdue' ? 'active' : ''; ?> status-overdue" data-type="overdue"><a href="#"><?php _e('Overdue','dwqa') ?></a></li>
                                    <li class="<?php echo $selected == 'pending-review' ? 'active' : ''; ?> status-pending-review" data-type="pending-review"><a href="#"><?php _e('Queue','dwqa') ?></a></li>
 
                                    <?php endif; ?>
                                </ul>
                            </li>
                        </div>
                        <div class="filter sort-by">
                                <div class="filter-by-category select">
                                    <?php 
                                        $selected = false;
                                        if( $taxonomy_category ) {
                                            $term = get_term_by( 'slug', $taxonomy_category, 'dwqa-question_category' );
                                            $selected = $term->term_id;
                                        }
                                        $selected_label = __('Select a category','dwqa');
                                        if( $selected  && $selected != 'all' ) {
                                            $selected_term = get_term_by( 'id', $selected, 'dwqa-question_category' );
                                            $selected_label = $selected_term->name;
                                        }
                                    ?>
                                    <span class="current-select"><?php echo $selected_label; ?></span>
                                    <ul id="dwqa-filter-by-category" class="category-list" data-selected="<?php echo $selected; ?>">
                                    <?php  
                                        wp_list_categories( array(
                                            'show_option_all'   =>  __('All','dwqa'),
                                            'show_option_none'  => __('Empty','dwqa'),
                                            'taxonomy'          => 'dwqa-question_category',
                                            'hide_empty'        => 0,
                                            'show_count'        => 0,
                                            'title_li'          => '',
                                            'walker'            => new Walker_Category_DWQA
                                        ) );
                                    ?>  
                                    </ul>
                                </div>
                            <?php 
                                $tag_field = '';
                                if( $taxonomy_tag ) {
                                    $selected = false;
                                    
                                    $term = get_term_by( 'slug', $taxonomy_tag, 'dwqa-question_tag');
                                    $selected = $term->term_id;
                                    if( isset( $selected )  &&  $selected != 'all' ) {
                                        $tag_field = '<input type="hidden" name="dwqa-filter-by-tags" id="dwqa-filter-by-tags" value="'.$selected.'" >';
                                    }
                                } 
                                $tag_field = apply_filters( 'dwqa_filter_bar', $tag_field ); 
                                echo $tag_field;
                            ?>
                            <ul class="order">
                                <li class="most-reads <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'views' ? 'active' : ''; ?>"  data-type="views" >
                                    <span><?php _e('View', 'dwqa') ?></span> <i class="fa fa-sort <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'views' ? 'icon-sort-up' : ''; ?>"></i>
                                </li>
                                <li class="most-answers <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'answers' ? 'active' : ''; ?>" data-type="answers" >
                                    <span href="#"><?php _e('Answer', 'dwqa') ?></span> <i class="fa fa-sort <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'answers' ? 'fa-sort-up' : ''; ?>"></i>
                                </li>
                                <li class="most-votes <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'votes' ? 'active' : ''; ?>" data-type="votes" >
                                    <span><?php _e('Vote', 'dwqa') ?></span> <i class="fa fa-sort <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'votes' ? 'fa-sort-up' : ''; ?>"></i>
                                </li>
                            </ul>
                            <?php  
                                global $dwqa_general_settings;
                                $posts_per_page = isset($dwqa_general_settings['posts-per-page']) ?  $dwqa_general_settings['posts-per-page'] : get_query_var( 'posts_per_page' );
                            ?>
                            <input type="hidden" id="dwqa_filter_posts_per_page" name="posts_per_page" value="<?php echo $posts_per_page; ?>">
                        </div>
                    </div>
                    
                    <?php do_action( 'dwqa-before-question-list' ); ?>
 
                    <?php  do_action('dwqa-prepare-archive-posts');?>
                    <?php if ( have_posts() ) : ?>
                    <div class="questions-list">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php dwqa_load_template( 'content', 'question' ); ?>
                    <?php endwhile; ?>
                    </div>
                    <div class="archive-question-footer">
                    <?php 
                        if( $taxonomy == 'dwqa-question_category' ) { 
                            $args = array(
                                'post_type' => 'dwqa-question',
                                'posts_per_page'    =>  -1,
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => $taxonomy,
                                        'field' => 'slug',
                                        'terms' => $term_name
                                    )
                                )
                            );
                            $query = new WP_Query( $args );
                            $total = $query->post_count;
                        } else if( 'dwqa-question_tag' == $taxonomy ) {
 
                            $args = array(
                                'post_type' => 'dwqa-question',
                                'posts_per_page'    =>  -1,
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => $taxonomy,
                                        'field' => 'slug',
                                        'terms' => $term_name
                                    )
                                )
                            );
                            $query = new WP_Query( $args );
                            $total = $query->post_count;
                        } else {
                            $total = wp_count_posts( 'dwqa-question' );
                            $total = $total->publish;
                        }
 
                        $number_questions = $total;
 
                        $number = get_query_var( 'posts_per_page' );
 
                        $pages = ceil( $number_questions / $number );
                        
                        if( $pages > 1 ) {
                    ?>
                        <div class="pagination">
                            <ul data-pages="<?php echo $pages; ?>" >
                                <?php  
                                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                                    $i = 0;
                                    echo '<li class="prev';
                                    if( $i == 0 ) {
                                        echo ' dwqa-hide';
                                    }
                                    echo '"><a href="javascript:void()">'.__('Prev', 'dwqa').'</a></li>';
                                    $link = get_post_type_archive_link( 'dwqa-question' );
                                    $start = $paged - 2;
                                    $end = $paged + 2;
 
                                    if( $end > $pages ) {
                                        $end = $pages;
                                        $start = $pages -  5;
                                    }
 
                                    if( $start < 1 ) {
                                        $start = 1;
                                        $end = 5;
                                        if( $end > $pages ) {
                                            $end = $pages;
                                        }
                                    }
                                    if( $start > 1 ) {
                                        echo '<li><a href="'.add_query_arg('paged',1,$link).'">1</a></li><li class="dot"><span>...</span></li>';
                                    }
                                    for ($i=$start; $i <= $end; $i++) { 
                                        $current = $i == $paged ? 'class="active"' : '';
                                        if( $i == 1 ) {
                                            echo '<li '.$current.'><a href="'.$link.'">'.$i.'</a></li>';
                                        }else{
                                            echo '<li '.$current.'><a href="'.add_query_arg('paged', $i, $link).'">'.$i.'</a></li>';
                                        }
                                    }
 
                                    if( $i - 1 < $pages ) {
                                        echo '<li class="dot"><span>...</span></li><li><a href="'.add_query_arg('paged',$pages,$link).'">'.$pages.'</a></li>';
                                    }
                                    echo '<li class="next';
                                    if( $paged == $pages ) {
                                        echo ' dwqa-hide';
                                    }
                                    echo '"><a href="javascript:void()">'.__('Next', 'dwqa') .'</a></li>';
 
                                ?>
                            </ul>
                        </div>
                        <?php } ?>
                        <?php if( $dwqa_options['pages']['submit-question'] && $submit_question_link ) { ?>
                        <a href="<?php echo $submit_question_link ?>" class="dwqa-btn dwqa-btn-success"><?php _e('Ask a question','dwqa') ?></a>
                        <?php } ?>
                    </div>
                    <?php else: ?>
                        <?php
                            if( ! dwqa_current_user_can('read_question') ) {
                                echo '<div class="alert">'.__('You do not have permission to view questions','dwqa').'</div>';
                            }
                            echo '<p class="not-found">';
                             _e('Sorry, but nothing matched your filter.', 'dwqa' );
                             if( is_user_logged_in() ) {
                                global $dwqa_options;
                                if( isset($dwqa_options['pages']['submit-question']) ) {
                                    
                                    $submit_link = get_permalink( $dwqa_options['pages']['submit-question'] );
                                    if( $submit_link ) {
                                        printf('%s <a href="">%s</a>',
                                            __('You can ask question','dwqa'),
                                            $submit_link,
                                            __('here','dwqa')
                                        );
                                    }
                                }
                             } else {
                                printf('%s <a href="%s">%s</a>',
                                    __('Please','dwqa'),
                                    wp_login_url( get_post_type_archive_link( 'dwqa-question' ) ),
                                    __('Login','dwqa')
                                );
 
                                $register_link = wp_register('', '',false);
                                if( ! empty($register_link) && $register_link  ) {
                                    echo __(' or','dwqa').' '.$register_link;
                                }
                                _e(' to submit question.','dwqa');
                                wp_login_form();
                             }
 
                            echo  '</p>';
                        ?>
                    <?php endif; ?>
                    <?php do_action( 'dwqa-after-archive-posts' ); ?>
                </div>
            </div>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        wp_enqueue_script( 'dwqa-questions-list', DWQA_URI . 'inc/templates/default/assets/js/dwqa-questions-list.js', array( 'jquery' ), $script_version, true );
        wp_localize_script( 'dwqa-questions-list', 'dwqa', $dwqa_sript_vars );
        return $html;
    }
