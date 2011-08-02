<?PHP
/**
 * OpenSocial gadget block for Moodle
 * By Evgeny Bogdanov
 * 
 * This block allows Moodle admins to add OpenSocial gadgets to blocks from an Apache Shindig server
 *
 * Note that you need to set your Shindig server URL in Site Administration
 * using the Settings->Site administration->Plugins->Blocks->OpenSocial gadget settings page
 *
 * @copyright 2011 Evgeny Bogdanov
 */

class Gadget {
    
    function build($gadget_url,$gadget_id) {
      $this->url = $gadget_url;
      $this->id = $gadget_id;
      
      echo $this->get_content();
    }
        
    // executes php statements and
    // returns file as a string instead of including it
    function get_include_contents($file) {
      if (!is_file($file) || !file_exists($file) || !is_readable($file)) return false;
      ob_start();
      include($file);
      $contents = ob_get_contents();
      ob_end_clean();
      return $contents;
    }
    
    function get_content() {
      // http://graaasp.epfl.ch/gadget/demo/pad3d_ext/gadget.xml
      // http://iamac71.epfl.ch/viewer.xml
      
      global $CFG;
               
      // get shindig script to build gadgets
      $output = '<script src="' . $this->get_shindig_url() . '/gadgets/js/shindig-container:rpc.js?c=1&debug=1&nocache=1" type="text/javascript" charset="utf-8"></script>';
    
      // load html file for gadget
      // javascript builder for gadget
      $output.= $this->get_include_contents($CFG->dirroot . "/mod/widgetspace/lib/gadget.html");
              echo 'bug';
      return $output;
    }        
    
    function get_gadget_token() {
      // TODO: token should be encoded properly for security reasons
      
      // var token = ""+gadget.owner_id+":"+gadget.viewer_id+":"+gadget.gadget_id+
      // ":default:"+escape("http://"+gadget_url)+":"+gadget.gadget_id+":1";
        global $USER, $COURSE, $CFG;
        
        $token = "";
        $token.= $USER->id.":"; // owner_id
        $token.= $USER->id.":"; // viewer_id
        $token.= $this->id.":"; // module_id
        $token.= "default:";
        $token.= urlencode($this->url) . ":"; // escape("http://"+gadget_url)
        $token.= $this->id.":"; // module_id
        $token.= "1"; 
                
        return $token;
    }
    
    function get_shindig_url() {
      global $CFG;
      
      // return $CFG->block_shindig_url;
      return "http://localhost:8080";
    }
    
    function get_gadget_height() {
      if ($this->config->gadgetheight) {
        return $this->config->gadgetheight;
      } else {
        return 200;
      }
    }
    
}

?>