<?php
	/*
	Plugin Name: 20steps Bricks Bridge for Wordpress
	Plugin URI: https://20steps.de
	Description: Send messages to Bricks API on changes of posts in Wordpress so that Bricks can invalidate caches for nodes in Pages Brick and Varnish,  update search indices in Found Brick etc.
	Version: 0.2
	Author: Helmut Hoffer von Ankershoffen (hhva@20steps.de)
	Author URI: https://20steps.de
	License: http://www.apache.org/licenses/LICENSE-2.0
	Text Domain: twentysteps-bricks-bridge-wp
	Network: true
	
	Copyright 2017: Helmut Hoffer von Ankershoffen (hhva@20steps.de)
	
	*/
	
	class BricksBridge {
        
        /**
		 * @var null|string
		 */
		protected $domain = null;
		
		/**
		 * @var integer
		 */
		protected $blogId = null;
		
		/**
		 * @var array
		 */
		protected $eventQueue = [];
		
		// initialization
		
		public function __construct() {
			$this->blogId=get_current_blog_id();
			$this->domain=get_blog_details()->{'domain'};
			
			// Domain Mapping plugin support to determine mapped domain
			if ((int) get_site_option('be_mssc_dms')) {
				global $wpdb;
				$domain = $wpdb->get_var($wpdb->prepare("SELECT domain FROM wp_domain_mapping WHERE blog_id = %d AND active = 1 LIMIT 1", $this->blogId));
				if (!empty($domain)) {
					$this->domain = rtrim($domain, '/');
				}
			}
			add_action( 'init', array( &$this, 'init' ) );
		}
		
		public function init() {
			load_plugin_textdomain( 'twentysteps-bricks-bridge-wp' );
			
			// settings page
			add_action('network_admin_menu', array($this, 'addSettingsPage') );
			add_action('admin_post_settings_update',  array($this, 'settingsUpdate'));
			
			// forwarding of events
			foreach ($this->getRegisterEvents() as $type => $function) {
				add_action($type, array($this, $function), 10, 2 );
			}
			add_action( 'shutdown', array($this, 'flushEventQueue') );
			
			// manual cache and CDNs invalidation
			if ($this->isInvalidateAllEnabled()) {
				if ( isset($_GET['twentysteps_bricks_bridge_wp_invalidate_all']) && check_admin_referer('twentysteps-bricks-bridge-wp') ) {
					add_action( 'admin_notices' , array( $this, 'invalidateAllMessage'));
				}
				
				if (
					// SingleSite - admins can always purge
					( !is_multisite() && current_user_can('activate_plugins') ) ||
					// Multisite - Network Admin can always purge
					current_user_can('manage_network') ||
					// Multisite - Site admins can purge UNLESS it's a subfolder install and we're on site #1
					( is_multisite() && !current_user_can('manage_network') && ( SUBDOMAIN_INSTALL || ( !SUBDOMAIN_INSTALL && ( BLOG_ID_CURRENT_SITE != $this->blogId ) ) ) )
				) {
					add_action( 'admin_bar_menu', array( $this, 'adminbarInvalidateAll' ), 100 );
				}
			}
			
			// preview in app on devices
			if ($this->isPreviewEnabled()) {
				add_action('show_user_profile', [$this, 'extendUserProfile']);
				add_action('edit_user_profile', [$this, 'extendUserProfile']);
				add_action('personal_options_update', [$this, 'extendUserProfileUpdate']);
			}
			if ($this->isForcePreviewBeforePublishEnabled()) {
    			add_action('admin_init', array($this,'forcePreviewBeforePublishInit'));
				add_action('edit_form_advanced', array($this,'forcePreviewBeforePublish'));
			}
			
			if ( isset($_GET['flash']) ) {
				add_action( 'admin_notices' , array( $this, 'getFlashMessage'));
			}
			
			// push to app
			add_action('admin_post_push_to_app_settings_update',  array($this, 'pushToAppSettingsUpdate'));
			if ($this->isPushEnabled()) {
				add_action('admin_post_push_to_app_send',  array($this, 'pushToAppSend'));
			}
			add_action('admin_menu',array($this,'addPushMenu'));
		}
		
		
		// get/set site options
		
		private function getApiKey() {
			return  get_site_option('bricks_basic_pages_bridge_api_key','geheim');
		}
		
		private function setApiKey($value) {
			update_site_option('bricks_basic_pages_bridge_api_key',$value);
		}
		
		private function getApiHost() {
			return get_site_option('bricks_api_host','api.localhost.com');
		}
		
		private function setApiHost($value) {
			update_site_option('bricks_api_host',$value);
		}
		
		private function getApiProtocol() {
			return get_site_option('bricks_api_protocol','http');
		}
		
		private function setApiProtocol($value) {
			update_site_option('bricks_api_protocol',$value);
		}
		
		private function checkBridgeSettings() {
			$url = $this->getApiUrlPrefix().'check?key='.$this->getApiKey().'&kernel=wordpress&domain='.urlencode($this->domain).'&scope='.urlencode($this->blogId);
			$response=wp_remote_get($url);
			if (is_wp_error($response)) {
			    return '['.$response->get_error_message().']';
            } elseif (is_array($response)) {
			    $responseCode = wp_remote_retrieve_response_code($response);
			    if ($responseCode == 200) {
			        return '[OK]';
                } else {
			        $responseBody = wp_remote_retrieve_body($response);
			        return '['.$responseCode.','.substr(strip_tags($responseBody),0,512).']';
                }
			} else {
			    return '[ITERNAL_ERROR]';
            }
		}
		
		private function getCheckBridgeSettingsResult() {
		    if (array_key_exists('check',$_GET)) {
                return $_GET['check'];
            }
		    return 'n/a';
        }
		
		private function getFlash() {
			if (array_key_exists('flash',$_GET)) {
				return $_GET['flash'];
			}
			return 'n/a';
		}
		
		public function getFlashMessage() {
		    echo '<div class="notice notice-success"><p>'.$this->getFlash().'</p></div>';
		}
		
		public function getInvalidateAllEnabled() {
			return get_site_option('bricks_basic_pages_bridge_invalidate_all_enabled','true');
		}
		
		public function isInvalidateAllEnabled() {
			return $this->getInvalidateAllEnabled()=='true';
		}
		
		public function setInvalidateAllEnabled($value) {
			if ($value!= 'true' && $value!='false') {
				$value='true';
			}
			return update_site_option('bricks_basic_pages_bridge_invalidate_all_enabled', $value);
		}
		
		public function getPreviewEnabled() {
			return get_site_option('bricks_basic_pages_bridge_preview_enabled','true');
		}
		
		public function isPreviewEnabled() {
			return $this->getPreviewEnabled()=='true';
		}
		
		public function setPreviewEnabled($value) {
			if ($value!= 'true' && $value!='false') {
				$value='true';
			}
			return update_site_option('bricks_basic_pages_bridge_preview_enabled', $value);
		}
		
		public function getForcePreviewBeforePublishEnabled() {
			return get_site_option('bricks_basic_pages_bridge_preview_force_before_publish_enabled','false');
		}
		
		public function isForcePreviewBeforePublishEnabled() {
			return $this->getForcePreviewBeforePublishEnabled()=='true';
        }
		
		public function setForcePreviewBeforePublishEnabled($value) {
			if ($value!= 'true' && $value!='false') {
				$value='true';
			}
			return update_site_option('bricks_basic_pages_bridge_preview_force_before_publish_enabled', $value);
		}
		
		public function getCRUDEventsEnabled() {
			return get_site_option('bricks_basic_pages_bridge_crud_events_enabled','true');
		}
		
		public function isCRUDEventsEnabled() {
			return $this->getCRUDEventsEnabled()=='true';
		}
		
		public function setCRUDEventsEnabled($value) {
			if ($value!= 'true' && $value!='false') {
				$value='true';
			}
			return update_site_option('bricks_basic_pages_bridge_crud_events_enabled', $value);
		}
		
		public function getPushEnabled() {
			return get_option('bricks_basic_pages_bridge_push_enabled','true');
		}
		
		public function isPushEnabled() {
			return $this->getPushEnabled()=='true';
		}
		
		public function setPushEnabled($value) {
			if ($value!= 'true' && $value!='false') {
				$value='false';
			}
			return update_option('bricks_basic_pages_bridge_push_enabled', $value);
		}
		
		private function getPushApiKey() {
			return  get_option('bricks_basic_pages_bridge_push_api_key','geheim');
		}
		
		private function setPushApiKey($value) {
			update_option('bricks_basic_pages_bridge_push_api_key',$value);
		}
		
		private function getPushApiHost() {
			return get_option('bricks_api_push_host','api.localhost.com');
		}
		
		private function setPushApiHost($value) {
			update_option('bricks_api_push_host',$value);
		}
		
		private function getPushApiProtocol() {
			return get_option('bricks_api_push_protocol','http');
		}
		
		private function setPushApiProtocol($value) {
			update_option('bricks_api_push_protocol',$value);
		}
		
		private function getPushApiPath() {
			return get_option('bricks_api_push_path','/bricks/api/v1.0/your-custom-bundle-key/push/message');
		}
		
		private function setPushApiPath($value) {
			$value = trim($value,'/');
			$value='/'.$value;
			update_option('bricks_api_push_path',$value);
		}
		
		private function getPushApiUrlPrefix() {
			return $this->getPushApiProtocol().'://'.$this->getPushApiHost().$this->getPushApiPath();
		}
		
		
		private function setPushApiChannelsFromString($value) {
			$segments = explode(',',trim($value));
			$value='';
			foreach ($segments as $segment) {
				$value.=trim($segment).',';
			}
			update_option('bricks_api_push_channels_string',trim($value,','));
		}
		
		private function getPushApiChannelsString() {
			return get_option('bricks_api_push_channels_string','');
		}
		
		
		private function getPushApiChannels() {
			$segments = explode(',',$this->getPushApiChannelsString());
			$channels = array();
			for ($i=0; $i<count($segments); $i+=2) {
				$arn = $segments[$i];
				if (count($segments)>$i+1) {
					$name=$segments[$i+1];
					$channels[$arn]=$name;
				}
			}
			return $channels;
		}
		
		// determine api url prefix
		
		private function getApiUrlPrefix() {
			return $this->getApiProtocol().'://'.$this->getApiHost().'/de/bricks/system/brick/pages/bridge/';
		}
		
		
		// network settings page
		
		public function addSettingsPage() {
			add_submenu_page('settings.php','Bricks Bridge','Bricks Bridge','manage_options','bricks-bridge',array($this, 'settingsPage'));
		}
		
		public function settingsPage() {
			?>
            <div class="wrap">
                <h1>Configure Bricks Bridge</h1>
                <form action="<?php echo admin_url('admin-post.php?action=settings_update'); ?>" method="post">
					<?php wp_nonce_field('twentysteps_bricks_bridge_nonce'); ?>
                    <h3>Connector</h3>
                    <p>Settings for connecting Wordpress to Bricks by 20steps</p>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label for="api_key">API Key</label>
                            </th>
                            <td>
                                <input type="text" id="api_key" name="api_key" class="regular-text" value="<?php echo $this->getApiKey() ?>">

                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_host">API Host<br/></label>
                            </th>
                            <td>
                                <input type="text" id="api_host" name="api_host" class="regular-text" value="<?php echo $this->getApiHost() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_protocol">API Protocol<br/></label>
                            </th>
                            <td>
                                <input type="text" id="api_protocol" name="api_protocol" class="regular-text" value="<?php echo $this->getApiProtocol() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="invalidate_all_enabled">Invalidate All enabled? (enter "true" or "false")<br/></label>
                            </th>
                            <td>
                                <input type="text" id="invalidate_all_enabled" name="invalidate_all_enabled" class="regular-text" value="<?php echo $this->getInvalidateAllEnabled() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="preview_enabled">Preview enabled? (enter "true" or "false")<br/></label>
                            </th>
                            <td>
                                <input type="text" id="preview_enabled" name="preview_enabled" class="regular-text" value="<?php echo $this->getPreviewEnabled() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="preview_force_before_publish_enabled">Force preview before publish enabled? (enter "true" or "false")<br/></label>
                            </th>
                            <td>
                                <input type="text" id="preview_force_before_publish_enabled" name="preview_force_before_publish_enabled" class="regular-text" value="<?php echo $this->getForcePreviewBeforePublishEnabled() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="crud_events_enabled">CRUD Events enabled? (enter "true" or "false")<br/></label>
                            </th>
                            <td>
                                <input type="text" id="crud_events_enabled" name="crud_events_enabled" class="regular-text" value="<?php echo $this->getCRUDEventsEnabled() ?>">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div>=> CHECK: <?php echo $this->getCheckBridgeSettingsResult() ?></div>
                    <div>=> API URL Prefix: <?php echo $this->getApiUrlPrefix() ?></div>
                    <p class="submit">
                        <input type="submit" id="submit" name="submit" class="button button-primary" value="Save and check settings">
                    </p>
                </form>
            </div>
			<?php
		}
		
		
		public function settingsUpdate(){
			// check if nonce valid and user allowed to update settings
			check_admin_referer('twentysteps_bricks_bridge_nonce');
			if(!current_user_can('manage_network_options')) {
				wp_die('rejected');
			}
			
			// update settings
			$this->setApiKey($_POST['api_key']);
			$this->setApiHost($_POST['api_host']);
			$this->setApiProtocol($_POST['api_protocol']);
			$this->setInvalidateAllEnabled($_POST['invalidate_all_enabled']);
			$this->setPreviewEnabled($_POST['preview_enabled']);
			$this->setForcePreviewBeforePublishEnabled($_POST['preview_force_before_publish_enabled']);
			$this->setCRUDEventsEnabled($_POST['crud_events_enabled']);
			
			// check bridge and show settings page again
			wp_redirect(admin_url('network/settings.php?page=bricks-bridge&check='.urlencode($this->checkBridgeSettings())));
			
			exit;
		}
		
		// extend user profile (e.g. for external preview)
		
		public function extendUserProfile($user) {
			?>
            <hr/>
            <h3 id="twentysteps_bricks_bridge">20steps Bricks Bridge</h3>
            <table class="form-table">
                <tr>
                    <th>
                        <label for="twentysteps_bricks_bridge_wp_username_app"><?php _e('Username in App'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="twentysteps_bricks_bridge_wp_username_app" id="twentysteps_bricks_bridge_wp_username_app" value="<?php echo esc_attr( get_the_author_meta( 'twentysteps_bricks_bridge_wp_username_app', $user->ID ) ); ?>" class="regular-text" />
                        <br><span class="description">Your username in the associated Mobile App</span>
                    </td>
                </tr>
            </table>
            <hr/>
			<?php
		}
		
		public function extendUserProfileUpdate($userId) {
			if ( current_user_can('edit_user',$userId)) {
				update_user_meta($userId, 'twentysteps_bricks_bridge_wp_username_app', $_POST['twentysteps_bricks_bridge_wp_username_app']);
			}
		}
		
		// invalidate all
		
		public function adminbarInvalidateAll($admin_bar){
			$admin_bar->add_menu( array(
				'id'	=> 'twentysteps-bricks-bridge-wp-invalidate-all',
				'title' => 'Invalidate Varnish and CDN',
				'href'  => wp_nonce_url(add_query_arg('twentysteps_bricks_bridge_wp_invalidate_all', 1), 'twentysteps-bricks-bridge-wp'),
				'meta'  => array(
					'title' => __('Invalidate Varnish and CDN','twentysteps-bricks-bridge-wp'),
				),
			));
		}
		
		public function invalidateAll() {
			$url = wp_nonce_url(admin_url('?twentysteps_bricks_bridge_wp_invalidate_all'), 'twentysteps-bricks-bridge-wp');
			$intro = sprintf( __('<a href="%1$s">20steps Bricks Bridge / Invalidate All</a> 20steps Bricks Bridge automatically invalidates pages and posts in Varnish and CDN when published or updated. Click here to invalidate everything e.g. after changing theme options.', 'twentysteps-bricks-bridge-wp' ), 'http://wordpress.org/plugins/twentysteps-bricks-bridge-wp/' );
			$button =  __('Press the button below to force it to purge your entire cache.', 'twentysteps-bricks-bridge-wp' );
			$button .= '</p><p><span class="button"><a href="'.$url.'"><strong>';
			$button .= __('Invalidate Varnish and CDN', 'twentysteps-bricks-bridge-wp' );
			$button .= '</strong></a></span>';
			$nobutton =  __('You do not have permission to invalidate all. Please contact your super user.', 'twentysteps-bricks-bridge-wp' );
			if (
				// SingleSite - admins can always purge
				( !is_multisite() && current_user_can('activate_plugins') ) ||
				// Multisite - Network Admin can always purge
				current_user_can('manage_network') ||
				// Multisite - Site admins can purge UNLESS it's a subfolder install and we're on site #1
				( is_multisite() && !current_user_can('manage_network') && ( SUBDOMAIN_INSTALL || ( !SUBDOMAIN_INSTALL && ( BLOG_ID_CURRENT_SITE != $this->blogId ) ) ) )
			) {
				$text = $intro.' '.$button;
			} else {
				$text = $intro.' '.$nobutton;
			}
			echo "<p class='twentysteps-bricks-bridge-wp-invalidate-all'>$text</p>\n";
		}
		
		public function invalidateAllMessage() {
			echo "<div id='message' class='updated fade'><p><strong>".__('All objects in Varnish tier and content delivery network have been invalidated. ', 'twentysteps-bricks-bridge-wp')."</strong></p></div>";
		}
		
		// push to app
		
		
		public function addPushMenu() {
			if ($this->isPushEnabled()) {
				add_object_page('Quick push','Mobile Push', 'edit_posts', 'push_to_app', array($this,'pushToAppPage'),'dashicons-rss',14);
				add_submenu_page('push_to_app','Quick Push','Quick Push', 'edit_posts', 'push_to_app', array($this,'pushToAppPage') );
				add_submenu_page('push_to_app','Settings','Settings', 'manage_options', 'push_to_app_settings', array($this,'pushToAppSettingsPage') );
			} else {
				add_menu_page('Push to App','Push Menu', 'manage_options', 'push_to_app_settings', array($this,'pushToAppSettingsPage') );
			}
		}
		
		public function pushToAppSettingsPage() {
			?>
            <div class="wrap">
                <h1>Configure Mobile Push</h1>
                <form action="<?php echo admin_url('admin-post.php?action=push_to_app_settings_update'); ?>" method="post">
					<?php wp_nonce_field('twentysteps_bricks_bridge_nonce'); ?>
                    <h3>Connector</h3>
                    <p>Settings for connecting Wordpress to Push Module of Bricks based App</p>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label for="push_enabled">Enable push form for editors? (enter "true" or "false")<br/></label>
                            </th>
                            <td>
                                <input type="text" id="push_enabled" name="push_enabled" class="regular-text" value="<?php echo $this->getPushEnabled() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="push_api_protocol">API Protocol for Push<br/></label>
                            </th>
                            <td>
                                <input type="text" id="push_api_protocol" name="push_api_protocol" class="regular-text" value="<?php echo $this->getPushApiProtocol() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="push_api_host">API Host for Push<br/></label>
                            </th>
                            <td>
                                <input type="text" id="push_api_host" name="push_api_host" class="regular-text" value="<?php echo $this->getPushApiHost() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="push_api_path">API Path for Push<br/></label>
                            </th>
                            <td>
                                <input type="text" id="push_api_path" name="push_api_path" class="regular-text" value="<?php echo $this->getPushApiPath() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="push_api_key">API Key for Push</label>
                            </th>
                            <td>
                                <input type="text" id="push_api_key" name="push_api_key" class="regular-text" value="<?php echo $this->getPushApiKey() ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="push_api_channels">Topics<br/><small>arn,name,arn,name,arn,name,...</small></label>
                            </th>
                            <td>
                                <input type="text" id="push_api_channels" name="push_api_channels" class="regular-text" value="<?php echo $this->getPushApiChannelsString() ?>">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p>
                    <PRE>=> Push API URL Prefix: <?php echo $this->getPushApiUrlPrefix() ?></PRE>
                    </p>
                    <p class="submit">
                        <input type="submit" id="submit" name="submit" class="button button-primary" value="Save Changes">
                    </p>
                </form>
            </div>
			<?php
		}
		
		public function pushToAppSettingsUpdate() {
			
			// check if nonce valid and user allowed to update settings
			check_admin_referer('twentysteps_bricks_bridge_nonce');
			if(!current_user_can('manage_options')) {
				wp_die('rejected');
			}
			
			
			$this->setPushApiKey($_POST['push_api_key']);
			$this->setPushApiHost($_POST['push_api_host']);
			$this->setPushApiProtocol($_POST['push_api_protocol']);
			$this->setPushApiPath($_POST['push_api_path']);
			$this->setPushApiChannelsFromString($_POST['push_api_channels']);
			$this->setPushEnabled($_POST['push_enabled']);
			
			// process your fields from $_POST here and update_site_option
			wp_redirect(admin_url('admin.php?page=push_to_app_settings'));
			exit;
		}
		
		public function pushToAppPage() {
			?>
            <div class="wrap">
                <h1>Quick Push</h1>
                <form action="<?php echo admin_url('admin-post.php?action=push_to_app_send'); ?>" method="post">
					<?php wp_nonce_field('twentysteps_bricks_bridge_nonce'); ?>
                    <h3>Push message to topic wihout further targeting.</h3>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="channel">Topic<br/><small>Ask your product owner for definitions</small></label>
                                </th>
                                <td>
                                    <select name="arn" size="1"><?php
                                            foreach ($this->getPushApiChannels() as $arn => $name) {
                                                if (strpos($name, 'Editors') !== false) {
                                                    echo '<option value="'.$arn.'" selected="selected">'.$name.'</option>';
                                                } else {
                                                    echo '<option value="'.$arn.'">'.$name.'</option>';
                                                }
                                            }
                                        ?></select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="text">Message<br/><small>Text, max. 200 characters</small></label>
                                </th>
                                <td>
                                    <input type="text" id="text" name="text" class="regular-text" value="">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="url">URL (optional)<br/><small>e.g. http://www.what.com/ever</small></label>
                                </th>
                                <td>
                                    <input type="text" id="url" name="url" class="regular-text" value="">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" id="submit" name="submit" class="button button-primary" value="Quick push">
                    </p>
                </form>
            </div>
			<?php
		}
		
		public function pushToAppSend() {
			
			// check if nonce valid and user allowed to update settings
			check_admin_referer('twentysteps_bricks_bridge_nonce');
			
			$url = $this->getPushApiUrlPrefix().'?key='.$this->getPushApiKey().'&text='.urlencode($_POST['text']).'&url='.urlencode($_POST['url']).'&arn='.urlencode($_POST['arn']);
			$response = wp_remote_get($url);
			
			if (is_wp_error($response)) {
				$flash = '['.$response->get_error_message().']';
			} elseif (is_array($response)) {
				$responseCode = wp_remote_retrieve_response_code($response);
				if ($responseCode == 200) {
					$flash = '[OK]';
				} else {
					$responseBody = wp_remote_retrieve_body($response);
					$flash = '['.$responseCode.','.substr(strip_tags($responseBody),0,512).']';
				}
			} else {
				$flash = '[INTERNAL_ERROR]';
			}
			
			// process your fields from $_POST here and update_site_option
			wp_redirect(admin_url('admin.php?page=push_to_app&flash='.urlencode($flash)));
			exit;
		}
		
		
		// event forwarding
		
		// events to register and callbacks to call on events received
		protected function getRegisterEvents() {
			$events = array();
			if ($this->isCRUDEventsEnabled()) {
				$events = array_merge($events,array(
					'save_post' => 'queueSavePostEvent',
					'deleted_post' => 'queueDeletedPostEvent',
					'trashed_post' => 'queueTrashedPostEvent',
					'edit_post' => 'queueEditPostEvent',
					'publish_post' => 'queuePublishPostEvent',
					'publish_future_post' => 'queuePublishFuturePostEvent',
					'transition_post_status' => 'queueTransitionPostStatusEvent',
					'created_term' => 'queueCreatedTermEvent',
					'edited_term' => 'queueEditedTermEvent',
					'delete_term' => 'queueDeleteTermEvent'
				));
			}
			if ($this->isPreviewEnabled()) {
				$events = array_merge($events,array(
					'wp_footer' => 'queuePreviewPostEvent'
				));
			}
			return $events;
		}
		
		// callbacks
		
		public function queuePreviewPostEvent() {
			if ( array_key_exists('preview',$_GET) && $_GET['preview']=='true') {
				$currentUser = wp_get_current_user();
				if (array_key_exists('p',$_GET)) {
					$id=$_GET['p'];
				} else {
					$id=$_GET['preview_id'];
				}
				$this->queueEvent('NODE_PREVIEW',array(
					'id' => $id,
					'username' => $currentUser->user_login,
					'username_app' => get_the_author_meta( 'twentysteps_bricks_bridge_wp_username_app', $currentUser->ID )
				));
			}
		}
		
		public function forcePreviewBeforePublishInit() {
			wp_enqueue_script('jquery');
		}
		
		public function insertHidePreview() {
			echo "<script type='text/javascript'>\n";
			echo "
					  jQuery('#publish').hide();
					  jQuery('#post-preview').click(function() {
						jQuery('body').append(\"<div style='display: none' id='twentysteps_previewed'></div>\");
						jQuery('#publish').show();
					  });
				  ";
			echo "</script>\n";
		}
		
		public function forcePreviewBeforePublish() {
			
			// Global object containing current admin page
			global $pagenow;
			
			$postTypes = ['post','video']; // FIXME: make configurable
			
			$postType = null;
			if ( 'post.php' === $pagenow && isset($_GET['post'])) {
				$postType = get_post_type( $_GET['post'] );
			} else if ('post-new.php' == $pagenow) {
				$postType = 'post';
				if (isset($_GET['post_type'])) {
					$postType = $_GET['post_type'];
				}
			}
			if (in_array($postType,$postTypes)) {
				$this->insertHidePreview();
			}
			
		}
		
		public function queueSavePostEvent($id) {
			$this->queueEvent('NODE_CREATED',array('id' => $id));
		}
		
		public function queueTransitionPostStatusEvent($newStatus, $oldStatus, $post=null) {
			if($post != null) {
				/**
				 * @var stdClass $post
				 */
				$this->queueEvent('NODE_UPDATED',array('id' => $post->ID));
			}
		}
		
		public function queuePublishPostEvent($id) {
			$this->queueEvent('NODE_PUBLISHED',array('id' => $id));
		}
		
		public function queuePublishFuturePostEvent($id) {
			$this->queueEvent('NODE_PLANNED',array('id' => $id));
		}
		
		public function queueEditPostEvent($id) {
			$this->queueEvent('NODE_UPDATED',array('id' => $id));
		}
		
		public function queueTrashedPostEvent($id) {
			$this->queueEvent('NODE_TRASHED', array('id' => $id));
		}
		
		public function queueDeletedPostEvent($id) {
			$this->queueEvent('NODE_DELETED',array('id' => $id));
		}
		
		public function queueCreatedTermEvent($id,$taxonomyId) {
			$this->queueEvent('TERM_CREATED',array('id' => $id, 'taxonomyId' => $taxonomyId));
		}
		
		public function queueEditedTermEvent($id,$taxonomyId) {
			$this->queueEvent('TERM_UPDATED',array('id' => $id, 'taxonomyId' => $taxonomyId));
		}
		
		public function queueDeleteTermEvent($id,$taxonomyId) {
			$this->queueEvent('TERM_DELETED',array('id' => $id, 'taxonomyId' => $taxonomyId));
		}
		
		// queue event for flushing later
		public function queueEvent($event,$data) {
			$this->eventQueue[$event]=$data;
		}
		
		public function inEventQueue($checkEvent) {
			foreach ($this->eventQueue as $event => $data) {
				if ($event==$checkEvent) {
					return true;
				}
			}
			return false;
		}
		
		public function pruneEvent($checkEvent) {
			$prunedEvents = array();
			foreach ($this->eventQueue as $event => $data) {
				if ($event!=$checkEvent) {
					$prunedEvents[$event]=$data;
				}
			}
			$this->eventQueue = $prunedEvents;
		}
		
		public function pruneEventQueue() {
			if ($this->inEventQueue('NODE_DELETED')) {
				$this->pruneEvent('NODE_TRASHED');
				$this->pruneEvent('NODE_UPDATED');
			}
			if ($this->inEventQueue('NODE_TRASHED')) {
				$this->pruneEvent('NODE_UPDATED');
			}
			if ($this->inEventQueue('NODE_CREATED')) {
				$this->pruneEvent('NODE_UPDATED');
			}
			if ($this->inEventQueue('NODE_PREVIEW')) {
				$this->pruneEvent('NODE_UPDATED');
			}
			if ($this->inEventQueue('TERM_CREATED')) {
				$this->pruneEvent('TERM_UPDATED');
			}
		}
		
		// called on shutdown of Wordpress request (after sending response to user) to send requests to Bricks
		public function flushEventQueue() {
			$this->pruneEventQueue();
			
			// iterate event queue
			foreach ($this->eventQueue as $event => $data) {
				if ($event == 'NODE_PREVIEW') {
					//require_once(dirname(__FILE__).'/../../../wp-admin/includes/post.php');
					//wp_create_post_autosave($data['id']);
				}
				// push event authenticating using api key
				$url = $this->getApiUrlPrefix().'event?key='.$this->getApiKey().'&kernel=wordpress&domain='.urlencode($this->domain).'&scope='.urlencode($this->blogId).'&event='.urlencode($event).'&data='.urlencode(serialize($data));
				wp_remote_get($url);
				if ($event == 'NODE_PREVIEW') {
					update_post_meta($data['id'],'bricks_basic_pages_bridge_previewed',true);
				}
			}
			
			if (isset($_GET['twentysteps_bricks_bridge_wp_invalidate_all']) && current_user_can('manage_options') && check_admin_referer('twentysteps-bricks-bridge-wp')) {
				// push event authenticating using api key
				$url = $this->getApiUrlPrefix().'event?key='.$this->getApiKey().'&kernel=wordpress&domain='.urlencode($this->domain).'&scope='.urlencode($this->blogId).'&event='.urlencode('INVALIDATE_ALL').'&data='.urlencode(serialize($data));
				wp_remote_get($url);
			}
		}
		
	}
	
	// create singleton
 
	$bricksBridge = new BricksBridge();
