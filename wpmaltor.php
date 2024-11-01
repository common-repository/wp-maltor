<?php
/*
Plugin Name: WP MalTor
Plugin URI: http://wordpress.org/plugins/wp-maltor/
Description: This plugin bans Tor Network's and malicious IPs from visiting the site.
Version: 0.1.5
Author: davidmerinas,miguel.arroyo
Author URI: http://www.davidmerinas.com
*/

$_SESSION['WP_MalTor_PATH']=plugin_dir_path( __FILE__ );

function WP_MalTor_action(){
	$active=get_option("WP_MalTor_active");
	$refreshtime=35;//Minutes
	
	if($active=="on")
	{
		WP_MalTor_refreshIPs($refreshtime);
		$ip=WP_MalTor_getuserIP();
		//WP_MalTor_checkip($ip);
	}
}

function WP_MalTor_refreshIPs($refreshtime=0){
	$lastupdate = get_option("WP_MalTor_lastupdate");
	$torlist=get_option("WP_MalTor_torlist");
	
	//$urls=array("http://myip.ms/files/blacklist/csf/latest_blacklist.txt");
	$urls=array("http://iprep.wpmaltor.com/");
	if($torlist=="on")
	{
		//$urls[]="https://www.dan.me.uk/torlist/";
		$urls[]="http://torlist.wpmaltor.com/";
	}
	
	$error=false;
	//Checking lastupdate time
	if(!$lastupdate||(time()-$lastupdate>=$refreshtime*60))
	{
		//$iplist = get_option("WP_MalTor_iplist");
		$streamoptions = stream_context_create(array(
				'http' => array(
						'timeout' => 10
				)
			)
		);
		$iplist=array();
		foreach($urls as $url)
		{
			try{
				@$list=file_get_contents($url,0,$streamoptions);
				
				if($list!=""&&$list!=null)
				{
					$iplist=array_merge($iplist,explode("\n",$list));
				}
				else
				{
					$error=true;
				}
			}
			catch(Exception $e){
				$iplist=$e->getMessage();
				$error=true;
			}
		}
		if(!$error)
		{
			update_option("WP_MalTor_iplist", $iplist);
			update_option("WP_MalTor_lastupdate", time());
			delete_option("WP_MalTor_error");
		}
		else 
		{
			update_option("WP_MalTor_error", "Error downloading IP List");
		}
	}
}

function WP_MalTor_checkip($ip){
	$iplist = get_option("WP_MalTor_iplist");
	if(is_array($iplist)&&in_array($ip,$iplist))
	{
		$blocked = get_option("WP_MalTor_blockedlist");
		if(!$blocked)
		{
			$blocked=array('ip'=>$ip,'time'=>time());
		}
		else 
		{
			$blocked[]=array('ip'=>$ip,'time'=>time());
		}
		update_option("WP_MalTor_blockedlist", $blocked);
		
		$action = get_option("WP_MalTor_action");
		switch($action)
		{
			case "blank":
				echo("Sorry, not found");
				break;
			case "redirection":
				$url = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'),htmlspecialchars(get_option("WP_MalTor_redirect_url"),ENT_QUOTES));
				if(!isset($url)||$url==null||trim($url)=="")
				{
					$url='http://www.google.com';
				}
				
				header('Location: '.$url,true,302);
				break;
			case "error404":
				header("HTTP/1.0 404 Not Found");
				echo("<html><head>
					<title>404 Not Found</title>
					</head><body>
					<h1>Not Found</h1>
					<p>The requested URL was not found on this server.</p>
					<p>Additionally, a 404 Not Found
					error was encountered while trying to use an ErrorDocument to handle the request.</p>
					<hr>
					<address>Server</address>
					</body></html>");
				break;
			default:
				$path=get_bloginfo('url')."/wp-content/plugins/".basename( dirname( __FILE__ ) )."/";
				echo(str_replace("{{IMAGEN}}","<img src='".$path."images/default.png' alt='WP MalTor'/>",file_get_contents($path.'render_default.html')));				
		}
		
		die();
	}
}

function WP_MalTor_getuserIP(){
	if (isset($_SERVER['HTTP_CLIENT_IP'])){
		$ip=$_SERVER['HTTP_CLIENT_IP'];
		WP_MalTor_checkip($ip);
	}
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		WP_MalTor_checkip($ip);
	}
	if (isset($_SERVER['REMOTE_ADDR'])){
		$ip=$_SERVER['REMOTE_ADDR'];
		WP_MalTor_checkip($ip);
	}
	return $ip;
}


function WP_MalTor_settings_page() {
	?>
<div class="wrap">
<h2>WP Maltor</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'WP_MalTor-settings-group' ); ?>
    <?php do_settings_sections( 'WP_MalTor-settings-group' ); ?>
    <p><?php _e('Malicious IPs are blocked by default','WP_MalTor');?>.</p>
    <table class="form-table">
		<tr style="width:420px" valign="top">
			<th scope="row"><?php _e('Active','WP_MalTor');?> Maltor</th>
			<td><input type="checkbox" name="WP_MalTor_active" <?php echo get_option('WP_MalTor_active')?'checked="checked"':''; ?>/></td>
        </tr>
		<tr style="width:420px" valign="top">
			<th scope="row"><?php _e('Block IPs from Tor','WP_MalTor');?></th>
			<td><input type="checkbox" name="WP_MalTor_torlist" <?php echo get_option('WP_MalTor_torlist')?'checked="checked"':''; ?>/></td>
        </tr>
         
		 <tr style="width:420px" valign="top">
        <th scope="row"><?php _e('Action','WP_MalTor');?></th>
        <td>
			<select style="width:120px" name="WP_MalTor_action">
				<option value="default" <?php echo(get_option('WP_MalTor_action')=="default"?'selected="selected"':'')?>><?php _e('Default','WP_MalTor');?></option>
				<option value="blank" <?php echo(get_option('WP_MalTor_action')=="blank"?'selected="selected"':'')?>><?php _e('Blank Page','WP_MalTor');?></option>
				<option value="error404" <?php echo(get_option('WP_MalTor_action')=="error404"?'selected="selected"':'')?>><?php _e('Error 404','WP_MalTor');?></option>
				<option value="redirection" <?php echo(get_option('WP_MalTor_action')=="redirection"?'selected="selected"':'')?>><?php _e('Redirection','WP_MalTor');?></option>
			</select>
		</td>
        </tr>
		
		<tr style="width:420px" valign="top">
        <th scope="row">Redirection URL (http://...)</th>
        <td><input style="width:320px" type="text" name="WP_MalTor_redirect_url" value="<?php echo str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'),htmlspecialchars(get_option('WP_MalTor_redirect_url'),ENT_QUOTES)); ?>" /></td>
        </tr>
        
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php
}

function WP_MalTor_register_mysettings() {
	//register our settings
	register_setting( 'WP_MalTor-settings-group', 'WP_MalTor_active' );
	register_setting( 'WP_MalTor-settings-group', 'WP_MalTor_action' );
	register_setting( 'WP_MalTor-settings-group', 'WP_MalTor_torlist' );
	register_setting( 'WP_MalTor-settings-group', 'WP_MalTor_redirect_url' );
}

// Add settings link on plugin page
function WP_MalTor_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=wp_maltor_options">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

function WP_MalTor_activate() {
	update_option("WP_MalTor_active",'on');
	update_option("WP_MalTor_torlist",'false');
	update_option("WP_MalTor_action",'default');
}

function WP_MalTor_adminmenu() {
	add_options_page('WP Maltor options', 'WP Maltor', 'manage_options', 'wp_maltor_options','WP_MalTor_settings_page');
}

//Add action
add_action('init', 'WP_MalTor_action');
register_activation_hook( __FILE__, 'WP_MalTor_activate' );

if ( is_admin() ){
	$plugin = plugin_basename(__FILE__); 
	add_action( 'admin_init', 'WP_MalTor_register_mysettings' );
	add_filter("plugin_action_links_$plugin", 'WP_MalTor_settings_link' );
	add_action( 'admin_menu', 'Wp_MalTor_adminmenu' );
}



?>