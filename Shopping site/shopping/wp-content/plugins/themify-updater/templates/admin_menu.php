<div class="wrap">
	<h2 class="nav-tab-wrapper wp-clearfix">
		<a href="<?php echo add_query_arg( array( 'page' => 'themify-license' ), admin_url( 'index.php' ) ); ?>" class="nav-tab<?php echo ! isset( $_GET['promotion'] ) ? ' nav-tab-active' : ''; ?>"><?php _e( 'Manage License', 'themify-updater' ) ?></a>
		<a href="<?php echo add_query_arg( array( 'page' => 'themify-license', 'promotion' => 1 ), admin_url( 'index.php' ) ); ?>" class="nav-tab<?php echo isset( $_GET['promotion'] ) && $_GET['promotion'] == 1 ? ' nav-tab-active' : ''; ?>"><?php _e( 'Themes', 'themify-updater' ) ?></a>
		<a href="<?php echo add_query_arg( array( 'page' => 'themify-license', 'promotion' => 2 ), admin_url( 'index.php' ) ); ?>" class="nav-tab<?php echo isset( $_GET['promotion'] ) && $_GET['promotion'] == 2 ? ' nav-tab-active' : ''; ?>"><?php _e( 'Plugins', 'themify-updater' ) ?></a>
		<div id="themify-updater-search" style="float: right;">
			<div class="search-promo">
				<label for="promo-search" class="search-icon dashicons dashicons-search"></label>
				<input id="promo-search" type="text" class="promo-search" name="promo-search">
				<span class="dashicons dashicons-no-alt clear-search"></span>
			</div>
		</div>
	</h2>
	<?php if ( ! isset( $_GET['promotion'] ) ) : ?>
		<form method="post" action="">
			<h2><?php _e( 'Themify License Settings', 'themify-updater' ) ?></h2>
			<p><?php _e( 'Enter your Themify username (that is your Themify user ID, not email address) and license key to auto update all Themify themes and plugins.', 'themify-updater' ) ?></p>
			<p><?php printf( __( 'To get your license key, go to <a href="%s" target="_blank">Themify\'s Member Area &gt; License</a> (if you don\'t see your license key, <a href="%s" target="_blank">contact Themify</a>).', 'themify-updater' ), 'https://themify.me/member/softsale/license', 'https://themify.me/contact' ) ?></p>
			<p><?php printf( __( 'Refer to <a href="%s" target="_blank">documentation</a> for more info.', 'themify-updater' ), 'https://themify.me/docs/themify-updater-documentation' ) ?></p>
			<table>
				<tr>
					<td><strong><?php _e( 'Themify Username', 'themify-updater' ) ?></strong></td>
					<td><input type="text" value="<?php echo $username; ?>" name="themify_username" /></td>
				</tr>
				<tr>
					<td><strong><?php _e( 'License Key', 'themify-updater' ) ?></strong></td>
					<td><input type="text" value="<?php echo $key; ?>" name="updater_licence" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="checkbox" value="1" <?php echo $hideKey!=false ? 'checked="checked"' : ''; ?> name="hidekey" /><?php _e('Hide my license key', 'themify-updater'); ?></td>
				</tr>
				<tr>
					<td><strong><?php _e( 'Update Notice', 'themify-updater' ) ?></strong></td>
					<td><input type="checkbox" value="1" <?php echo $hideNotice!=false ? 'checked="checked"' : ''; ?> name="hidenotice" /><?php _e('Do not show update notices on admin dashboard', 'themify-updater'); ?></td>
				</tr>
			</table>
			<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Save"></p>
		</form>
	<?php else : ?>
		<?php
            wp_enqueue_script( 'wp-util' );
            wp_localize_script('wp-util', 'themify_promotion', $this->get_downloadable_products( $_GET['promotion'] == 1 ? 'theme' : 'plugin', true, true ) );
            require ( THEMIFY_UPDATER_DIR_PATH.'/templates/promotion.php' );
         ?>
	<?php endif; ?>
</div>