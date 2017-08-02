<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	require_once( LEGISEARCH__PLUGIN_DIR . '/DataClasses/LegisearchSettings.class.php' );
	$legisearch_settings = LegisearchSettings::getInstance();
	require_once( LEGISEARCH__PLUGIN_DIR . '/admin/settings.action.php' );
?>
<div class="wrap">
	<h1>Legisearch Settings</h1>
	<?php legisearch_show_messages(); ?>

	<form method="POST" action="">
	<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="legisearch_os_apikey">Sunlight Foundation API Key</label></th>
		<td>
			<input type="text" size="34" name="legisearch_os_apikey" value="<?php echo $legisearch_settings->os_apikey; ?>" /><br />
			<?php if( empty( $legisearch_settings->os_apikey ) ) { ?>
				You must register for a Sunlight Foundation API Key for this application by visiting the Sunlight Foundation's
				<a href="http://sunlightfoundation.com/api/accounts/register/">API Registration</a>.
			<?php } ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="legisearch_os_timeout">Sunlight Foundation Cache Timeout</label></th>
		<td>
			<input type="text" size="6" name="legisearch_os_cachetimeout" value="<?php echo $legisearch_settings->os_cache_timeout; ?>" /> hours&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="remove_cache" value="Remove Cache" class="button-secondary" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="legisearch_gm_serverkey">Google Maps Server Key</label></th>
		<td>
			<input type="text" size="44" name="legisearch_gm_serverkey" value="<?php echo $legisearch_settings->gm_serverkey; ?>" /><br />
			<?php if( empty( $legisearch_settings->gm_serverkey ) ): ?>
				You must register for a Google Maps Server Key by visiting Google's
				<a href="https://developers.google.com/maps/documentation/geocoding/get-api-key">Google Maps Geocoding API</a>.
			<?php endif; ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="legisearch_gm_browserkey">Google Maps Browser Key</label></th>
		<td>
			<input type="text" size="44" name="legisearch_gm_browserkey" value="<?php echo $legisearch_settings->gm_browserkey; ?>" /><br />
			<?php if( empty( $legisearch_settings->gm_browserkey ) ): ?>
				You must register for a Google Maps Browser Key by visiting Google's
				<a href="https://developers.google.com/maps/documentation/geocoding/get-api-key">Google Maps Geocoding API</a>.
			<?php endif; ?>
		</td>
	</tr>
	<tr valign="top">
		<td>
		<?php wp_nonce_field( 'legisearch_change_settings','legisearch_action' ); ?>
		<input type="submit" name="save_settings" value="Save Options" class="button-primary" />
		</td>
	</tr>
	</table>
	</form>
