<?php

class Bazooka_Shopping_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'bzkshopping', __('Bazooka Shopping', 'bazooka-shopping'), array(
			'classname' => 'widget_bzkshopping',
			'description' => __('Automatic generation of affiliate links easily and effortlessly.', 'bazooka-shopping'),
			'customize_selective_refresh' => true
			)
		);
	}

	public function widget($args, $instance) {
		echo $args['before_widget'];
		$title = !empty($instance['title']) ? $instance['title'] : '';
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		$shortcode_atts = shortcode_atts(array(
			'keyword' => '',
			'count' => '',
			'template' => '',
			'merchants' => ''
		), $instance);
		$shortcode_atts = array_filter($shortcode_atts);
		$shortcode = '[bzkshopping';
		foreach($shortcode_atts as $shortcode_att_key => $shortcode_att_value) {
			$shortcode .= ' '.$shortcode_att_key.'="'.esc_attr($shortcode_att_value).'"';
		}
		$shortcode .= ']';
		echo do_shortcode($shortcode);
		
		echo $args['after_widget'];
	}

	public function form($instance) {
		$instance = wp_parse_args((array) $instance, array(
			'title' => '',
			'keyword' => '',
			'count' => '',
			'template' => '',
			'merchants' => ''
		));
		ob_start();
		include dirname(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'widget_form.php';
		echo ob_get_clean();
	}

	public function update($new_instance, $old_instance) {
		$fields = array(
			'title' => '',
			'keyword' => '',
			'count' => '',
			'template' => '',
			'merchants' => ''
		);
		$new_instance = wp_parse_args((array) $new_instance, $fields);
		$instance = $old_instance;
		foreach(array_keys($fields) as $field) {
			$instance[$field] = sanitize_text_field($new_instance[$field]);
		}
		return $instance;
	}

}
