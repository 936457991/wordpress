<?php
/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 *
 * @package hreflang-manager
 */

use Daextteam\PluginUpdateChecker\PluginUpdateChecker;

/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 */
class Dahm_Shared {


	/**
	 * Regex used to validate a number with 1 to 10 digits.
	 *
	 * @var string
	 */
	public $regex_number_ten_digits = '/^\s*\d{1,10}\s*$/';

	private $hreflang_checker = null;

	/**
	 * The singleton instance of the class.
	 *
	 * @var Dahm_Shared
	 */
	protected static $instance = null;

	/**
	 * The data of the plugin.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 */
	private function __construct() {

		add_action( 'init', array( $this, 'cron_schedules_init' ) );

		// Add the action associated with the "da_hm_cron_hook". Which is scheduled in Dahm_Admin::schedule_cron_event().
		add_action( 'da_hm_cron_hook', array( $this, 'da_hm_cron_exec' ) );

		add_action( 'da_hm_cron_hook_2', array( $this, 'da_hm_cron_exec_2' ) );

		$this->data['slug'] = 'da_hm';
		$this->data['ver']  = '1.43';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, - 7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, - 7 );

		require_once $this->get( 'dir' ) . 'inc/class-dahm-hreflang-checker.php';
		$this->hreflang_checker = Dahm_Hreflang_Checker::get_instance( $this );

		// Here are stored the plugin option with the related default values.
		$this->data['options'] = array(

			// Database Version (not available in the options UI).
			$this->get( 'slug' ) . '_database_version'            => '7',

			// Options version (not available in the options UI).
			$this->get( 'slug' ) . '_options_version'             => '2',

			// General ------------------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_detect_url_mode'             => 'wp_request',
			$this->get( 'slug' ) . '_https'                       => '1',
			$this->get( 'slug' ) . '_auto_trailing_slash'         => '1',
			$this->get( 'slug' ) . '_auto_delete'                 => '1',
			$this->get( 'slug' ) . '_auto_alternate_pages'        => '0',
			$this->get( 'slug' ) . '_sample_future_permalink'     => '0',
			$this->get( 'slug' ) . '_show_log'                    => '0',
			$this->get( 'slug' ) . '_connections_in_menu'         => '10',
			$this->get( 'slug' ) . '_meta_box_post_types'         => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_set_max_execution_time'      => '1',
			$this->get( 'slug' ) . '_max_execution_time_value'    => '300',
			$this->get( 'slug' ) . '_set_memory_limit'            => '1',
			$this->get( 'slug' ) . '_memory_limit_value'          => '1024',

			// Sync.
			$this->get( 'slug' ) . '_sync_status'                 => '0',
			$this->get( 'slug' ) . '_sync_role'                   => '0',
			$this->get( 'slug' ) . '_sync_frequency'              => '2',
			$this->get( 'slug' ) . '_sync_master_rest_endpoint'   => '',
			$this->get( 'slug' ) . '_sync_mode'                   => '0',
			$this->get( 'slug' ) . '_sync_language'               => 'en',
			$this->get( 'slug' ) . '_sync_script'                 => '',
			$this->get( 'slug' ) . '_sync_locale'                 => '',
			$this->get( 'slug' ) . '_sync_delete_target'          => '0',

			// Import.
			$this->get( 'slug' ) . '_import_mode'                 => 'exact_copy',
			$this->get( 'slug' ) . '_import_language'             => 'en',
			$this->get( 'slug' ) . '_import_script'               => '',
			$this->get( 'slug' ) . '_import_locale'               => '',

			// Capabilities.
			$this->get( 'slug' ) . '_meta_box_capability'         => 'manage_options',
			$this->get( 'slug' ) . '_editor_sidebar_capability'   => 'manage_options',
			$this->get( 'slug' ) . '_connections_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_bulk_import_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_tools_menu_capability'       => 'manage_options',
			$this->get( 'slug' ) . '_checker_menu_capability'     => 'manage_options',
			$this->get( 'slug' ) . '_maintenance_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_license_provider'            => 'daext_com',
			$this->get( 'slug' ) . '_license_key'                 => '',

			// Checker ------------------------------------------------------------------------------------------------.

			// WP-Cron.
			$this->get( 'slug' ) . '_checker_checks_per_iteration'                 => '2',
			$this->get( 'slug' ) . '_checker_cron_schedule_interval'                 => '60',
			$this->get( 'slug' ) . '_checker_request_timeout'                 => '10',

			// Hreflang Checks.
			$this->get( 'slug' ) . '_checker_invalid_http_response'                  => '1',
			$this->get( 'slug' ) . '_checker_duplicate_hreflang_entries'             => '1',
			$this->get( 'slug' ) . '_checker_missing_self_referencing_hreflang'      => '1',
			$this->get( 'slug' ) . '_checker_incorrect_language_script_region_codes' => '1',
			$this->get( 'slug' ) . '_checker_missing_hreflang_x_default'             => '0',
			$this->get( 'slug' ) . '_checker_missing_return_link'                    => '0',
			$this->get( 'slug' ) . '_checker_canonical_and_hreflang_conflict'        => '1',

		);

		// Defaults ---------------------------------------------------------------------------------------------------.
		for ( $i = 1; $i <= 100; $i ++ ) {
			$this->data['options'][ $this->get( 'slug' ) . '_default_language_' . $i ] = 'en';
			$this->data['options'][ $this->get( 'slug' ) . '_default_script_' . $i ]   = '';
			$this->data['options'][ $this->get( 'slug' ) . '_default_locale_' . $i ]   = '';
		}

		// language list (ISO_639-1).
		$da_hm_language                                              = array(
			"Don't target a specific language or locale"                       => 'x-default',
			'Abkhaz'                                                           => 'ab',
			'Afar'                                                             => 'aa',
			'Afrikaans'                                                        => 'af',
			'Akan'                                                             => 'ak',
			'Albanian'                                                         => 'sq',
			'Amharic'                                                          => 'am',
			'Arabic'                                                           => 'ar',
			'Aragonese'                                                        => 'an',
			'Armenian'                                                         => 'hy',
			'Assamese'                                                         => 'as',
			'Avaric'                                                           => 'av',
			'Avestan'                                                          => 'ae',
			'Aymara'                                                           => 'ay',
			'Azerbaijani'                                                      => 'az',
			'Bambara'                                                          => 'bm',
			'Bashkir'                                                          => 'ba',
			'Basque'                                                           => 'eu',
			'Belarusian'                                                       => 'be',
			'Bengali/Bangla'                                                   => 'bn',
			'Bihari'                                                           => 'bh',
			'Bislama'                                                          => 'bi',
			'Bosnian'                                                          => 'bs',
			'Breton'                                                           => 'br',
			'Bulgarian'                                                        => 'bg',
			'Burmese'                                                          => 'my',
			'Catalan/Valencian'                                                => 'ca',
			'Chamorro'                                                         => 'ch',
			'Chechen'                                                          => 'ce',
			'Chichewa/Chewa/Nyanja'                                            => 'ny',
			'Chinese'                                                          => 'zh',
			'Chuvash'                                                          => 'cv',
			'Cornish'                                                          => 'kw',
			'Corsican'                                                         => 'co',
			'Cree'                                                             => 'cr',
			'Croatian'                                                         => 'hr',
			'Czech'                                                            => 'cs',
			'Danish'                                                           => 'da',
			'Divehi/Dhivehi/Maldivian'                                         => 'dv',
			'Dutch'                                                            => 'nl',
			'Dzongkha'                                                         => 'dz',
			'English'                                                          => 'en',
			'Esperanto'                                                        => 'eo',
			'Estonian'                                                         => 'et',
			'Ewe'                                                              => 'ee',
			'Faroese'                                                          => 'fo',
			'Fijian'                                                           => 'fj',
			'Finnish'                                                          => 'fi',
			'French'                                                           => 'fr',
			'Fula/Fulah/Pulaar/Pular'                                          => 'ff',
			'Galician'                                                         => 'gl',
			'Georgian'                                                         => 'ka',
			'German'                                                           => 'de',
			'Greek/Modern'                                                     => 'el',
			'Guaraní'                                                          => 'gn',
			'Gujarati'                                                         => 'gu',
			'Haitian/Haitian Creole'                                           => 'ht',
			'Hausa'                                                            => 'ha',
			'Hebrew (modern)'                                                  => 'he',
			'Herero'                                                           => 'hz',
			'Hindi'                                                            => 'hi',
			'Hiri Motu'                                                        => 'ho',
			'Hungarian'                                                        => 'hu',
			'Interlingua'                                                      => 'ia',
			'Indonesian'                                                       => 'id',
			'Interlingue'                                                      => 'ie',
			'Irish'                                                            => 'ga',
			'Igbo'                                                             => 'ig',
			'Inupian'                                                          => 'ik',
			'Ido'                                                              => 'io',
			'Icelandic'                                                        => 'is',
			'Italian'                                                          => 'it',
			'Inuktitut'                                                        => 'iu',
			'Japanese'                                                         => 'ja',
			'Javanese'                                                         => 'jv',
			'Kalaallisut/Greenlandic'                                          => 'kl',
			'Kannada'                                                          => 'kn',
			'Kanuri'                                                           => 'kr',
			'Kashmiri'                                                         => 'ks',
			'Kazakh'                                                           => 'kk',
			'Khmer'                                                            => 'km',
			'Kikuyu/Gikuyu'                                                    => 'ki',
			'Kinyarwanda'                                                      => 'rw',
			'Kyrgyz'                                                           => 'ky',
			'Komi'                                                             => 'kv',
			'Kongo'                                                            => 'kg',
			'Korean'                                                           => 'ko',
			'Kurdish'                                                          => 'ku',
			'Kwanyama/Kuanyama'                                                => 'kj',
			'Latin'                                                            => 'la',
			'Luxembourgish/Letzeburgesch'                                      => 'lb',
			'Ganda'                                                            => 'lg',
			'Limburgish/Limburgan/Limburger'                                   => 'li',
			'Lingala'                                                          => 'ln',
			'Lao'                                                              => 'lo',
			'Lithuanian'                                                       => 'lt',
			'Luba-Katanga'                                                     => 'lu',
			'Latvian'                                                          => 'lv',
			'Manx'                                                             => 'gv',
			'Macedonian'                                                       => 'mk',
			'Malagasy'                                                         => 'mg',
			'Malay'                                                            => 'ms',
			'Malayalam'                                                        => 'ml',
			'Maltese'                                                          => 'mt',
			'Māori'                                                            => 'mi',
			'Marathi/Marāṭhī'                                                  => 'mr',
			'Marshallese'                                                      => 'mh',
			'Mongolian'                                                        => 'mn',
			'Nauru'                                                            => 'na',
			'Navajo/Navaho'                                                    => 'nv',
			'Norwegian Bokmål'                                                 => 'nb',
			'North Ndebele'                                                    => 'nd',
			'Nepali'                                                           => 'ne',
			'Ndonga'                                                           => 'ng',
			'Norwegian Nynorsk'                                                => 'nn',
			'Norwegian'                                                        => 'no',
			'Nuosu'                                                            => 'ii',
			'South Ndebele'                                                    => 'nr',
			'Occitan'                                                          => 'oc',
			'Ojibwe/Ojibwa'                                                    => 'oj',
			'Old C. Slavonic/C. Slavic/C. Slavonic/Old Bulgarian/Old Slavonic' => 'cu',
			'Oromo'                                                            => 'om',
			'Orija'                                                            => 'or',
			'Ossetian/Ossetic'                                                 => 'os',
			'Panjabi/Punjabi'                                                  => 'pa',
			'Pāli'                                                             => 'pi',
			'Persian (Farsi)'                                                  => 'fa',
			'Polish'                                                           => 'pl',
			'Pashto/Pushto'                                                    => 'ps',
			'Portuguese'                                                       => 'pt',
			'Quechua'                                                          => 'qu',
			'Romansh'                                                          => 'rm',
			'Kirundi'                                                          => 'rn',
			'Romanian'                                                         => 'ro',
			'Russian'                                                          => 'ru',
			'Sanskrit (Saṁskṛta)'                                              => 'sa',
			'Sardinian'                                                        => 'sc',
			'Sindhi'                                                           => 'sd',
			'Northern Sami'                                                    => 'se',
			'Samoan'                                                           => 'sm',
			'Sango'                                                            => 'sg',
			'Serbian'                                                          => 'sr',
			'Scottish Gaelic/Gaelic'                                           => 'gd',
			'Shona'                                                            => 'sn',
			'Sinhala/Sinhalese'                                                => 'si',
			'Slovak'                                                           => 'sk',
			'Slovene'                                                          => 'sl',
			'Somali'                                                           => 'so',
			'Southern Sotho'                                                   => 'st',
			'South Azebaijani'                                                 => 'az',
			'Spanish/Castilian'                                                => 'es',
			'Sundanese'                                                        => 'su',
			'Swahili'                                                          => 'sw',
			'Swati'                                                            => 'ss',
			'Swedish'                                                          => 'sv',
			'Tamil'                                                            => 'ta',
			'Telugu'                                                           => 'te',
			'Tajik'                                                            => 'tg',
			'Thai'                                                             => 'th',
			'Tigrinya'                                                         => 'ti',
			'Tibetan Standard/Tibetan/Central'                                 => 'bo',
			'Turkmen'                                                          => 'tk',
			'Tagalog'                                                          => 'tl',
			'Tswana'                                                           => 'tn',
			'Tonga (Tonga Islands)'                                            => 'to',
			'Turkish'                                                          => 'tr',
			'Tsonga'                                                           => 'ts',
			'Tatar'                                                            => 'tt',
			'Twi'                                                              => 'tw',
			'Tahitian'                                                         => 'ty',
			'Uyghur/Uighur'                                                    => 'ug',
			'Ukrainian'                                                        => 'uk',
			'Urdu'                                                             => 'ur',
			'Uzbek'                                                            => 'uz',
			'Venda'                                                            => 've',
			'Vietnamese'                                                       => 'vi',
			'Volapük'                                                          => 'vo',
			'Walloon'                                                          => 'wa',
			'Welsh'                                                            => 'cy',
			'Wolof'                                                            => 'wo',
			'Western Frisian'                                                  => 'fy',
			'Xhosa'                                                            => 'xh',
			'Yiddish'                                                          => 'yi',
			'Yoruba'                                                           => 'yo',
			'Zhuang/Chuang'                                                    => 'za',
			'Zulu'                                                             => 'zu',
		);
		$this->data['options'][ $this->get( 'slug' ) . '_language' ] = $da_hm_language;

		// Script list (ISO 15924).
		$da_hm_script                                              = array(
			'Adlam'                                                                                           => 'Adlm',
			'Afaka'                                                                                           => 'Afak',
			'Caucasian Albanian'                                                                              => 'Aghb',
			'Ahom, Tai Ahom'                                                                                  => 'Ahom',
			'Arabic'                                                                                          => 'Arab',
			'Arabic (Nastaliq variant)'                                                                       => 'Aran',
			'Imperial Aramaic'                                                                                => 'Armi',
			'Armenian'                                                                                        => 'Armn',
			'Avestan'                                                                                         => 'Avst',
			'Balinese'                                                                                        => 'Bali',
			'Bamum'                                                                                           => 'Bamu',
			'Bassa Vah'                                                                                       => 'Bass',
			'Batak'                                                                                           => 'Batk',
			'Bengali (Bangla)'                                                                                => 'Beng',
			'Bhaiksuki'                                                                                       => 'Bhks',
			'Blissymbols'                                                                                     => 'Blis',
			'Bopomofo'                                                                                        => 'Bopo',
			'Brahmi'                                                                                          => 'Brah',
			'Braille'                                                                                         => 'Brai',
			'Buginese'                                                                                        => 'Bugi',
			'Buhid'                                                                                           => 'Buhd',
			'Chakma'                                                                                          => 'Cakm',
			'Unified Canadian Aboriginal Syllabics'                                                           => 'Cans',
			'Carian'                                                                                          => 'Cari',
			'Cham'                                                                                            => 'Cham',
			'Cherokee'                                                                                        => 'Cher',
			'Chorasmian'                                                                                      => 'Chrs',
			'Cirth'                                                                                           => 'Cirt',
			'Coptic'                                                                                          => 'Copt',
			'Cypro-Minoan'                                                                                    => 'Cpmn',
			'Cypriot syllabary'                                                                               => 'Cprt',
			'Cyrillic'                                                                                        => 'Cyrl',
			'Cyrillic (Old Church Slavonic variant)'                                                          => 'Cyrs',
			'Devanagari (Nagari)'                                                                             => 'Deva',
			'Dives Akuru'                                                                                     => 'Diak',
			'Dogra'                                                                                           => 'Dogr',
			'Deseret (Mormon)'                                                                                => 'Dsrt',
			'Duployan shorthand, Duployan stenography'                                                        => 'Dupl',
			'Egyptian demotic'                                                                                => 'Egyd',
			'Egyptian hieratic'                                                                               => 'Egyh',
			'Egyptian hieroglyphs'                                                                            => 'Egyp',
			'Elbasan'                                                                                         => 'Elba',
			'Elymaic'                                                                                         => 'Elym',
			'Ethiopic (Geʻez)'                                                                                => 'Ethi',
			'Khutsuri (Asomtavruli and Nuskhuri)'                                                             => 'Geok',
			'Georgian (Mkhedruli and Mtavruli)'                                                               => 'Geor',
			'Glagolitic'                                                                                      => 'Glag',
			'Gunjala Gondi'                                                                                   => 'Gong',
			'Masaram Gondi'                                                                                   => 'Gonm',
			'Gothic'                                                                                          => 'Goth',
			'Grantha'                                                                                         => 'Gran',
			'Greek'                                                                                           => 'Grek',
			'Gujarati'                                                                                        => 'Gujr',
			'Gurmukhi'                                                                                        => 'Guru',
			'Han with Bopomofo (alias for Han + Bopomofo)'                                                    => 'Hanb',
			'Hangul (Hangŭl, Hangeul)'                                                                        => 'Hang',
			'Han (Hanzi, Kanji, Hanja)'                                                                       => 'Hani',
			'Hanunoo (Hanunóo)'                                                                               => 'Hano',
			'Han (Simplified variant)'                                                                        => 'Hans',
			'Han (Traditional variant)'                                                                       => 'Hant',
			'Hatran'                                                                                          => 'Hatr',
			'Hebrew'                                                                                          => 'Hebr',
			'Hiragana'                                                                                        => 'Hira',
			'Anatolian Hieroglyphs (Luwian Hieroglyphs, Hittite Hieroglyphs)'                                 => 'Hluw',
			'Pahawh Hmong'                                                                                    => 'Hmng',
			'Nyiakeng Puachue Hmong'                                                                          => 'Hmnp',
			'Japanese syllabaries (alias for Hiragana + Katakana)'                                            => 'Hrkt',
			'Old Hungarian (Hungarian Runic)'                                                                 => 'Hung',
			'Indus (Harappan)'                                                                                => 'Inds',
			'Old Italic (Etruscan, Oscan, etc.)'                                                              => 'Ital',
			'Jamo (alias for Jamo subset of Hangul)'                                                          => 'Jamo',
			'Javanese'                                                                                        => 'Java',
			'Japanese (alias for Han + Hiragana + Katakana)'                                                  => 'Jpan',
			'Jurchen'                                                                                         => 'Jurc',
			'Kayah Li'                                                                                        => 'Kali',
			'Katakana'                                                                                        => 'Kana',
			'Kharoshthi'                                                                                      => 'Khar',
			'Khmer'                                                                                           => 'Khmr',
			'Khojki'                                                                                          => 'Khoj',
			'Khitan large script'                                                                             => 'Kitl',
			'Khitan small script'                                                                             => 'Kits',
			'Kannada'                                                                                         => 'Knda',
			'Korean (alias for Hangul + Han)'                                                                 => 'Kore',
			'Kpelle'                                                                                          => 'Kpel',
			'Kaithi'                                                                                          => 'Kthi',
			'Tai Tham (Lanna)'                                                                                => 'Lana',
			'Lao'                                                                                             => 'Laoo',
			'Latin (Fraktur variant)'                                                                         => 'Latf',
			'Latin (Gaelic variant)'                                                                          => 'Latg',
			'Latin'                                                                                           => 'Latn',
			'Leke'                                                                                            => 'Leke',
			'Lepcha (Róng)'                                                                                   => 'Lepc',
			'Limbu'                                                                                           => 'Limb',
			'Linear A'                                                                                        => 'Lina',
			'Linear B'                                                                                        => 'Linb',
			'Lisu (Fraser)'                                                                                   => 'Lisu',
			'Loma'                                                                                            => 'Loma',
			'Lycian'                                                                                          => 'Lyci',
			'Lydian'                                                                                          => 'Lydi',
			'Mahajani'                                                                                        => 'Mahj',
			'Makasar'                                                                                         => 'Maka',
			'Mandaic, Mandaean'                                                                               => 'Mand',
			'Manichaean'                                                                                      => 'Mani',
			'Marchen'                                                                                         => 'Marc',
			'Mayan hieroglyphs'                                                                               => 'Maya',
			'Medefaidrin (Oberi Okaime, Oberi Ɔkaimɛ)'                                                        => 'Medf',
			'Mende Kikakui'                                                                                   => 'Mend',
			'Meroitic Cursive'                                                                                => 'Merc',
			'Meroitic Hieroglyphs'                                                                            => 'Mero',
			'Malayalam'                                                                                       => 'Mlym',
			'Modi, Moḍī'                                                                                      => 'Modi',
			'Mongolian'                                                                                       => 'Mong',
			'Moon (Moon code, Moon script, Moon type)'                                                        => 'Moon',
			'Mro, Mru'                                                                                        => 'Mroo',
			'Meitei Mayek (Meithei, Meetei)'                                                                  => 'Mtei',
			'Multani'                                                                                         => 'Mult',
			'Myanmar (Burmese)'                                                                               => 'Mymr',
			'Nandinagari'                                                                                     => 'Nand',
			'Old North Arabian (Ancient North Arabian)'                                                       => 'Narb',
			'Nabataean'                                                                                       => 'Nbat',
			'Newa, Newar, Newari, Nepāla lipi'                                                                => 'Newa',
			'Naxi Dongba (na²¹ɕi³³ to³³ba²¹, Nakhi Tomba)'                                                    => 'Nkdb',
			"Naxi Geba (na²¹ɕi³³ gʌ²¹ba²¹, 'Na-'Khi ²Ggŏ-¹baw, Nakhi Geba)"                                   => 'Nkgb',
			'N’Ko'                                                                                            => 'Nkoo',
			'Nüshu'                                                                                           => 'Nshu',
			'Ogham'                                                                                           => 'Ogam',
			'Ol Chiki (Ol Cemet’, Ol, Santali)'                                                               => 'Olck',
			'Old Turkic, Orkhon Runic'                                                                        => 'Orkh',
			'Oriya (Odia)'                                                                                    => 'Orya',
			'Osage'                                                                                           => 'Osge',
			'Osmanya'                                                                                         => 'Osma',
			'Old Uyghur'                                                                                      => 'Ougr',
			'Palmyrene'                                                                                       => 'Palm',
			'Pau Cin Hau'                                                                                     => 'Pauc',
			'Proto-Cuneiform'                                                                                 => 'Pcun',
			'Proto-Elamite'                                                                                   => 'Pelm',
			'Old Permic'                                                                                      => 'Perm',
			'Phags-pa'                                                                                        => 'Phag',
			'Inscriptional Pahlavi'                                                                           => 'Phli',
			'Psalter Pahlavi'                                                                                 => 'Phlp',
			'Book Pahlavi'                                                                                    => 'Phlv',
			'Phoenician'                                                                                      => 'Phnx',
			'Miao (Pollard)'                                                                                  => 'Plrd',
			'Klingon (KLI pIqaD)'                                                                             => 'Piqd',
			'Inscriptional Parthian'                                                                          => 'Prti',
			'Proto-Sinaitic'                                                                                  => 'Psin',
			'Reserved for private use (start)'                                                                => 'Qaaa',
			'Reserved for private use (end)'                                                                  => 'Qabx',
			'Ranjana'                                                                                         => 'Ranj',
			'Rejang (Redjang, Kaganga)'                                                                       => 'Rjng',
			'Hanifi Rohingya'                                                                                 => 'Rohg',
			'Rongorongo'                                                                                      => 'Roro',
			'Runic'                                                                                           => 'Runr',
			'Samaritan'                                                                                       => 'Samr',
			'Sarati'                                                                                          => 'Sara',
			'Old South Arabian'                                                                               => 'Sarb',
			'Saurashtra'                                                                                      => 'Saur',
			'SignWriting'                                                                                     => 'Sgnw',
			'Shavian (Shaw)'                                                                                  => 'Shaw',
			'Sharada, Śāradā'                                                                                 => 'Shrd',
			'Shuishu'                                                                                         => 'Shui',
			'Siddham, Siddhaṃ, Siddhamātṛkā'                                                                  => 'Sidd',
			'Khudawadi, Sindhi'                                                                               => 'Sind',
			'Sinhala'                                                                                         => 'Sinh',
			'Sogdian'                                                                                         => 'Sogd',
			'Old Sogdian'                                                                                     => 'Sogo',
			'Sora Sompeng'                                                                                    => 'Sora',
			'Soyombo'                                                                                         => 'Soyo',
			'Sundanese'                                                                                       => 'Sund',
			'Syloti Nagri'                                                                                    => 'Sylo',
			'Syriac'                                                                                          => 'Syrc',
			'Syriac (Estrangelo variant)'                                                                     => 'Syre',
			'Syriac (Western variant)'                                                                        => 'Syrj',
			'Syriac (Eastern variant)'                                                                        => 'Syrn',
			'Tagbanwa'                                                                                        => 'Tagb',
			'Takri, Ṭākrī, Ṭāṅkrī'                                                                            => 'Takr',
			'Tai Le'                                                                                          => 'Tale',
			'New Tai Lue'                                                                                     => 'Talu',
			'Tamil'                                                                                           => 'Taml',
			'Tangut'                                                                                          => 'Tang',
			'Tai Viet'                                                                                        => 'Tavt',
			'Telugu'                                                                                          => 'Telu',
			'Tengwar'                                                                                         => 'Teng',
			'Tifinagh (Berber)'                                                                               => 'Tfng',
			'Tagalog (Baybayin, Alibata)'                                                                     => 'Tglg',
			'Thaana'                                                                                          => 'Thaa',
			'Thai'                                                                                            => 'Thai',
			'Tibetan'                                                                                         => 'Tibt',
			'Tirhuta'                                                                                         => 'Tirh',
			'Tangsa'                                                                                          => 'Tnsa',
			'Toto'                                                                                            => 'Toto',
			'Ugaritic'                                                                                        => 'Ugar',
			'Vai'                                                                                             => 'Vaii',
			'Visible Speech'                                                                                  => 'Visp',
			'Vithkuqi'                                                                                        => 'Vith',
			'Warang Citi (Varang Kshiti)'                                                                     => 'Wara',
			'Wancho'                                                                                          => 'Wcho',
			'Woleai'                                                                                          => 'Wole',
			'Old Persian'                                                                                     => 'Xpeo',
			'Cuneiform, Sumero-Akkadian'                                                                      => 'Xsux',
			'Yezidi'                                                                                          => 'Yezi',
			'Yi'                                                                                              => 'Yiii',
			'Zanabazar Square (Zanabazarin Dörböljin Useg, Xewtee Dörböljin Bicig, Horizontal Square Script)' => 'Zanb',
			'Code for inherited script'                                                                       => 'Zinh',
			'Mathematical notation'                                                                           => 'Zmth',
			'Symbols (Emoji variant)'                                                                         => 'Zsye',
			'Symbols'                                                                                         => 'Zsym',
			'Code for unwritten documents'                                                                    => 'Zxxx',
			'Code for undetermined script'                                                                    => 'Zyyy',
			'Code for uncoded script'                                                                         => 'Zzzz',
		);
		$this->data['options'][ $this->get( 'slug' ) . '_script' ] = $da_hm_script;

		// Country list (ISO 3166-1 alpha-2).
		$da_hm_locale                                              = array(
			'Andorra'                                      => 'ad',
			'United Arab Emirates'                         => 'ae',
			'Afghanistan'                                  => 'af',
			'Antigua and Barbuda'                          => 'ag',
			'Anguilla'                                     => 'ai',
			'Albania'                                      => 'al',
			'Armenia'                                      => 'am',
			'Angola'                                       => 'ao',
			'Antartica'                                    => 'aq',
			'Argentina'                                    => 'ar',
			'American Samoa'                               => 'as',
			'Austria'                                      => 'at',
			'Australia'                                    => 'au',
			'Aruba'                                        => 'aw',
			'Åland Islands'                                => 'ax',
			'Azerbaijan'                                   => 'az',
			'Bosnia and Herzegovina'                       => 'ba',
			'Barbados'                                     => 'bb',
			'Bangladesh'                                   => 'bd',
			'Belgium'                                      => 'be',
			'Burkina Faso'                                 => 'bf',
			'Bulgaria'                                     => 'bg',
			'Bahrain'                                      => 'bh',
			'Burundi'                                      => 'bi',
			'Benin'                                        => 'bj',
			'Saint Barthélemy'                             => 'bl',
			'Bermuda'                                      => 'bm',
			'Brunei Darussalam'                            => 'bn',
			'Bolivia'                                      => 'bo',
			'Bonaire, Sint Eustatius and Saba'             => 'bq',
			'Brazil'                                       => 'br',
			'Bahamas'                                      => 'bs',
			'Bhutan'                                       => 'bt',
			'Bouvet Island'                                => 'bv',
			'Botswana'                                     => 'bw',
			'Belarus'                                      => 'by',
			'Belize'                                       => 'bz',
			'Canada'                                       => 'ca',
			'Cocos (Keeling) Islands'                      => 'cc',
			'Congo Democratic Republic'                    => 'cd',
			'Central African Republic'                     => 'cf',
			'Congo'                                        => 'cg',
			'Switzerland'                                  => 'ch',
			'Côte d\'Ivoire'                               => 'ci',
			'Cook Islands'                                 => 'ck',
			'Chile'                                        => 'cl',
			'Cameroon'                                     => 'cm',
			'China'                                        => 'cn',
			'Colombia'                                     => 'co',
			'Costa Rica'                                   => 'cr',
			'Cuba'                                         => 'cu',
			'Cape Verde'                                   => 'cv',
			'Curaçao'                                      => 'cw',
			'Christmas Island'                             => 'cx',
			'Cyprus'                                       => 'cy',
			'Czech Republic'                               => 'cz',
			'Germany'                                      => 'de',
			'Djibouti'                                     => 'dj',
			'Denmark'                                      => 'dk',
			'Dominica'                                     => 'dm',
			'Dominican Republic'                           => 'do',
			'Algeria'                                      => 'dz',
			'Ecuador'                                      => 'ec',
			'Estonia'                                      => 'ee',
			'Egypt'                                        => 'eg',
			'Western Sahara'                               => 'eh',
			'Eritrea'                                      => 'er',
			'Spain'                                        => 'es',
			'Ethiopia'                                     => 'et',
			'Finland'                                      => 'fi',
			'Fiji'                                         => 'fj',
			'Falkland Islands (Malvinas)'                  => 'fk',
			'Micronesia Federated States of'               => 'fm',
			'Faroe Islands'                                => 'fo',
			'France'                                       => 'fr',
			'Gabon'                                        => 'ga',
			'United Kingdom'                               => 'gb',
			'Grenada'                                      => 'gd',
			'Georgia'                                      => 'ge',
			'French Guiana'                                => 'gf',
			'Guernsey'                                     => 'gg',
			'Ghana'                                        => 'gh',
			'Gibraltar'                                    => 'gi',
			'Greenland'                                    => 'gl',
			'Gambia'                                       => 'gm',
			'Guinea'                                       => 'gn',
			'Guadeloupe'                                   => 'gp',
			'Equatorial Guinea'                            => 'gq',
			'Greece'                                       => 'gr',
			'South Georgia and the South Sandwich Islands' => 'gs',
			'Guatemala'                                    => 'gt',
			'Guam'                                         => 'gu',
			'Guinea-Bissau'                                => 'gw',
			'Guyana'                                       => 'gy',
			'Hong Kong'                                    => 'hk',
			'Heard Island and McDonald Islands'            => 'hm',
			'Honduras'                                     => 'hn',
			'Croatia'                                      => 'hr',
			'Haiti'                                        => 'ht',
			'Hungary'                                      => 'hu',
			'Indonesia'                                    => 'id',
			'Ireland'                                      => 'ie',
			'Israel'                                       => 'il',
			'Isle of Man'                                  => 'im',
			'India'                                        => 'in',
			'British Indian Ocean Territory'               => 'io',
			'Iraq'                                         => 'iq',
			'Iran, Islamic Republic of'                    => 'ir',
			'Iceland'                                      => 'is',
			'Italy'                                        => 'it',
			'Jersey'                                       => 'je',
			'Jamaica'                                      => 'jm',
			'Jordan'                                       => 'jo',
			'Japan'                                        => 'jp',
			'Kenya'                                        => 'ke',
			'Kyrgyzstan'                                   => 'kg',
			'Cambodia'                                     => 'kh',
			'Kiribati'                                     => 'ki',
			'Comoros'                                      => 'km',
			'Saint Kitts and Nevis'                        => 'kn',
			'Korea, Democratic People\'s Republic of'      => 'kp',
			'Korea, Republic of'                           => 'kr',
			'Kuwait'                                       => 'kw',
			'Cayman Islands'                               => 'ky',
			'Kazakhstan'                                   => 'kz',
			'Lao People\'s Democratic Republic'            => 'la',
			'Lebanon'                                      => 'lb',
			'Saint Lucia'                                  => 'lc',
			'Liechtenstein'                                => 'li',
			'Sri Lanka'                                    => 'lk',
			'Liberia'                                      => 'lr',
			'Lesotho'                                      => 'ls',
			'Lithuania'                                    => 'lt',
			'Luxembourg'                                   => 'lu',
			'Latvia'                                       => 'lv',
			'Libya'                                        => 'ly',
			'Morocco'                                      => 'ma',
			'Monaco'                                       => 'mc',
			'Moldova, Republic of'                         => 'md',
			'Montenegro'                                   => 'me',
			'Saint Martin (French part)'                   => 'mf',
			'Madagascar'                                   => 'mg',
			'Marshall Islands'                             => 'mh',
			'Macedonia, the former Yugoslav Republic of'   => 'mk',
			'Mali'                                         => 'ml',
			'Myanmar'                                      => 'mm',
			'Mongolia'                                     => 'mn',
			'Macao'                                        => 'mo',
			'Northern Mariana Islands'                     => 'mp',
			'Martinique'                                   => 'mq',
			'Mauritania'                                   => 'mr',
			'Montserrat'                                   => 'ms',
			'Malta'                                        => 'mt',
			'Mauritius'                                    => 'mu',
			'Maldives'                                     => 'mv',
			'Malawi'                                       => 'mw',
			'Mexico'                                       => 'mx',
			'Malaysia'                                     => 'my',
			'Mozambique'                                   => 'mz',
			'Namibia'                                      => 'na',
			'New Caledonia'                                => 'nc',
			'Niger'                                        => 'ne',
			'Norfolk Island'                               => 'nf',
			'Nigeria'                                      => 'ng',
			'Nicaragua'                                    => 'ni',
			'Netherlands'                                  => 'nl',
			'Norway'                                       => 'no',
			'Nepal'                                        => 'np',
			'Nauru'                                        => 'nr',
			'Niue'                                         => 'nu',
			'New Zealand'                                  => 'nz',
			'Oman'                                         => 'om',
			'Panama'                                       => 'pa',
			'Peru'                                         => 'pe',
			'French Polynesia'                             => 'pf',
			'Papua New Guinea'                             => 'pg',
			'Philippines'                                  => 'ph',
			'Pakistan'                                     => 'pk',
			'Poland'                                       => 'pl',
			'Saint Pierre and Miquelon'                    => 'pm',
			'Pitcairn'                                     => 'pn',
			'Puerto Rico'                                  => 'pr',
			'Palestine, State of'                          => 'ps',
			'Portugal'                                     => 'pt',
			'Palau'                                        => 'pw',
			'Paraguay'                                     => 'py',
			'Qatar'                                        => 'qa',
			'Réunion'                                      => 're',
			'Romania'                                      => 'ro',
			'Serbia'                                       => 'rs',
			'Russian Federation'                           => 'ru',
			'Rwanda'                                       => 'rw',
			'Saudi Arabia'                                 => 'sa',
			'Solomon Islands'                              => 'sb',
			'Seychelles'                                   => 'sc',
			'Sudan'                                        => 'sd',
			'Sweden'                                       => 'se',
			'Singapore'                                    => 'sg',
			'Saint Helena, Ascension and Tristan da Cunha' => 'sh',
			'Slovenia'                                     => 'si',
			'Svalbard and Jan Mayen'                       => 'sj',
			'Slovakia'                                     => 'sk',
			'Sierra Leone'                                 => 'sl',
			'San Marino'                                   => 'sm',
			'Senegal'                                      => 'sn',
			'Somalia'                                      => 'so',
			'Suriname'                                     => 'sr',
			'South Sudan'                                  => 'ss',
			'Sao Tome and Principe'                        => 'st',
			'El Salvador'                                  => 'sv',
			'Sint Maarten (Dutch part)'                    => 'sx',
			'Syrian Arab Republic'                         => 'sy',
			'Swaziland'                                    => 'sz',
			'Turks and Caicos Islands'                     => 'tc',
			'Chad'                                         => 'td',
			'French Southern Territories'                  => 'tf',
			'Togo'                                         => 'tg',
			'Thailand'                                     => 'th',
			'Tajikistan'                                   => 'tj',
			'Tokelau'                                      => 'tk',
			'Timor-Leste'                                  => 'tl',
			'Turkmenistan'                                 => 'tm',
			'Tunisia'                                      => 'tn',
			'Tonga'                                        => 'to',
			'Turkey'                                       => 'tr',
			'Trinidad and Tobago'                          => 'tt',
			'Tuvalu'                                       => 'tv',
			'Taiwan, Province of China'                    => 'tw',
			'Tanzania, United Republic of'                 => 'tz',
			'Ukraine'                                      => 'ua',
			'Uganda'                                       => 'ug',
			'United States Minor Outlying Islands'         => 'um',
			'United States'                                => 'us',
			'Uruguay'                                      => 'uy',
			'Uzbekistan'                                   => 'uz',
			'Holy See (Vatican City State)'                => 'va',
			'Saint Vincent and the Grenadines'             => 'vc',
			'Venezuela, Bolivarian Republic of'            => 've',
			'Virgin Islands, British'                      => 'vg',
			'Virgin Islands, U.S.'                         => 'vi',
			'Viet Nam'                                     => 'vn',
			'Vanuatu'                                      => 'vu',
			'Wallis and Futuna'                            => 'wf',
			'Samoa'                                        => 'ws',
			'Yemen'                                        => 'ye',
			'Mayotte'                                      => 'yt',
			'South Africa'                                 => 'za',
			'Zambia'                                       => 'zm',
			'Zimbabwe'                                     => 'zw',
		);
		$this->data['options'][ $this->get( 'slug' ) . '_locale' ] = $da_hm_locale;
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return Dahm_Shared|self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Filters the non-default cron schedules.
	 *
	 * @return void
	 */
	public function cron_schedules_init() {
		add_filter( 'cron_schedules', array( $this, 'custom_cron_schedule' ) );
	}

	/**
	 * Retrieve data.
	 *
	 * @param string $index The index of the data to retrieve.
	 *
	 * @return mixed
	 */
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/**
	 *
	 * Generate an array with the connections associated with the current url
	 *
	 * @param string $context The context in which the function is called. Possible values are 'page_html' and 'log'.
	 *
	 * @return false|void
	 */
	public function echo_hreflang_output( $context ) {

		// Get the current url.
		$current_url = $this->get_current_url();

		global $wpdb;

		/**
		 * If the 'Auto Trailing Slash' option is enabled compare the 'url_to_connect' value in the database not only
		 * with $current_url, but also with the URL present in $current_url with the trailing slash manually added or
		 * removed.
		 */
		if ( 1 === intval( get_option( 'da_hm_auto_trailing_slash' ), 10 ) ) {

			if ( substr( $current_url, strlen( $current_url ) - 1 ) === '/' ) {

				/**
				 * In this case there is a trailing slash, so remove it and compare the 'url_to_connect' value in the
				 * database not only with $current_url, but also with $current_url_without_trailing_slash, which is
				 * $current_url with the trailing slash removed.
				 */
				$current_url_without_trailing_slash = substr( $current_url, 0, - 1 );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$results = $wpdb->get_row(
					$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE url_to_connect = %s or url_to_connect = %s", $current_url, $current_url_without_trailing_slash )
				);

			} else {

				/**
				 * In this case there is no trailing slash, so add it and compare the 'url_to_connect' value in the
				 * database not only with $current_url, but also with $current_url_with_trailing_slash, which is
				 * $current_url with the trailing slash added.
				 */
				$current_url_with_trailing_slash = $current_url . '/';

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$results = $wpdb->get_row(
					$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE url_to_connect = %s or url_to_connect = %s", $current_url, $current_url_with_trailing_slash )
				);

			}
		} else {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$results = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE url_to_connect = %s", $current_url )
			);

		}

		if ( null === $results ) {

			return false;

		} else {

			// Init $hreflang_output.
			$hreflang_output = array();

			// Convert the json strings to objects.
			$results->url      = json_decode( $results->url );
			$results->language = json_decode( $results->language );
			$results->script   = json_decode( $results->script );
			$results->locale   = json_decode( $results->locale );

			// Generate an array with all the connections.
			for ( $i = 1; $i <= 100; $i ++ ) {

				// Check if this is a valid hreflang.
				if ( strlen( $results->url->{$i} ) > 0 && strlen( $results->language->{$i} ) > 0 ) {

					$language = $results->language->{$i};

					if ( strlen( $results->script->{$i} ) > 0 ) {
						$script = '-' . $results->script->{$i};
					} else {
						$script = '';
					}

					if ( strlen( $results->locale->{$i} ) > 0 ) {
						$locale = '-' . $results->locale->{$i};
					} else {
						$locale = '';
					}

					if ( 'page_html' === $context ) {

						// Echo the hreflang tags in the page HTML.
						echo '<link rel="alternate" href="' . esc_url( $results->url->{$i} ) . '" hreflang="' . esc_attr( $language . $script . $locale ) . '" />';

					} else {

						// Echo the hreflang tags in the log element of the UI.
						echo '<p>' . esc_html( '<link rel="alternate" href="' . $results->url->{$i} . '" hreflang="' . $language . $script . $locale . '" />' ) . '</p>';

					}
				}
			}
		}
	}

	/**
	 * Get the current URL.
	 *
	 * @return string
	 */
	public function get_current_url() {

		if ( get_option( 'da_hm_detect_url_mode' ) === 'server_variable' ) {

			// Detect the URL using the "Server Variable" method.
			if ( 0 === intval( get_option( 'da_hm_https' ), 10 ) ) {
				$protocol = 'http';
			} else {
				$protocol = 'https';
			}

			return esc_url_raw( $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		} else {

			// Detect the URL using the "WP Request" method.
			global $wp;

			return trailingslashit( home_url( add_query_arg( array(), $wp->request ) ) );

		}
	}

	/**
	 * Returns the number of records available in the '[prefix]_da_hm_connect' db table
	 *
	 * @return string|null The number of records available in the '[prefix]_da_hm_connect' db table
	 */
	public function number_of_connections() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_connect" );

		return $total_items;
	}

	/**
	 * Generates the 'url_to_connect' value based on the connection data and on the provided language, script, and
	 * locale.
	 *
	 * @param string $connection String in json format with all the information of a connection (all the fields of the 'connect' db table
	 * except 'id').
	 * @param string $language String in json format with all the information of a language (all the fields of the 'connect' db table
	 * * except 'id').
	 * @param string $script String in json format with all the information of a script (all the fields of the 'connect' db table
	 * * except 'id').
	 * @param string $locale String in json format with all the information of a locale (all the fields of the 'connect' db table
	 * * except 'id').
	 *
	 * @return String
	 */
	public function generate_url_to_connect( $connection, $language, $script, $locale ) {

		// Get the objects from the serialized data.
		$connection['url']      = json_decode( $connection['url'] );
		$connection['language'] = json_decode( $connection['language'] );
		$connection['script']   = json_decode( $connection['script'] );
		$connection['locale']   = json_decode( $connection['locale'] );

		/**
		 * Search the 'url' where the related 'language', 'script' and 'locale' are the same of the ones specified with
		 * the 'Import Language', 'Import Script' and 'Import Locale' options. In case this 'url' is found use it as the
		 * 'url_to_connect' value.
		 */
		for ( $i = 1; $i <= 100; $i ++ ) {

			if ( (string) $connection['language']->{$i} === (string) $language
			     && (string) $connection['script']->{$i} === (string) $script
			     && (string) $connection['locale']->{$i} === (string) $locale ) {
				$url_to_connect = $connection['url']->{$i};
				break;
			}
		}

		/**
		 * If a specific 'url_to_connect' is found return it, otherwise use the 'url_to_connect' value available in the
		 * imported XML file.
		 */
		if ( isset( $url_to_connect ) ) {
			return $url_to_connect;
		} else {
			return $connection['url_to_connect'];
		}
	}

	/**
	 * Get the permalink of the post.
	 *
	 * Note that if the:
	 *
	 * - "Sample Permalink" option is enabled
	 * - And if the post status is 'future'
	 *
	 * The value of the permalink field is generated with the get_sample_permalink() function.
	 *
	 * @param int $post_id The post id.
	 * @param bool $required True if the wp-admin/includes/post.php file should be required.
	 *
	 * @return String The permalink of the post associated with the provided post id.
	 */
	public function get_permalink( $post_id, $required = false ) {

		$post_status = get_post_status( $post_id );

		/**
		 * If the post status is 'future' the value of the url_to_connect field is generated
		 * with the get_future_permalink() function. Otherwise, it's generated with the get_permalink() function.
		 */
		if ( 1 === intval( get_option( 'da_hm_sample_future_permalink' ), 10 ) && 'future' === $post_status ) {

			if ( $required ) {
				require_once ABSPATH . 'wp-admin/includes/post.php';
			}

			$permalink_a = get_sample_permalink( $post_id );
			$permalink   = preg_replace( '/\%[^\%]+name\%/', $permalink_a[1], $permalink_a[0] );

		} else {

			$permalink = get_permalink( $post_id );

		}

		return $permalink;
	}

	/**
	 * Verifies if the provided ISO_639-1 language code is valid.
	 * Returns True if it's valid, otherwise returns False.
	 *
	 * @param string $code The ISO_639-1 language code.
	 *
	 * @return bool
	 */
	public function is_valid_language( $code ) {

		$found = false;

		$language = get_option( 'da_hm_language' );
		foreach ( $language as $value ) {
			if ( $code === $value ) {
				$found = true;
			}
		}

		return $found;
	}

	/**
	 * Verifies if the provided ISO 15924 script code is valid.
	 * Returns True if it's valid, otherwise returns False.
	 *
	 * @param string $code The ISO 15924 script code.
	 *
	 * @return bool
	 */
	public function is_valid_script( $code ) {

		$found = false;

		$script = get_option( 'da_hm_script' );
		foreach ( $script as $value ) {
			if ( $code === $value ) {
				$found = true;
			}
		}

		return $found;
	}

	/**
	 * Verifies if the provided ISO 3166-1 alpha-2 locale code is valid.
	 * Returns True if it's valid, otherwise returns False.
	 *
	 * @param string $code The ISO_639-1 language code.
	 *
	 * @return bool
	 */
	public function is_valid_locale( $code ) {

		$found = false;

		$locale = get_option( 'da_hm_locale' );
		foreach ( $locale as $value ) {
			if ( $code === $value ) {
				$found = true;
			}
		}

		return $found;
	}

	/**
	 * Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
	 *
	 * @return void
	 */
	public function set_met_and_ml() {
		/*
		 * Set the custom "Max Execution Time Value" defined in the options if the 'Set Max Execution Time' option is
		 * set to "Yes".
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_set_max_execution_time' ), 10 ) === 1 ) {
			ini_set(
				'max_execution_time',
				intval( get_option( $this->get( 'slug' ) . '_max_execution_time_value' ), 10 )
			);
		}

		/*
		 * Set the custom "Memory Limit Value" (in megabytes) defined in the options if the 'Set Memory Limit' option is
		 * set to "Yes".
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_set_memory_limit' ), 10 ) === 1 ) {
			ini_set( 'memory_limit', intval( get_option( $this->get( 'slug' ) . '_memory_limit_value' ), 10 ) . 'M' );
		}
	}

	/**
	 * This method runs all the cron tasks associated with the "da_hm_cron_hook" action hook.
	 */
	public function da_hm_cron_exec() {

		// Sync the connections.
		$this->sync_connections();
	}

	/**
	 * This method syncs the connections of a responder website with the connections available in the master.
	 *
	 * Specifically, it does what follows:
	 *
	 * 1 - Verifies that the sync feature is enabled and that this website is set to responder.
	 * 2 - Retrieves the connections present in the master by using the REST API endpoint defined in the options.
	 * 3 - Finds the indexes of the connections present in master that already exists in the database table of the
	 * current website (responder).
	 * 4 - Delete from the database of this website (responder) the connections present in master that already exists in the
	 * database table of the current website (responder).
	 * 5 - Adds in the database table of this website (responder) all the connections present in master that don't already
	 * exists in the database table of the current website
	 * of the $shared_record_a array.
	 *
	 * Note that the connections are not simply copied from the master to the responder to avoid deleting and creating each
	 * time records that are already in sync.
	 */
	public function sync_connections() {

		// Run this task only if Sync Status is equal to "Enabled" and the Sync Role is equal to "Responder".
		$sync_status        = intval( get_option( $this->get( 'slug' ) . '_sync_status' ), 10 );
		$sync_role          = intval( get_option( $this->get( 'slug' ) . '_sync_role' ), 10 );
		$sync_delete_target = intval( get_option( $this->get( 'slug' ) . '_sync_delete_target' ), 10 );
		if ( ( 1 === $sync_status && 1 === $sync_role ) === false ) {
			return;
		}

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->set_met_and_ml();

		/**
		 * The $shared_record_a variable includes indexes of the records shared between the master and the responder.
		 *
		 * Each item of the array includes an array with the index of the $master_connection_a array ('master_id') and
		 * the value of the 'id' field in the responder database.
		 *
		 * Example of an array element:
		 *
		 *    [
		 *     'master_id' => 0, // The index of the master in the $master_connection_a.
		 *     'slave_id' => 345 // The 'id' field of the record in the responder database.
		 * ];
		 */
		$shared_record_a = array();

		// Read the REST API endpoint of the master website.
		$master_rest_endpoint = get_option( $this->get( 'slug' ) . '_sync_master_rest_endpoint' );
		$response             = wp_remote_get( $master_rest_endpoint );

		// If the response is not a WP Error instance decode the json.
		if ( ! is_wp_error( $response ) ) {
			$master_connection_a = json_decode( $response['body'] );
		} else {
			return;
		}

		// Do not proceed if there are no connection in the master website.
		if ( false === $master_connection_a ) {
			return;
		}

		/**
		 * Do not proceed if the items in the generated array don't include the proper indexes.
		 *
		 * This check is useful to avoid errors (and avoid adding to the plugin empty connections) when the
		 * "Master Endpoint" includes a URL to a json source which doesn't include the expecting data.
		 */
		foreach ( $master_connection_a as $master_connection ) {

			if ( ! isset( $master_connection->url_to_connect ) ||
			     ! isset( $master_connection->url ) ||
			     ! isset( $master_connection->language ) ||
			     ! isset( $master_connection->script ) ||
			     ! isset( $master_connection->locale ) ) {
				return;
			}
		}

		/**
		 * Find the indexes of the connections present in $master_connection_a that already exists in the "_connect" db
		 * table and save them in the $shared_record_a array.
		 *
		 * Note that the "Url to Connect" of the destination is calculated to properly compare the connection resulting
		 * from the master data. (The connection resulting from the master data has the url_to_connect field adapted
		 * based on the "Sync Language", "Sync Script", and "Sync Locale" options)
		 */
		foreach ( $master_connection_a as $key => $master_connection ) {

			/**
			 * If the "Sync Mode" option is set to "Based on Sync Options" generate the "url_to_connect" value based on the
			 * "Sync Language", "Sync Script", and "Sync Locale" options.
			 */
			if ( intval( get_option( $this->get( 'slug' ) . '_sync_mode', 10 ) ) === 1 ) {

				// Retrieve the 'Sync Language' and the 'Sync Locale' from the options.
				$sync_language = get_option( $this->get( 'slug' ) . '_sync_language' );
				$sync_script   = get_option( $this->get( 'slug' ) . '_sync_script' );
				$sync_locale   = get_option( $this->get( 'slug' ) . '_sync_locale' );

				// Get the URL to connect.
				$adapted_url_to_connect = $this->generate_url_to_connect(
					array(
						'url_to_connect' => $master_connection->url_to_connect,
						'url'            => $master_connection->url,
						'language'       => $master_connection->language,
						'script'         => $master_connection->script,
						'locale'         => $master_connection->locale,
					),
					$sync_language,
					$sync_script,
					$sync_locale
				);

			} else {

				// Use the original url to connect of the connection.
				$adapted_url_to_connect = $master_connection->url_to_connect;

			}

			/**
			 * Verify if the iterated master connection is available in the responder database.
			 */
			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$connection_obj = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE
                 url_to_connect = %s AND
                 url = %s AND
                 language = %s AND
                 script = %s AND
                 locale = %s",
					$adapted_url_to_connect,
					$master_connection->url,
					$master_connection->language,
					$master_connection->script,
					$master_connection->locale
				)
			);

			/**
			 * If the iterated master connection is available in the responder database add the corresponding two indexes
			 * in the $shared_record_a array.
			 */
			if ( false !== $connection_obj && null !== $connection_obj ) {
				$shared_record_a[] = array(
					'master_id' => $key,
					'slave_id'  => $connection_obj->id,
				);
			}
		}

		/**
		 * Delete from the "_connect" db table the connections that are not shared between master and responder (in other
		 * words with indexes not included in the $shared_record_a array).
		 *
		 * Note that if the "Delete Target" option is equal to "All" any connection that is not included in the
		 * $shared_record_a array is deleted. If instead the "Delete Target" option is equal to "Inherited", only the
		 * connections not included in the $shared_record_a array with the "inherit" field equal to "1" are deleted.
		 * This behavior is used to preserve existing connections of the responder site that should not be deleted and
		 * delete only the connections that have been previously inherited from the master site.
		 */
		if ( 0 === $sync_delete_target ) {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$slave_connection_a = $wpdb->get_results(
				"SELECT * FROM {$wpdb->prefix}da_hm_connect ORDER BY ID ASC",
				ARRAY_A
			);

		} else {

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$slave_connection_a = $wpdb->get_results(
				"SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE inherited = 1 ORDER BY ID ASC",
				ARRAY_A
			);

		}

		foreach ( $slave_connection_a as $slave_connection ) {

			// If this record is not included in the db_id field of $shared_record_a delete it.
			$found = false;
			foreach ( $shared_record_a as $already_updated_single ) {

				if ( intval( $already_updated_single['slave_id'], 10 ) === intval( $slave_connection['id'], 10 ) ) {
					$found = true;
					break;
				}
			}

			// Delete this record.
			if ( false === $found ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM {$wpdb->prefix}da_hm_connect WHERE id = %d ", $slave_connection['id'] )
				);

			}
		}

		/**
		 * Add in the "_connect" db table all the connections in $master_connection_a that don't have an index in the
		 * 'master_id' index of the $shared_record_a array.
		 */
		foreach ( $master_connection_a as $key => $connection ) {

			// If the index of $master_connection_a is present in the key field $already_update return.
			$found = false;
			foreach ( $shared_record_a as $shared_record ) {

				if ( intval( $key, 10 ) === intval( $shared_record['master_id'], 10 ) ) {
					$found = true;
				}
			}
			if ( $found ) {
				continue;
			}

			$this->add_synced_connection( $connection );

		}
	}

	/**
	 * Add the provided connection to the database. Note that the Url to Connect value is adapted based on the following
	 * options:
	 *
	 * - Sync -> Mode
	 * - Sync -> Language
	 * - Sync -> Script
	 * - Sync -> Locale
	 *
	 * @param Object $connection The data of the connection.
	 */
	public function add_synced_connection( $connection ) {

		/**
		 * If the "Sync Mode" option is set to "Based on Sync Options" generate the "url_to_connect" value based on the
		 * "Sync Language", "Sync Script", and "Sync Locale" options.
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_sync_mode', 10 ) ) === 1 ) {

			// Retrieve the 'Sync Language' and the "Sync Locale" from the options.
			$sync_language = get_option( $this->get( 'slug' ) . '_sync_language' );
			$sync_script   = get_option( $this->get( 'slug' ) . '_sync_script' );
			$sync_locale   = get_option( $this->get( 'slug' ) . '_sync_locale' );

			// Get the URL to connect.
			$adapted_url_to_connect = $this->generate_url_to_connect(
				array(
					'url_to_connect' => $connection->url_to_connect,
					'url'            => $connection->url,
					'language'       => $connection->language,
					'script'         => $connection->script,
					'locale'         => $connection->locale,
				),
				$sync_language,
				$sync_script,
				$sync_locale
			);

		} else {

			// Use the original url to connect of the connection.
			$adapted_url_to_connect = $connection->url_to_connect;

		}

		// Add a new connection into the database.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$query_result = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}da_hm_connect SET url_to_connect = %s ,
                 url = %s,
                 language = %s,
                 script = %s,
                 locale = %s,
                 inherited = %d",
				$adapted_url_to_connect,
				$connection->url,
				$connection->language,
				$connection->script,
				$connection->locale,
				1
			)
		);
	}

	/**
	 * Returns an array with the data used by React to initialize the options.
	 *
	 * @return array[]
	 */
	public function menu_options_configuration() {

		// Get the public post types that have a UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );
		unset( $post_types_with_ui['attachment'] );
		$post_types_select_options = array();
		foreach ( $post_types_with_ui as $post_type ) {
			$post_types_select_options[] = array(
				'value' => $post_type,
				'text'  => $post_type,
			);
		}

		$configuration = array(
			array(
				'title'       => __( 'General', 'hreflang-manager' ),
				'description' => __( 'Configure general plugin options.', 'hreflang-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'URL Detection', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'          => 'da_hm_detect_url_mode',
								'label'         => __( 'Detect URL Mode', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Select the method used to detect the URL of the page.',
									'hreflang-manager'
								),
								'help'          => __( 'Select the method used to detect the URL of the page.', 'hreflang-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'server_variable',
										'text'  => __( 'Server Variable', 'hreflang-manager' ),
									),
									array(
										'value' => 'wp_request',
										'text'  => __( 'WP Request', 'hreflang-manager' ),
									),
								),
							),
							array(
								'name'    => 'da_hm_https',
								'label'   => __( 'HTTPS', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" if your website is using the HTTPS protocol. This option will be considered only if "Detect URL Mode" is set to "Server Variable".', 'hreflang-manager' ),
								'help'    => __( 'Match URLs that use the HTTPS protocol.', 'hreflang-manager' ),
							),
							array(
								'name'    => 'da_hm_auto_trailing_slash',
								'label'   => __( 'Auto Trailing Slash', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Enable this option to compare the URL defined in the "URL to Connect" field with the URL of the page with and without trailing slash.',
									'hreflang-manager'
								),
								'help'    => __( 'Match URLs with or without the trailing slash.', 'hreflang-manager' ),
							),
						),
					),
					array(
						'title'   => __( 'Post Editor', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'    => 'da_hm_sample_future_permalink',
								'label'   => __( 'Sample Future Permalink', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Enable this option to assign a permalink based on the post name to the posts scheduled to be published in a future date.',
									'hreflang-manager'
								),
								'help'    => __( 'Automatically assign a permalink to future posts.', 'hreflang-manager' ),
							),
							array(
								'name'          => 'da_hm_meta_box_post_types',
								'label'         => __( 'Meta Box Post Types', 'hreflang-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'The post types where the "Hreflang Manager" meta box should be loaded.',
									'hreflang-manager'
								),
								'help'          => __( 'Select the post types where the "Hreflang Manager" meta box should be loaded.', 'hreflang-manager' ),
								'selectOptions' => $post_types_select_options,
							),
						),
					),
					array(
						'title'   => __( 'Connections Menu', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'    => 'da_hm_auto_alternate_pages',
								'label'   => __( 'Auto Alternate Pages', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the plugin automatically generates the connections for the alternate pages. This option should only be used if this WordPress installation serves the alternate pages.',
									'hreflang-manager'
								),
								'help'    => __( 'Automatically generate the connections for the alternate pages.', 'hreflang-manager' ),
							),
							array(
								'name'      => 'da_hm_connections_in_menu',
								'label'     => __( 'Alternate Pages in Menu', 'hreflang-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This option determines the number of alternate pages displayed in the "Connections" menu, the "Hreflang Manager" block editor sidebar, and the "Hreflang Manager" meta box.',
									'hreflang-manager'
								),
								'help'      => __( 'Set the number of alternate pages displayed in the plugin UI.', 'hreflang-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 100,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Common', 'hreflang-manager' ),
						'options' => array(

							array(
								'name'    => 'da_hm_auto_delete',
								'label'   => __( 'Auto Delete', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Enable this option to automatically delete the connection associated with a post when the post is trashed.',
									'hreflang-manager'
								),
								'help'    => __( 'Delete a connection when the related post is trashed.', 'hreflang-manager' ),
							),
							array(
								'name'    => 'da_hm_show_log',
								'label'   => __( 'Show Log', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Select "Yes" to display the log on the front-end. Please note that the log will be displayed only to the users who have access to the "Connections" menu.',
									'hreflang-manager'
								),
								'help'    => __( "Display a log with the hreflang tags in the site's front end.", 'hreflang-manager' ),
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'Sync', 'hreflang-manager' ),
				'description' => __( 'Automatically sync all the websites of the network.', 'hreflang-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'General Settings', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'    => 'da_hm_sync_status',
								'label'   => __( 'Status', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Whether to enable or not the sync feature.',
									'hreflang-manager'
								),
								'help'    => __( 'Enable the sync functionalities of the plugin.', 'hreflang-manager' ),
							),
							array(
								'name'          => 'da_hm_sync_role',
								'label'         => __( 'Role', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The role of this website in the synced network. Note that a website with the "Controller" role exposes its hreflang data with a REST API endpoint, while a website with the "Responder" role imports the hreflang data from the REST API endpoint defined with the "Controller Endpoint" option. IMPORTANT: By setting a website as a "Responder", you will lose all the hreflang data stored in the website.',
									'hreflang-manager'
								),
								'help'          => __( 'Set the role of this website in the network.', 'hreflang-manager' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Controller', 'hreflang-manager' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'Responder', 'hreflang-manager' ),
									),
								),
							),
						),
					),

					array(
						'title'   => __( 'Responder Role', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'    => 'da_hm_sync_master_rest_endpoint',
								'label'   => __( 'Controller Endpoint', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The REST API endpoint used to sync a responder website with a controller website. This value should be equal to the URL of the controller website plus the suffix "/wp-json/daext-hreflang-manager/v1/sync". For example, if the controller website is "https://example.com" the value of this option should be "https://example.com/wp-json/daext-hreflang-manager/v1/sync".',
									'hreflang-manager'
								),
								'help'    => __( 'Enter the REST API endpoint where the controller website makes the hreflang data available.', 'hreflang-manager' ),
							),
							array(
								'name'          => 'da_hm_sync_frequency',
								'label'         => __( 'Frequency', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option determines how often the sync should occur.',
									'hreflang-manager'
								),
								'help'          => __( 'Set the frequency of the sync operation.', 'hreflang-manager' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Hourly', 'hreflang-manager' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'Twice Daily', 'hreflang-manager' ),
									),
									array(
										'value' => '2',
										'text'  => __( 'Daily', 'hreflang-manager' ),
									),
									array(
										'value' => '3',
										'text'  => __( 'Weekly', 'hreflang-manager' ),
									),
								),
							),
							array(
								'name'          => 'da_hm_sync_mode',
								'label'         => __( 'Sync Mode', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Select "Exact Copy" to sync an exact copy of the connections available in the controller website or "Based on Sync Options" to determine the "URL to Connect" value of the synced connections by using the "Language", "Script", and "Locale" options available in this section.',
									'hreflang-manager'
								),
								'help'          => __( "Select how the responder site should treat the controller site's data.", 'hreflang-manager' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Exact Copy', 'hreflang-manager' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'Based on Sync Options', 'hreflang-manager' ),
									),
								),
							),
							array(
								'name'          => 'da_hm_sync_language',
								'label'         => __( 'Language', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option is used to determine the "URL to Connect" value of the synced connections.',
									'hreflang-manager'
								),
								'help'          => __( 'Select the language of the responder site.', 'hreflang-manager' ),
								'selectOptions' => ( function () {

									$data           = array();
									$array_language = get_option( 'da_hm_language' );
									$counter        = 1;
									foreach ( $array_language as $key => $value ) {
										$data[] = array(
											'value' => $value,
											'text'  => $value . ' - ' . $key,
										);
										$counter ++;
									}

									return $data;
								} )(),
							),
							array(
								'name'          => 'da_hm_sync_script',
								'label'         => __( 'Script', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option is used to determine the "URL to Connect" value of the synced connections.',
									'hreflang-manager'
								),
								'help'          => __( 'Select the script of the responder site.', 'hreflang-manager' ),
								'selectOptions' => ( function () {

									$data         = array();
									$data[]       = array(
										'value' => '',
										'text'  => __( 'Not assigned', 'hreflang-manager' ),
									);
									$array_script = get_option( 'da_hm_script' );
									foreach ( $array_script as $key => $value ) {
										$data[] = array(
											'value' => $value,
											'text'  => $value . ' - ' . $key,
										);
									}

									return $data;
								} )(),
							),
							array(
								'name'          => 'da_hm_sync_locale',
								'label'         => __( 'Locale', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option is used to determine the "URL to Connect" value of the synced connections.',
									'hreflang-manager'
								),
								'help'          => __( 'Select the locale of the responder site.', 'hreflang-manager' ),
								'selectOptions' => ( function () {

									$data         = array();
									$data[]       = array(
										'value' => '',
										'text'  => __( 'Not assigned', 'hreflang-manager' ),
									);
									$array_locale = get_option( 'da_hm_locale' );
									foreach ( $array_locale as $key => $value ) {
										$data[] = array(
											'value' => $value,
											'text'  => $value . ' - ' . $key,
										);
									}

									return $data;
								} )(),
							),
							array(
								'name'          => 'da_hm_sync_delete_target',
								'label'         => __( 'Delete Target', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option determines which connections of a "Responder" site can be deleted during the sync process. With "All" selected, any connection can be deleted. With "Inherited" selected, only the connections inherited from the controller site can be deleted.',
									'hreflang-manager'
								),
								'help'          => __( 'Select which connections of the responder site can be deleted during the sync process.', 'hreflang-manager' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'All', 'hreflang-manager' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'Inherited', 'hreflang-manager' ),
									),
								),
							),
						),
					),

				),
			),
			array(
				'title'       => __( 'Import', 'hreflang-manager' ),
				'description' => __( 'Customize the import process.', 'hreflang-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Connections', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'          => 'da_hm_import_mode',
								'label'         => __( 'Mode', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Select "Exact Copy" to import an exact copy of the connections stored in your XML file or "Based on Import Options" to determine the "URL to Connect" value of the imported connections by using the "Import Language", "Import Script" and "Import Locale" options.',
									'hreflang-manager'
								),
								'help'          => __( 'Select how this site should treat the imported data.', 'hreflang-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'exact_copy',
										'text'  => __( 'Exact Copy', 'hreflang-manager' ),
									),
									array(
										'value' => 'import_options',
										'text'  => __( 'Based on Import Options', 'hreflang-manager' ),
									),
								),
							),
							array(
								'name'          => 'da_hm_import_language',
								'label'         => __( 'Language', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option is used to determine the "URL to Connect" value of the imported connections.',
									'hreflang-manager'
								),
								'help'          => __( 'Select the language of this site.', 'hreflang-manager' ),
								'selectOptions' => ( function () {

									$data           = array();
									$array_language = get_option( 'da_hm_language' );
									foreach ( $array_language as $key => $value ) {
										$data[] = array(
											'value' => $value,
											'text'  => $value . ' - ' . $key,
										);
									}

									return $data;
								} )(),
							),
							array(
								'name'          => 'da_hm_import_script',
								'label'         => __( 'Script', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option is used to determine the "URL to Connect" value of the synced connections.',
									'hreflang-manager'
								),
								'help'          => __( 'Select the script of this site.', 'hreflang-manager' ),
								'selectOptions' => ( function () {

									$data         = array();
									$data[]       = array(
										'value' => '',
										'text'  => __( 'Not assigned', 'hreflang-manager' ),
									);
									$array_script = get_option( 'da_hm_script' );
									foreach ( $array_script as $key => $value ) {
										$data[] = array(
											'value' => $value,
											'text'  => $value . ' - ' . $key,
										);
									}

									return $data;
								} )(),
							),
							array(
								'name'          => 'da_hm_import_locale',
								'label'         => __( 'Locale', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'This option is used to determine the "URL to Connect" value of the synced connections.',
									'hreflang-manager'
								),
								'help'          => __( 'Select the locale of this site.', 'hreflang-manager' ),
								'selectOptions' => ( function () {

									$data         = array();
									$data[]       = array(
										'value' => '',
										'text'  => __( 'Not assigned', 'hreflang-manager' ),
									);
									$array_locale = get_option( 'da_hm_locale' );
									foreach ( $array_locale as $key => $value ) {
										$data[] = array(
											'value' => $value,
											'text'  => $value . ' - ' . $key,
										);
									}

									return $data;
								} )(),
							),
						),
					),

				),
			),
			array(
				'title'       => __( 'Defaults', 'hreflang-manager' ),
				'description' => __( 'Set the initial values to create connections quickly.', 'hreflang-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Initial Values', 'hreflang-manager' ),
						'options' =>
							( function () {

								// For iteration from 1 to 100. counter is named "i".
								$data = array();
								for ( $i = 1; $i <= 100; $i ++ ) {

									$data[] = array(
										'name'          => 'da_hm_default_language_' . $i,
										'label'         => __( 'Language', 'hreflang-manager' ) . ' ' . $i,
										'type'          => 'select',
										'tooltip'       => __(
											                   'This option determines the default language of the alternate page',
											                   'hreflang-manager'
										                   ) . ' ' . $i . '.',
										'help'          => __( 'Select the default language of the alternate page', 'hreflang-manager' ) . ' ' . $i . '.',
										'selectOptions' => ( function () {

											$data           = array();
											$array_language = get_option( 'da_hm_language' );
											foreach ( $array_language as $key => $value ) {
												$data[] = array(
													'value' => $value,
													'text'  => $value . ' - ' . $key,
												);
											}

											return $data;
										} )(),
									);
									$data[] = array(
										'name'          => 'da_hm_default_script_' . $i,
										'label'         => __( 'Script', 'hreflang-manager' ) . ' ' . $i,
										'type'          => 'select',
										'tooltip'       => __(
											                   'This option determines the default script of the alternate page',
											                   'hreflang-manager'
										                   ) . ' ' . $i . '.',
										'help'          => __( 'Select the default script of the alternate page', 'hreflang-manager' ) . ' ' . $i . '.',
										'selectOptions' => ( function () {

											$data         = array();
											$data[]       = array(
												'value' => '',
												'text'  => __( 'Not assigned', 'hreflang-manager' ),
											);
											$array_script = get_option( 'da_hm_script' );
											foreach ( $array_script as $key => $value ) {
												$data[] = array(
													'value' => $value,
													'text'  => $value . ' - ' . $key,
												);
											}

											return $data;
										} )(),
									);
									$data[] = array(
										'name'          => 'da_hm_default_locale_' . $i,
										'label'         => __( 'Locale', 'hreflang-manager' ) . ' ' . $i,
										'type'          => 'select',
										'tooltip'       => __(
											                   'This option determines the default locale of the alternate page',
											                   'hreflang-manager'
										                   ) . ' ' . $i . '.',
										'help'          => __( 'Select the default locale of the alternate page', 'hreflang-manager' ) . ' ' . $i . '.',
										'selectOptions' => ( function () {

											$data         = array();
											$data[]       = array(
												'value' => '',
												'text'  => __( 'Not assigned', 'hreflang-manager' ),
											);
											$array_locale = get_option( 'da_hm_locale' );
											foreach ( $array_locale as $key => $value ) {
												$data[] = array(
													'value' => $value,
													'text'  => $value . ' - ' . $key,
												);
											}

											return $data;
										} )(),
									);

								}

								return $data;
							} )(),
					),

				),

			),
			array(
				'title'       => __( 'Advanced', 'hreflang-manager' ),
				'description' => __( 'Manage advanced plugin settings.', 'hreflang-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'PHP Parameters', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'    => 'da_hm_set_max_execution_time',
								'label'   => __( 'Set Max Execution Time', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Select "Yes" to enable your custom "Max Execution Time Value" on long running scripts.',
									'hreflang-manager'
								),
								'help'    => __( 'Enable a custom max execution time.', 'hreflang-manager' ),
							),
							array(
								'name'      => 'da_hm_max_execution_time_value',
								'label'     => __( 'Max Execution Time Value', 'hreflang-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the maximum number of seconds allowed to execute long running scripts.',
									'hreflang-manager'
								),
								'help'      => __( 'Set the max execution time value.', 'hreflang-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 3600,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'da_hm_set_memory_limit',
								'label'   => __( 'Set Memory Limit', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'Select "Yes" to enable your custom "Memory Limit Value" on long running scripts.',
									'hreflang-manager'
								),
								'help'    => __( 'Enable a custom memory limit.', 'hreflang-manager' ),

							),
							array(
								'name'      => 'da_hm_memory_limit_value',
								'label'     => __( 'Memory Limit Value', 'hreflang-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the PHP memory limit in megabytes allowed to execute long running scripts.',
									'hreflang-manager'
								),
								'help'      => __( 'Set the memory limit value.', 'hreflang-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 16384,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Checker', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'    => $this->get( 'slug' ) . '_checker_invalid_http_response',
								'label'   => __( 'Invalid HTTP Response', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Verifies that hreflang URLs return a 200 (OK) HTTP response. Any other response indicates an error.', 'hreflang-manager' ),
								'help'    => __( 'Check for invalid HTTP responses in hreflang URLs to ensure they are accessible.', 'hreflang-manager' ),
							),
							array(
								'name'    => $this->get( 'slug' ) . '_checker_duplicate_hreflang_entries',
								'label'   => __( 'Duplicate Hreflang Entries', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Detects duplicate hreflang tags within a page, which can cause conflicts and affect SEO performance.', 'hreflang-manager' ),
								'help'    => __( 'Check for duplicate hreflang entries on the same page to prevent conflicts.', 'hreflang-manager' ),
							),
							array(
								'name'    => $this->get( 'slug' ) . '_checker_missing_self_referencing_hreflang',
								'label'   => __( 'Missing Self-Referencing Hreflang', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Detects pages missing a hreflang tag that points to themselves, ensuring correct self-referencing.', 'hreflang-manager' ),
								'help'    => __( 'Ensure pages include a self-referencing hreflang tag.', 'hreflang-manager' ),
							),
							array(
								'name'    => $this->get( 'slug' ) . '_checker_incorrect_language_script_region_codes',
								'label'   => __( 'Incorrect Language/Script/Region Codes', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Check for incorrect language, script, or region codes in hreflang tags to ensure compliance with ISO standards.', 'hreflang-manager' ),
								'help'    => __( 'Check for incorrect language, script, or region codes in hreflang tags.', 'hreflang-manager' ),
							),
							array(
								'name'    => $this->get( 'slug' ) . '_checker_missing_hreflang_x_default',
								'label'   => __( 'Missing Hreflang X-Default', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Check for the absence of the hreflang "x-default" tag, which is required for pages that serve as the fallback for users with unsupported languages, scripts, or regions.', 'hreflang-manager' ),
								'help'    => __( 'Check for the absence of the hreflang "x-default" tag.', 'hreflang-manager' ),
							),
							array(
								'name'    => $this->get( 'slug' ) . '_checker_missing_return_link',
								'label'   => __( 'Missing Return Link', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Check for missing return hreflang tags, ensuring bidirectional linking between different language, script, or region versions.', 'hreflang-manager' ),
								'help'    => __( 'Check for missing return hreflang tags.', 'hreflang-manager' ),
							),
							array(
								'name'    => $this->get( 'slug' ) . '_checker_canonical_and_hreflang_conflict',
								'label'   => __( 'Canonical and Hreflang Conflict', 'hreflang-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Identifies pages where canonical tags and hreflang tags conflict, potentially leading to SEO issues and incorrect content targeting.', 'hreflang-manager' ),
								'help'    => __( 'Check for conflicts between canonical tags and hreflang tags.', 'hreflang-manager' ),
							),
							array(
								'name'      => 'da_hm_checker_checks_per_iteration',
								'label'     => __( 'WP-Cron HTTP Requests Per Run', 'hreflang-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the number of HTTP requests sent at each run of the WP-Cron event. This parameter is used in the WP-Cron event used to verify the HTTP response of the URLs used in the hreflang implementation.',
									'hreflang-manager'
								),
								'help'      => __( 'Set the number of HTTP requests sent at each run of the WP-Cron event.', 'hreflang-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 10,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'da_hm_checker_cron_schedule_interval',
								'label'     => __( 'WP-Cron Event Interval', 'hreflang-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the interval in seconds between the custom WP-Cron events used to verify the HTTP response of the URLs used in the hreflang implementation.',
									'hreflang-manager'
								),
								'help'      => __( 'Set the interval in seconds between the WP-Cron events.', 'hreflang-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 3600,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'da_hm_checker_request_timeout',
								'label'     => __( 'Request Timeout', 'hreflang-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines how long the connection should stay open in seconds for the HTTP requests used to verify the HTTP response of the URLs used in the hreflang implementation.',
									'hreflang-manager'
								),
								'help'      => __( 'Set the HTTP request timeout in seconds.', 'hreflang-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 3600,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Capabilities', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'    => 'da_hm_meta_box_capability',
								'label'   => __( 'Meta Box', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Hreflang Manager" meta box.',
									'hreflang-manager'
								),
								'help'    => __( 'The capability required to access the "Hreflang Manager" meta box.', 'hreflang-manager' ),
							),
							array(
								'name'    => 'da_hm_editor_sidebar_capability',
								'label'   => __( 'Editor Sidebar', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Hreflang Manager" block editor sidebar.',
									'hreflang-manager'
								),
								'help'    => __( 'The capability required to access the "Hreflang Manager" block editor sidebar.', 'hreflang-manager' ),
							),
							array(
								'name'    => 'da_hm_connections_menu_capability',
								'label'   => __( 'Connections Menu', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Connections" menu.',
									'hreflang-manager'
								),
								'help'    => __( 'The capability required to access the "Connections" menu.', 'hreflang-manager' ),
							),
							array(
								'name'    => 'da_hm_bulk_import_menu_capability',
								'label'   => __( 'Bulk Import Menu', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Bulk Import" menu.',
									'hreflang-manager'
								),
								'help'    => __( 'The capability required to access the "Bulk Import" menu.', 'hreflang-manager' ),
							),
							array(
								'name'    => 'da_hm_tools_menu_capability',
								'label'   => __( 'Tools Menu', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Tools" menu.',
									'hreflang-manager'
								),
								'help'    => __( 'The capability required to access the "Tools" menu.', 'hreflang-manager' ),
							),
							array(
								'name'    => 'da_hm_checker_menu_capability',
								'label'   => __( 'Checker Menu', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Checker" menu.',
									'hreflang-manager'
								),
								'help'    => __( 'The capability required to access the "Checker" menu.', 'hreflang-manager' ),
							),
							array(
								'name'    => 'da_hm_maintenance_menu_capability',
								'label'   => __( 'Maintenance Menu', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Maintenance" menu.',
									'hreflang-manager'
								),
								'help'    => __( 'The capability required to access the "Maintenance" menu.', 'hreflang-manager' ),
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'License', 'hreflang-manager' ),
				'description' => __( 'Configure your license for seamless updates and support.', 'hreflang-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'License Management', 'hreflang-manager' ),
						'options' => array(
							array(
								'name'          => 'da_hm_license_provider',
								'label'         => __( 'Provider', 'hreflang-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Use this option to indicate where you purchased your license. Choosing the correct license provider ensures that your license is properly verified on our system.',
									'hreflang-manager'
								),
								'help'          => __( 'Select your license provider.', 'hreflang-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'daext_com',
										'text'  => __( 'DAEXT.COM', 'hreflang-manager' ),
									),
									array(
										'value' => 'envato_market',
										'text'  => __( 'Envato Market', 'hreflang-manager' ),
									),
								),
							),
							array(
								'name'    => 'da_hm_license_key',
								'label'   => __( 'Key', 'hreflang-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'For licenses purchased on daext.com, find and manage your keys at https://daext.com/account/. For Envato Market licenses, use the item purchase code available in your downloads area.',
									'hreflang-manager'
								),
								'help'    => __( 'Enter your license key.', 'hreflang-manager' ),
							),
						),
					),
				),
			),
		);

		return $configuration;
	}

	/**
	 * Echo the SVG icon specified by the $icon_name parameter.
	 *
	 * @param string $icon_name The name of the icon to echo.
	 *
	 * @return void
	 */
	public function echo_icon_svg( $icon_name ) {

		switch ( $icon_name ) {

			case 'list':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M21 12L9 12M21 6L9 6M21 18L9 18M5 12C5 12.5523 4.55228 13 4 13C3.44772 13 3 12.5523 3 12C3 11.4477 3.44772 11 4 11C4.55228 11 5 11.4477 5 12ZM5 6C5 6.55228 4.55228 7 4 7C3.44772 7 3 6.55228 3 6C3 5.44772 3.44772 5 4 5C4.55228 5 5 5.44772 5 6ZM5 18C5 18.5523 4.55228 19 4 19C3.44772 19 3 18.5523 3 18C3 17.4477 3.44772 17 4 17C4.55228 17 5 17.4477 5 18Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'database-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 5C21 6.65685 16.9706 8 12 8C7.02944 8 3 6.65685 3 5M21 5C21 3.34315 16.9706 2 12 2C7.02944 2 3 3.34315 3 5M21 5V19C21 20.66 17 22 12 22C7 22 3 20.66 3 19V5M21 9.72021C21 11.3802 17 12.7202 12 12.7202C7 12.7202 3 11.3802 3 9.72021M21 14.44C21 16.1 17 17.44 12 17.44C7 17.44 3 16.1 3 14.44" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'tool-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 6L10.5 10.5M6 6H3L2 3L3 2L6 3V6ZM19.259 2.74101L16.6314 5.36863C16.2354 5.76465 16.0373 5.96265 15.9632 6.19098C15.8979 6.39183 15.8979 6.60817 15.9632 6.80902C16.0373 7.03735 16.2354 7.23535 16.6314 7.63137L16.8686 7.86863C17.2646 8.26465 17.4627 8.46265 17.691 8.53684C17.8918 8.6021 18.1082 8.6021 18.309 8.53684C18.5373 8.46265 18.7354 8.26465 19.1314 7.86863L21.5893 5.41072C21.854 6.05488 22 6.76039 22 7.5C22 10.5376 19.5376 13 16.5 13C16.1338 13 15.7759 12.9642 15.4298 12.8959C14.9436 12.8001 14.7005 12.7521 14.5532 12.7668C14.3965 12.7824 14.3193 12.8059 14.1805 12.8802C14.0499 12.9501 13.919 13.081 13.657 13.343L6.5 20.5C5.67157 21.3284 4.32843 21.3284 3.5 20.5C2.67157 19.6716 2.67157 18.3284 3.5 17.5L10.657 10.343C10.919 10.081 11.0499 9.95005 11.1198 9.81949C11.1941 9.68068 11.2176 9.60347 11.2332 9.44681C11.2479 9.29945 11.1999 9.05638 11.1041 8.57024C11.0358 8.22406 11 7.86621 11 7.5C11 4.46243 13.4624 2 16.5 2C17.5055 2 18.448 2.26982 19.259 2.74101ZM12.0001 14.9999L17.5 20.4999C18.3284 21.3283 19.6716 21.3283 20.5 20.4999C21.3284 19.6715 21.3284 18.3283 20.5 17.4999L15.9753 12.9753C15.655 12.945 15.3427 12.8872 15.0408 12.8043C14.6517 12.6975 14.2249 12.7751 13.9397 13.0603L12.0001 14.9999Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'refresh-ccw-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 18.8746C19.1213 17.329 20.5 14.8255 20.5 12C20.5 7.30555 16.6944 3.49998 12 3.49998H11.5M12 20.5C7.30558 20.5 3.5 16.6944 3.5 12C3.5 9.17444 4.87867 6.67091 7 5.12537M11 22.4L13 20.4L11 18.4M13 5.59998L11 3.59998L13 1.59998" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'file-code-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5 18.5C5 18.9644 5 19.1966 5.02567 19.3916C5.2029 20.7378 6.26222 21.7971 7.60842 21.9743C7.80337 22 8.03558 22 8.5 22H16.2C17.8802 22 18.7202 22 19.362 21.673C19.9265 21.3854 20.3854 20.9265 20.673 20.362C21 19.7202 21 18.8802 21 17.2V9.98822C21 9.25445 21 8.88757 20.9171 8.5423C20.8436 8.2362 20.7224 7.94356 20.5579 7.67515C20.3724 7.3724 20.113 7.11296 19.5941 6.59411L16.4059 3.40589C15.887 2.88703 15.6276 2.6276 15.3249 2.44208C15.0564 2.27759 14.7638 2.15638 14.4577 2.08289C14.1124 2 13.7455 2 13.0118 2H8.5C8.03558 2 7.80337 2 7.60842 2.02567C6.26222 2.2029 5.2029 3.26222 5.02567 4.60842C5 4.80337 5 5.03558 5 5.5M9 14.5L11.5 12L9 9.5M5 9.5L2.5 12L5 14.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'upload-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M16 12L12 8M12 8L8 12M12 8V17.2C12 18.5907 12 19.2861 12.5505 20.0646C12.9163 20.5819 13.9694 21.2203 14.5972 21.3054C15.5421 21.4334 15.9009 21.2462 16.6186 20.8719C19.8167 19.2036 22 15.8568 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 15.7014 4.01099 18.9331 7 20.6622" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'dataflow-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 4V15.2C12 16.8802 12 17.7202 12.327 18.362C12.6146 18.9265 13.0735 19.3854 13.638 19.673C14.2798 20 15.1198 20 16.8 20H17M17 20C17 21.1046 17.8954 22 19 22C20.1046 22 21 21.1046 21 20C21 18.8954 20.1046 18 19 18C17.8954 18 17 18.8954 17 20ZM7 4L17 4M7 4C7 5.10457 6.10457 6 5 6C3.89543 6 3 5.10457 3 4C3 2.89543 3.89543 2 5 2C6.10457 2 7 2.89543 7 4ZM17 4C17 5.10457 17.8954 6 19 6C20.1046 6 21 5.10457 21 4C21 2.89543 20.1046 2 19 2C17.8954 2 17 2.89543 17 4ZM12 12H17M17 12C17 13.1046 17.8954 14 19 14C20.1046 14 21 13.1046 21 12C21 10.8954 20.1046 10 19 10C17.8954 10 17 10.8954 17 12Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'zap-fast':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 17.5H3.5M6.5 12H2M9 6.5H4M17 3L10.4036 12.235C10.1116 12.6438 9.96562 12.8481 9.97194 13.0185C9.97744 13.1669 10.0486 13.3051 10.1661 13.3958C10.3011 13.5 10.5522 13.5 11.0546 13.5H16L15 21L21.5964 11.765C21.8884 11.3562 22.0344 11.1519 22.0281 10.9815C22.0226 10.8331 21.9514 10.6949 21.8339 10.6042C21.6989 10.5 21.4478 10.5 20.9454 10.5H16L17 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'server-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 9C19 12.866 15.866 16 12 16M19 9C19 5.13401 15.866 2 12 2M19 9H5M12 16C8.13401 16 5 12.866 5 9M12 16C13.7509 14.0832 14.7468 11.5956 14.8009 9C14.7468 6.40442 13.7509 3.91685 12 2M12 16C10.2491 14.0832 9.25498 11.5956 9.20091 9C9.25498 6.40442 10.2491 3.91685 12 2M12 16V18M5 9C5 5.13401 8.13401 2 12 2M14 20C14 21.1046 13.1046 22 12 22C10.8954 22 10 21.1046 10 20M14 20C14 18.8954 13.1046 18 12 18M14 20H21M10 20C10 18.8954 10.8954 18 12 18M10 20H3" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'settings-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18.7273 14.7273C18.6063 15.0015 18.5702 15.3056 18.6236 15.6005C18.6771 15.8954 18.8177 16.1676 19.0273 16.3818L19.0818 16.4364C19.2509 16.6052 19.385 16.8057 19.4765 17.0265C19.568 17.2472 19.6151 17.4838 19.6151 17.7227C19.6151 17.9617 19.568 18.1983 19.4765 18.419C19.385 18.6397 19.2509 18.8402 19.0818 19.0091C18.913 19.1781 18.7124 19.3122 18.4917 19.4037C18.271 19.4952 18.0344 19.5423 17.7955 19.5423C17.5565 19.5423 17.3199 19.4952 17.0992 19.4037C16.8785 19.3122 16.678 19.1781 16.5091 19.0091L16.4545 18.9545C16.2403 18.745 15.9682 18.6044 15.6733 18.5509C15.3784 18.4974 15.0742 18.5335 14.8 18.6545C14.5311 18.7698 14.3018 18.9611 14.1403 19.205C13.9788 19.4489 13.8921 19.7347 13.8909 20.0273V20.1818C13.8909 20.664 13.6994 21.1265 13.3584 21.4675C13.0174 21.8084 12.5549 22 12.0727 22C11.5905 22 11.1281 21.8084 10.7871 21.4675C10.4461 21.1265 10.2545 20.664 10.2545 20.1818V20.1C10.2475 19.7991 10.1501 19.5073 9.97501 19.2625C9.79991 19.0176 9.55521 18.8312 9.27273 18.7273C8.99853 18.6063 8.69437 18.5702 8.39947 18.6236C8.10456 18.6771 7.83244 18.8177 7.61818 19.0273L7.56364 19.0818C7.39478 19.2509 7.19425 19.385 6.97353 19.4765C6.7528 19.568 6.51621 19.6151 6.27727 19.6151C6.03834 19.6151 5.80174 19.568 5.58102 19.4765C5.36029 19.385 5.15977 19.2509 4.99091 19.0818C4.82186 18.913 4.68775 18.7124 4.59626 18.4917C4.50476 18.271 4.45766 18.0344 4.45766 17.7955C4.45766 17.5565 4.50476 17.3199 4.59626 17.0992C4.68775 16.8785 4.82186 16.678 4.99091 16.5091L5.04545 16.4545C5.25503 16.2403 5.39562 15.9682 5.4491 15.6733C5.50257 15.3784 5.46647 15.0742 5.34545 14.8C5.23022 14.5311 5.03887 14.3018 4.79497 14.1403C4.55107 13.9788 4.26526 13.8921 3.97273 13.8909H3.81818C3.33597 13.8909 2.87351 13.6994 2.53253 13.3584C2.19156 13.0174 2 12.5549 2 12.0727C2 11.5905 2.19156 11.1281 2.53253 10.7871C2.87351 10.4461 3.33597 10.2545 3.81818 10.2545H3.9C4.2009 10.2475 4.49273 10.1501 4.73754 9.97501C4.98236 9.79991 5.16883 9.55521 5.27273 9.27273C5.39374 8.99853 5.42984 8.69437 5.37637 8.39947C5.3229 8.10456 5.18231 7.83244 4.97273 7.61818L4.91818 7.56364C4.74913 7.39478 4.61503 7.19425 4.52353 6.97353C4.43203 6.7528 4.38493 6.51621 4.38493 6.27727C4.38493 6.03834 4.43203 5.80174 4.52353 5.58102C4.61503 5.36029 4.74913 5.15977 4.91818 4.99091C5.08704 4.82186 5.28757 4.68775 5.50829 4.59626C5.72901 4.50476 5.96561 4.45766 6.20455 4.45766C6.44348 4.45766 6.68008 4.50476 6.9008 4.59626C7.12152 4.68775 7.32205 4.82186 7.49091 4.99091L7.54545 5.04545C7.75971 5.25503 8.03183 5.39562 8.32674 5.4491C8.62164 5.50257 8.9258 5.46647 9.2 5.34545H9.27273C9.54161 5.23022 9.77093 5.03887 9.93245 4.79497C10.094 4.55107 10.1807 4.26526 10.1818 3.97273V3.81818C10.1818 3.33597 10.3734 2.87351 10.7144 2.53253C11.0553 2.19156 11.5178 2 12 2C12.4822 2 12.9447 2.19156 13.2856 2.53253C13.6266 2.87351 13.8182 3.33597 13.8182 3.81818V3.9C13.8193 4.19253 13.906 4.47834 14.0676 4.72224C14.2291 4.96614 14.4584 5.15749 14.7273 5.27273C15.0015 5.39374 15.3056 5.42984 15.6005 5.37637C15.8954 5.3229 16.1676 5.18231 16.3818 4.97273L16.4364 4.91818C16.6052 4.74913 16.8057 4.61503 17.0265 4.52353C17.2472 4.43203 17.4838 4.38493 17.7227 4.38493C17.9617 4.38493 18.1983 4.43203 18.419 4.52353C18.6397 4.61503 18.8402 4.74913 19.0091 4.91818C19.1781 5.08704 19.3122 5.28757 19.4037 5.50829C19.4952 5.72901 19.5423 5.96561 19.5423 6.20455C19.5423 6.44348 19.4952 6.68008 19.4037 6.9008C19.3122 7.12152 19.1781 7.32205 19.0091 7.49091L18.9545 7.54545C18.745 7.75971 18.6044 8.03183 18.5509 8.32674C18.4974 8.62164 18.5335 8.9258 18.6545 9.2V9.27273C18.7698 9.54161 18.9611 9.77093 19.205 9.93245C19.4489 10.094 19.7347 10.1807 20.0273 10.1818H20.1818C20.664 10.1818 21.1265 10.3734 21.4675 10.7144C21.8084 11.0553 22 11.5178 22 12C22 12.4822 21.8084 12.9447 21.4675 13.2856C21.1265 13.6266 20.664 13.8182 20.1818 13.8182H20.1C19.8075 13.8193 19.5217 13.906 19.2778 14.0676C19.0339 14.2291 18.8425 14.4584 18.7273 14.7273Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'grid-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M8.4 3H4.6C4.03995 3 3.75992 3 3.54601 3.10899C3.35785 3.20487 3.20487 3.35785 3.10899 3.54601C3 3.75992 3 4.03995 3 4.6V8.4C3 8.96005 3 9.24008 3.10899 9.45399C3.20487 9.64215 3.35785 9.79513 3.54601 9.89101C3.75992 10 4.03995 10 4.6 10H8.4C8.96005 10 9.24008 10 9.45399 9.89101C9.64215 9.79513 9.79513 9.64215 9.89101 9.45399C10 9.24008 10 8.96005 10 8.4V4.6C10 4.03995 10 3.75992 9.89101 3.54601C9.79513 3.35785 9.64215 3.20487 9.45399 3.10899C9.24008 3 8.96005 3 8.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 3H15.6C15.0399 3 14.7599 3 14.546 3.10899C14.3578 3.20487 14.2049 3.35785 14.109 3.54601C14 3.75992 14 4.03995 14 4.6V8.4C14 8.96005 14 9.24008 14.109 9.45399C14.2049 9.64215 14.3578 9.79513 14.546 9.89101C14.7599 10 15.0399 10 15.6 10H19.4C19.9601 10 20.2401 10 20.454 9.89101C20.6422 9.79513 20.7951 9.64215 20.891 9.45399C21 9.24008 21 8.96005 21 8.4V4.6C21 4.03995 21 3.75992 20.891 3.54601C20.7951 3.35785 20.6422 3.20487 20.454 3.10899C20.2401 3 19.9601 3 19.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 14H15.6C15.0399 14 14.7599 14 14.546 14.109C14.3578 14.2049 14.2049 14.3578 14.109 14.546C14 14.7599 14 15.0399 14 15.6V19.4C14 19.9601 14 20.2401 14.109 20.454C14.2049 20.6422 14.3578 20.7951 14.546 20.891C14.7599 21 15.0399 21 15.6 21H19.4C19.9601 21 20.2401 21 20.454 20.891C20.6422 20.7951 20.7951 20.6422 20.891 20.454C21 20.2401 21 19.9601 21 19.4V15.6C21 15.0399 21 14.7599 20.891 14.546C20.7951 14.3578 20.6422 14.2049 20.454 14.109C20.2401 14 19.9601 14 19.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M8.4 14H4.6C4.03995 14 3.75992 14 3.54601 14.109C3.35785 14.2049 3.20487 14.3578 3.10899 14.546C3 14.7599 3 15.0399 3 15.6V19.4C3 19.9601 3 20.2401 3.10899 20.454C3.20487 20.6422 3.35785 20.7951 3.54601 20.891C3.75992 21 4.03995 21 4.6 21H8.4C8.96005 21 9.24008 21 9.45399 20.891C9.64215 20.7951 9.79513 20.6422 9.89101 20.454C10 20.2401 10 19.9601 10 19.4V15.6C10 15.0399 10 14.7599 9.89101 14.546C9.79513 14.3578 9.64215 14.2049 9.45399 14.109C9.24008 14 8.96005 14 8.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-up':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 15L12 9L6 15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-down':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 9L12 15L18 9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 17L13 12L18 7M11 17L6 12L11 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 18L15 12L9 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 17L11 12L6 7M13 17L18 12L13 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'arrow-up-right':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7 17L17 7M17 7H7M17 7V17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'plus':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 5V19M5 12H19" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-circle-broken':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-in-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 8L16 12M16 12L12 16M16 12H3M3.33782 7C5.06687 4.01099 8.29859 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C8.29859 22 5.06687 19.989 3.33782 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-out-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 8L22 12M22 12L18 16M22 12H9M15 4.20404C13.7252 3.43827 12.2452 3 10.6667 3C5.8802 3 2 7.02944 2 12C2 16.9706 5.8802 21 10.6667 21C12.2452 21 13.7252 20.5617 15 19.796" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-asc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H9M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-desc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H21M3 18H9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'clipboard-icon-svg':
				$xml = '<?xml version="1.0" encoding="utf-8"?>
				<svg version="1.1" id="Layer_3" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
					 viewBox="0 0 20 20" style="enable-background:new 0 0 20 20;" xml:space="preserve">
				<path d="M14,18H8c-1.1,0-2-0.9-2-2V7c0-1.1,0.9-2,2-2h6c1.1,0,2,0.9,2,2v9C16,17.1,15.1,18,14,18z M8,7v9h6V7H8z"/>
				<path d="M5,4h6V2H5C3.9,2,3,2.9,3,4v9h2V4z"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'x':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 7L7 17M7 7L17 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'diamond-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2.49954 9H21.4995M9.99954 3L7.99954 9L11.9995 20.5L15.9995 9L13.9995 3M12.6141 20.2625L21.5727 9.51215C21.7246 9.32995 21.8005 9.23885 21.8295 9.13717C21.8551 9.04751 21.8551 8.95249 21.8295 8.86283C21.8005 8.76114 21.7246 8.67005 21.5727 8.48785L17.2394 3.28785C17.1512 3.18204 17.1072 3.12914 17.0531 3.09111C17.0052 3.05741 16.9518 3.03238 16.8953 3.01717C16.8314 3 16.7626 3 16.6248 3H7.37424C7.2365 3 7.16764 3 7.10382 3.01717C7.04728 3.03238 6.99385 3.05741 6.94596 3.09111C6.89192 3.12914 6.84783 3.18204 6.75966 3.28785L2.42633 8.48785C2.2745 8.67004 2.19858 8.76114 2.16957 8.86283C2.144 8.95249 2.144 9.04751 2.16957 9.13716C2.19858 9.23885 2.2745 9.32995 2.42633 9.51215L11.385 20.2625C11.596 20.5158 11.7015 20.6424 11.8279 20.6886C11.9387 20.7291 12.0603 20.7291 12.1712 20.6886C12.2975 20.6424 12.4031 20.5158 12.6141 20.2625Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			default:
				$xml = '';

				break;

		}

		echo wp_kses( $xml, $allowed_html );
	}

	/**
	 * Display the dismissible notices stored in the "daim_dismissible_notice_a" option.
	 *
	 * Note that the dismissible notice will be displayed only once to the user.
	 *
	 * The dismissable notice is first displayed (only to the same user with which has been generated) and then it is
	 * removed from the "daim_dismissible_notice_a" option.
	 *
	 * @return void
	 */
	public function display_dismissible_notices() {

		$dismissible_notice_a = get_option( 'daim_dismissible_notice_a' );

		// Iterate over the dismissible notices with the user id of the same user.
		if ( is_array( $dismissible_notice_a ) ) {
			foreach ( $dismissible_notice_a as $key => $dismissible_notice ) {

				// If the user id of the dismissible notice is the same as the current user id, display the message.
				if ( get_current_user_id() === $dismissible_notice['user_id'] ) {

					$message = $dismissible_notice['message'];
					$class   = $dismissible_notice['class'];

					?>
					<div class="<?php echo esc_attr( $class ); ?> notice">
						<p><?php echo esc_html( $message ); ?></p>
						<div class="notice-dismiss-button"><?php $this->echo_icon_svg( 'x' ); ?></div>
					</div>

					<?php

					// Remove the echoed dismissible notice from the "daim_dismissible_notice_a" WordPress option.
					unset( $dismissible_notice_a[ $key ] );

					update_option( 'daim_dismissible_notice_a', $dismissible_notice_a );

				}
			}
		}
	}

	/**
	 * Save a dismissible notice in the "daim_dismissible_notice_a" WordPress.
	 *
	 * @param string $message The message of the dismissible notice.
	 * @param string $element_class The class of the dismissible notice.
	 *
	 * @return void
	 */
	public function save_dismissible_notice( $message, $element_class ) {

		$dismissible_notice = array(
			'user_id' => get_current_user_id(),
			'message' => $message,
			'class'   => $element_class,
		);

		// Get the current option value.
		$dismissible_notice_a = get_option( 'daim_dismissible_notice_a' );

		// If the option is not an array, initialize it as an array.
		if ( ! is_array( $dismissible_notice_a ) ) {
			$dismissible_notice_a = array();
		}

		// Add the dismissible notice to the array.
		$dismissible_notice_a[] = $dismissible_notice;

		// Save the dismissible notice in the "daim_dismissible_notice_a" WordPress option.
		update_option( 'daim_dismissible_notice_a', $dismissible_notice_a );
	}

	/**
	 * Returns true if there are exportable data or false if here are no exportable data.
	 */
	public function exportable_data_exists() {

		$exportable_data = false;
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_connect" );
		if ( $total_items > 0 ) {
			$exportable_data = true;
		}

		return $exportable_data;
	}

	/**
	 * Display the connection codes with links based on the provided connection id.
	 *
	 * @param int $connection_id The connection id.
	 *
	 * @return void
	 */
	public function display_connection_codes( $connection_id ) {

		// Get the connection.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$connection_obj = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}da_hm_connect WHERE id = %d", $connection_id ) );

		$url      = json_decode( $connection_obj->url );
		$language = json_decode( $connection_obj->language );
		$script   = json_decode( $connection_obj->script );
		$locale   = json_decode( $connection_obj->locale );

		for ( $i = 1; $i <= 100; $i ++ ) {

			if ( isset( $url->{$i} ) && strlen( $url->{$i} ) > 0 ) {
				echo '<a target="_blank" href="' . esc_attr( stripslashes( $url->{$i} ) ) . '">' . esc_html( stripslashes( $language->{$i} ) );
				if ( strlen( $script->{$i} ) > 0 ) {
					echo '-' . esc_html( stripslashes( $script->{$i} ) );
				}
				if ( strlen( $locale->{$i} ) > 0 ) {
					echo '-' . esc_html( stripslashes( $locale->{$i} ) );
				}
				echo '</a> ';
			}
		}
	}

	/**
	 * Return "Yes" if the provided value $inherited is true, otherwise return "No".
	 *
	 * @param int $inherited A bool indicating the value of inherited.
	 *
	 * @return void
	 */
	public function prepare_displayed_value_inherited( $inherited ) {

		if ( $inherited ) {
			return __( 'Yes', 'hreflang-manager' );
		} else {
			return __( 'No', 'hreflang-manager' );
		}
	}

	/**
	 * Make the database data compatible with the new plugin versions.
	 *
	 * Only Task 1 is available at the moment. Use Task 2, Task 3, etc. for additional operation on the database.
	 */
	public function convert_options_data() {

		/**
		 * Task 1:
		 *
		 * Convert all the options that include list of post types saved as a comma separated list of values to an
		 * array.
		 *
		 * Note that these options are saved serialized starting from version 1.33
		 */
		$option_name_a = array(
			'_meta_box_post_types',
		);

		foreach ( $option_name_a as $option_name ) {

			$option_value = get_option( $this->get( 'slug' ) . $option_name );

			if ( false === $option_value || is_array( $option_value ) || ( is_string( $option_value ) && strlen( $option_value ) === 0 ) ) {
				continue;
			}

			$activate_post_types = preg_replace( '/\s+/', '', $option_value );
			$post_type_a         = explode( ',', $activate_post_types );

			if ( is_array( $post_type_a ) ) {
				update_option( $this->get( 'slug' ) . $option_name, $post_type_a );
			}
		}
	}

	/**
	 * Sanitize the data of the table provided as an escaped json string.
	 *
	 * @param string $table_data The table data provided as an escaped json string.
	 *
	 * @return array|bool
	 */
	public function sanitize_table_data( $table_data ) {

		// Decode the table data provided in json format.
		$table_data = json_decode( $table_data );

		// Verify if data property of the returned object is an array.
		if ( ! isset( $table_data ) || ! is_array( $table_data ) ) {
			return false;
		}

		foreach ( $table_data as $row_index => $row_data ) {

			// Verify if the table row data are provided as an array.
			if ( ! is_array( $row_data ) ) {
				return false;
			}

			for ( $i = 0; $i <= 400; $i ++ ) {

				/**
				 * This conditional is used to determine the type of data that is sanitized based its position in the
				 * $row_data array.
				 */
				if ( 0 === $i ||
				     1 === $i ||
				     0 === ( $i - 1 ) % 4 ) {

					// Sanitize URLs with esc_url_raw().
					$table_data[ $row_index ][ $i ] = esc_url_raw( $row_data[ $i ] );

				} else {

					// Sanitize codes for language, script, or locale with sanitize_key().
					$table_data[ $row_index ][ $i ] = sanitize_key( $row_data[ $i ] );

				}
			}
		}

		return $table_data;
	}

	/**
	 * Sanitize the data of an uploaded file.
	 *
	 * @param array $file The data of the uploaded file.
	 *
	 * @return array
	 */
	public function sanitize_uploaded_file( $file ) {

		return array(
			'name'     => sanitize_file_name( $file['name'] ),
			'type'     => $file['type'],
			'tmp_name' => $file['tmp_name'],
			'error'    => intval( $file['error'], 10 ),
			'size'     => intval( $file['size'], 10 ),
		);
	}

	/**
	 * Schedule the "da_hm_cron_hook" action hook with the frequency defined with the Sync -> Frequency option.
	 */
	public function schedule_cron_event() {

		// Get the sync frequency from the plugin options.
		$sync_frequency = intval( get_option( $this->get( 'slug' ) . '_sync_frequency' ), 10 );

		// Find the corresponding recurrence value.
		$recurrence = null;
		switch ( $sync_frequency ) {

			case 0:
				$recurrence = 'hourly';
				break;

			case 1:
				$recurrence = 'twicedaily';
				break;

			case 2:
				$recurrence = 'daily';
				break;

			case 3:
				$recurrence = 'weekly';
				break;

		}

		// Clear the existing scheduled hook "da_hm_cron_hook".
		wp_clear_scheduled_hook( 'da_hm_cron_hook' );

		// Schedule the hook "da_hm_cron_hook" with the defined frequency.
		wp_schedule_event( time(), $recurrence, 'da_hm_cron_hook' );

	}

	/**
	 * Send a request to the remote server to get the plugin information.
	 *
	 * These plugin information are used:
	 *
	 * - To include the plugin information for our custom plugin using the callback of the "plugins_api" filter.
	 * - To report the plugin as out of date if needed by adding information to the transient.
	 * - As a verification of the license. Since a response with an error is returned if the license is not valid.
	 *
	 * @return false|object
	 */
	public function fetch_remote_plugin_info() {

		// The transient expiration is set to 24 hours.
		$transient_expiration = 86400;

		$remote = get_transient( DAHM_WP_PLUGIN_UPDATE_INFO_TRANSIENT );

		/**
		 * If the transient does not exist, does not have a value, or has expired, then fetch the plugin information
		 * from the remote server on daext.com.
		 */
		if ( false === $remote ) {

			$license_provider = get_option( 'da_hm_license_provider' );
			$license_key      = get_option( 'da_hm_license_key' );

			// Prepare the body of the request.
			$body = wp_json_encode(
				array(
					'license_provider' => $license_provider,
					'license_key'      => $license_key,
					'slug'             => 'hreflang-manager',
					'domain'           => site_url(),
				)
			);

			$remote = wp_remote_post(
				DAHM_WP_PLUGIN_UPDATE_INFO_API_URL,
				array(
					'method'  => 'POST',
					'timeout' => 10,
					'body'    => $body,
					'headers' => array(
						'Content-Type' => 'application/json',
					),
				)
			);

			$response_code = wp_remote_retrieve_response_code( $remote );

			if (
				is_wp_error( $remote )
				|| 200 !== $response_code
				|| empty( wp_remote_retrieve_body( $remote ) )
			) {

				/**
				 * For valid response where the license has been verified, and it's invalid, save a specific
				 * 'invalid_license' error in the transient.
				 */
				$remote_body = json_decode( wp_remote_retrieve_body( $remote ) );
				if ( 403 === $response_code && 'invalid_license' === $remote_body->error ) {

					$error_res = new WP_Error( 'invalid_license', 'Invalid License' );
					set_transient( DAHM_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );

				} else {

					/**
					 * With other error response codes save a generic error response in the transient.
					 */
					$error_res = new WP_Error( 'generic_error', 'Generic Error' );
					set_transient( DAHM_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );

				}

				return $error_res;

			} else {

				/**
				 * With a valid license, save the plugin information in the transient and return the plugin information.
				 * Otherwise, save the error in the transient and return the error.
				 */

				$remote = json_decode( wp_remote_retrieve_body( $remote ) );

				// Check if the fields of a valid response are also set.
				if ( isset( $remote->name ) &&
				     isset( $remote->slug ) ) {
					set_transient( DAHM_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $remote, $transient_expiration );

					return $remote;
				} else {
					$error_res = new WP_Error( 'generic_error', 'Generic Error' );
					set_transient( DAHM_WP_PLUGIN_UPDATE_INFO_TRANSIENT, $error_res, $transient_expiration );

					return $error_res;
				}

			}

		}

		return $remote;
	}

	/**
	 * Display a notice to the user to activate the license.
	 *
	 * @return void
	 */
	public function display_license_activation_notice() {

		require_once $this->get( 'dir' ) . 'vendor/autoload.php';
		$plugin_update_checker = new PluginUpdateChecker( DAHM_PLUGIN_UPDATE_CHECKER_SETTINGS );

		if ( ! $plugin_update_checker->is_valid_license() ) {
			?>
			<div class="dahm-license-activation-notice">
				<h2><?php esc_html_e( 'Activate Your License', 'hreflang-manager' ); ?></h2>
				<p><?php esc_html_e( "Please activate your license to enable plugin updates and access technical support. It's important to note that without updates, the plugin may stop functioning correctly when new WordPress versions are released. Furthermore, not receiving updates could expose your site to security issues.", 'hreflang-manager' ) . '</p>'; ?>
				<h4><?php esc_html_e( 'License Activation Steps', 'hreflang-manager' ); ?></h4>
				<ol>
					<li>
						<?php esc_html_e( 'If you purchased the plugin from daext.com, you can find your license key in your account at', 'hreflang-manager' ); ?>
						&nbsp<a href="https://daext.com/account/"
						        target="_blank"><?php esc_html_e( 'https://daext.com/account/', 'hreflang-manager' ); ?></a>.
						<?php esc_html_e( 'For Envato Market purchases, use the item purchase code available in your', 'hreflang-manager' ); ?>
						&nbsp<strong><?php esc_html_e( 'Downloads', 'hreflang-manager' ); ?></strong>&nbsp<?php esc_html_e( 'area.', 'hreflang-manager' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Navigate to', 'hreflang-manager' ); ?>
						&nbsp<strong><?php esc_html_e( 'Options → License → License Management', 'hreflang-manager' ); ?></strong>&nbsp<?php esc_html_e( 'to enter your license key.', 'hreflang-manager' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Once in the', 'hreflang-manager' ); ?>
						&nbsp<strong><?php esc_html_e( 'License Management', 'hreflang-manager' ); ?></strong>&nbsp<?php esc_html_e( 'section, choose the appropriate license provider from the dropdown menu.', 'hreflang-manager' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Enter your license key into the provided field.', 'hreflang-manager' ); ?></li>
					<li>
						<?php esc_html_e( 'Click the', 'hreflang-manager' ); ?>
						&nbsp<strong><?php esc_html_e( 'Save settings', 'hreflang-manager' ); ?></strong>&nbsp<?php esc_html_e( 'button to activate your license.', 'hreflang-manager' ); ?>
					</li>
				</ol>
				<h4><?php esc_html_e( 'Reasons for This Notice', 'hreflang-manager' ); ?></h4>
				<p><?php esc_html_e( 'Here are the common reasons for this notification:', 'hreflang-manager' ) . '</p>'; ?>
				<ul>
					<li>
						<?php esc_html_e( 'A license key has not been provided.', 'hreflang-manager' ); ?></li>
					<li>
						<?php esc_html_e( 'The configured license key is invalid or expired.', 'hreflang-manager' ); ?></li>
					<li><?php esc_html_e( "The configured license key is already linked to other sites, and the license tier related to this key doesn't allow additional activations.", 'hreflang-manager' ); ?></li>
					<li><?php echo esc_html__( 'Our server is temporarily unable to validate your license. To manually verify your license, click ', 'hreflang-manager' ) . '<a href="' . esc_url( $this->get_current_admin_page_verify_license_url() ) . '">' . esc_html__( 'this link', 'hreflang-manager' ) . '</a>' . esc_html__( '. A successful validation will dismiss this notice.', 'hreflang-manager' ); ?></li>
				</ul>
				<h4><?php esc_html_e( 'Support and Assistance', 'hreflang-manager' ); ?></h4>
				<p><?php esc_html_e( "If you're having trouble activating your license or locating your key, visit our", 'hreflang-manager' ); ?>
					&nbsp<a href="https://daext.com/support/"
					        target="_blank"><?php esc_html_e( 'support page', 'hreflang-manager' ); ?></a>&nbsp<?php esc_html_e( 'for help.', 'hreflang-manager' ); ?>
				</p>
			</div>
			<?php
		}

	}

	/**
	 * Verify the license key. If the license is not valid display a message and return false.
	 *
	 * @return bool
	 */
	public function is_valid_license() {

		$plugin_info = get_transient( DAHM_WP_PLUGIN_UPDATE_INFO_TRANSIENT );

		if ( false === $plugin_info || is_wp_error( $plugin_info ) ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Get the current page URL with the da_hm_verify_license=1 parameter and a nonce.
	 *
	 * @return string|null
	 */
	public function get_current_admin_page_verify_license_url() {

		// Generate a nonce for the action "da_hm_verify_license".
		$nonce = wp_create_nonce( 'da_hm_verify_license' );

		// Add query parameters 'da_hm_verify_license' and 'da_hm_verify_license_nonce'.
		return admin_url(
			'admin.php?page=' . $this->get( 'slug' ) . '_connections' . '&' . http_build_query(
				array(
					'da_hm_verify_license'       => 1,
					'da_hm_verify_license_nonce' => $nonce,
				)
			)
		);
	}

	/**
	 * Ajax handler used to generate the http status archive displayed in the "Http Status" menu.
	 *
	 * @return void
	 */
	public function update_hreflang_checker_issues() {

		// Clear the existing cron event.
		wp_clear_scheduled_hook( 'da_hm_cron_hook_2' );

		// Delete all the items in the "_hreflang_checker_issues" db table.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}da_hm_hreflang_checker_issue" );

		// Delete all the items in the "_hreflang_checker_queue" db table.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}da_hm_hreflang_checker_queue" );

		// Create a new list of URLs to check.
		$this->create_hreflang_checker_queue();

		// Schedule the cron event.
		$this->schedule_cron_event_2();

//		update_option( $this->get( 'slug' ) . '_http_status_data_last_update', current_time( 'mysql' ) );
	}

	/**
	 * Create the list of URLs for which the HTTP status code should be checked. The records are saved in the
	 * "_http_status" db table.
	 */
	public function create_hreflang_checker_queue() {

		// Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
		$this->set_met_and_ml();

		// Delete all the items in the "_hreflang_checker_queue" db table.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}da_hm_hreflang_checker_queue" );

		// Get all the items from the "_connect" db table.
		$connect_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}da_hm_connect", ARRAY_A );

		// Iterate over the items from the "_connect" db table.
		foreach ( $connect_a as $key => $connect ) {

			// Create an item of the "_hreflang_checker_queue" db table.
			$wpdb->insert(
				$wpdb->prefix . 'da_hm_hreflang_checker_queue',
				array(
					'url_to_connect'      => $connect['url_to_connect'],
				)
			);

		}

	}

	/**
	 * Execute the cron jobs.
	 */
	public function da_hm_cron_exec_2() {

		// Generate the hreflang checker issues of a limited number of URLs saved in the "hreflang_checker_queue" db table.
		$this->hreflang_checker->generate_hreflang_checker_issues();

		/**
		 * Check in the "_hreflang_checker_queue" db table if all the URLs have been checked. (if there are URLs to check)
		 * If all the links have been checked, clear the schedule of the cron hook.
		 */
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}da_hm_hreflang_checker_queue" );

		if ( intval( $count, 10 ) === 0 ) {
			wp_clear_scheduled_hook( 'da_hm_cron_hook_2' );
		}
	}

	/**
	 * Schedule the cron event.
	 */
	public function schedule_cron_event_2() {
		if ( ! wp_next_scheduled( 'da_hm_cron_hook_2' ) ) {
			wp_schedule_event( time(), 'da_hm_custom_schedule_2', 'da_hm_cron_hook_2' );
		}
	}

	/**
	 * Adds a custom cron schedule that uses the interval defined in the "http_status_cron_schedule_interval" option.
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 *
	 * @return array Filtered array of non-default cron schedules.
	 */
	public function custom_cron_schedule( $schedules ) {

		// Add a custom schedule named 'da_hm_custom_schedule_2' to the existing set.
		$schedules['da_hm_custom_schedule_2'] = array(
			'interval' => intval( get_option( $this->get( 'slug' ) . '_checker_cron_schedule_interval' ), 10 ),
			'display'  => __( 'Custom schedule based on the Hreflang Manager options.', 'hreflang-manager' ),
		);

		return $schedules;
	}

}