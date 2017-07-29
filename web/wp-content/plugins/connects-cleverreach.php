<?php
/**
* Plugin Name: Connects - CleverReach Addon
* Plugin URI: 
* Description: Use this plugin to integrate CleverReach with Connects..
* Version: 2.1.1
* Author: Brainstorm Force
* Author URI: https://www.brainstormforce.com/
* License:
*/

define( 'CPCR_CLEVERREACH_API_URL', 'http://api.cleverreach.com/soap/interface_v5.1.php?wsdl' );


if(!class_exists('Smile_Mailer_Cleverreach')){
	class Smile_Mailer_Cleverreach{
	
		//Class variables
		private $slug;
		private $setting;
		protected $api;
		
		/*
		 * Function Name: __construct
		 * Function Description: Constructor
		 */
		
		function __construct(){
			add_action( 'admin_init', array( $this, 'enqueue_scripts' ) );
			require_once('cleverreach/cpcr-api-class.php');
			add_action( 'wp_ajax_get_cleverreach_data', array($this,'get_cleverreach_data' ));
			add_action( 'wp_ajax_update_cleverreach_authentication', array($this,'update_cleverreach_authentication' ));
			add_action( 'wp_ajax_disconnect_cleverreach', array($this,'disconnect_cleverreach' ));
			add_action( 'wp_ajax_cleverreach_add_subscriber', array($this,'cleverreach_add_subscriber' ));
			add_action( 'wp_ajax_nopriv_cleverreach_add_subscriber', array($this,'cleverreach_add_subscriber' ));
			$this->setting  = array(
				'name' => 'CleverReach',
				'parameters' => array( 'api_key' ),
				'where_to_find_url' => 'http://support.cleverreach.de/hc/en-us/articles/202373121-Locating-API-keys-list-IDs-and-form-IDs',
				'logo_url' => plugins_url('images/logo.png', __FILE__)
			);
			$this->slug = 'cleverreach';
		}
		
		
		/*
		 * Function Name: enqueue_scripts
		 * Function Description: Add custon scripts
		 */
		
		function enqueue_scripts() {
			if( function_exists( 'cp_register_addon' ) ) {
				cp_register_addon( $this->slug, $this->setting );
			}
			wp_register_script( $this->slug.'-script', plugins_url('js/' . $this->slug . '-script.js', __FILE__), array('jquery'), '1.1', true );
			wp_enqueue_script( $this->slug.'-script' );
			add_action( 'admin_head', array( $this, 'hook_css' ) );
		}

		/*
		 * Function Name: hook_css
		 * Function Description: Adds background style script for mailer logo.
		 */


		function hook_css() {
			if( isset( $this->setting['logo_url'] ) ) {
				if( $this->setting['logo_url'] != '' ) {
					$style = '<style>table.bsf-connect-optins td.column-provider.'.$this->slug.'::after {background-image: url("'.$this->setting['logo_url'].'");}.bend-heading-section.bsf-connect-list-header .bend-head-logo.'.$this->slug.'::before {background-image: url("'.$this->setting['logo_url'].'");} .cn-form-check {
						    margin-top: 15px; width: 100%; display: inline-block; } .cn-form-check > label{ float:left; width: 65%; } .cn-form-check .switch-wrapper{ float:left; width: 7%; margin-top:0; } .bsf-cnlist-form-row div span.cp-form-decision { font-size: 14px; color: #444; }
					</style>';
					echo $style;
				}
			}
			
		}
		
		/*
		 * Function Name: get_cleverreach_data
		 * Function Description: Get cleverreach input fields
		 */
		 
		function get_cleverreach_data(){
			$isKeyChanged = false;
			$connected = false;
			$lists = $html = null;
			$islistempty = false;

			ob_start();
			
			$cleverreach_api_key = $auth['api_key'] = get_option($this->slug.'_api_key');
			
			if( $cleverreach_api_key != '' ) {
            	
				$this->api = new cpcr_cleverreach_api( $auth['api_key'] );
				$api_test_result = $this->api->cpcr_test_api_key();
				$api_connected = $api_test_result['success'];
				$api_message =  $api_test_result['message'];

				if( true === $api_connected ) {
					$list_result = $this->api->cpcr_get_lists();
					if( $list_result['success'] ){
						$lists = $list_result['lists'];
						$lists = json_decode( json_encode( $lists), true );
						if ( count( $lists ) < 1 ) {
							$formstyle = '';
							$lists = false;
							$islistempty = true;	
						}
					}else{
						$lists = false;
					}
				}else{
					$lists = false;
				}


				if( $lists == false && $islistempty == false ) {
					$formstyle = '';
					$isKeyChanged = true;	
				}else{
					$formstyle = 'style="display:none;"';
				}
			} else {
            	$formstyle = '';
			}
            ?>
            <div class="bsf-cnlist-form-row" <?php echo $formstyle; ?> >
				<label for="<?php echo $this->slug; ?>_api_key"><?php _e( $this->setting['name']." API Key", "smile" ); ?></label>            
	            <input type="text" autocomplete="off" id="<?php echo $this->slug; ?>_api_key" name="<?php echo $this->slug; ?>-api-key" value="<?php echo esc_attr( $cleverreach_api_key ); ?>"/>
	        	
	        </div>
			
			<div class="bsf-cnlist-form-row <?php echo $this->slug; ?>-list">
	            <?php
	   	        if( $cleverreach_api_key == '' && $isKeyChanged )
				 	$lists = '';
				
				if( $lists != '' ){
					$connected = true;

					$html .= '<label for="' . $this->slug . '-list">'.__( "Select List", "smile" ).'</label>';
					$html .= '<select id="' . $this->slug . '-list" class="bsf-cnlist-select" name="' . $this->slug . '-list">';
					foreach( $lists as $offset => $list ) {
						$html .= '<option value="'.$list['id'].'">'.$list['name'].'</option>';
					}
					$html .= '</select>';

					$html .= $this->cp_display_form_selection($lists);

					echo $html;
				}
	            ?>
            </div>
			
			<?php if( $islistempty == true ){ ?><div class="bsf-cnlist-form-row cleverreach-list-empty"><span class="bsf-mailer-error"><?php _e( "You have zero lists in your ".$this->setting['name']." account. You must have at least one list before integration.", "smile"); ?></span></div><?php } ?>

			<div class="bsf-cnlist-form-row">

            	<?php if( ( $cleverreach_api_key != '' && $islistempty == true ) || ( $cleverreach_api_key == ''  ) ) { ?>
	            	
        		    <?php if( $islistempty == false ){ ?>
	            	<button id="auth-<?php echo $this->slug; ?>" class="button button-secondary auth-button" <?php if( $islistempty == false ){ ?>disabled<?php } ?>><?php _e( "Authenticate ".$this->setting['name'],"smile" ); ?></button><span class="spinner" style="float: none;"></span>
	            	<?php } else { ?>
	            	<div id="disconnect-<?php echo $this->slug; ?>" class="button button-secondary auth-button" data-mailerslug="<?php echo $this->slug; ?>" data-mailer="<?php echo $this->slug; ?>"><span><?php _e( "Use different '" . $this->setting['name'] . "' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            	<?php } ?>

	            <?php } else {
	            		if( $isKeyChanged ) {
	            ?>
	            	<div id="update-<?php echo $this->slug; ?>" class="update-mailer" data-mailerslug="<?php echo $this->setting['name']; ?>" data-mailer="<?php echo $this->slug; ?>"><span><?php _e( "Your credentials seems to be changed.</br>Use different '" . $this->setting['name'] . " credentials?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            <?php
	            		} else {
	            ?>
	            	<div id="disconnect-<?php echo $this->slug; ?>" class="" data-mailerslug="<?php echo $this->slug; ?>" data-mailer="<?php echo $this->slug; ?>"><span><?php _e( "Use different '" . $this->setting['name'] . "' account?", "smile" ); ?></span></div><span class="spinner" style="float: none;"></span>
	            <?php
	            		}}
	            ?>
	        </div>
			

            <?php
            $content = ob_get_clean();
            $result['data'] = $content;
            $result['helplink'] = $this->setting['where_to_find_url'];
            $result['isconnected'] = $connected;
            echo json_encode($result);
            exit();
        }
		
		
		/*
		 * Function Name: update_cleverreach_authentication
		 * Function Description: Update cleverreach values to ConvertPlug
		 */
		
		function update_cleverreach_authentication(){
			$post = $_POST;
			$cpts_err = false;
			$lists = null;
			

			if( $post[$this->slug.'_api_key'] == '' ) {
				$status = 'error';
				$message = 'API Key is missing.';
				$cpts_err = true;
			}
			if ( !$cpts_err ) {
				$data = array();
				$auth['api_key'] = $post[$this->slug.'_api_key'];
				
				$this->api = new cpcr_cleverreach_api( $auth['api_key'] );
				$api_test_result = $this->api->cpcr_test_api_key();
				
				$api_connected = $api_test_result['success'];
				$api_message =  $api_test_result['message'];

				if( true === $api_connected ) {
					$list_result = $this->api->cpcr_get_lists();

					if( $list_result['success'] ){
						$lists = $list_result['lists'];
					}else{
						$lists = false;
					}
				} else {
					print_r(json_encode(array(
						'status' => 'fail', 
						'message' => 'Access denied: Invalid API Key credentials.'
					)));
					exit();
				}
				
				if( $lists !== false ){

					
					if( count($lists) == 0 ) {
						print_r(json_encode(array(
							'status' => "error",
							'message' => __( "You have zero lists in your " . $this->setting['name'] . " account. You must have at least one list before integration." , "smile" )
						)));
						die();
					} else {
						
						
						$lists = json_decode( json_encode( $lists), true );
						$ts_lists = array();
						$html = $query = '';
						$html .= '<label for="' . $this->slug . '-list">'.__( "Select List", "smile" ).'</label>';
						$html .= '<select id="' . $this->slug . '-list" class="bsf-cnlist-select" name="' . $this->slug . '-list">';
						foreach( $lists as $offset => $list ) {
							if( isset($list['id']) ) {
								$html .= '<option value="'.$list['id'].'">'.$list['name'].'</option>';
								$query .= $list['id'].'|'.$list['name'].',';
								$ts_lists[$list['id']] = $list['name'];
							}
						}

						$html .= '</select>';

						$html .= $this->cp_display_form_selection($lists);

						$html .= '<input type="hidden" id="mailer-all-lists" value="'.esc_attr($query).'"/>';
						$html .= '<input type="hidden" id="mailer-list-action" value="update_' . $this->slug . '_list"/>';

						ob_start();
						?>
						<div class="bsf-cnlist-form-row">
							<div id="disconnect-<?php echo $this->slug; ?>" class="" data-mailerslug="<?php echo $this->slug; ?>" data-mailer="<?php echo $this->slug; ?>">
								<span>
									<?php _e( "Use different '" . $this->setting['name'] . "' account?", "smile" ); ?>
								</span>
							</div>
							<span class="spinner" style="float: none;"></span>
						</div>
						<?php
						$html .= ob_get_clean();

						$status = 'success';
						$message = $html;
					}
				}else{
					print_r(json_encode(array(
						'status' => 'fail', 
						'message' => 'Access denied: Invalid API Key credentials.'
					)));
					exit();
				}
			}else{

					print_r(json_encode(array(
						'status' => $status, 
						'message' => $message
					)));
					exit();
			}


			update_option( $this->slug.'_api_key', $auth['api_key'] );
			update_option($this->slug.'_lists',$ts_lists);

			print_r(json_encode(array(
				'status' => $status, 
				'message' => $message
			)));
			exit();
		}
		
		
		/*
		 * Function Name: cleverreach_add_subscriber
		 * Function Description: Add subscriber
		 */
		
		function cleverreach_add_subscriber(){
			$ret = true;
			$email_status = false;
			$errorMsg = '';
			$style_id = isset( $_POST['style_id'] ) ? $_POST['style_id'] : '';
            $contact = $_POST['param'];
            $contact['source'] = ( isset( $_POST['source'] ) ) ? $_POST['source'] : '';
            $msg = isset( $_POST['message'] ) ? $_POST['message'] : __( 'Thanks for subscribing. Please check your mail and confirm the subscription.', 'smile' );
			$cleverreach_api_key = $auth['api_key'] = get_option($this->slug.'_api_key');
			$data = array();
			$customfields = array();
			$customfields_param = '';

			if ( is_user_logged_in() && current_user_can( 'access_cp' ) ) {
                $default_error_msg = __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'smile' );
            } else {
                $default_error_msg = __( 'THERE WAS AN ISSUE WITH YOUR REQUEST. Administrator has been notified already!', 'smile' );
            }

			//	Check Email in MX records
			if( isset( $_POST['param']['email'] ) ) {
                $email_status = ( !( isset( $_POST['only_conversion'] ) ? true : false ) ) ? apply_filters('cp_valid_mx_email', $_POST['param']['email'] ) : false;
            }
			
			if($email_status) {
				if( function_exists( "cp_add_subscriber_contact" ) ){
					$isuserupdated = cp_add_subscriber_contact( $_POST['option'] , $contact );
				}

				if ( !$isuserupdated ) {  // if user is updated dont count as a conversion
					// update conversions
					smile_update_conversions($style_id);
				}
				
				if( isset( $_POST['param']['email'] ) ) {
					$status = 'success';
					
					$data = array(
						"cleverreach_email" => $_POST['param']['email'],
					);

					foreach( $_POST['param'] as $key => $p ) {
                        if( $key != 'email' && $key != 'user_id' && $key != 'date' ){
                        	$data[$key] = $p;
                        }
                    }
									
					// sync contacts with mailer
					$this->api = new cpcr_cleverreach_api( $auth['api_key'] );
					$form_id = '';

					if( isset($_POST['list_parent_index']) && $_POST['list_parent_index'] !== '' ) {
						$smile_lists = get_option('smile_lists');

						if( isset( $smile_lists[$_POST['list_parent_index']]['cleverreach-form'] ) ) {
							$form_id = $smile_lists[$_POST['list_parent_index']]['cleverreach-form'];
						}
					}

					$optinvar   = get_option( 'convert_plug_settings' );
		            $d_optin    = isset($optinvar['cp-double-optin']) ? $optinvar['cp-double-optin'] : 1;

		            // if form id is not available, process as single optin
		            if( $form_id == '' ) {
		            	$d_optin = 0;
		            }

 					$result = $this->api->cpcr_subscribe_user( $data, $_POST['list_id'], $d_optin );
 						
 					// if form id is available and double optin is on, send activation email	
 					if( $form_id !== '' && $d_optin == 1 ) {
 						$result = $this->api->cpcr_send_activation_mail( $form_id, $data['cleverreach_email'] );
 					}

					if( isset($result['success']) && $result['success'] == 'DUPLICATE' ) {
						//	Show message for already subscribed users
						$optinvar =	get_option( 'convert_plug_settings' );
						
                        $msg = ( $optinvar['cp-default-messages'] ) ? isset( $optinvar['cp-already-subscribed']) ? stripslashes( $optinvar['cp-already-subscribed'] ) : __( 'Already Subscribed!', 'smile' ) : __( 'Already Subscribed!', 'smile' );
                        
						if( isset( $_POST['source'] ) ) {
			        		return true;
			        	} else {
							print_r(json_encode(array(
								'action' => ( isset( $_POST['message'] ) ) ? 'message' : 'redirect',
								'email_status' => $email_status,
								'status' => 'success',
								'message' => $msg,
								'url' => ( isset( $_POST['message'] ) ) ? 'none' : $_POST['redirect'],
							)));
							die();
						}
					}elseif( isset($result['success']) && !$result['success'] == 'OK' ) {						
						if ( is_user_logged_in() && current_user_can( 'access_cp' ) ) {
			                $detailed_msg = isset($result['message']) ? $result['message'] : '';
			            } else {
			                $detailed_msg = '';
			            }			
			            if( $detailed_msg !== '' && $detailed_msg !== null ) {
			                $page_url = isset( $_POST['cp-page-url'] ) ? $_POST['cp-page-url'] : '';

			                // notify error message to admin
			                if( function_exists('cp_notify_error_to_admin') ) {
			                    $result   = cp_notify_error_to_admin($page_url);
			                }
			            }
						if( isset( $_POST['source'] ) ) {
			        		return false;
			        	} else {
							print_r(json_encode(array(
								'action' => ( isset( $_POST['message'] ) ) ? 'message' : 'redirect',
								'email_status' => $email_status,
								'status' => 'error',
								'message' => $default_error_msg,
								'detailed_msg' => $detailed_msg,
								'url' => ( isset( $_POST['message'] ) ) ? 'none' : $_POST['redirect'],
							)));
							die();
						}
					}
				}
			} else {
				if( isset( $_POST['only_conversion'] ) ? true : false ){
					// update conversions 
					$status = 'success';
					smile_update_conversions( $style_id );
					$ret = true;
				} else if( isset( $_POST['param']['email'] ) ) {
                    $msg = ( isset( $_POST['msg_wrong_email']  )  && $_POST['msg_wrong_email'] !== '' ) ? $_POST['msg_wrong_email'] : __( 'Please enter correct email address.', 'smile' );
                    $status = 'error';
                    $ret = false;
                } else if( !isset( $_POST['param']['email'] ) ) {
                    //$msg = __( 'Something went wrong. Please try again.', 'smile' );
                    $msg  = $default_error_msg;
                    $errorMsg = __( 'Email field is mandatory to set in form.', 'smile' );
                    $status = 'error';
                }
			}

			if ( is_user_logged_in() && current_user_can( 'access_cp' ) ) {
                $detailed_msg = $errorMsg;
            } else {
                $detailed_msg = '';
            }

            if( $detailed_msg !== '' &&  $detailed_msg !== null ) {
                $page_url = isset( $_POST['cp-page-url'] ) ? $_POST['cp-page-url'] : '';

                // notify error message to admin
                if( function_exists('cp_notify_error_to_admin') ) {
                    $result   = cp_notify_error_to_admin($page_url);
                }
            }

			if( isset( $_POST['source'] ) ) {
	    		return $ret;
	    	} else {
	    		print_r(json_encode(array(
					'action' => ( isset( $_POST['message'] ) ) ? 'message' : 'redirect',
					'email_status' => $email_status,
					'status' => $status,
					'message' => $msg,
					'detailed_msg' => $detailed_msg,
					'url' => ( isset( $_POST['message'] ) ) ? 'none' : $_POST['redirect'],
				)));

				exit();
	    	}
		}
		
		
		/*
		 * Function Name: disconnect_cleverreach
		 * Function Description: Disconnect current TotalSend from wp instance
		 */
		
		function disconnect_cleverreach(){
			delete_option( $this->slug.'_api_key' );
			delete_option( $this->slug.'_lists' );


			$smile_lists = get_option('smile_lists');
			if( !empty( $smile_lists ) ){
				foreach( $smile_lists as $key => $list ) {
					$provider = $list['list-provider'];
					if( strtolower( $provider ) == strtolower( $this->slug ) ){
						$smile_lists[$key]['list-provider'] = "Convert Plug";
						$contacts_option = "cp_" . $this->slug . "_" . preg_replace( '#[ _]+#', '_', strtolower( $list['list-name'] ) );
                        $contact_list = get_option( $contacts_option );
                        $deleted = delete_option( $contacts_option );
                        $status = update_option( "cp_connects_" . preg_replace( '#[ _]+#', '_', strtolower( $list['list-name'] ) ), $contact_list );
					}
				}
				update_option( 'smile_lists', $smile_lists );
			}

			print_r(json_encode(array(
                'message' => "disconnected",
			)));
			die();
		}


		function cp_display_form_selection($lists) {
			
			$uniq = uniqid();
			$html = '<div class="cn-form-check">';

			$html .= '<label>Do You Want Form Based Campaign? <br> <span class="cp-form-decision">( Select YES if you are looking for Double Optin support. )</span></label>';
			$html .= '<div class="switch-wrapper">
				<input type="text" id="smile_cn_form_display" class="form-control smile-input smile-switch-input " value="Modal Popup">
				<input type="checkbox"  id="smile_cn_form_display_btn_'.$uniq.'" name="cn_form_display" class="ios-toggle smile-input smile-switch-input switch-checkbox smile-switch " value="form check">
				<label class="smile-switch-btn checkbox-label" data-on="YES" data-off="NO" data-id="smile_cn_form_display" for="smile_cn_form_display_btn_'.$uniq.'">
				</label>
			</div>';

			$html .= '</div>';

			$html .= "<span class='cp-form-notice' style='display:none;' >You have zero forms attached to this list.</span>";

			$html .= '<select id="' . $this->slug . '-form" class="bsf-cnform-select" name="' . $this->slug . '-form" style="display:none;">';

			if( is_array($lists) ) {
				foreach( $lists as $offset => $list ) {
					if( is_array($list['forms']) && count($lists) > 0 ) {
						foreach ($list['forms'] as $key => $value) {
							$html .= '<option data-list="'.$list['id'].'" value="'.$value['id'].'">'.$value['name'].'</option>';
						}
					}
				}
			}

			$html .= '</select>';

			return $html;
		}



	}
	new Smile_Mailer_Cleverreach;	
}

$bsf_core_version_file = realpath(dirname(__FILE__).'/admin/bsf-core/version.yml');
if(is_file($bsf_core_version_file)) {
	global $bsf_core_version, $bsf_core_path;
	$bsf_core_dir = realpath(dirname(__FILE__).'/admin/bsf-core/');
	$version = file_get_contents($bsf_core_version_file);
	if(version_compare($version, $bsf_core_version, '>')) {
		$bsf_core_version = $version;
		$bsf_core_path = $bsf_core_dir;
	}
}
add_action('init', 'bsf_core_load', 999);
if(!function_exists('bsf_core_load')) {
	function bsf_core_load() {
		global $bsf_core_version, $bsf_core_path;
		if(is_file(realpath($bsf_core_path.'/index.php'))) {
			include_once realpath($bsf_core_path.'/index.php');
		}
	}
}
?>