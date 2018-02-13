<?php

namespace CustomizeStaticLayout;

/**
 * Class ControlAddWidget
 *
 * @package CustomizeStaticLayout
 */
class ControlAddWidget extends \WP_Customize_Control
{
    /**
     * Control type
     * @var string
     */
    public $type = 'add_widget';

    /**
     * Constructor
     *
     * @param \WP_Customize_Manager $manager
     * @param string                $id
     * @param array                 $args
     */
    public function __construct( $manager, $id, array $args = [] )
    {
        $this->type = StaticLayout::NAME . "_{$this->type}";
        parent::__construct( $manager, $id, $args );
    }

    /**
     * Refresh the parameters passed to the JavaScript via JSON
     *
     * @see WP_Customize_Control::json()
     *
     * @return array
     */
    public function json()
    {
        $json = parent::json();
        $json['l10n'] = [
            'addNew' => sprintf( __( 'Add New %s', 'customize-static-layout' ), $this->label ),
        ];

        return $json;
    }

    public function enqueue()
    {
        parent::enqueue();

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script(
            StaticLayout::NAME . '-control-add-widget',
            plugins_url( "assets/js/control-add-widget$suffix.js", __DIR__ ),
            [ 'jquery', 'underscore', 'jquery-ui-sortable', 'customize-models', StaticLayout::NAME . '-control-edit-widget' ],
            CUSTOMIZE_STATIC_LAYOUT,
            true
        );
        wp_localize_script( StaticLayout::NAME . '-control-add-widget', 'customizeStaticLayoutControlAddWidget', [
            'type'      => $this->type,
            'namespace' => StaticLayout::NAME,
        ] );
    }

    /**
     * Render the control's content
     */
    public function content_template()
    {
        ?>
        <button
            type="button"
            class="button button-primary"
        >{{{ data.l10n.addNew }}}</button>
        <?php
    }

    protected function render_content()
    {

    }
}