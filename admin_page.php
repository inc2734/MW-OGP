<?php
class mw_ogp_admin_page {

	public $types = array(
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
	
	public $locales = array(
		'Afrikaans' => 'af_ZA',
		'Arabic' => 'ar_AR',
		'Azeri' => 'az_AZ',
		'Belarusian' => 'be_BY',
		'Bulgarian' => 'bg_BG',
		'Bengali' => 'bn_IN',
		'Bosnian' => 'bs_BA',
		'Catalan' => 'ca_ES',
		'Czech' => 'cs_CZ',
		'Welsh' => 'cy_GB',
		'Danish' => 'da_DK',
		'German' => 'de_DE',
		'Greek' => 'el_GR',
		'English (UK)' => 'en_GB',
		'English (Pirate)' => 'en_PI',
		'English (Upside Down)' => 'en_UD',
		'English (US)' => 'en_US',
		'Esperanto' => 'eo_EO',
		'Spanish (Spain)' => 'es_ES',
		'Spanish' => 'es_LA',
		'Estonian' => 'et_EE',
		'Basque' => 'eu_ES',
		'Persian' => 'fa_IR',
		'Leet Speak' => 'fb_LT',
		'Finnish' => 'fi_FI',
		'Faroese' => 'fo_FO',
		'French (Canada)' => 'fr_CA',
		'French (France)' => 'fr_FR',
		'Frisian' => 'fy_NL',
		'Irish' => 'ga_IE',
		'Galician' => 'gl_ES',
		'Hebrew' => 'he_IL',
		'Hindi' => 'hi_IN',
		'Croatian' => 'hr_HR',
		'Hungarian' => 'hu_HU',
		'Armenian' => 'hy_AM',
		'Indonesian' => 'id_ID',
		'Icelandic' => 'is_IS',
		'Italian' => 'it_IT',
		'Japanese' => 'ja_JP',
		'Georgian' => 'ka_GE',
		'Khmer' => 'km_KH',
		'Korean' => 'ko_KR',
		'Kurdish' => 'ku_TR',
		'Latin' => 'la_VA',
		'Lithuanian' => 'lt_LT',
		'Latvian' => 'lv_LV',
		'Macedonian' => 'mk_MK',
		'Malayalam' => 'ml_IN',
		'Malay' => 'ms_MY',
		'Norwegian (bokmal)' => 'nb_NO',
		'Nepali' => 'ne_NP',
		'Dutch' => 'nl_NL',
		'Norwegian (nynorsk)' => 'nn_NO',
		'Punjabi' => 'pa_IN',
		'Polish' => 'pl_PL',
		'Pashto' => 'ps_AF',
		'Portuguese (Brazil)' => 'pt_BR',
		'Portuguese (Portugal)' => 'pt_PT',
		'Romanian' => 'ro_RO',
		'Russian' => 'ru_RU',
		'Slovak' => 'sk_SK',
		'Slovenian' => 'sl_SI',
		'Albanian' => 'sq_AL',
		'Serbian' => 'sr_RS',
		'Swedish' => 'sv_SE',
		'Swahili' => 'sw_KE',
		'Tamil' => 'ta_IN',
		'Telugu' => 'te_IN',
		'Thai' => 'th_TH',
		'Filipino' => 'tl_PH',
		'Turkish' => 'tr_TR',
		'Ukrainian' => 'uk_UA',
		'Vietnamese' => 'vi_VN',
		'Simplified Chinese (China)' => 'zh_CN',
		'Traditional Chinese (Hong Kong)' => 'zh_HK',
		'Traditional Chinese (Taiwan)' => 'zh_TW'
	);

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * do update_option
	 *  @return	Boolean
	 */
	private function _updateOption() {
		$updateFlg = update_option( 'mw_ogp', array(
			'app_id' => $_POST['app_id'],
			'type' => $_POST['type'],
			'image' => $_POST['image'],
			'locale' => $_POST['locale'],
		) );
		return $updateFlg;
	}

	/**
	 * view
	 */
	public function view() {
		if ( isset( $_POST['submit'] ) ) {
			if ( ! current_user_can( 'manage_options' ) )
				die( __( 'You cannot edit the search-by-category options.' ) );
			check_admin_referer( 'mw_ogp-updatesettings', 'mw_ogp-updatesettings' );
			$updateFlg = $this->_updateOption();
		}
		$options = get_option( 'mw_ogp' );
		?>
<div class="wrap">
	<?php screen_icon( 'edit-pages' ); ?>
	<h2>MW OGP</h2>
	<?php if ( !empty( $updateFlg ) ) : ?>
	<div class="updated"><p><strong>Settings has been updated.</strong></p></div>
	<?php endif; ?>
	<ul>
		<li><a href="https://developers.facebook.com/" target="_blank">facebook DEVELOPERS</a></li>
		<li><a href="http://developers.facebook.com/tools/debug" target="_blank">Debugger - Facebook Developers</a></li>
		<li><a href="http://2inc.org/" target="_blank">モンキーレンチ</a></li>
	</ul>
	<form action="" method="post" id="mw_ogp-config">
		<table class="form-table">
			<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( 'mw_ogp-updatesettings', 'mw_ogp-updatesettings' ); ?>
			<tr>
				<th scope="row" valign="top" style="width:20%"><label for="app_id">fb:app_id</label></th>
				<td><input type="text" name="app_id" id="app_id" class="regular-text" value="<?php echo esc_html( $options['app_id'] ); ?>"/></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="type">og:type( Front Page )<br />Defaut : blog</label></th>
				<td>
					<select name="type" id="type">
						<?php foreach ( $this->types as $optgroupLbl => $optgroup ) : ?>
						<optgroup label="<?php echo esc_html( $optgroupLbl ); ?>">
							<?php foreach ( $optgroup as $type ) : ?>
							<option value="<?php echo esc_html( $type ); ?>"<?php selected( $options['type'], $type ); ?>><?php echo esc_html( $type ); ?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="image">og:image</label></th>
				<td><input type="text" name="image" id="image" class="regular-text" value="<?php echo esc_html( $options['image'] ); ?>"/></td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label for="locale">og:locale<br />Defaut : Japanese</label></th>
				<td>
					<select name="locale" id="locale">
						<?php foreach ( $this->locales as $localeLbl => $locale ) : ?>
						<option value="<?php echo esc_html( $locale ); ?>"<?php selected( $options['locale'], $locale ); ?>><?php echo esc_html( $localeLbl ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<br/>
		<span class="submit" style="border: 0;"><input type="submit" name="submit" value="Save Settings" /></span>
	</form>
</div>
		<?php
	}
}
?>