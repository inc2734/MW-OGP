<?php
// Update Settings
if ( isset( $_POST['submit'] ) ) {
	if ( ! current_user_can( 'manage_options' ) )
		die( __( 'You cannot edit the search-by-category options.' ) );
	check_admin_referer( 'mw_ogp-updatesettings' );

	// Get our new option values
	// Update the DB with the new option values
	update_option( 'mw_ogp', array(
		'app_id' => $_POST['app_id'],
		'type' => $_POST['type'],
		'image' => $_POST['image']
	) );
}

// Get Current DB Values
$options = get_option( 'mw_ogp' );

$types = array(
	'Activities' => array(
		'activity',
		'sport'
	),
	'Businesses' => array(
		'bar',
		'company',
		'cafe',
		'hotel',
		'restaurant'
	),
	'Groups' => array(
		'cause',
		'sports_league',
		'sports_team'
	),
	'Organizations' => array(
		'band',
		'government',
		'non_profit',
		'school',
		'university'
	),
	'People' => array(
		'actor',
		'athlete',
		'author',
		'director',
		'musician',
		'politician',
		'public_figure'
	),
	'Places' => array(
		'city',
		'country',
		'landmark',
		'state_province'
	),
	'Products and Entertainment' => array(
		'album',
		'book',
		'drink',
		'food',
		'game',
		'product',
		'song',
		'movie',
		'tv_show'
	),
	'Websites' => array(
		'blog',
		'website',
		'article'
	)
);
?>
<div class="wrap">
	<h2>MW OGP</h2>
	<ul>
		<li><a href="https://developers.facebook.com/" target="_blank">facebook DEVELOPERS</a></li>
		<li><a href="http://2inc.org/" target="_blank">モンキーレンチ</a></li>
	</ul>
	<form action="" method="post" id="mw_ogp-config">
		<table class="form-table">
			<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'mw_ogp-updatesettings' ); ?>
			<tr>
				<th scope="row" valign="top" style="width:20%"><label for="app_id">fb:app_id</label></th>
				<td><input type="text" name="app_id" id="app_id" class="regular-text" value="<?php echo $options['app_id']; ?>"/></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="type">fb:type( Front Page )<br />Defaut : blog</label></th>
				<td>
					<select name="type" id="type">
						<?php foreach ( $types as $optgroupLbl => $optgroup ) : ?>
						<optgroup label="<?php echo esc_html( $optgroupLbl ); ?>">
							<?php foreach ( $optgroup as $type ) : ?>
							<?php $selected = ( $options['type'] == $type ) ? ' selected="selected"' : ''; ?>
							<option value="<?php echo esc_html( $type ); ?>"<?php echo $selected; ?>><?php echo esc_html( $type ); ?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="image">fb:image</label></th>
				<td><input type="text" name="image" id="image" class="regular-text" value="<?php echo $options['image']; ?>"/></td>
			</tr>
		</table>
		<br/>
		<span class="submit" style="border: 0;"><input type="submit" name="submit" value="Save Settings" /></span>
	</form>
</div>
