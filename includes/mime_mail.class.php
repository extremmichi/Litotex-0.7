<?php

class MIME_Mail{
	
	var $version = "1.0rc4";
	
	var $bound1;
	var $bound2;

	var $header;
	var $mimeheader;
	var $prio_header;
	var $msprio_header;

	var $plainPart;
	var $htmlPart;

	var $to;
	var $from;
	var $cc;
	var $bcc;
	var $replyto;
	var $subject;
	var $envelop;
	var $from_address;
	
	var $internal_from;
	var $internal_envelop;
	var $internal_replyto;
	var $internal_receiverList;
	
	var $attachments;
	
	var $init = false;
	
	var $error = 0;
	
	var $checkSenderAddress = false;
	var $checkReceiverAddress = false;
	var $checkWWWAddress = true;
	var $shortLines = false;
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Initializes all variables
	/////////////////////////////////////////////////////////////////////////////////////////	
	function init(){
		// init boundaries:
		srand( (double) microtime() * 1000000 );
		$this->bound1 = "----=_NextPart_001_";
		for( $i=0; $i<30; $i++){
			$zahl = rand( 0, 9 );
			$this->bound1 .= "$zahl";
		}
		$this->bound2 = "----=_NextPart_002_";
		for( $i=0; $i<30; $i++){
			$zahl = rand( 0, 9 );
			$this->bound2 .= "$zahl";
		}
		
		// init variables:
		$this->error = 0;
		$this->prio_header = "";
		$this->msprio_header = "";
		$this->plainPart = "";
		$this->htmlPart = "";
		$this->to = "";
		$this->from = "";
		$this->cc = "";
		$this->bcc = "";
		$this->replyto = "";
		$this->subject = "";
		$this->envelop = "";
		$this->from_address = "";
		$this->internal_from = "";
		$this->internal_envelop = "";
		$this->internal_replyto = "";
		$this->internal_receiverList = array();
		$this->attachments = array();

			
		$this->mimeheader = "MIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"".$this->bound1."\"\n";
		$this->header="User-Agent: MIME_MAIL_CLASS ver. ".$this->version." (via PHP)\n";
		$this->init = true;
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Turn checks for shortlines on/off
	/////////////////////////////////////////////////////////////////////////////////////////
	function setShortlinesOn( $on=true ){
		if(! $this->init ){ $this->init(); }
		if( $on ){
			$this->shortLines = true;
		} else {
			$this->shortLines = false;
		}
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Turn checks for sender on/off
	/////////////////////////////////////////////////////////////////////////////////////////
	function setSenderCheckOn( $on=true ){
		if(! $this->init ){ $this->init(); }
		if( $on ){
			$this->checkSenderAddress = true;
		} else {
			$this->checkSenderAddress = false;
		}
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Turn checks for receivers on/off
	/////////////////////////////////////////////////////////////////////////////////////////
	function setReceiverCheckOn( $on=true ){
		if(! $this->init ){ $this->init(); }
		if( $on ){
			$this->checkReceiverAddress = true;
		} else {
			$this->checkReceiverAddress = false;
		}
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Turn checks for WWW-addresses on/off
	/////////////////////////////////////////////////////////////////////////////////////////
	function setWWWCheckOn( $on=true ){
		if(! $this->init ){ $this->init(); }
		if( $on ){
			$this->checkWWWAddress = true;
		} else {
			$this->checkWWWAddress = false;
		}
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Add an additional Header
	/////////////////////////////////////////////////////////////////////////////////////////
	function addHeader( $head ){
		if(! $this->init ){ $this->init(); }
		$this->xheader .= "$head\n";
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Add an additional Header
	/////////////////////////////////////////////////////////////////////////////////////////
	function addXHeader( $key, $val ){
		if(! $this->init ){ $this->init(); }
		$this->xheader .= "X-$key: $val\n";
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Add an Priority-Header
	/////////////////////////////////////////////////////////////////////////////////////////
	function setPriority( $prio ){
		if(! $this->init ){ $this->init(); }
		
		switch( $prio ){
			case 1:{
				$prio_head = "X-Priority: 1 (Highest)";
				break;
			}
			case 2:{
				$prio_head = "X-Priority: 2 (Medium)"; // ???    hmmm, I'm not sure about that...
				break;
			}
			case 3:{
				$prio_head = "X-Priority: 3 (Normal)"; // ???    hmmm, I'm not sure about that...
				break;
			}
			case 4:{
				$prio_head = "X-Priority: 4 (Low)";    // ???    hmmm, I'm not sure about that...
				break;
			}
		}
		
		$this->prio_header = "$prio_head\n";
	}



	/////////////////////////////////////////////////////////////////////////////////////////
	//  Add an MS-Priority-Header
	/////////////////////////////////////////////////////////////////////////////////////////
	function setMSPriority( $prio ){
		if(! $this->init ){ $this->init(); }
		
		switch( $prio ){
			case 1:{
				$prio_head = "X-MSMail-priority: High";
				break;
			}
			case 2:{
				$prio_head = "X-MSMail-priority: Medium"; // ???    hmmm, I'm not sure about that...
				break;
			}
			case 3:{
				$prio_head = "X-MSMail-priority: Normal"; // ???    hmmm, I'm not sure about that...
				break;
			}
			case 4:{
				$prio_head = "X-MSMail-priority: Low";    // ???    hmmm, I'm not sure about that...
				break;
			}
		}
		
		$this->msprio_header = "$prio_head\n";
	}

	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Set the plaintext (alternative) part
	/////////////////////////////////////////////////////////////////////////////////////////
	function setPlainPart( $plain ){
		if(! $this->init ){ $this->init(); }
		$this->plainPart = $this->quotedPrintable( $plain );
		//$this->plainPart = $plain ;
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Set the html (alternative) part
	/////////////////////////////////////////////////////////////////////////////////////////
	function setHTMLPart( $html ){
		if(! $this->init ){ $this->init(); }
		$this->htmlPart = $this->quotedPrintable( $html );
		//$this->htmlPart = $html ;
	}
	
		
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Add a file as an attachment (and encode as b64)
	/////////////////////////////////////////////////////////////////////////////////////////
	function addFile( $filename, $filetype = "", $cid = "", $disposition = "" ){
		if(! $this->init ){ $this->init(); }
		
		$added = false;
		
		if (file_exists( $filename ) ){
			$fd = fopen( $filename, "r" );
			$content = fread( $fd, filesize( $filename ) );
			fclose( $fd );
			
			$content_b64 = base64_encode( $content );
			
			if( $this->shortLines ){
				$c_b64 = "";
				$max=76;
				$len = strlen( $content_b64 );
				for( $i=0; $i<$len; $i++ ){
					$c_b64 .= $content_b64[$i];
					$bst = $i+1;
					if( $bst%$max == 0 ){
						// TODO: CRLF oder nur LF...???
						$c_b64 .= "\n";
					}
				}
				$content_b64 = $c_b64;
			}
			
			$file = basename( $filename );
			$content_id = "";
			
			if( empty($filetype) ){
				$filetype = $this->getFiletype( $file );
			}
			if( empty($filetype) ){
				$filetype = "application/octet-stream";
			}
			$dispo = empty($disposition) ? "attachment" : $disposition;
			
			if(!empty($cid)){
				$content_id = "Content-ID: <$cid>\n";
				$dispo = empty($disposition) ? "inline" : $disposition;
			}
			
			$att = $content_id."Content-Type: $filetype; name=\"$file\"\nContent-Transfer-Encoding: base64\nContent-disposition: $dispo; filename=\"$file\"\n\n$content_b64\n";
			
			$atts = sizeof( $this->attachments );
			$atts = (int) $atts;
			
			$this->attachments[ $atts ] = $att;
			$atts = sizeof( $this->attachments );
			
			$added = true;
			
		} else {
			$this->error |= 32;
			$added = false;
		}
		
		return $added;
		
	}
	
		
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Add a string as an attachment
	/////////////////////////////////////////////////////////////////////////////////////////
	function addAttachment( $at_content, $at_name, $at_type, $at_encoding, $cid = "", $disposition = "" ){
		if(! $this->init ){ $this->init(); }
		$content_id = "";
		
		$dispo = empty($disposition) ? "attachment" : $disposition;
		
		if(!empty($cid)){
			$content_id = "Content-ID: <$cid>\n";
			$dispo = empty($disposition) ? "inline" : $disposition;
		}
		
		$at_type = empty( $at_type) ? $this->getFiletype( $at_name ) : $at_type;
		$at_type = empty( $at_type) ? "text/plain" : $at_type;
		$at_encoding = empty( $at_encoding) ? "us-ascii" : $at_encoding;


//		$longLines = true;



		// if we have an enc-type of auto, we do a quoted-printable encoding:
		if( $at_encoding == "auto" ){
			$at_encoding = "quoted-printable";
			$at_content_qp = $this->quotedPrintable( $at_content );
			$at_content = $at_content_qp;
			$longLines = false;    // quoted-printable has already short lines...
		} else {

		  //		if( $this->shortLines && $longLines ){
		  //			$at_short = "";
		  //			$max=74;
		  //			$len = strlen( $at_content );
		  //			for( $i=0; $i<$len; $i++ ){
		  //				$at_short .= $at_content[$i];
		  //				$bst = $i+1;
		  //				if( $bst%$max == 0 ){
		  //					// TODO: CRLF oder nur LF...???
		  //					$at_short .= "\n";
		  //				}
		  //			}
		  //			$at_content = $at_short;
		  //		}
		}

		
		$att = $content_id."Content-Type: $at_type; name=\"$at_name\"\nContent-Transfer-Encoding: $at_encoding\nContent-disposition: $dispo; filename=\"$at_name\"\n\n$at_content\n";		
		
		$atts = sizeof( $this->attachments );
		$atts = (int) $atts;
		
		$this->attachments[ $atts ] = $att;
		$atts = sizeof( $this->attachments );
		
		return true;
		
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Sets the sender-email
	/////////////////////////////////////////////////////////////////////////////////////////
	function setFrom( $from_email, $from_name="" ){
		if(! $this->init ){ $this->init(); }
		if( empty( $from_name ) ){
			$this->from = $from_email;
		} else {
			$from_name = ereg_replace( "\"", "'", $from_name );
			$this->from = "\"$from_name\" <$from_email>";
		}
		$this->from_address = $from_email;
		
		$this->internal_from = $from_email;
		
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Sets the Reply-to
	/////////////////////////////////////////////////////////////////////////////////////////
	function setReplyto( $rpl_email, $rpl_name="" ){
		if(! $this->init ){ $this->init(); }
		if( empty( $rpl_name ) ){
			$this->replyto = $rpl_email;
		} else {
			$rpl_name = ereg_replace( "\"", "'", $rpl_name );
			$this->replyto = "\"$rpl_name\" <$rpl_email>";
		}

		$this->internal_replyto = $rpl_email;
				
	}
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Sets the subject
	/////////////////////////////////////////////////////////////////////////////////////////
	function setSubject( $subj ){
		if(! $this->init ){ $this->init(); }
		$this->subject = $subj;
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Fifth-argument of the mail-command
	/////////////////////////////////////////////////////////////////////////////////////////
	function setEnvelop( $env ){
		if(! $this->init ){ $this->init(); }
		$this->envelop = $env;

		$this->internal_envelop = $env;
		
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Adds an addressee to the TO
	/////////////////////////////////////////////////////////////////////////////////////////
	function addTo( $to_email, $to_name="" ){
		if(! $this->init ){ $this->init(); }
		if( empty( $to_name ) ){
			$addto = $to_email;
		} else {
			$to_name = ereg_replace( "\"", "'", $to_name );
			$addto = "\"$to_name\" <$to_email>";
		}
		
		if( empty($this->to) ){
			$this->to = $addto;
		} else {
			$this->to .= ", $addto";
		}
		
		$r_num = sizeof( $this->internal_receiverList );
		$this->internal_receiverList[ $r_num ] = $to_email;
		
		
	}
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Adds an addressee to the CC
	/////////////////////////////////////////////////////////////////////////////////////////
	function addCc( $cc_email, $cc_name="" ){
		if(! $this->init ){ $this->init(); }
		if( empty( $cc_name ) ){
			$addcc = $cc_email;
		} else {
			$cc_name = ereg_replace( "\"", "'", $cc_name );
			$addcc = "\"$cc_name\" <$cc_email>";
		}
		
		if( empty($this->cc) ){
			$this->cc = $addcc;
		} else {
			$this->cc .= ", $addcc";
		}
		
		$r_num = sizeof( $this->internal_receiverList );
		$this->internal_receiverList[ $r_num ] = $cc_email;
		
		
	}
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Adds an addressee to the BCC
	/////////////////////////////////////////////////////////////////////////////////////////
	function addBcc( $bcc_email, $bcc_name="" ){
		if(! $this->init ){ $this->init(); }
		if( empty( $bcc_name ) ){
			$addbcc = $bcc_email;
		} else {
			$bcc_name = ereg_replace( "\"", "'", $bcc_name );
			$addbcc = "\"$bcc_name\" <$bcc_email>";
		}
		
		if( empty($this->bcc) ){
			$this->bcc = $addbcc;
		} else {
			$this->bcc .= ", $addbcc";
		}
		
		$r_num = sizeof( $this->internal_receiverList );
		$this->internal_receiverList[ $r_num ] = $bcc_email;
		
		
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Gets the error-code
	/////////////////////////////////////////////////////////////////////////////////////////
	function getErrorCode(){
		return $this->error;
	}	
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Resets the error-code
	/////////////////////////////////////////////////////////////////////////////////////////
	function resetErrorCode(){
		$this->error = 0;
	}	
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Gets the error-message
	/////////////////////////////////////////////////////////////////////////////////////////
	function getErrorMsg(){
		$msg = "";
		
		$msg .= ( (($this->error) & 1)  == 1 ) ? "No sender-address specified.\n" : "";
		$msg .= ( (($this->error) & 2)  == 2 ) ? "No recipient-address specified.\n" : "";
		$msg .= ( (($this->error) & 4)  == 4 ) ? "No message-body specified.\n" : "";
		$msg .= ( (($this->error) & 8)  == 8 ) ? "One or more receiver-addresses failed checks (MX, syntax or WWW).\n" : "";
		$msg .= ( (($this->error) & 16) == 16) ? "One or more sender-addresses failed checks (MX, syntax or WWW).\n" : "";
		$msg .= ( (($this->error) & 32) == 32) ? "Attachment-Error: File does not exist.\n" : "";
		$msg .= ( (($this->error) & 256) == 256) ? "Mail-Transport-Error.\n" : "";
		
		return $msg;
	}	
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Send the mail
	/////////////////////////////////////////////////////////////////////////////////////////
	function sendMail(){
		
		if(! $this->init ){ return false; }
		
		// we need at least an sender-email, a receiver-email, and a body...
		$err = 0;
		if( empty( $this->from ) ){ $err |= 1; }
		if( empty( $this->to ) ){ $err |= 2; }
		if( empty( $this->plainPart ) && empty( $this->htmlPart ) ){ $err |= 4; }
		
		if( $this->checkReceiverAddress ){
			
			$wrong_addresses = 0;
			
			for( $i=0; $i< sizeof( $this->receiverList ); $i++ ){
				if( $this->checkWWWAddress ){
					$okay = $this->isDauEmailOkay( $this->internal_receiverList[ $i ] ); 
				} else {
					$okay = $this->isEmailOkay( $this->internal_receiverList[ $i ] );
				}
				if( ! $okay ){
					$wrong_addresses++;
				}
			}
			
			if( $wrong_addresses > 0 ){
				$err |= 8;
			}
		}
		
		if( $this->checkSenderAddress ){
			
			$wrong_addresses = 0;
			if( $this->checkWWWAddress ){
				$okay = $this->isDauEmailOkay( $this->internal_from ) && $this->isDauEmailOkay( $this->internal_replyto ) && $this->isDauEmailOkay( $this->internal_envelop ) ; 
			} else {
				$okay = $this->isEmailOkay( $this->internal_from ) && $this->isEmailOkay( $this->internal_replyto ) && $this->isEmailOkay( $this->internal_envelop ) ;
			}

			if( ! $okay ){
				$err |= 16;
			}
			
		}
		
		$this->error = $err;
		
		if($err > 0){
			return false;
		}
		
				
		$success = false;
		
		$body = "";
		$preheader = "";
		
		$preheader .= "From: ".$this->from."\n";
		$preheader .= "Reply-To: ".$this->replyto."\n";
		//$preheader .= "To: ".$this->to."\n";
		$preheader .= "Cc: ".$this->cc."\n";
		$preheader .= "Bcc: ".$this->bcc."\n";
		
		$alternative = false;

		if( !empty( $this->htmlPart) ){
			$alternative = true;
		}

		$atts = sizeof( $this->attachments );
		
		if( $alternative ){
			
			$body = "This is a multipart-message in MIME-format

--".$this->bound1."
Content-Type: multipart/alternative; boundary=\"".$this->bound2."\"

--".$this->bound2."
Content-Type: text/plain; charset=\"iso-8859-1\"
Content-Transfer-Encoding: quoted-printable

".$this->plainPart."

--".$this->bound2."
Content-Type: text/html; charset=\"iso-8859-1\"
Content-Transfer-Encoding: quoted-printable

".$this->htmlPart."

--".$this->bound2."--

";

		} else {
		
			$body = "--".$this->bound1."
Content-Type: text/plain; charset=\"iso-8859-1\"
Content-Transfer-Encoding: quoted-printable

".$this->plainPart."\n";
		}
		
		for( $i=0; $i<$atts; $i++ ){
			$body .= "--".$this->bound1."\n";
			$body .= $this->attachments[$i];
			$body .= "\n";
			
			
		}
		$body .= "--".$this->bound1."--";
		
		
		$f = empty( $this->envelop ) ? $this->from_address : $this->envelop;
		$s = empty( $this->subject ) ? "(No subject)" : $this->subject ;
		
		$success = mail( $this->to, $s, $body, $preheader.$this->mimeheader.$this->msprio_header.$this->prio_header.$this->xheader , "-f ".$f);
		
		if( ! $success ){
			$this->error |= 256;
		}
		
		return $success;
	}
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Mail-Check-Routines:
	/////////////////////////////////////////////////////////////////////////////////////////
	
	function isMxOkay($email){
		$domain = eregi_replace("^.+@","",$email);
		$mx_exist = getmxrr( $domain, $mxhosts );
		$ret = ($mx_exist) ? true : false ;
		
		return $ret;
	}

	function isSyntaxOkay( $email ){
		$reg_exp_mail = "^[a-zA-Z0-9][_a-zA-Z0-9-]*[\.[_a-zA-Z0-9-]+]*@[\._a-zA-Z0-9-]+\.([a-zA-Z]{2,4})$";
		if( ! eregi( $reg_exp_mail, $email) ){
			$stat = false; // Syntactically wrong
		} else {
			$stat = true;  // Syntactically okay
		}
		return $stat;
	}
	
	function isWWWEmail( $email ){
		$reg_exp_mail = "^www[\.\-].+$";
		
		if( eregi( $reg_exp_mail, $email) ){
			$stat = true; // Email starts with www.
		} else {
			$stat = false; // Email does not start with www.
		}
		return $stat;
	}
	
	function isEmailOkay($email){
		$stat = false;
		if ( $this->isSyntaxOkay($email) ){
			$stat = $this->isMxOkay( $email );
		}
		return $stat;
	}
	
	function isDauEmailOkay($email){
		$stat = false;
		if( $this->isWWWEmail( $email ) ){
			$stat = false;
		} else{
			$stat = $this->isEmailOkay( $email );
		}
		return $stat;
	}
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Encoding:
	/////////////////////////////////////////////////////////////////////////////////////////
	
	function quotedPrintable( $string ){
		
		$qp_string = "";
		
		$max = 73;
		$linepos = 0;
		
		for( $i=0; $i<strlen( $string ); $i++){
			
			
			
			$char = substr( $string, $i, 1 );
			$num = ord( $char );
			
			// we even encode characters 33, 34, 35, 36, 64, 91, 92, 93, 94, 96, 123, 124, 125, 127 as the RFC recommends...
			
			if( 	   ( $num > 126 ) 
				|| ( $num == 61 ) 
				|| ( ($num < 33) && ($num != 13) && ($num != 10) ) 
				|| ( $num == 33)
				|| ( $num == 34)
				|| ( $num == 35)
				|| ( $num == 36)
				|| ( $num == 64)
				|| ( $num == 91)
				|| ( $num == 92)
				|| ( $num == 93)
				|| ( $num == 94)
				|| ( $num == 123)
				|| ( $num == 124)
				|| ( $num == 125)
				|| ( $num == 126) ) {
				$hex = sprintf( "%X", $num );
				if( $num < 17 ){
					$hex = "0"."$hex";
				}
				$qp_char = "=".$hex;
				$linepos = $linepos + 3;   // quoted-printable-character has three letters...
			} else {
				$qp_char = $char;
				$linepos = $linepos + 1;   // non-quoted-character has one letters...
			}
			
			if( $char == "\n" || $char == "\r" ){
				$linepos = 0;
			}
			
			$qp_string .= $qp_char;
			
			if( $linepos >= $max ){
				$linepos = 0;
				$qp_string .= "=\n";
			}
			
		}
		
		return $qp_string;
	}
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//  Filetype:
	/////////////////////////////////////////////////////////////////////////////////////////
	function getFiletype( $fname ){
		// only by suffix, and only a few important:
		$ftypes = array(
			"gif"  => "image/gif",
			"jpg"  => "image/jpeg",
			"png"  => "image/png",
			"pdf"  => "application/pdf",
			"ps"   => "application/postscript",
			"exe"  => "application/octet-stream",
			"txt"  => "text/plain",
			"html" => "text/html",
			"htm"  => "text/html",
			"mp3"  => "audio/mpeg",
			"ra"   => "audio/x-pn-realaudio"
		);
		
		$parts = split( "\.", $fname );
		$num = sizeof( $parts );
		
		if( $num ){
			$suffix = $parts[ $num - 1 ];
		}
		
		
		if( $suffix ){
			$file_type = $ftypes[ $suffix ];
		}
		
		//if( empty($file_type) ){
		//	$file_type = "application/octet-stream";
		//}
		
		return $file_type;
		
	}
	
}





?>
