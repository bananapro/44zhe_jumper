<?php

/**
 * Locale Component.
 *
 * This component permits the possibility to have multi lingual pages using one set
 * of templates.
 * It is possible to use this class to return a message in a requested language, or
 * where required to render a view file written for one specific language. The latter is
 * useful where the page formatting is language specific, or the content is static.
 *
 */
class LocaleComponent extends Object {
    var $controller = true;
    var $components = Array ('Session');

/**
 * Languages to be processed. First item is the default language.
 *
 * @var array
 */
    var $acceptedLanguages = array (
                                    "zh_cn",
                                    "zh_hk",
                                    "ja_jp",
                                    "en_us"
                                    );

/**
 * variable to hold the array of messages. The format for this array is
 * $_messages[languagecode][message id]
 *
 * @var array
 */
	var $_messages = Array();

/**
 * folder holding php files with the messages defined for each language.
 * relative to component folder
 *
 * @var array
 */
	var $_messageFolder = "messages";

/**
 * Initialize method, called from the controller to set up the locale info.
 * If a none-default language is requested and the controller has the field multiLingualFiles
 * set to true, the view filename will be changed if a language specific file is available.
 * E.g. if the session language is de_de, for the function index, IF the file index_de_de.thtml
 * exists, it will be used instead of the normal view file. If it doesn't exist, the default
 * index file will be used
 *
 */
			function startup(){//set language by domain
			  $lang = '';
				$lang_arr = explode('.',env('HTTP_HOST'));
		
				switch ($lang_arr[0]){
					case 'www'  : $lang='zh_cn';break;
					case 'akari': $lang='zh_cn';break;
					case 'en'   : $lang='en_us';break;
					case 'hk'   : $lang='zh_hk';break;
					case 'ja'   : $lang='ja_jp';break;
					default: $lang='zh_cn';
				}
				$this->changeCode($lang);
			}
			
//    function startup(&$controller)
//    {
//        $this->controller = $controller;
//        $Code = $this->_getCode();
//        $this->_setCode($Code);
//
//        $this->_populateMessages ($Code);
//        $this->Session->write("Lang.Messages", $this->_messages[$Code]);
//
//		$name = $this->controller->name;
//		if ($name=="pages")
//		{
//			$viewFile = $this->controller->passed_args[0];
//		}
//		else
//		{
//			$viewFile = $this->controller->action;			
//		}
//        if (
//            ($Code<>$this->acceptedLanguages[0])
//            )
//        {
//            if (is_file(VIEWS.$name.DS.$viewFile."_".$Code.".thtml"))
//            {
//                $this->controller->render($viewFile."_".$Code);
//                die;
//            }
//            else
//            {
//                /* Optional, display flash message */
//                /*
//                $msg = __("Language specific version not available for this page", Array("$Code"));
//                $this->Session->setFlash($msg); */
//            }
//        }
//    }
/**
 * Returns or creates an instance of the LocaleComponent.
 *
 * @return LocaleComponent instance
 */
/**
 * Return a singleton instance of the Locale Component.
 *
 * @return Locale instance
 * @access public
 */
   function &getInstance()
   {
      static $instance= array();
      if (!$instance)
      {
         $instance[0] =& new LocaleComponent();
      }
      return $instance[0];
   }

/**
 * Change the language code for all messages. Only permits changes to values which
 * are in the acceptedLanguages parameter. This message is not intended to receive
 * user defined input.
 *
 * @param string $langcode 5 character language code
 * @return true if code changed, false if not.
 */
    function changeCode ($langcode=NULL) {
        if (!in_array($langcode,$this->acceptedLanguages))
        {
            $msg = __("Invalid language code",Array($langcode));
            $this->Session->setFlash($msg);
            return false;
        }
        else
        {
            $this->Session->del("Lang.Menu");
            $this->Session->del("Lang.Messages");
            $this->Session->write("Lang.Code", $langcode);
            return true;
        }
     }

/**
 * returns a translated string. This method will return the requested message in the
 * language requested if it exists; if not the message in the default language if it exists
 * and finally the message $id if no message is defined.
 *
 * @param string $id the message or message id
 * @param array $MessageArgs variables to be substitued into the message defenition
 * @param int $capitalize how should the message be capitalized
 *      0 = no change
 *      1 = first letter of first word
 *      2 = fist character of all words
 *      Assumed that the message is stored lowercase except when upper case is required
 *      Such as with objects e.g. in German "der Mann".
 * @param int $punctuate how should the message be punctuated
 *      0 = .
 *      1 = !
 *      2 = ?
 * @param string $Code override for the language to be used.
 * @return string output message.
 */
	function getString( $id, $MessageArgs=NULL, $capitalize=1, $punctuate=0 ,$Code=NULL)
    {
        $Code = (!$Code)?$this->_getCode():$Code;
        $this->_populateMessages ($Code);
	    $this->_populateMessages ($this->acceptedLanguages[0]);

        if( isset($id, $this->_messages[$Code][$id]))
         {
              if ($MessageArgs)
              {
                  $string = vsprintf ($this->_messages[$Code][ $id ], $MessageArgs);
              }
              else
              {
		          $string = $this->_messages[$Code][ $id ];
              }
	    }
        elseif ( isset($id, $this->_messages[$this->acceptedLanguages[0]][$id]))
        {
              if ($MessageArgs)
              {
                  $string = vsprintf ($this->_messages[$this->acceptedLanguages[0]][ $id ], $MessageArgs);
              }
              else
              {
		          $string = $this->_messages[$this->acceptedLanguages[0]][ $id ];
              }
	    }
        else
        {
	    	$string = $id;
	    }

	    switch ($capitalize)
        {
          case 0:
            break;
          case 1:
            $string = ucfirst($string);
            break;
          case 2:
            $string = ucwords($string);
            break;
        }

	    switch ($punctuate)
        {
          case 0:
            break;
          case 1:
            if ($Code=='es_es')
            {
                $string = "".$string.".";
            } else
            {
                $string = $string.".";
            }
            break;
          case 2:
            if ($Code=='es_es')
            {
                $string = "¡".$string."!";
            } else
            {
                $string = $string."!";
            }
            break;
          case 3:
            if ($Code=='es_es')
            {
                $string = "¿".$string."?";
            } else
            {
                $string = $string."?";
            }
            break;
        }
 		return $string;
	}

/**
 * returns the language code for the session. If a language code has not been defined
 * the code determined from the ethod  _checkBrowserLanguage is returned
 * @return string 5 character language code
 */
    function _getCode () {
      if (isset($this->Session))
      {
       return $this->Session->read("Lang.Code")?$this->Session->read("Lang.Code"): $this->_checkBrowserLanguage();
      }
      else
      {
        return isset($_SESSION['Lang']['Code'])?$_SESSION['Lang']['Code']:$this->_checkBrowserLanguage();
      }
    }

/**
 * stores the passed code in the session.
 */
    function _setCode ($langcode=NULL)
    {
        $this->Session->write("Lang.Code", $langcode);
    }

/**
 * This method will return the most appropriate language defined in the acceptedLanguages list
 * based upon the browser language settings. If no language matches the acceptedLanguages
 * list, the first item of the acceptedLanguages list (the default language)is returned.
 * This method isn't optimised and needs improving.
 *
 * @return string 5 character language code
 */
    function _checkBrowserLanguage () {
      	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
      	{	    
	        $Languages = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	        $SLanguages = array();
	        foreach ($Languages as $Key=> $Language) {
	            $Language = ereg_replace("-","_",$Language);
	            $Language = explode(";", $Language);
	            if (isset($Language[1])) {
	                $Priority = explode("q=", $Language[1]);
	                $Priority=$Priority[1];
	            }
	            else
	            {
	                $Priority ="1.0";
	            }
	            $SLanguages[] = array('priority' => $Priority, 'language' => $Language[0]);
	        }
	        // Obtain a list of columns
	        foreach ($SLanguages as $key => $row) {
	            $priority[$key]  = $row['priority'];
	            $language[$key] = $row['language'];
	        }
	        // Sort the Slanguges with priority descending, languagecode ascending
	        // Add $Languages as the last parameter, to sort by the common key
	        array_multisort($priority, SORT_DESC, $language, SORT_ASC, $SLanguages);
	
	        $ALangString = implode(";",$this->acceptedLanguages);
	        foreach ($SLanguages as $A) {
	            $key = array_search($A['language'], $this->acceptedLanguages);
	            if ($key===FALSE) {
	                $GenericLanguage = explode("_", $A['language']);
	                $pos1 = strpos($ALangString, $GenericLanguage[0]);
	                if (is_numeric($pos1)) {
	                    $key = $pos1/6;
	                }
	            }
	            if (is_numeric($key)&&(!isset($Code))) {
	                $Code = $this->acceptedLanguages[$key];
	            }
	        }
	        if (!isset($Code)) 
			{
	            $Code = $this->acceptedLanguages[0];
	        }
        }
        else
        {
		  $Code = $this->acceptedLanguages[0];
		}
        return $Code;
    }

/**
 * set the _message variable for the specified language code
 */
	function _populateMessages($langcode)
    {
        if (isset($this->Session))
        {
            if ($this->Session->check('Lang.Messages'))
            {
                $sessionMessages = $this->Session->read("Lang.Messages");
            }
            else
            {
                $sessionMessages = NULL;
            }
        }
        else
        {
            $sessionMessages = isset($_SESSION['Lang']["Messages"])?$_SESSION['Lang']["Messages"]:NULL;
        }

        if (isset($sessionMessages[$langcode]))
        {
            $this->_messages[$langcode] = $sessionMessages[$langcode];
        }
        else
        {
			$this->_loadLocaleFile($langcode);
			if ($langcode<>$this->acceptedLanguages[0])
			{
                $this->_loadLocaleFile($this->acceptedLanguages[0]);
            }
        }
   }

/**
 * Load the language file for the specified language code
 *
 * @return true if file loaded, false if not.
 */
	function _loadLocaleFile($langcode) {
		$messages = array();
		$fileName = dirname(__FILE__).DS.$this->_messageFolder.DS.$langcode . ".php";
        if (file_exists($fileName))
        {
            include( $fileName );
    		$this->_messages[$langcode] = $messages;
            return true;
        }
        else
        {
    		$this->_messages[$langcode] = NULL;
            return false;
            //$msg = __("not found", Array("$fileName"));
            //$this->log($msg);
        }
	}
}
?>