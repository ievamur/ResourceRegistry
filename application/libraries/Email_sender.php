<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * ResourceRegistry3
 * 
 * @package     RR3
 * @author      Middleware Team HEAnet 
 * @copyright   Copyright (c) 2012, HEAnet Limited (http://www.heanet.ie)
 * @license     MIT http://www.opensource.org/licenses/mit-license.php
 *  
 */

/**
 * Email_sender Class
 * 
 * @package     RR3
 * @subpackage  Libraries
 * @author      Janusz Ulanowski <janusz.ulanowski@heanet.ie>
 */
class Email_sender {
    function __construct() {
        $this->ci = & get_instance();
        $this->ci->load->library('doctrine');
        $this->em = $this->ci->doctrine->em;
    }

   /**
    * $to may be single email or array of mails
    */
   function send($to,$subject,$body)
   {
      $sending_enabled = $this->ci->config->item('mail_sending_active');
      log_message('debug','Mail:: preparing');
      log_message('debug','Mail:: To: '. serialize($to));
      log_message('debug','Mail:: Subject: '. $subject);
      log_message('debug','Mail:: Body: '. $body);
      
      if(!$sending_enabled)
      {
          log_message('debug','Mail:: cannot be sent because $config[mail_sending_active] is not true');
          return false;
      }
      else
      {
          log_message('debug','Preparing to send email');
      }
      $full_subject = $subject ." " . $this->ci->config->item('mail_subject_suffix');
      $list = array();
      if(!is_array($to))
      {
         $list[] = $to;
      }
      else
      {
         $list = $to;
      }
      foreach($list as $k)
      {
          $this->ci->email->clear();
          $this->ci->email->from($this->ci->config->item('mail_from'), '');
          $this->ci->email->to($k, '');
          $this->ci->email->subject($full_subject);
          $footer = $this->ci->config->item('mail_footer');
          $message = $body . $footer;
          $this->ci->email->message($message);
          if($this->ci->email->send())
          {
             log_message('debug','email sent to '.$k);
          }
          else
          {
             log_message('error','email couldnt be sent to '.$k);
             log_message('error',$this->ci->email->print_debugger());
          }

      } 
      return true;
   } 

}

