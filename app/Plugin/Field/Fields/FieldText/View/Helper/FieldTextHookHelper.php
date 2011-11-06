<?php
class FieldTextHookHelper extends AppHelper {
    function field_text_view($data) {
        return $this->_View->element('view', array('data' => $data), array('plugin' => 'FieldText'));
    }

    function field_text_edit($data) {
        return $this->_View->element('edit', array('data' => $data), array('plugin' => 'FieldText'));
    }

    function field_text_formatter($data) {
        if (isset($data['settings']['text_processing']) && !empty($data['settings']['text_processing'])) {
            $this->_View->Layout->hook('text_processing_' . $data['settings']['text_processing'], $data['content']);
        }

        switch($data['format']['type']) {
            case 'plain':
                $data['content'] = $this->__filterText($data['content']);
            break;

            case 'trimmed':
                $len = @$data['format']['trim_length'];
                $data['content'] = $this->__trimmer($data['content'], $len);
            break;
        }

        return $data['content'];
    }
    
    // already saved in plain, nothing to do
    public function text_processing_plain(&$text) {
        $text = $this->__email2Link($text);
        $text = $this->__url2Link($text);        
        $text = nl2br($text);
    }

    // already saved in plain, nothing to do
    public function text_processing_filtered(&$text) {
        $text = $this->__email2Link($text);
        $text = $this->__url2Link($text);                
        $text = nl2br($text);
    }

    // convert from plain text markdown to html
    public function text_processing_markdown(&$text) {
        if (!isset($this->MarkdownParser) || !is_object($this->MarkdownParser)) {
            App::import('Lib', 'FieldText.Markdown');
            
            $this->MarkdownParser = new Markdown_Parser;
        }

        $text = $this->MarkdownParser->transform($text);
        $text = $this->__email2Link($text);
        $text = str_replace('<p>h', '<p> h', $text);
        $text = $this->__url2Link($text);            
    }

    public function text_processing_full(&$text) {
        $text = $this->__email2Link($text);
        $text = $this->__url2Link($text);
    }

    public function close_open_tags($html) {
        #put all opened tags into an array
        preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);

        $openedtags = $result[1];

        #put all closed tags into an array
        preg_match_all("#</([a-z]+)>#iU", $html, $result);

        $closedtags = $result[1];
        $len_opened = count($openedtags);

        # all tags are closed
        if(count($closedtags) == $len_opened){
            return $html;
        }

        $openedtags = array_reverse($openedtags);
        # close tags
        for($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</' . $openedtags[$i] . '>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }

        return $html;
    }

    private function __filterText($text) {
        return $this->_View->Layout->removeHookTags(strip_tags($text));
    }

    // Convert url to <a> HTML tag, also ignore URLs in existing <a> tags        
    public static function __url2Link($text) {
        $pattern = array(
            '/(?<!http:\/\/|https:\/\/|\"|=|\'|\'>|\">)(www\..*?)(\s|\Z|\.\Z|\.\s|\<|\>|,)/i',
            '/(?<!\"|=|\'|\'>|\">|site:)(https?:\/\/(www){0,1}.*?)(\s|\Z|\.\Z|\.\s|\<|\>|,)/i'
        );

        $replacement = array(
            "<a href=\"http://$1\">$1</a>$2",
            "<a href=\"$1\" target=\"_blank\">$1</a>$3"
        );

        return preg_replace($pattern, $replacement, $text);
    }

    private function __email2Link($text) {
        preg_match_all("/([a-z0-9_\-\.]+)@([a-z0-9-]{1,64})\.([a-z]{2,10})/i", $text, $emails);

        foreach ($emails[0] as $email) {
            $email = trim($email);
            $text = str_replace($email, $this->obfuscate_email($email), $text);
        }

        return $text;
    }

    private function __trimmer($text, $len = false) {
        if (!preg_match('/[0-9]+/i', $len)) { # not numeric, readmore line (<!-- readmore -->)
            $read_more = explode($len, $text);

            return $this->close_open_tags($read_more[0]);
        }

        $len = !$len || !is_numeric($len) || $len === 0 ? 600 : $len;
        $text = $this->__filterText($text);
        $textLen = strlen($text);

        if ($textLen > $len) {
            return substr($text, 0, $len) . ' ...';
        }

        return $text;
    }
    
    public function obfuscate_email($email) {
        $plaintext = $email;
        $result = "<a href=\"";
        $result .= htmlentities("mailto:" . urlencode($plaintext));
        $result .= "\">";
        $result .= htmlentities($plaintext);
        $result .= "</a>";
        $pt = $result; // "<a href='mailto:" + plaintext + "'>" + plaintext + "</a>";
        $result2 = "<script>document.write(";

        for ($i = 0; $i < strlen($pt); ++$i) {
            switch ($pt[$i]) {
                case "'": 
                    $result2 .= "\"'\"";
                break;

                case '"': 
                    $result2 .= '"\'"';
                break;

                default:
                    $result2 .= "'" . $pt[$i] . "'";
                break;
            }

            if ($i < (strlen($pt)-1)) {
                $result2 .= '+';
            }

            if ($i % 25 == 24) {
              $result2 .= "\n";
            }
        }

        $result2 .= ");</script><noscript>[" . __d('field_text', 'Turn on JavaScript to see the email address') . "]</noscript>";

        return $result2;
    }
}