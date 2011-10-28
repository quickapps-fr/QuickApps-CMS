<?php
if (!class_exists('L10n')) {
    App::import('I18n', 'L10n');
}

class Locale {
    public function languages() {
        $L10n = new L10n;
        $catalog = $L10n->catalog();
        $r = array();

        foreach ($catalog as $language => $info) {
            $code = $L10n->map($language);

            if ($code) {
                $r[$code] = $info['language'];
            }
        }

        return $r;
    }

    public function language_direction($code = false) {
        if (!$code) {
            $code = Configure::read('Config.language');
        }

        $L10n = new L10n;
        $l = $L10n->catalog($L10n->map($code));

        if (!$l) {
            return 'ltr';
        }

        return $l['direction'];
    }

    public function countries_list() {
        return $L10n->catalog();
    }

    public function time_zones($blank = NULL) {
        $zonelist = timezone_identifiers_list();
        $zones = $blank ? array('' => __t('- None selected -')) : array();

        foreach ($zonelist as $zone) {
            if (preg_match('!^((Africa|America|Antarctica|Arctic|Asia|Atlantic|Australia|Europe|Indian|Pacific)/|UTC$)!', $zone)) {
                $zones[$zone] = __t('%s: %s', __t(str_replace('_', ' ', $zone)), self::format_date(env('REQUEST_TIME'), 'custom', __t('l, F j, Y - H:i') . ' O', $zone));
            }
        }

        asort($zones);

        return $zones;
    }

    public function format_date($timestamp, $type = 'medium', $format = '', $timezone = null, $langcode = null) {
        if (!isset($timezone)) {
            $timezone = date_default_timezone_get();
        }

        // Store DateTimeZone objects in an array rather than repeatedly
        // constructing identical objects over the life of a request.
        if (!isset($timezones[$timezone])) {
            $timezones[$timezone] = timezone_open($timezone);
        }

        switch ($type) {
            case 'small':
                $format = __t('m/d/Y - H:i');
            break;

            case 'large':
                $format = __t('l, F j, Y - H:i');
            break;

            case 'custom':
                // No change to format.
            break;

            case 'medium':
                default:
                    $format = __t('D, m/d/Y - H:i');
            break;
        }

        // Create a DateTime object from the timestamp.
        $date_time = date_create('@' . $timestamp);
        // Set the time zone for the DateTime object.
        date_timezone_set($date_time, $timezones[$timezone]);

        // Encode markers that should be translated. 'A' becomes '\xEF\AA\xFF'.
        // xEF and xFF are invalid UTF-8 sequences, and we assume they are not in the
        // input string.
        // Paired backslashes are isolated to prevent errors in read-ahead evaluation.
        // The read-ahead expression ensures that A matches, but not \A.
        $format = preg_replace(array('/\\\\\\\\/', '/(?<!\\\\)([AaeDlMTF])/'), array("\xEF\\\\\\\\\xFF", "\xEF\\\\\$1\$1\xFF"), $format);

        // Call date_format().
        $format = date_format($date_time, $format);

        // Pass the langcode to format_date_callback().
        self::format_date_callback(NULL, $langcode);

        // Translate the marked sequences.
        return preg_replace_callback('/\xEF([AaeDlMTF]?)(.*?)\xFF/', array('Locale', 'format_date_callback') , $format);
    }

/**
* Callback function for preg_replace_callback().
*/
    public function format_date_callback(array $matches = null, $new_langcode = null) {
        // We cache translations to avoid redundant and rather costly calls to t().
        static $cache, $langcode;

        if (!isset($matches)) {
            $langcode = $new_langcode;
            return;
        }

        $code = $matches[1];
        $string = $matches[2];

        if (!isset($cache[$langcode][$code][$string])) {
            $options = array( 'langcode' => $langcode );

            if ($code == 'F') {
                $options['context'] = 'Long month name';
            }

            if ($code == '') {
                $cache[$langcode][$code][$string] = $string;
            } else {
                $cache[$langcode][$code][$string] = __t($string, array(), $options);
            }
        }

        return $cache[$langcode][$code][$string];
    }
}