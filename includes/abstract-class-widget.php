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
        $this->type = "{$args['panel']}_{$this->type}";
        parent::__construct( $manager, $id, wp_parse_args( $args, static::get_args() ) );
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
        $this->manager->add_setting( $key, [
            'type'      => 'option',
            'transport' => 'postMessage',
        ] );
        $args['section'] = $this->id;
        $class = "\\$control_class";
        $control = new $class( $this->manager, $key, $args );
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
     */
    public static function render_widget( array $data, $id, $number )
    {
        // Implement render logic
    }
}