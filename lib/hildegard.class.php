<?
/*
class hildegard 
   
php4-class for parsing emailheaders.

changes:

2006-02-25 Martin Stevens bugdester@budgester.com    
2002-08-28 Achim Schmidt schmidt@waaf.net
2002-12-11 Achim Schmidt schmidt@waaf.net
2003-05-06 Achim Schmidt schmidt@waaf.net
2003-12-06 Achim Schmidt schmidt@waaf.net
*/
class hildegard {

    var $head;          // the whole mails header in one string
    var $from;          // the complete sender ('Testferd Test' <test@waaf.net>)
    var $from_name;     // the senders Name ('Testferd Test')
    var $from_addr;     // the sender mailaddr (test@waaf.net)
    var $cc;            // the complete Cc ('CC' <cc-test@waaf.net>)
    var $to;            // the complete Recipient-Line ('R. E. Cipient' <rec@waaf.net>)
    var $date;          // the time the mail was sent
    var $subject;       // the mails subject
    var $msgid;         // the message-id of the message
    var $xirm;          // the IRM X header message 
    var $anh;           // are there attachements? (0 | 1)

    function hildegard($headers){
        $i = 0;
        foreach($headers as $ln => $value){
                $z1 = substr($value,0,1);
                if($z1 == "\t"){
                        $n_headers[$i] .= " ".trim($value);
                } else {
                        $i = $i + 1;
                        $n_headers[$i] = trim($value);
                }
        }
        $headers = $n_headers;

        $this->head = join( $headers, "\n" );

        $subj=preg_grep ("/^Subject:(.*)$/", $headers);
        $subj=join($subj, "\n" );
        $this->subject = trim(decode_mime_string(quoted_printable_decode(str_replace ("\"", "'", (str_replace ("Subject:", "", $subj))))));
        if($this->subject == ""){
                $subj=preg_grep ("/^subject:(.*)$/", $headers);
                $subj=join($subj, "\n" );
                $this->subject = trim(decode_mime_string(quoted_printable_decode(str_replace ("\"", "'", (str_replace ("subject:", "", $subj))))));
        }
        if($this->subject == ""){
                $subj=preg_grep ("/^SUBJECT:(.*)$/", $headers);
                $subj=join($subj, "\n" );
                $this->subject = trim(decode_mime_string(quoted_printable_decode(str_replace ("\"", "'", (str_replace ("SUBJECT:", "", $subj))))));
        }

        $msgid=preg_grep ("/^Message-ID:(.*)$/", $headers);
        $msgid=join($msgid, "\n" );
        $this->msgid = trim(decode_mime_string(str_replace ("\"", "'", (str_replace ("Message-ID:", "",$msgid)))));

        if($this->msgid == ""){
                $msgid=preg_grep ("/^X-MessageId:(.*)$/", $headers);
                $msgid=join($msgid, "\n" );
                $this->msgid = trim(decode_mime_string(str_replace ("\"", "'", (str_replace ("X-MessageId:", "",$msgid)))));
        }
        if($this->msgid == ""){
                $msgid=preg_grep ("/^Message-Id:(.*)$/", $headers);
                $msgid=join($msgid, "\n" );
                $this->msgid = trim(decode_mime_string(str_replace ("\"", "'", (str_replace ("Message-Id:", "",$msgid)))));
        }

        $to=preg_grep ("/^To:(.*)$/", $headers);
        $to=join($to, "\n" );
        $this->to = trim(decode_mime_string(str_replace ("\"", "'", (str_replace ("To:", "", $to)))));

        if($this->to == ""){
                $to=preg_grep ("/^to:(.*)$/", $headers);
                $to=join($to, "\n" );
                $this->to = trim(decode_mime_string(str_replace ("\"", "'", (str_replace ("to:", "", $to)))));
        }
        if($this->to == ""){
                $to=preg_grep ("/^TO:(.*)$/", $headers);
                $to=join($to, "\n" );
                $this->to = trim(decode_mime_string(str_replace ("\"", "'", (str_replace ("TO:", "", $to)))));
        }


        $cc=preg_grep ("/^Cc:(.*)$/", $headers);
        $cc=join($cc, "\n" );
        $this->cc = trim(decode_mime_string(str_replace ("\"", "'", (str_replace ("Cc: ", "", $cc)))));

        $xirm=preg_grep ("/^X-From-IRM:(.*)$/", $headers);
        $xirm=join($xirm, "\n" );
        $this->xirm = trim(decode_mime_string(str_replace ("\"", "'", (str_replace ("X-From-IRM: ", "", $xirm)))));


        $from=preg_grep ("/^From:(.*)$/", $headers);
        $from=join($from, "\n" );
        $from_org = str_replace ("From:", "", $from);
        $this->from = $from_org;

        if($this->from == ""){
                $from=preg_grep ("/^from:(.*)$/", $headers);
                $from=join($from, "\n" );
                $from_org = str_replace ("from:", "", $from);
                $this->from = $from_org;
        }
        if($this->from == ""){
                $from=preg_grep ("/^FROM:(.*)$/", $headers);
                $from=join($from, "\n" );
                $from_org = str_replace ("FROM:", "", $from);
                $this->from = $from_org;
        }


        $this->from = str_replace ("\"", "", $this->from);

        $from23 = str_replace ("<", "", $this->from);
        $from23 = str_replace (">", "", $from23);

        $from2 = preg_split ("/[\s,]+/", $from23);
        for($z=0;$z<=count($from2);$z++){
                if(preg_match("/(\S*)@(\S*)/", $from2[$z])){
                        $this->from_addr = trim(decode_mime_string($from2[$z]));
                } else {
                        $this->from_name .= trim(decode_mime_string($from2[$z])). " ";
                }

        }
        $this->from_name=trim($this->from_name);
        $this->from = trim(decode_mime_string($from_org));


        $date=preg_grep("/^Date:(.*)$/", $headers);
        $date=join($date, "\n" );
        $this->date = trim(decode_mime_string(str_replace ("Date:", "", $date)));
        if($this->date == ""){
                $date=preg_grep("/^date:(.*)$/", $headers);
                $date=join($date, "\n" );
                $this->date = trim(decode_mime_string(str_replace ("date:", "", $date)));
        }
        if($this->date == ""){
                $date=preg_grep("/^DATE:(.*)$/", $headers);
                $date=join($date, "\n" );
                $this->date = trim(decode_mime_string(str_replace ("DATE:", "", $date)));
        }


        $headers=join($headers, "\n" );
        if (eregi("Content-Type: multipart", $headers)) {
                $this->anh=1;
        } else { $this->anh=0; }

        }
}

/*
  function to decode a MIME-encoded string, returns the original string, if not MIME
  Thanks to horde!
  function taken from: http://ftp.horde.org/pub/imp/tarballs/old/imp-2.0.0.tar.gz
*/
function decode_mime_string ($string) {
   if (eregi("=?([A-Z,0-9,-]+)?([A-Z,0-9,-]+)?([A-Z,0-9,-,=,_]+)?=", $string)) {
      if (ereg("^=?", $string)) $string = ' ' . $string;
      $coded_strings = explode(' =?', $string);
      $counter = 1;
      $string = $coded_strings[0]; /* add non encoded text that is before the encoding */
      while ($counter < sizeof($coded_strings)) {
         $elements = explode('?', $coded_strings[$counter]); /* part 0 = charset */

         /* part 1 == encoding */
         /* part 2 == encoded part */
         /* part 3 == unencoded part beginning with a = */
         /* How can we use the charset information? */

         if (eregi("Q", $elements[1])) {
            $elements[2] = str_replace('_', ' ', $elements[2]);
            $elements[2] = eregi_replace("=([A-F,0-9]{2})", "%\\1", $elements[2]);
            $string .= urldecode($elements[2]);
         } else { /* we should check for B the only valid encoding other then Q */
            $elements[2] = str_replace('=', '', $elements[2]);
            if ($elements[2]) { $string .= base64_decode($elements[2]); }
         }

         if (isset($elements[3]) && $elements[3] != '') {
            $elements[3] = ereg_replace("^=", '', $elements[3]);
            $string .= $elements[3];
         }
         $counter++;
      }
   }
   return $string;
}


?>
