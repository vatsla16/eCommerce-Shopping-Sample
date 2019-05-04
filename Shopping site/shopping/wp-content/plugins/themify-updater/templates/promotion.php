<?php $current_theme = wp_get_theme(); ?>

<div class="promote-themes" style="display:none;">
	<div class="container"></div>
</div>
<div class="promote-plugins" style="display:none;">
	<ul class="plugin-category">
		<li class="active" data-type="promo-plugins"><a href="#">Plugins</a></li>
		<li data-type="promo-builder-addons"><a href="#">Builder Addons</a></li>
		<li data-type="promo-ptb-addons"><a href="#">PTB Addons</a></li>
	</ul>
	<div class="container"></div>
</div>

<script type="text/html" id="tmpl-themify-featured-theme-item">
<ol class="grid3 theme-list clearfix">
    <# var extra = data.extra; #>
	<# jQuery.each( data, function( i, e ) { #>
		<li class="theme-post">
			<figure class="theme-image">
				<a href="{{{e.url}}}" target="_blank">
                    <img src="https://themify.me/wp-content/product-img/{{{e.slug}}}-thumb.jpg" alt="{{{e.title}}}">
				</a>
			</figure>
			<div class="theme-info">
				<div class="theme-title">
					<h3><a href="{{{e.url}}}" target="_blank">{{{e.title}}}</a></h3>
				    	<a class="tag-button lightbox" target="_blank" href="https://themify.me/demo/#theme={{{e.slug}}}"><?php _e( 'demo', 'themify-updater' ); ?></a>
                </div>
				<!-- /theme-title -->
				<div class="theme-excerpt">
					<p>{{{e.description}}}</p>
                    <# for ( promotion in themify_promotion['install'] ) { 
							if ( themify_promotion['install'][promotion]['promo'] == e.slug ){ #>
                        <a class="install-button lightbox" href="#" onclick="themify_updater_install( event , '{{{themify_promotion['install'][promotion]['name']}}}' , '{{{extra.type}}}' , '{{{promotion}}}' )"><?php _e( 'Install', 'themify-updater' ); ?></a>
                    <#		temp=true;
							break;
							}
						}
					for ( promotion in themify_promotion['buy'] ) { 
							if ( themify_promotion['buy'][promotion]['promo'] == e.slug ){ #>
                        <a class="install-button lightbox" href="{{{e.url}}}" target="_blank" ><?php _e( 'Buy', 'themify-updater' ); ?></a>
                    <#		temp=true;
							break;
							}
						}
					for ( promotion in themify_promotion['installed'] ) { 
							if ( themify_promotion['installed'][promotion]['promo'] == e.slug ){ #>
                        <span class="installed-button lightbox" ><?php _e( 'Installed', 'themify-updater' ); ?></span>
                    <#		temp=true;
							break;
							}
						}
					#>
                </div>
				<!-- /theme-excerpt -->
			</div>
			<!-- /theme-info -->	
		</li>
	<# } ) #>
</ol>
</script>

<script type="text/html" id="tmpl-themify-featured-plugin-item">
<ol class="grid3 theme-list clearfix">
    <# var extra = data.extra, demolink = 'https://themify.me/demo/themes/';
	#>
	<# jQuery.each( data, function( i, e ) { #>
	<# 
		if (e.category === 'promo-builder-addons') {
			e.demolink =  demolink + 'addon-' + e.slug;
			
		} else if (e.category === 'promo-plugins') {
			e.demolink =  demolink + e.slug;
			switch (e.slug) {
				case 'shopify-buy-button':
					e.demolink = demolink + 'simple';
					break;
				case 'themify-product-filter':
					e.demolink = demolink + 'wc-product-filter';
					break;
				case 'post-type-builder':
					e.demolink = demolink + 'ptb-bundle';
					break;
				case 'themify-icons':
					e.demolink = e.url;
					break;
				case 'event-post':
					e.demolink = demolink + 'events-post';
					break;
			}
		} else {
			e.demolink =  demolink + 'ptb-addon-' + e.slug;
			switch (e.slug) {
				case 'relation':
					e.demolink = demolink + 'ptb-bundle/celebrity-relation/';
					break;
				case 'map-view':
					e.demolink = demolink + 'ptb-bundle/map-view/';
					break;
				case 'search':
					e.demolink = demolink + 'ptb-bundle/properties/';
					break;
			}
		}
	#>
		<li class="theme-post {{{e.category}}}">
			<figure class="theme-image">
				<a href="{{{e.url}}}" target="_blank">
                    <img src="https://themify.me/wp-content/product-img/{{{ e.url.replace('https:\/\/themify.me\/', '') }}}.jpg" alt="{{{e.title}}}">
				</a>
			</figure>
			<div class="theme-info">
				<div class="theme-title">
					<h3><a href="{{{e.url}}}" target="_blank">{{{e.title}}}</a></h3>
				    	<a class="tag-button lightbox" target="_blank" href="{{{e.demolink}}}"><?php _e( 'demo', 'themify-updater' ); ?></a>
                </div>
				<!-- /theme-title -->
				<div class="theme-excerpt">
					<p>{{{e.description}}}</p>
                    <# for ( promotion in themify_promotion['install'] ) { 
							if ( themify_promotion['install'][promotion]['promo'] == e.slug ){ #>
                        <a class="install-button lightbox" href="#" onclick="themify_updater_install( event , '{{{themify_promotion['install'][promotion]['name'].replace('-plugin','')}}}' , '{{{extra.type}}}' , '{{{promotion}}}' )"><?php _e( 'Install', 'themify-updater' ); ?></a>
                    <#		temp=true;
							break;
							}
						}
					for ( promotion in themify_promotion['buy'] ) { 
							if ( themify_promotion['buy'][promotion]['promo'] == e.slug ){ #>
                        <a class="install-button lightbox" href="{{{e.url}}}" target="_blank" ><?php _e( 'Buy', 'themify-updater' ); ?></a>
                    <#		temp=true;
							break;
							}
						}
					for ( promotion in themify_promotion['installed'] ) { 
							if ( themify_promotion['installed'][promotion]['promo'] == e.slug ){ #>
                        <span class="installed-button lightbox" ><?php _e( 'Installed', 'themify-updater' ); ?></span>
                    <#		temp=true;
							break;
							}
						}
					#>
                </div>
				<!-- /theme-excerpt -->
			</div>
			<!-- /theme-info -->	
		</li>
	<# } ) #>
</ol>
</script>

<script type="text/javascript">

	jQuery(function($) {
		
		
		var promo_data = false;
		var type = "<?php echo ($_GET['promotion'] == 1 ? 'theme' : 'plugin'); ?>";
		var container = $('.promote-'+ type +'s .container');
		
		$(document).bind('themify_update_promo', function () {
			
			container.parent().show();
			
			if (!promo_data) {
				container.text('Loading...');
				$.getJSON( 'https://themify.me/public-api/featured-'+ type +'s/index.json' )
				.done(function( data ){
					data.currentThemeURI = "<?php echo $current_theme->display( 'ThemeURI' ); ?>";
					data.installLink = "<?php echo esc_url( wp_nonce_url( add_query_arg('install', '%themify_updater%'), 'install_product_' . $_GET['promotion'] ) ); ?>";
					data.extra = {'type': type};

					promo_data = data;
					$(document).trigger('themify_update_promo');
				}).fail(function( jqxhr, textStatus, error ){
					container.html( '<p><?php _e( 'Something went wrong while fetching the Featured Themes. Please try again later.', 'themify-updater' ); ?></p>' );
				});
				
				if (type == 'plugin') {
					$('.promote-plugins ul.plugin-category a').on('click', themify_plugin_change_cat);
				}
			}
			
			var template = wp.template( 'themify-featured-'+ type +'-item' );
			container.html( template( promo_data ) );
			if (type == 'plugin') {
				$('.promote-plugins ul.plugin-category li.active a').click();
			}
			
		}).ready( function () {
			$(document).trigger('themify_update_promo');
		});
		
		function themify_plugin_change_cat (e) {

			e.preventDefault();
			e.stopPropagation();
			
			$th = $(e.target).parent();
			$th.addClass('active').siblings().removeClass('active');
			$item = $('.theme-post.'+ $th.data('type')).show();
			$siblings = $item.siblings('li:not(.'+ $th.data('type') +')');

			$siblings.hide();
			
			$item.parent().append($siblings);
			
		}
	
	}(jQuery));
	
	function themify_updater_install (e , name, type, nonce) {
		e.preventDefault();

		if (!confirm(themify_upgrader.installation_message)) return;

		adminLink = "<?php echo network_admin_url( 'update.php' ); ?>";
		document.location = adminLink + "?action=install-" + type + "&" + type + "=" + name + "&_wpnonce=" + nonce;

	}
</script>