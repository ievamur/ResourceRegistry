<?php
if (!defined('BASEPATH'))
    exit('Ni direct script access allowed');
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
 * Federation_registration Class
 * 
 * @package     RR3
 * @author      Janusz Ulanowski <janusz.ulanowski@heanet.ie>
 */

class Federation_registration extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->helper('url');
        $loggedin = $this->j_auth->logged_in();
        $this->current_site = current_url();
        if (!$loggedin)
        {
            $this->session->set_flashdata('target', $this->current_site);
            redirect('auth/login', 'location');
        }
        $this->session->set_userdata(array('currentMenu'=>'register'));
        $this->title = lang('rr_federation_regform_title');
        $this->load->library('approval');
        $this->load->library('zacl');
    }

    function index()
    {
        
        $access = $this->zacl->check_acl('federation','read','','');
        if($access)
        {
                $data['content_view'] = 'federation/federation_register_form';
        }
        else
        {
                $data['content_view'] = 'nopermission';
                $data['error'] = lang('rrerror_noperm_regfed');
        }
        $this->load->view('page', $data);
    }

    public function submit()
    {
        if ($this->_submit_validate() === FALSE)
        {
            $this->index();
            return;
        }
        $access = $this->zacl->check_acl('federation','read','','');
        if(!$access)
        {
                $data['content_view'] = 'nopermission';
                $data['error'] = lang('rrerror_noperm_regfed');
                $this->load->view('page', $data);
            
        }
        $fedname = $this->input->post('fedname');
        $federation = new models\Federation;
        $federation->setName($fedname);
        $federation->setUrn($this->input->post('fedurn'));
        $ispub = $this->input->post('ispublic');
        if (!empty($ispub) && $ispub == 'public')
        {
            $federation->publish();
        } else
        {
            $federation->unPublish();
        }
        $federation->setAsActive();
        $federation->setDescription($this->input->post('description'));
        $federation->setTou($this->input->post('termsofuse'));
        $q = $this->approval->addToQueue($federation,'Create');
        $this->em->persist($q);
        $this->em->flush();
        /**
         * @todo send mail to confirm link if needed, and to admin for approval
         */
            /**
             * send email
             */
            $recipients = array();
            $a = $this->em->getRepository("models\AclRole")->findOneBy(array('name'=>'Administrator'));
            $a_members = $a->getMembers();
            foreach($a_members as $m)
            {
                $recipients[] = $m->getEmail();
            }
            $sbj = "Federation registration request";
            $body = "Dear Administrator\r\n";
            $body .= "".$q->getEmail()." just filled Federation Registration form\r\n";
            $body .= "Federation name: ".$fedname. "\r\n";
            $body .="You can approve or reject it on ".base_url()."reports/awaiting/detail/".$q->getToken()."\r\n";
            $this->load->library('email_sender');
            $this->email_sender->send($recipients,$sbj,$body);

        $data['success'] = lang('rr_fed_req_sent');
        $data['content_view'] = 'federation/success_view';
        $this->load->view('page',$data);
    }

    private function _submit_validate()
    {
        /**
         * @todo add more checks link unique 
         */
        $this->form_validation->set_rules('fedname', lang('rr_fed_name'), 'required|min_length[5]|max_length[128]|xss_clean');
        $this->form_validation->set_rules('fedurn', lang('rr_fed_urn'), 'required|min_length[5]|max_length[128]|xss_clean');
        $this->form_validation->set_rules('description', lang('rr_fed_desc'), 'min_length[5]|max_length[500]|xss_clean');
        $this->form_validation->set_rules('termsofuse', lang('rr_fed_tou'), 'min_length[5]|max_length[1000]|xss_clean');
        return $this->form_validation->run();
    }

}
