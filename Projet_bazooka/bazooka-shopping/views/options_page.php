<?php
global $wp_settings_sections, $wp_settings_fields;
?> <div id=bazooka-shopping-options-page class=wrap>
	<h1><?php echo __('Bazooka Shopping', 'bazooka-shopping'); ?></h1>
	<div id=poststuff>
		<ul class=nav-tab-wrapper style=padding:0> <?php foreach ((array) $wp_settings_sections['bazooka-shopping'] as $section) :
														$section_id = substr($section['id'], strlen('bazooka-shopping-'));
														if ($section_id === 'options') {
															$section_icon = 'admin-settings';
														} else {
															$section_icon = Bazooka_Shopping::merchant_active($section_id) ? 'plugins-checked' : 'admin-plugins';
														}
													?> <li><a class="nav-tab dashicons-before dashicons-<?php echo $section_icon ?>" href="#<?php echo esc_attr($section['id']) ?>"> <?php echo esc_html($section['title']) ?> </a></li> <?php endforeach ?> </ul>
		<form method=post action=options.php novalidate> <?php settings_fields('bazooka-shopping'); ?> <?php foreach ((array) $wp_settings_sections['bazooka-shopping'] as $section) : ?> <div id="<?php echo esc_attr($section['id']); ?>"> <?php if ($section['callback']) : ?> <p><?php call_user_func($section['callback'], $section); ?></p> <?php endif;
																																																																																			if (!isset($wp_settings_fields) || !isset($wp_settings_fields['bazooka-shopping']) || !isset($wp_settings_fields['bazooka-shopping'][$section['id']])) {
																																																																																				continue;
																																																																																			}
																																																																																				?> <table class=form-table role=presentation> <?php do_settings_fields('bazooka-shopping', $section['id']) ?> </table>
				</div> <?php endforeach ?> <?php submit_button(); ?> </form>
		<div class=postbox-container>
			<div class=postbox>
				<h3 class=hndle><span><?php echo __('How does it work?', 'bazooka-shopping'); ?></span></h3>
				<div class=inside> <?php include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'help.php'; ?> </div>
			</div>
		</div>
	</div>
</div>