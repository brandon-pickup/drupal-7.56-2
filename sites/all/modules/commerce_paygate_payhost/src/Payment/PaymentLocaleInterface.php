<?php

namespace Drupal\commerce_paygate_payhost\Payment;

/**
 * List of available locales for payment.
 */
interface PaymentLocaleInterface {

  const LOCALES = [
    'Af' => 'Afrikaans',
    'Sq' => 'Albanian',
    'ar-sa' => 'Arabic (Saudi Arabia)',
    'ar-iq' => 'Arabic (Iraq)',
    'ar-eg' => 'Arabic (Egypt)',
    'ar-ly' => 'Arabic (Libya)',
    'ar-dz' => 'Arabic (Algeria)',
    'ar-ma' => 'Arabic (Morocco)',
    'ar-tn' => 'Arabic (Tunisia)',
    'ar-om' => 'Arabic (Oman)',
    'ar-ye' => 'Arabic (Yemen)',
    'ar-sy' => 'Arabic (Syria)',
    'ar-jo' => 'Arabic (Jordan)',
    'ar-lb' => 'Arabic (Lebanon)',
    'ar-kw' => 'Arabic (Kuwait)',
    'ar-ae' => 'Arabic (U.A.E.)',
    'ar-bh' => 'Arabic (Bahrain)',
    'ar-qa' => 'Arabic (Qatar)',
    'Eu' => 'Basque',
    'bg' => 'Bulgarian',
    'Be' => 'Belarusian',
    'Ca' => 'Catalan',
    'zh-tw' => 'Chinese (Taiwan)',
    'zh-hk' => 'Chinese (Hong Kong SAR)',
    'zh-cn' => 'Chinese (PRC)',
    'zh-sg' => 'Chinese (Singapore)',
    'Hr' => 'Croatian',
    'cs' => 'Czech',
    'da' => 'Danish',
    'nl' => 'Dutch',
    'nl-be' => 'Dutch (Belgium)',
    'en' => 'English',
    'En' => 'Caribbean',
    'en-us' => 'English (United States)',
    'en-gb' => 'English (United Kingdom)',
    'en-au' => 'English (Australia)',
    'en-ca' => 'English (Canada)',
    'en-nz' => 'English (New Zealand)',
    'en-ie' => 'English (Ireland)',
    'en-za' => 'English (South Africa)',
    'en-jm' => 'English (Jamaica)',
    'en-bz' => 'English (Belize)',
    'en-tt' => 'English (Trinidad)',
    'et' => 'Estonian',
    'fo' => 'Faeroese',
    'fa' => 'Farsi',
    'fi' => 'Finnish',
    'fr' => 'French',
    'fr-be' => 'French (Belgium)',
    'fr-ca' => 'French (Canada)',
    'fr-ch' => 'French (Switzerland)',
    'fr-lu' => 'French (Luxembourg)',
    'gd' => 'Gaelic (Scotland)',
    'ga' => 'Irish',
    'de' => 'German',
    'de-ch' => 'German (Switzerland)',
    'de-at' => 'German (Austria)',
    'de-lu' => 'German (Luxembourg)',
    'de-li' => 'German (Liechtenstein)',
    'el' => 'Greek',
    'he' => 'Hebrew',
    'hi' => 'Hindi',
    'hu' => 'Hungarian',
    'is' => 'Icelandic',
    'id' => 'Indonesian',
    'it' => 'Italian',
    'it-ch' => 'Italian (Switzerland)',
    'ja' => 'Japanese',
    'ko' => 'Korean',
    'lv' => 'Latvian',
    'lt' => 'Lithuanian',
    'mk' => 'Macedonian (FYROM)',
    'ms' => 'Malaysian',
    'mt' => 'Maltese',
    'no' => 'Norwegian (Bokmal)',
    'pl' => 'Polish',
    'pt' => 'Portuguese',
    'pt-br' => 'Portuguese (Brazil)',
    'rm' => 'Rhaeto-Romanic',
    'ro' => 'Romanian',
    'ro-mo' => 'Romanian (Republic of Moldova)',
    'ru' => 'Russian',
    'ru-mo' => 'Republic of Moldova',
    'sz' => 'Sami (Lappish)',
    'sr' => 'Serbian',
    'sk' => 'Slovak',
    'sl' => 'Slovenian',
    'sb' => 'Sorbian',
    'es' => 'Spanish',
    'es-mx' => 'Spanish (Mexico)',
    'es-gt' => 'Spanish (Guatemala)',
    'es-cr' => 'Spanish (Costa Rica)',
    'es-pa' => 'Spanish (Panama)',
    'es-do' => 'Spanish (Dominican Republic)',
    'es-ve' => 'Spanish (Venezuela)',
    'es-co' => 'Spanish (Colombia)',
    'es-pe' => 'Spanish (Peru)',
    'es-ar' => 'Spanish (Argentina)',
    'es-ec' => 'Spanish (Ecuador)',
    'es-cl' => 'Spanish (Chile)',
    'es-uy' => 'Spanish (Uruguay)',
    'es-py' => 'Spanish (Paraguay)',
    'es-bo' => 'Spanish (Bolivia)',
    'es-sv' => 'Spanish (El Salvador)',
    'es-hn' => 'Spanish (Honduras)',
    'es-ni' => 'Spanish (Nicaragua)',
    'es-pr' => 'Spanish (Puerto Rico)',
    'sx' => 'Sutu',
    'sv' => 'Swedish',
    'sv-fi' => 'Swedish (Finland)',
    'th' => 'Thai',
    'ts' => 'Tsonga',
    'tn' => 'Tswana',
    'tr' => 'Turkish',
    'uk' => 'Ukrainian',
    'ur' => 'Urdu',
    've' => 'Venda',
    'vi' => 'Vietnamese',
    'xh' => 'Xhosa',
    'ji' => 'Yiddish',
    'zu' => 'Zulu',
  ];

}
