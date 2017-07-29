<?php
function wp_ihs_admin_notice()
{
	add_thickbox();
	$sharelink_text_array_ihs = array
						(
						"I use Insert HTML Snippet wordpress plugin from @xyzscripts and you should too.",
						"Insert HTML Snippet wordpress plugin from @xyzscripts is awesome",
						"Thanks @xyzscripts for developing such a wonderful wordpress plugin for inserting html",
						"I was looking for a HTML Snippet insertion plugin and I found this. Thanks @xyzscripts",
						"Its very easy to use Insert HTML Snippet wordpress plugin from @xyzscripts",
						"I installed Insert HTML Snippet from @xyzscripts,it works flawlessly",
						"Insert HTML Snippet wordpress plugin that i use works terrific",
						"I am using Insert HTML Snippet wordpress plugin from @xyzscripts and I like it",
						"The Insert HTML Snippet plugin from @xyzscripts is simple and works fine",
						"I've been using this Snippet plugin for a while now and it is really good",
						"Insert HTML Snippet wordpress plugin is a fantastic plugin",
						"Insert HTML Snippet wordpress plugin is easy to use and works great. Thank you!",
						"Good and flexible  Insert HTML Snippet plugin especially for beginners",
						"The best Insert HTML Snippet wordpress plugin I have used ! THANKS @xyzscripts",
						);
$sharelink_text_ihs = array_rand($sharelink_text_array_ihs, 1);
$sharelink_text_ihs = $sharelink_text_array_ihs[$sharelink_text_ihs];

	
	echo '<style>
	#TB_window { width:50% !important;  height: 100px !important;
	margin-left: 25% !important; 
	left: 0% !important; 
	}
	</style>
	<script type="text/javascript">
	function share_html_snippet(){
	tb_show("Share on","#TB_inline?width=500&amp;height=75&amp;inlineId=show_share_icons_ihs&class=thickbox");
	}
	</script>
	<div id="ihs_notice_td" class="error" style="color: #666666;margin-left: 2px;background: none repeat scroll 0pt 0pt infobackground; border: 1px solid inactivecaption; padding: 5px;line-height:16px;">
	<p>It looks like you have been enjoying using <a href="https://wordpress.org/plugins/insert-html-snippet/" target="_blank"> Insert HTML Snippet  </a> plugin from Xyzscripts for atleast 30 days.Would you consider supporting us with the continued development of the plugin using any of the below methods?</p>
	<p>
	<a href="https://wordpress.org/support/view/plugin-reviews/insert-html-snippet" class="button" style="color:black;text-decoration:none;margin-right:4px;" target="_blank">Rate it 5★\'s on wordpress</a>';
	if(get_option('xyz_credit_link')=="0")
		echo '<a class="button xyz_ihs_backlink" style="color:black;text-decoration:none;margin-right:4px;" target="_blank">Enable Backlink</a>';
	
	echo '<a class="button" onclick=share_html_snippet(); style="color:black;text-decoration:none;margin-right:4px;">Share on</a>
	
	<a href="admin.php?page=insert-html-snippet-settings&ihs_notice=hide" class="button" style="color:black;text-decoration:none;margin-right:4px;">Don\'t Show This Again</a>
	</p>
	
	<div id="show_share_icons_ihs" style="display: none;">
	<a class="button" style="background-color:#3b5998;color:white;margin-right:4px;margin-left:100px;margin-top: 25px;" href="http://www.facebook.com/sharer/sharer.php?u=https://wordpress.org/plugins/insert-html-snippet/&text='.$sharelink_text_ihs.'" target="_blank">Facebook</a>
	<a class="button" style="background-color:#00aced;color:white;margin-right:4px;margin-left:20px;margin-top: 25px;" href="http://twitter.com/share?url=https://wordpress.org/plugins/insert-html-snippet/&text='.$sharelink_text_ihs.'" target="_blank">Twitter</a>
	<a class="button" style="background-color:#007bb6;color:white;margin-right:4px;margin-left:20px;margin-top: 25px;" href="http://www.linkedin.com/shareArticle?mini=true&url=https://wordpress.org/plugins/insert-html-snippet/" target="_blank">LinkedIn</a>
	<a class="button" style="background-color:#dd4b39;color:white;margin-right:4px;margin-left:20px;margin-top: 25px;" href="https://plus.google.com/share?&hl=en&url=https://wordpress.org/plugins/insert-html-snippet/" target="_blank">google+</a>
	</div>
	</div>';	
	
}
$ihsap_installed_date = get_option('ihsap_installed_date');
if ($ihsap_installed_date=="") {
	$ihsap_installed_date = time();
}

if($ihsap_installed_date < ( time() - (30*24*60*60) ))
{
	if (get_option('xyz_ihs_dnt_shw_notice') != "hide")
	{
		add_action('admin_notices', 'wp_ihs_admin_notice');
	}
}
?>