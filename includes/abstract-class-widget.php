<?php

namespace CustomizeStaticLayout;

/**
 * Class AbstractWidget
 *
 * @package CustomizeStaticLayout
 */
abstract class AbstractWidget extends \WP_Customize_Section
{
    /**
     * Section type
     * @var string
     */
    public $type = 'widget';
    /**
     * Widget controls
     * @var array
     */
    protected $_controls = [];
    /**
     * @var int
     */
    protected $_control_priority = 1;

    /**
     * Constructor
     *
     * @uses \WP_Customize_Section::__construct()
     *
     * @param \WP_Customize_Manager $manager
     * @param string                $id
     * @param array                 $args
     */
    public function __construct( $manager, $id, array $args )
    {
        $this->type = StaticLayout::NAME . "_{$this->type}";
        parent::__construct( $manager, $id, wp_parse_args( $args, static::get_args() ) );

        add_filter( 'customize_dynamic_setting_args', function ( $setting_args, $setting_id ) {
            return $this->_dynamic_setting_args( $setting_args, $setting_id );
        }, 10, 2 );
    }

    /**
     * Method for adding controls (use $this->_add_control())
     */
    abstract public function build_controls();

    /**
     * Adds control (field) to widget
     *
     * @param        $id
     * @param array  $args
     * @param string $control_class
     */
    final public function add_control( $id, array $args, $control_class = 'WP_Customize_Control' )
    {
        $key = "{$this->id}[$id]";
        $this->manager->add_setting( $key, static::_get_setting_args() );
        $args['section'] = $this->id;
        $class = "\\$control_class";
        $control = new $class( $this->manager, $key, wp_parse_args( $args, [
            'priority' => $this->_control_priority++,
        ] ) );
        $this->_controls[ $key ] = $control;
        $this->manager->add_control( $control );
    }

    /**
     * @param string $id
     * @param array  $args
     * @param string $widget_class
     */
    final public function add_nested_widgets( $id, array $args, $widget_class )
    {
        $key = "{$this->id}[$id]";
        /**
         * @var $widget AbstractWidget
         */
        $widget = new $widget_class( $this->manager, $key, [
            'capability' => $this->capability,
            'panel'      => $this->panel,
            'type'       => 'outer',
        ] );
        $this->manager->add_section( $widget );
        $widget->build_controls();
        $controls_args = wp_parse_args( [
            'section'  => $this->id,
        ], $args );
        $edit_key = "_$key";
        $this->manager->add_setting( $edit_key, static::_get_setting_args() );
        $this->manager->add_control( new ControlEditWidget( $this->manager, $edit_key, wp_parse_args( [
            'active_callback' => '__return_false',
            'priority'        => $this->_control_priority++,
        ], $controls_args ) ) );
        $this->manager->add_setting( $key, static::_get_setting_args() );
        $control = new ControlAddWidget( $this->manager, $key, wp_parse_args( $controls_args, [
            'priority' => $this->_control_priority++,
        ] ) );
        $this->_controls[ $key ] = $control;
        $this->manager->add_control( $control );
    }

    /**
     * Returns widget constols
     *
     * @return array
     */
    public function get_controls()
    {
        return $this->_controls;
    }

    /**
     * @param array|bool $setting_args
     * @param string     $setting_id
     *
     * @return array
     */
    protected function _dynamic_setting_args( $setting_args, $setting_id )
    {
        if ( false === $setting_args && substr( $setting_id, 0, strlen( $this->id ) ) === $this->id ) {
            $setting_args = static::_get_setting_args();
        }

        return $setting_args;
    }

    /**
     * Returns config array with widget's info
     *
     * @return array
     */
    public static function get_args()
    {
        return [];
    }

    /**
     * Displays widget content on frontend
     *
     * @param array  $data
     * @param string $id
     * @param int    $number
     * @param string $attrs
     */
    public static function render_widget( array $data, $id, $number, $attrs )
    {
        // Implement render logic
    }

    /**
     * @param array  $data
     * @param string $key
     * @param string $parent_id
     * @param        $widget_class
     */
    public static function render_nested_widgets( array $data, $key, $parent_id, $widget_class )
    {
        if ( is_array( $data[ $key ] ) ) {
            $indexes = array_filter( array_keys( $data[ $key ] ), 'is_int' );

            if ( !empty( $indexes ) && false !== ( $last_index = max( $indexes ) ) ) {
                for ( $i = 0; $i <= $last_index; $i++ ) {
                    $id = "{$parent_id}[$key][$i]";
                    /**
                     * @var AbstractWidget $widget_class
                     */
                    $widget_class::render_widget( isset( $data[ $key ][ $i ] ) ? $data[ $key ][ $i ] : [], $id, $i, StaticLayout::get_widget_attrs( $id, $widget_class::get_args() ) );
                }
            }
        }
    }

    /**
     * @return array
     */
    protected static function _get_setting_args()
    {
        return [
            'type'      => 'option',
            'transport' => 'postMessage',
        ];
    }
}