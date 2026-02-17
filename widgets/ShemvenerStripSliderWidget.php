<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ShemvenerStripSliderWidget extends Widget_Base {

	public function get_name() {
		return 'shemvener_strip_slider';
	}

	public function get_title() {
		return esc_html__( 'Shemvener Strip Slider', 'shemvener-strip-slider' );
	}

	public function get_icon() {
		return 'eicon-post-slider';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_keywords() {
		return [ 'slider', 'deceased', 'shemvener', 'strip' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'shemvener-strip-slider' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'api_environment',
			[
				'label' => esc_html__( 'API Environment', 'shemvener-strip-slider' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'production'  => esc_html__( 'Production', 'shemvener-strip-slider' ),
					'development' => esc_html__( 'Development', 'shemvener-strip-slider' ),
					'other'       => esc_html__( 'Other', 'shemvener-strip-slider' ),
				],
				'default' => 'production',
			]
		);

		$this->add_control(
			'api_url',
			[
				'label' => esc_html__( 'Custom API URL', 'shemvener-strip-slider' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'https://shemvener.org.il/wp-json/shemvener/v1/strip-slider?format=json',
				'condition' => [
					'api_environment' => 'other',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$environment = $settings['api_environment'];

		if ( 'production' === $environment ) {
			$api_url = 'https://shemvener.org.il/wp-json/shemvener/v1/strip-slider?format=json';
		} elseif ( 'development' === $environment ) {
			$api_url = 'https://dev.shemvener.org.il/wp-json/shemvener/v1/strip-slider?format=json';
		} else {
			$api_url = $settings['api_url'];
		}

		// In a real scenario, we might want to cache this response
		$response = wp_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo 'Error fetching data from API: ' . esc_html( $response->get_error_message() );
			}
			return;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $data ) || ! is_array( $data ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo 'No data found or invalid response from API.';
			}
			return;
		}

		?>
		<div class="shemvener-slider">
            <div class="shemvener-slider-track">
            <?php foreach ( $data['results'] as $deceased ) :
                $first_name = isset( $deceased['first_name'] ) ? $deceased['first_name'] : '';
                $last_name = isset( $deceased['last_name'] ) ? $deceased['last_name'] : '';
                $year_of_birth = isset( $deceased['year_of_birth'] ) ? $deceased['year_of_birth'] : '';
                $year_of_death = isset( $deceased['year_of_death'] ) ? $deceased['year_of_death'] : '';
                $id = isset( $deceased['ID'] ) ? $deceased['ID'] : ( isset( $deceased['id'] ) ? $deceased['id'] : 0 );
                $info = isset( $deceased['description'] ) ? $deceased['description'] : ( isset( $deceased['info'] ) ? $deceased['info'] : '' );
                ?>

                <div class="shemvener-slider-item" style="background-image: url('<?= $deceased['label_image'] ?>');">
                    <div class="shemvener-slider-item-inner">
                        <div class="title">
                            <a target="_blank" href="<?php echo esc_url( 'https://names.shemvener.org.il/פרטי-תווית/?person_id=' . $id ); ?>" title="<?php echo esc_attr( $first_name ); ?>">
                                <span class="entry-title"><?php echo esc_html( $first_name . ' ' . $last_name ); ?></span>
                                <span class="entry-years"><?php echo esc_html( $year_of_birth . '-' . $year_of_death ); ?></span>
                            </a>
                        </div>

                        <div class="description">
                            <?php echo wp_kses_post( $info ); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
		<?php
	}

	protected function content_template() {
		// No content template for now as it relies on API data which is not available in JS easily
	}
}
