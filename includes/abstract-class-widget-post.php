<?php

namespace CustomizeStaticLayout;

/**
 * Class AbstractWidgetPost
 *
 * @package CustomizeStaticLayout
 */
abstract class AbstractWidgetPost extends AbstractWidget
{
    /**
     * @var array
     */
    protected static $_post_query_vars = [];
    /**
     * @var \WP_Query|null
     */
    protected static $_query = null;
    /**
     * @var array
     */
    protected static $_ids_settings = [];

    /**
     * Constructor
     *
     * @param \WP_Customize_Manager $manager
     * @param string                $id
     * @param array                 $args
     */
    public function __construct( \WP_Customize_Manager $manager, $id, array $args )
    {
        parent::__construct( $manager, $id, $args );
        add_action( 'customize_controls_enqueue_scripts', function () {
            $this->_enqueue_scripts();
        } );
    }

    /**
     * @param \WP_Query $query
     */
    public static function set_query( \WP_Query $query )
    {
        self::$_query = $query;
    }

    /**
     * @return null|\WP_Query
     */
    public static function get_query()
    {
        return self::$_query;
    }

    /**
     * Get widget configurations
     *
     * @return array
     */
    public static function get_args()
    {
        return apply_filters( StaticLayout::NAME . '_widget_post_args', [
            'title' => __( 'Post', 'customize-static-layout' ),
        ] );
    }

    /**
     * Add controls (fields) to widget
     */
    public function build_controls()
    {
        $this->add_control( 'id', [
            'label'           => __( 'Post', 'customize-static-layout' ),
            'type'            => 'object_selector',
            'post_query_vars' => static::get_post_query_vars(),
            'select2_options' => [
                'allowClear'  => true,
                'placeholder' => __( '&mdash; Select &mdash;', 'customize-static-layout' ),
            ],
        ], 'CustomizeObjectSelector\Control');
        self::$_ids_settings[] = "{$this->id}[id]";
    }

    /**
     * @return array
     */
    public static function get_post_query_vars()
    {
        return wp_parse_args( static::$_post_query_vars, apply_filters( StaticLayout::NAME . '_widget_post_query_vars', [
            'post_type'   => [ 'post' ],
            'post_status' => 'publish',
        ] ) );
    }

    protected function _enqueue_scripts()
    {
        wp_enqueue_script(
            StaticLayout::NAME . '-widget-post',
            plugins_url( 'assets/js/preview.js', __DIR__ ),
            [ 'jquery', 'underscore', 'customize-controls' ],
            CUSTOMIZE_STATIC_LAYOUT,
            true
        );
        wp_localize_script( StaticLayout::NAME . '-widget-post', 'customizeStaticLayoutWidgetPost', [
            'ids' => self::$_ids_settings,
        ] );
    }
}