<?php
/*
Plugin Name: All Posts WordPress MU Widget
Plugin URI: http://wordpress-mu.ru/All-Posts-WordPress-MU-Widget/
Description: Widget
Author: Akinfiev Yura
Version: 1.0
Author URI: http://wordpress-mu.ru/
*/ 

function all_posts_get_last_post($count=5){
	global $wpdb,$table_prefix;
	
	$blogs = $wpdb->get_results('SELECT `blog_id` FROM `'.$table_prefix.'blogs` ORDER BY `last_updated` DESC LIMIT '.$count);
	
	$union=array();
	foreach($blogs as $blog){
		if ($blog->blog_id==1)
			$union[]='SELECT *, (@blog:='.$blog->blog_id.') AS blog_id FROM `'.$table_prefix.'posts` WHERE `post_status`="publish" AND `post_type`="post" ';
		else
			$union[]='SELECT *, (@blog:='.$blog->blog_id.') AS blog_id FROM `'.$table_prefix.$blog->blog_id.'_posts`  WHERE `post_status`="publish" AND `post_type`="post" ';
	}

	$query	=	implode(' UNION ',$union).' ORDER BY `post_date` DESC LIMIT '.$count;
		
	$posts	=	$wpdb->get_results($query);
		
	$out='';
	foreach($posts as $post){
		$out .= "<li><h3><a href='".$post->guid."' title='".$post->post_title."'>".$post->post_title."</a></h3></li>";
	}	
		
	return $out;
	
}


function all_posts_widget_meta($args) {
    extract($args, EXTR_SKIP);
    $options = get_option('all_posts_widget_meta');
    $title = empty($options['title']) ? 'All Posts' : $options['title'];    
    $limit = $options['limit'];    
?>
        <?php echo $before_widget . $before_title . $title . $after_title; ?>           
            <ul>
          <?php  echo all_posts_get_last_post($limit); ?> 
            </ul>
        <?php echo $after_widget; ?>
<?php
}

function widget_control() {
    $options = $newoptions = get_option('all_posts_widget_meta');
    if ( $_POST["all_posts_widget_meta-submit"] ) {
        $newoptions['title'] = strip_tags(stripslashes($_POST["all_posts_widget_meta-title"]));
        $newoptions['limit'] = strip_tags(stripslashes($_POST["all_posts_widget_meta-limit"]));
    }
    if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('all_posts_widget_meta', $options);
    }
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $limit = htmlspecialchars($options['limit'], ENT_QUOTES);
?>
    
	<p>
		<label for="all_posts_widget_meta-title"><?php _e('Title:'); ?> 
		<input class="widefat" id="all_posts_widget_meta-title" name="all_posts_widget_meta-title" type="text" value="<?php echo $title; ?>" /></label>
	</p>
 	<p>
		<label for="all_posts_widget_meta-limit"><?php _e('Количество записей '); ?> 
		<input class="widefat" id="all_posts_widget_meta-limit" name="all_posts_widget_meta-limit" type="text" value="<?php echo $limit; ?>"/></label>
	</p>   
	<input type="hidden" id="all_posts_widget_meta-submit" name="all_posts_widget_meta-submit" value="1" />
<?php
}

function widget_init() {
    register_sidebar_widget('All Posts WordPress MU Widget', 'all_posts_widget_meta');
    register_widget_control('All Posts WordPress MU Widget', 'widget_control');
}

add_action('init', 'widget_init');
?> 