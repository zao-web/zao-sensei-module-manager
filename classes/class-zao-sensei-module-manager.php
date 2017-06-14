<?php

if ( ! defined('ABSPATH')) {
	exit;
}

class Zao_Sensei_Module_Manager {
	private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;
	private $order_page_slug;
	public $taxonomy;


	public function __construct( $file ) {
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		$this->taxonomy = 'collapse';
		$this->order_page_slug = 'module-collapse';

		// Enqueue CSS and JS scripts
		add_action( 'sensei_single_course_modules_content', array( $this, 'enqueue_module_manager_scripts' ), 10 );

		// Remove native Sensei module title and content display
		add_action( 'sensei_single_course_modules_before', array( $this, 'mod_title_remove_action' ) ); // priority of 1, but can be anything higher (lower number) then the priority of the action
		add_action( 'sensei_single_course_modules_content', array( $this, 'mod_content_remove_action' ) ); // priority of 1, but can be anything higher (lower number) then the priority of the action
		add_action( 'sensei_single_course_content_inside_after', array( $this, 'mod_content_remove_action_new' ), 5 ); // priority of 1, but can be anything higher (lower number) then the priority of the action

		// Add collapsible module content display Sensei < V1.9
		add_action( 'sensei_single_course_modules_content', array( $this, 'course_module_manager_content' ), 20 );

		// Add collapsible module content display Sensei >= V1.9
		add_action( 'sensei_single_course_content_inside_after', array( $this, 'load_course_module_manager_content_template' ), 8 );

		// Add collapsible module title for all Sensei versions
		add_action( 'sensei_single_course_modules_before', array( $this, 'course_modules_collapse_title' ), 21 );
		
		add_action( 'sensei_register_widgets', array( $this, 'register_custom_widgets' ) );

	}
	
	public function register_custom_widgets() {
		require_once 'class-zao-sensei-module-manager-widget.php';
		register_widget( 'Zao_Sensei_Module_Manager_Widget' );
	}
	/**
	 * Remove native Sensei modules title on single course page
	 */
	public function mod_title_remove_action() {
		remove_action( 'sensei_single_course_modules_before', array( Sensei()->modules, 'course_modules_title' ), 20 );
	}

	/**
	 * Remove native Sensei modules content on single course page
	 */
	public function mod_content_remove_action() {
		remove_action( 'sensei_single_course_modules_content', array( Sensei()->modules, 'course_module_content' ), 20 );
	}
	/**
	 * Remove native Sensei modules content on single course page for Sensei v1.9
	 */
	public function mod_content_remove_action_new() {
		remove_action( 'sensei_single_course_content_inside_after', array( Sensei()->modules, 'load_course_module_content_template' ) , 8 );
	}

	/**
	 * Load admin JS
	 * @return void
	 */
	public function enqueue_module_manager_scripts() {
		global $wp_version;

			wp_enqueue_style( 'module-collapse', $this->assets_url . 'css/zao-sensei-module-manager.css', '1.0.0' );
			wp_register_script( 'module-collapsed', $this->assets_url . 'js/zao-sensei-module-manager.js', array(), '1.0', true );
			wp_enqueue_script( 'module-collapsed' );

	}

	/**
	 * Add collapsible Sensei modules content on single course page for Sensei v1.9
	 */
	public function load_course_module_manager_content_template() {


		// load backwards compatible template name if it exists in the users theme
		$located_template = locate_template( Sensei()->template_url . 'single-course/course-modules.php' );
		if ( $located_template ) {

			Sensei_Templates::get_template( 'single-course/course-modules.php' );
			return;

		}
		// load collapsible Sensei template name if it exists in the users theme
		require( ABSPATH . 'wp-content/plugins/zao-sensei-module-manager/templates/collapse-modules.php' );

	} // end course_module_content


	/**
	 * Show the title modules on the single course template with Collapse All/Expand All links.
	 *
	 * Function is hooked into sensei_single_course_modules_before.
	 *
	 * Sensei < V1.9
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function course_modules_collapse_title() {
		echo '<header><h2>' . esc_html__( 'Modules', 'woothemes-sensei' ) . '</h2></header>';
		echo '<div class="listControl"><a class="expandList">' . esc_html__( 'Expand All', 'zao-sensei-module-manager' ) . '</a> | <a class="collapseList">' . esc_html__( 'Collapse All', 'zao-sensei-module-manager' ) . '</a></div></br>';

	}
	public function get_setting( $setting_token ) {

		// get all settings from sensei
		$settings = Sensei()->settings->get_settings();

		if ( empty( $settings )  || ! isset( $settings[ $setting_token ] ) ) {
			return '';
		}

		return $settings[ $setting_token ];
	}
	/**
	 *
	 * Display the single course modules content with Collapse/Expand Toggle
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function course_module_manager_content() {

		// Do not run function if Sensei version is above v1.9
		$version = Sensei()->version;
		if ( $version >= 1.9 ) {
			return;
		}
		global $post;
		$course_id = $post->ID;
		$modules = Sensei()->modules->get_course_modules( $course_id );

		// Display each module
		foreach ( $modules as $module ) {
			echo '<article class="post module">';
			echo '<section class="entry">';
			$module_progress = false;
			if ( is_user_logged_in() ) {
				global $current_user;
				wp_get_current_user();
				$module_progress = Sensei()->modules->get_user_module_progress( $module->term_id, $course_id, $current_user->ID );
			}


			$lessons = Sensei()->modules->get_lessons( $course_id, $module->term_id );

			if ( count( $lessons ) > 0 ) {

				$lessons_list = '';
				// Check if Module collapse settings

				$media_setting = $this->get_setting( 'sensei_module_notes' );
				$video_setting = $this->get_setting( 'sensei_module_video' );
				$time_setting = $this->get_setting( 'sensei_module_lesson_time' );
				$quiz_setting = $this->get_setting( 'sensei_module_quiz' );

				foreach ( $lessons as $lesson ) {
					$status = '';
					$lessons_time = '';
					$lesson_completed = WooThemes_Sensei_Utils::user_completed_lesson( $lesson->ID, get_current_user_id() );
					$lesson_video = get_post_meta( $lesson->ID, '_lesson_video_embed', true );
					$lesson_length = get_post_meta( $lesson->ID, '_lesson_length', true );
					$lesson_media = get_post_meta( $lesson->ID, '_attached_media', true );
					$lesson_quiz = get_post_meta( $lesson->ID, '_quiz_has_questions', true );
					$title = esc_attr( get_the_title( intval( $lesson->ID ) ) );

					// Get lesson completed status
					if ( $lesson_completed ) {
						$status = 'completed';
					}



					// Check if lesson has a video
					$has_video = '';
					if ( '' != $lesson_video && ($video_setting) ) {
						$has_video = '<i class="fa fa-video-camera"></i> ';
					}
					// Check if lesson has a media
					$has_media = '';
					if ( isset( $lesson_media ) && is_array( $lesson_media ) && count( $lesson_media ) > 0  && ($media_setting) ) {
						$has_media = '<i class="fa fa-file"></i> ';
					}
					// Check if lesson has a quiz
					$has_quiz = '';
					if ( $lesson_quiz &&  ($quiz_setting) ) {
						$has_quiz = '<i class="fa fa-check-square-o"></i> ';
					}

					// Get lesson time and set variable if it exists
					if (('' != $lesson_length) && ($time_setting) ) {
						$lessons_time = '<i class="fa fa-clock-o"></i> '.$lesson_length.__( 'm', 'woothemes-sensei' ) . '';
					}
					$lessons_list .= '<li class="' . $status . '"><a href="' . esc_url( get_permalink( intval( $lesson->ID ) ) ) . '" title="' . esc_attr( get_the_title( intval( $lesson->ID ) ) ) . '"><span class="lesson-title">' . apply_filters( 'sensei_module_lesson_list_title', $title, $lesson->ID ) . '</span><span class="lesson-length">' . $has_quiz . $has_media . $has_video . $lessons_time . '</span></a></li>';

					// Build array of displayed lesson for exclusion later
					$displayed_lessons = array();
					$displayed_lessons[] = $lesson->ID;
				}
				if ( $module_progress && $module_progress > 0 ) {
					$status = __( 'Completed', 'woothemes-sensei' );
					$class = 'completed';
					if ( $module_progress < 100 ) {
						$status = __( 'In progress', 'woothemes-sensei' );
						$class = 'in-progress';
					}
					echo '<p class="status module-status '.esc_attr($class).'">'.$status.'</p>';
				}
				?>

				<section class="module-lessons">
					<ul >
						<header class="expList">
							<?php
							$title_setting = $this->get_setting( 'sensei_module_title' );
							// module title header with collapsing toggle
							// Check if module title should be linked
							if ( ! $title_setting ) {
								$module_url = esc_url( add_query_arg( 'course_id', $course_id, get_term_link( $module, $this->taxonomy ) ) );
								$has_module_link = "<a href='" . esc_url( $module_url ) . "'>" . $module->name . "</a>";
							} else {
								$has_module_link = $module->name;
							}

							echo "<h2 class='expList'><span class='module-title expList'>" . $has_module_link . "</span><i class='expList fa tog-mod fa-chevron-down'></i></h2>"; ?>
						</header>
						<?php


						if ('' != $module->description) {
						echo '<p class="module-description">'.$module->description.'</p>';
						}
						?>
						<li >
							<ul class="expList2" >
								<?php echo $lessons_list; ?>
							</ul>
						</li>
					</ul>
				</section>

			<?php }//end count lessons  ?>
				</section>
			</article>
		<?php

		} // end each module

	} // end course_module_content

}