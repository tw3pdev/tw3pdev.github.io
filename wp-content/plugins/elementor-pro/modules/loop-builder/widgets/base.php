<?php
namespace ElementorPro\Modules\LoopBuilder\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use ElementorPro\Modules\LoopBuilder\Documents\Loop as LoopDocument;
use ElementorPro\Modules\LoopBuilder\Module;
use ElementorPro\Modules\LoopBuilder\Skins\Skin_Loop_Post;
use ElementorPro\Modules\Posts\Widgets\Posts;
use ElementorPro\Modules\QueryControl\Controls\Template_Query;
use ElementorPro\Modules\QueryControl\Module as QueryControlModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Base extends Posts {

	public function get_group_name() {
		return 'loop-builder';
	}

	/**
	 * Get Query Name
	 *
	 * Returns the query control name used in the widget's main query.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_query_name() {
		$skin_id = str_replace( '-', '_', $this->get_current_skin_id() );
		return $skin_id . '_' . Module::QUERY_ID;
	}

	/**
	 * Get Posts Per Page Value
	 *
	 * Returns the value of the Posts Per Page control of the widget.
	 *
	 * @since 3.8.0
	 * @access protected
	 *
	 * @return mixed
	 */
	protected function get_posts_per_page_value() {
		return $this->get_settings_for_display( 'posts_per_page' );
	}

	protected function register_skins() {
		$this->add_skin( new Skin_Loop_Post( $this ) );
	}

	protected function register_controls() {
		$this->register_layout_section();
		$this->register_query_section();
		$this->register_pagination_section_controls();

		$this->register_design_layout_controls();

		// The `_skins` control determines the Loop's query source, so it is renamed for this to be clearer to the user.
		$this->update_control( '_skin', [
			'label' => esc_html__( 'Choose template type', 'elementor-pro' ),
			'label_block' => true,
			'frontend_available' => true,
		] );
	}

	/**
	 * Register Layout Section
	 *
	 * This registers the Layout section in order to allow Skins to register their layout controls.
	 *
	 * @since 3.8.0
	 */
	protected function register_layout_section() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'elementor-pro' ),
			]
		);

		$this->add_control(
			'template_id',
			[
				'label' => esc_html__( 'Choose a template', 'elementor-pro' ),
				'type' => Template_Query::CONTROL_ID,
				'label_block' => true,
				'autocomplete' => [
					'object' => QueryControlModule::QUERY_OBJECT_LIBRARY_TEMPLATE,
					'query' => [
						'post_status' => Document::STATUS_PUBLISH,
						'meta_query' => [
							[
								'key' => Document::TYPE_META_KEY,
								'value' => LoopDocument::get_type(),
								'compare' => 'IN',
							],
						],
					],
				],
				'actions' => [
					'new' => [
						'visible' => true,
						'document_config' => [
							'type' => LoopDocument::get_type(),
						],
					],
					'edit' => [
						'visible' => true,
					],
				],
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__( 'Columns', 'elementor-pro' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'min' => 1,
				'max' => 12,
				'prefix_class' => 'elementor-grid%s-',
				'frontend_available' => true,
				'separator' => 'before',
				'condition' => [
					'template_id!' => '',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__( 'Items Per Page', 'elementor-pro' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'condition' => [
					'template_id!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register Query Section
	 *
	 * This registers the Query section in order to allow Skins to register their query controls.
	 *
	 * @since 3.8.0
	 */
	protected function register_query_section() {
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Query', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->end_controls_section();
	}

	public function register_pagination_section_controls() {
		parent::register_pagination_section_controls();

		$this->remove_responsive_control( 'align' );

		$this->start_injection( [
			'of' => 'text',
		] );

		$this->add_responsive_control(
			'load_more_button_align',
			[
				'label' => esc_html__( 'Alignment', 'elementor-pro' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start'    => [
						'title' => esc_html__( 'Start', 'elementor-pro' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'elementor-pro' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'End', 'elementor-pro' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'elementor-pro' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => 'center',
				'condition' => [
					'pagination_type' => 'load_more_on_click',
				],
				'selectors' => [
					'{{WRAPPER}}' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'start' => '--load-more-button-align: start;',
					'center' => '--load-more-button-align: center;',
					'end' => '--load-more-button-align: end;',
					'justify' => '--load-more-button-width: 100%;',
				],
			]
		);

		$this->end_injection();

		// Remove the HTML Entity arrows inherited from the Posts widget from the prev/next pagination link labels.
		$this->update_control(
			'pagination_prev_label',
			[
				'default' => esc_html__( 'Previous', 'elementor-pro' ),
			]
		);

		$this->update_control(
			'pagination_next_label',
			[
				'default' => esc_html__( 'Next', 'elementor-pro' ),
			]
		);
	}

	protected function register_design_layout_controls() {
		$this->start_controls_section(
			'section_design_layout',
			[
				'label' => esc_html__( 'Layout', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label' => esc_html__( 'Gap between columns', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label' => esc_html__( 'Gap between rows', 'elementor-pro' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'frontend_available' => true,
				'selectors' => [
					'{{WRAPPER}}' => '--grid-row-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}
}