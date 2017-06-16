<?php
/**
 * Handles the Course Modules Widget
 *
 * @see WP_Widget
 */
class Zao_Sensei_Module_Manager_Widget extends WP_Widget {

    protected $widget_title = '';

    /**
     * Sets up a new Associated Post Menu widget instance.
     *
     * @since 3.0.0
     * @access public
     */
    public function __construct() {
        $widget_ops = array(
            'customize_selective_refresh' => true,
            'description' => 'Will show the modules for the course when on the course, lesson, or quiz pages.',
            'classname'   => 'module-list',
        );
        parent::__construct( 'zao_sensei_module_manager', 'Zao Sensei Module Manager', $widget_ops );
    }

    /**
     * Outputs the content for the associated Menu widget instance.
     *
     * @since 3.0.0
     * @access public
     *
     * @param array $args     Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current Associated Post Menu widget instance.
     */
    public function widget( $args, $instance ) {
        global $zao_sensei_module_manager, $post;

        if ( ! is_singular( 'course' ) && ! is_singular( 'lesson' ) && ! is_singular( 'quiz' ) ) {
            return;
        }

        $thepost = get_queried_object();
        $sensei_post_types = array(
            'course',
            'lesson',
            'quiz',
        );

        if ( ! in_array( $thepost->post_type, $sensei_post_types ) ) {
            return;
        }

        $course = $lesson_id = false;
        switch ( $thepost->post_type ) {
            case 'course':
                $course = $thepost;
                $course_id = $thepost->ID;
                break;

            case 'lesson':
                $lesson_id = $thepost->ID;
                $course_id = Sensei()->lesson->get_course_id( $thepost->ID );
                $course = get_post( $course_id );
                break;

            case 'quiz':
                $lesson_id = Sensei()->quiz->get_lesson_id( $thepost->ID );
                $course_id = Sensei()->lesson->get_course_id( $lesson_id );
                $course = get_post( $course_id );
                break;
        }

        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $this->widget_title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

        echo $args['before_widget'];

        $hold = $post;
        $post = $course;
        Sensei_Core_Modules::setup_single_course_module_loop();

        if ( is_object( $zao_sensei_module_manager ) ) {
            remove_action( 'sensei_single_course_modules_before', array( $zao_sensei_module_manager, 'course_modules_collapse_title' ), 21 );
            if ( $this->widget_title ) {
                add_action( 'sensei_single_course_modules_before', array( $this, 'module_title' ), 21 );
            }

        }

        $this->do_modules();
	    

        // echo '<xmp>'. __LINE__ .') $course_id: '. print_r( $course_id, true ) .'</xmp>';
        Sensei_Utils::restore_wp_query();
        $post = $hold;

        echo $args['after_widget'];
    }

    public function module_title() {
        echo '<header><h2>'. $this->widget_title .'</h2></header>';
    }

    public function do_modules() {
	    
    	// Load up the modules template(same one we use on the course landing page)
    	require( ABSPATH . 'wp-content/plugins/Zao-Sensei-Module-Manager/templates/collapse-modules.php' );
    	
    }

    /**
     * Handles updating settings for the current Associated Post Menu widget instance.
     *
     * @since 3.0.0
     * @access public
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        if ( ! empty( $new_instance['title'] ) ) {
            $instance['title'] = sanitize_text_field( $new_instance['title'] );
        }

        return $instance;
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
     * Outputs the settings form for the Associated Post Menu widget.
     *
     * @since 3.0.0
     * @access public
     *
     * @param array $instance Current settings.
     */
    public function form( $instance ) {
        $title = isset( $instance['title'] ) ? $instance['title'] : '';

        // If no menus exists, direct the user to go and create some.
        ?>
        <div class="nav-menu-widget-form-controls">
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
            </p>
        </div>
        <?php
    }
}
