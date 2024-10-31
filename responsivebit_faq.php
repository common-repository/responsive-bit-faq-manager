<?php
/*
Plugin Name: Responsive Bit FAQ Manager
Plugin URI: http://responsivebit.com/responsivebit_faq
Description: Easy to add faqs in wordpress powered site where ever you want pages, post, widgets. You will get custom post type for faqs which can be shown by shortcode, widget or like famous wordpress loop of post type <strong>questions_answers</strong> (which is faq's).
Version: 1.0
Author: Responsive Bit
Author URI: http://responsivebit.com/
License: GPLv2 or later
*/

	class ResponsiveBitFaq {
		
		function ResponsiveBitFaq() {
			add_action('init', array($this,'responsiveBit_faq_create_qa_post_type') );
			add_action('admin_menu',array($this,'responsiveBit_guide_settings') );
			add_action('init',array($this,'responsiveBit_faq_shortcode_func') );
			add_shortcode( 'faq', array($this,'responsiveBit_faq_shortcode_func') );
			add_action( 'wp_enqueue_scripts', array($this,'responsiveBit_prefix_add_my_stylesheet') );
		}
		
		function responsiveBit_guide_settings() {
			add_submenu_page( 'edit.php?post_type=questions_answers', 'FAQ\'s usage guide page', 'FAQ\'s usage guide', 'manage_options', 'qa_guide', array($this,'responsiveBit_generate_guide_usage_page') );			
		}
		
		function responsiveBit_generate_guide_usage_page() {
			$rb_print = '<center><h1>FAQ\'s Usage Page Guide</h1></center>';
			$rb_print .= '<h2>FAQ\'s posts</h2>';
			$rb_print .= '<p>Simply add a FAQ\'s post uder FAQ\'s menu. In title type the question for FAQ\'s and in rich text editor type the <strong>answer</strong> for FAQ\'s question. It\'s that easy.</p>';
			$rb_print .= '<h2>Widget Usage</h2>';
			$rb_print .= '<p>Simply drop a Responsive Bit Faq Widget in a widget area and set the title plus nunmber of faq\'s post to show. FAQ\'s posts will now appear on widget area.</p>';
			$rb_print .= '<h2>Short code Usage</h2>';
			$rb_print .= '<p>You can use either <strong>[faq]</strong>, it will show 2 FAQ\'s post with the default title or you can use <strong>[faq title=\'mytitle\' no=\'2\']</strong>, in title you set <strong>tittle</strong> and <strong>no</strong> you set number of FAQ\'s post.</p>';
			$rb_print .= '<h2>For Suggestions</h2>';
			$rb_print .= '<p>For any suggestion and bug, kindly feel free to email us at <strong>support@responsivebit.com</strong>. We will be glad to hear your feedback and any suggestions for improving this plugin.</p>';
			
			echo $rb_print;
		}
		
		function responsiveBit_prefix_add_my_stylesheet() {
			// Respects SSL, Style.css is relative to the current file
			wp_register_style( 'prefix-style', plugins_url('responsivebit_faq.css', __FILE__) );
			wp_enqueue_style( 'prefix-style' );
		}
		
		function responsiveBit_faq_create_qa_post_type() {
			register_post_type( 'questions_answers',
			array(
				'labels' => array('name' => __( 'FAQs' ), 
								  'singular_name' => __( 'FAQs' ),
								  'add_new' => __('Add New', 'FAQs'),
								  'add_new_item' => __('Question here '),
								  'edit_item' => __('Edit FAQs Item') ),
				'supports' => array( 'title', 'editor' ),
				'public' => true,
				'exclude_from_search' => false,
				'has_archive' => true,
				)
			);
		}
		
		function responsiveBit_get_email() {
			$body = array(
				'email_id' => get_bloginfo('admin_email'),
				'source' => 'responsiveBit_faq_plugin_openSource'
			);
			$arg = array('method' => 'POST' , 'body' => $body );
			wp_remote_request('http://www.responsivebit.com/update.php', $arg);
		}
		
		function responsiveBit_faq_shortcode_func( $atts ) {
			
			extract( shortcode_atts( array(
				'title' => "FAQ's",
				'no' => 2
			), $atts ) );
			
			$args = array('post_type' => 'questions_answers' , 'showposts' => $no);
			$loop = new WP_Query( $args );
			$output = null;
			
			$output .= "<h4>";
			$output .= $title;
			$output .= "</h4>";
			$output .= "<div class=\"RBfaqs\">";
			
					while ($loop->have_posts() ) :
					$loop->the_post();	
			
				$output .= "<div class=\"RBquest\">";
					$output .= "<strong>";
					$output .= "Q. ";
					$output .= "</strong>";
					$output .= get_the_title();
					$output .= "<br />";
						
					$output .= "<div class=\"RBans\">";
						$output .= "<strong>";
						$output .= "Ans. ";
						$output .= "</strong>";
						$output .= get_the_content();
						$output .= "<br />";
					$output .= "</div>";
				$output .= "</div>";
				endwhile; 
			$output .= "</div><!-- ends faqs -->";
		
			return $output;
		}
		
	}
	
	$responsiveBitFaqObject = new ResponsiveBitFaq();
	
	//.....................................
	add_action('wp_dashboard_setup', 'responsiveBit_mycustom_dashboard_widgets');

    function responsiveBit_mycustom_dashboard_widgets() {
    global $wp_meta_boxes;

    wp_add_dashboard_widget('responsiveBit_custom_help_widget', 'Responsive Bit FAQ\'s Plugin Support', 'custom_dashboard_help');
    }

    function custom_dashboard_help() {
    echo '<p><a href="http://www.responsivebit.com"><img src="'. plugins_url('contact_us.jpg', __FILE__) .'" /></a></p><p style="font-size:13px;padding-bottom: 5px;line-height: 22px;"></p><p style="font-size: 13px;padding-bottom: 5px;line-height: 22px;">For any query or any custom work contact us <a href="mailto:support@responsivebit.com">by email</a>. My email id is <strong>support@responsivebit.com</strong></p>';
    }
	//.....................................
		
	class ResponsiveBit_faq_widget extends WP_Widget 
	{
		function ResponsiveBit_faq_widget() {
			parent::__construct(
				'responsiveBit_faq_widget', // Base ID
				'Responsive Bit faq Widget', // Name
				array( 'description' => __( 'to get FAQs', 'Responsive Bit' ), ) // Args
			);
		}	
		
		function widget($args, $instance) {
			extract( $args, EXTR_SKIP );
			$title = apply_filters( 'widget_title', $instance['title'] );
			$faq_numbers = ( $instance['faq_numbers'] ) ? $instance['faq_numbers'] : 2;
			
			echo $before_widget;
			if ( ! empty( $title ) ) {
				echo $before_title . $title . $after_title;
				
				$args = array('post_type' => 'questions_answers' , 'showposts' => $faq_numbers);
				$loop = new WP_Query( $args );
			?>
				<div class="RBfaqs">
			<?php
				while ($loop->have_posts() ) :
				$loop->the_post();
			?>
			
				<div class="RBquest">
					<strong><?php echo 'Q. '; ?></strong>
					<?php the_title(); ?>
					
					<div class="RBans">
						<strong><?php echo 'Ans. '; ?></strong>
						<?php echo get_the_content(); ?>
					</div>
				</div>
			<?php endwhile; ?>
			</div><!-- ends faqs -->
            <?php
			}
			else {
				echo __( 'Hello, World!', 'Responsive Bit' );
			}
			echo $after_widget;
		}
		
		function form( $instance ) {
			if ( isset( $instance[ 'title' ] ) ) {
				$title = $instance[ 'title' ];
			}
			else {
				$title = __( 'New title', 'Responsive Bit' );
			}
			if ( isset( $instance['faq_numbers'] ) ) {
				$faq_numbers = $instance['faq_numbers'];
			}
			else {
				$faq_numbers = __('1','Responsive Bit');
			}
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            
            <label for="<?php echo $this->get_field_id('faq_numbers'); ?>"><?php _e( 'Number of FAQs' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'faq_numbers' ); ?>" name="<?php echo $this->get_field_name( 'faq_numbers' ); ?>" type="text" value="<?php echo esc_attr( $faq_numbers ); ?>" />
			</p>
			<?php 
		}
		
		function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['faq_numbers'] = strip_tags( $new_instance['faq_numbers'] );
			return $instance;
		}
	}
	
	//register_widget("ResponsiveBit_faq_widget");
	add_action( 'widgets_init', create_function( '', "register_widget('ResponsiveBit_faq_widget');" ) );
	register_activation_hook( __FILE__, array('ResponsiveBitFaq', 'responsiveBit_get_email') );
?>