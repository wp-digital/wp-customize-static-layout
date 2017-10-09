<?php

namespace CustomizeStaticLayout;

/**
 * Class StaticLayout
 *
 * @package CustomizeStaticLayout
 */
final class StaticLayout
{
    /**
     * Plugin key name
     */
    const NAME = 'customize_static_layout';
    /**
     * Panel (builder) configurations
     * @var array
     */
    const PRIORITY = 110;
    /**
     * Capability required for the panel (builder)
     * @var string
     */
    const CAPABILITY = 'edit_theme_options';
    /**
     * Widgets (sections) which are included in panel (builder)
     * @var array
     */
    private $_widgets = [];
    /**
     * Panel id
     * @var string
     */
    private $_panel;
    /**
     * Builder settings
     * @var string
     */
    private $_settings;

    /**
     * Constructor
     *
     * @param string $panel
     * @param array  $settings
     */
    public function __construct( $panel, array $settings )
    {
        $this->_panel = $panel;
        $this->_settings = wp_parse_args( $settings, [
            'capability' => static::CAPABILITY,
            'priority'   => static::PRIORITY,
        ] );

        add_action( 'customize_register', function ( $wp_customize ) {
            $this->_includes();
            $this->_register( $wp_customize );
        }, 20 );
        add_action( static::NAME . "_{$panel}_render", function ($args ) {
            $this->_includes();
            $this->_render( $args );
        } );
        add_action( 'customize_controls_enqueue_scripts', function () {
            $this->_enqueue_scripts();
        } );
        add_action( 'customize_preview_init', function () {
            $this->_enqueue_preview_scripts();
        } );
    }

    /**
     * Sets builder widgets
     *
     * @param array $widgets
     */
    public function set_widgets( array $widgets )
    {
        $this->_widgets = $widgets;
    }

    /**
     * Returns builder widgets
     *
     * @return array
     */
    public function get_widgets()
    {
        return $this->_widgets;
    }

    /**
     * Returns builder settings
     *
     * @return array|string
     */
    public function get_settings()
    {
        return $this->_settings;
    }

    /**
     * Sets special setting
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set_setting( $key, $value )
    {
        if ( isset( $this->_settings[ $key ] ) ) {
            $this->_settings[ $key ] = $value;
        }
    }

    /**
     * Registers builder (panel)
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    private function _register( $wp_customize )
    {
        $wp_customize->add_panel( $this->_panel, $this->_settings );

        foreach ( $this->_widgets as $number => $widget ) {
            $this->_register_widget( $wp_customize, static::NAME . "_{$this->_panel}[$number]", $widget, $number );
        }
    }

    /**
     * Registers widget (section)
     *
     * @param \WP_Customize_Manager $wp_customize
     * @param string                $key
     * @param string                $widget_class
     * @param int                   $number
     *
     * @return AbstractWidget
     */
    private function _register_widget( $wp_customize, $key, $widget_class, $number )
    {
        $args = [
            'capability' => $this->_settings['capability'],
            'panel'      => $this->_panel,
        ];
        /**
         * @var $widget AbstractWidget
         */
        $widget = new $widget_class( $wp_customize, $key, $args );
        $wp_customize->add_section( $widget );
        $widget->build_controls();
        $wp_customize->selective_refresh->add_partial( $key, [
            'settings'            => array_keys( $widget->get_controls() ),
            'selector'            => "[data-widget=\"$key\"]",
            'container_inclusive' => false,
            'render_callback'     => function ( \WP_Customize_Partial $partial ) use ( $widget, $number ) {
                $data = [];

                foreach ( $partial->settings as $key ) {
                    $setting = $widget->manager->get_setting( $key );
                    $data[ $setting->id_data()['keys'][1] ] = $setting->value();
                }

                $widget::render_widget( $data, $widget->id, $number );
            },
            'fallback_refresh'    => false,
        ] );

        return $widget;
    }

    /**
     * Displays builder on frontend
     *
     * @param array $args
     */
    private function _render( array $args )
    {
        $data = get_option( static::NAME . "_{$this->_panel}" );

        if ( !empty( $data ) || is_customize_preview() ) {
            /**
             * Need to make it for calling widgets\AbstractWidget::render_widget()
             */
            require_once ABSPATH . WPINC . '/class-wp-customize-section.php';
            do_action( static::NAME . '_before', $this, $data, $args );

            foreach ( $this->_widgets as $number => $widget ) {
                $this->_render_widget( !empty( $data[ $number ] ) ? $data[ $number ] : [], $number, $widget );
            }

            do_action( static::NAME . '_after', $this, $data, $args );
        }
    }

    /**
     * Displays widget (section)
     *
     * @param array          $data
     * @param int            $number
     * @param AbstractWidget $widget_class
     */
    private function _render_widget( $data, $number, $widget_class )
    {
        $id = static::NAME . "_{$this->_panel}[$number]";
        $attrs = static::get_widget_attrs( $id, $widget_class::get_args() );
        echo apply_filters( static::NAME . '_before_widget', "<div $attrs>", $number, $attrs );
        $widget_class::render_widget( $data, $id, $number );
        echo apply_filters( static::NAME . '_after_widget', '</div>', $number, $attrs );
    }

    /**
     * Enqueues JavaScript files
     */
    private function _enqueue_scripts()
    {
        wp_enqueue_script(
            static::NAME,
            plugins_url( 'assets/js/admin.js', __DIR__ ),
            [ 'jquery', 'customize-controls' ],
            CUSTOMIZE_STATIC_LAYOUT,
            true
        );
        wp_localize_script( static::NAME, 'customizeStaticLayout', [
            'panel' => $this->_panel,
        ] );
    }

    /**
     * Enqueues JavaScript files for previewer
     */
    private function _enqueue_preview_scripts()
    {
        wp_enqueue_script(
            static::NAME . '-preview',
            plugins_url( 'assets/js/preview.js', __DIR__ ),
            [ 'jquery', 'customize-preview' ],
            CUSTOMIZE_STATIC_LAYOUT,
            true
        );
        wp_localize_script( static::NAME . '-preview', 'customizeStaticLayoutPreview', [
            'panel' => $this->_panel,
        ] );
    }

    /**
     * Returns widget wrapper attributes
     *
     * @param string $id
     * @param array  $args
     *
     * @return string
     */
    public static function get_widget_attrs( $id, array $args )
    {
        $attrs = '';

        if ( is_customize_preview() ) {
            $attrs .= 'data-widget="' . esc_attr( $id ) . '"';
        }

        if ( isset( $args['attributes'] ) ) {
            foreach ( $args['attributes'] as $key => $value ) {
                $attrs .= " $key=\"" . ( is_array( $value ) ? implode( ' ', $value ) : $value ) . '"';
            }
        }

        return $attrs;
    }

    private function _includes()
    {
        require_once __DIR__ . '/abstract-class-widget.php';

        if ( class_exists( 'CustomizeObjectSelector\\Plugin' ) ) {
            require_once __DIR__ . '/abstract-class-widget-post.php';
        }
    }
}