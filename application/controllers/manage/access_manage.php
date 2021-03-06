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
 * Access_manage Class
 * 
 * @package     RR3
 * @author      Janusz Ulanowski <janusz.ulanowski@heanet.ie>
 */

class Access_manage extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $loggedin = $this->j_auth->logged_in();
        $this->current_site = current_url();
        if (!$loggedin) {
            $this->session->set_flashdata('target', $this->current_site);
            redirect('auth/login', 'location');
        }
        $this->tmp_providers = new models\Providers;
        $this->load->helper('form');
        $this->load->library('zacl');
    }
    private function display_form_chng($access,$user,$action)
    {
        
        $form = '<div class="permset">'.form_open() . form_hidden('user',$user) . form_hidden('action',$action);
        if($access === 'allow')
        {
        	$form .='<button type="submit" name="change_access"  value="'.$access.'" class="btn positive"><span class="save">'.lang('btn_allow').'</span></button>';
        }
        else
        {
        	$form .='<button  type="submit" name="change_access"  value="'.$access.'"  class="btn negative"><span class="remove">'.lang('btn_deny').'</span></button>';
        }
        $form .= form_close().'</div>';
        return $form;
    
    }
    function entity($id)
    {
        $ent = $this->tmp_providers->getOneById($id);
        if(empty($ent))
        {
                show_error(lang('rerror_providernotexist'),404);
        }
        $group = strtolower($ent->getType());
        if($group === 'both')
        {
            $group = 'entity';
        }
        
       
        $has_manage_access = $this->zacl->check_acl($ent->getId(),'manage',$group,'');
        if(!$has_manage_access)
        {
                show_error(lang('rr_noperm'),403);
        }
        $is_local = $ent->getLocal();
        if(!$is_local)
        {
            show_error(lang('rr_externalentity'),403);
        }
        

        $submited = $this->input->post('change_access');
        if(!empty($submited))
        {
                log_message('debug','change access submited');
                if($submited === 'deny')
                {
                        $resource = $ent->getId();
                        $action = $this->input->post('action');
                        $user = $this->input->post('user');
                        $resource_type = 'entity';
                        if($action === 'read')
                        {
                                $y = $this->zacl->deny_access_fromUser($resource,$action,$user,$group,$resource_type);
                                $y = $this->zacl->deny_access_fromUser($resource,'write',$user,$group,$resource_type);
                                $y = $this->zacl->deny_access_fromUser($resource,'manage',$user,$group,$resource_type);

                        }
                        elseif($action === 'write')
                        {
                                $y = $this->zacl->deny_access_fromUser($resource,$action,$user,$group,$resource_type);
                                $y = $this->zacl->deny_access_fromUser($resource,'manage',$user,$group,$resource_type);
                        
                        }
                        elseif($action === 'manage')
                        {
                                $y = $this->zacl->deny_access_fromUser($resource,$action,$user,$group,$resource_type);
                        }
                        $this->em->flush();
                }
                elseif($submited === 'allow')
                {
                        $resource = $ent->getId();
                        $action = $this->input->post('action');
                        $user = $this->input->post('user');
                        $resource_type = 'entity';
                        if($action === 'manage')
                        {
                                $y = $this->zacl->add_access_toUser($resource,$action,$user,$group,$resource_type);
                                $y = $this->zacl->add_access_toUser($resource,'write',$user,$group,$resource_type);
                                $y = $this->zacl->add_access_toUser($resource,'read',$user,$group,$resource_type);
                        }
                        elseif($action === 'write')
                        {
                                $y = $this->zacl->add_access_toUser($resource,$action,$user,$group,$resource_type);
                                $y = $this->zacl->add_access_toUser($resource,'read',$user,$group,$resource_type);
                        
                        
                        }
                        elseif($action === 'read')
                        {
                                $y = $this->zacl->add_access_toUser($resource,$action,$user,$group,$resource_type);
                        }
                        $this->em->flush();
                
                }
                else
                {
                        log_message('error','access_manage: incorrect submit:'.$submited);
                        
                }
        
        }
        else
        {
                log_message('debug','no change access submited');
        }
        $this->em->flush();
        $this->zacl = new Zacl();

        $tmp_users = $this->em->getRepository("models\User")->findAll();
        $admin_role = $this->em->getRepository("models\AclRole")->findOneBy(array('name'=>'Administrator'));
        $admins = $admin_role->getMembers();
        $users_array = array();
        $users_objects = array();
        $actions = array('read','write','manage');
        $id_of_entity = $ent->getId();
        foreach($tmp_users as $u)
        {
            $users_objects[$u->getUsername()] = $u;
            foreach($actions as $a)
            {
                $users_array[$u->getUsername()][$a] = $this->zacl->check_acl_for_user($id_of_entity,$a,$u->getUsername(),$group);
            }
        }

        
        $row = array();
        $i = 0 ;
        $session_user = $_SESSION['username'];
        foreach($users_array as $key=>$value)
        {
           $is_me = '';
           $isitme = false;
           if($session_user == $key)
           {
                $is_me = '<span class="alert">'.lang('rr_you').'</span>';
                $isitme = true;
           }
           $u = $admins->contains($users_objects[$key]);
           if($u)
           {
                $k = 'admin';
           }
           else
           {
                $k = '';
           }
           if($k)
           {
                $row[$i] = array($key . " (Administrator)".$is_me,''.lang('rr_hasaccess').'',''.lang('rr_hasaccess').'',''.lang('rr_hasaccess').'');
           }
           else
           {    
                $row[$i][] = $key.' ('.$users_objects[$key]->getFullname().')'.$is_me;
                $hasAccess = lang('rr_hasaccess');
                $hasNoAccess = lang('rr_hasnoaccess');
                foreach($value as $ackey=>$acvalue)
                {
                        if($acvalue)
                        {
                            if(!$isitme)
                            {
                                $row[$i][] = $hasAccess . $this->display_form_chng('deny',$key,$ackey);
                            }
                            else
                            {
                                $row[$i][] = $hasAccess;
                            }
                        }
                        else
                        {
                            if(!$isitme)
                            {
                                 $row[$i][] = $hasNoAccess . $this->display_form_chng('allow',$key,$ackey);
                            }
                            else
                            {
                                 $row[$i][] = $hasNoAccess;
                            }
                        }

                }
           }
           $i++;
        }
        $entity_link = anchor(base_url().'providers/detail/show/'.$id_of_entity,'<img src="'.base_url().'images/icons/arrow.png"/>');
        $data['resource_name'] = $ent->getName() . " (".$ent->getEntityId().")".$entity_link;
        $data['resourcename'] = $ent->getName();
        if(empty($data['resourcename']))
        {
           $data['resourcename'] = $ent->getEntityId();
        }
        $data['entityid'] = $ent->getEntityId();
        $data['resourceid'] = $id_of_entity;
        $data['row'] = $row;
        $data['content_view'] = 'manage/access_manage_view';
        $this->load->view('page',$data);


    }
    function federation($id)
    {
        $fed = $this->em->getRepository("models\Federation")->findOneBy(array('id'=>$id));
        if(empty($fed))
        {
             show_error(lang('error_fednotfound'),404);
             return;
        }
        $group = 'federation';
        $owner = $fed->getOwner();
        $has_manage_access = $this->zacl->check_acl('f_'.$fed->getId(),'manage',$group,'');
        if(!$has_manage_access)
        {
             show_error(lang('rerror_noperm_mngperm'),403);
             return;
        }
        $submited = $this->input->post('change_access');
        if(!empty($submited))
        {
              log_message('debug','change access submited');
              if($submited === 'deny')
              {
                   $fresource = 'f_'.$fed->getId();
                   $action = $this->input->post('action');
                   $user = $this->input->post('user');
                   $resource_type = 'federation';
                   if($action === 'read')
                   {
                          $this->zacl->deny_access_fromUser($fresource,$action,$user,$group,$resource_type);
                          $this->zacl->deny_access_fromUser($fresource,'write',$user,$group,$resource_type);
                          $this->zacl->deny_access_fromUser($fresource,'manage',$user,$group,$resource_type);

                   }
                   elseif($action === 'write')
                   {
                          $this->zacl->deny_access_fromUser($fresource,$action,$user,$group,$resource_type);
                          $this->zacl->deny_access_fromUser($fresource,'manage',$user,$group,$resource_type);
                          
                   }
                   elseif($action === 'manage')
                   {
                          $this->zacl->deny_access_fromUser($fresource,$action,$user,$group,$resource_type);
                   }
                   $this->em->flush();
              }
              elseif($submited === 'allow')
              {
                   $fresource = 'f_'.$fed->getId();
                   $action = $this->input->post('action');
                   $user = $this->input->post('user');
                   $resource_type = "federation"; 
                   if($action === 'manage')
                   {
                        $this->zacl->add_access_toUser($fresource,$action,$user,$group,$resource_type);
                        $this->zacl->add_access_toUser($fresource,'write',$user,$group,$resource_type);
                        $this->zacl->add_access_toUser($fresource,'read',$user,$group,$resource_type);
                   }
                   elseif($action === 'write')
                   {
                        $this->zacl->add_access_toUser($fresource,$action,$user,$group,$resource_type);
                        $this->zacl->add_access_toUser($fresource,'read',$user,$group,$resource_type);
                   }
                   elseif($action === 'read')
                   {
                        $this->zacl->add_access_toUser($fresource,$action,$user,$group,$resource_type);
                   }
                   $this->em->flush();
              }
              else
              {
                        log_message('error','access_manage: incorrect submit:'.$submited);
 
              }
        }
        else
        {
               log_message('debug','no change access submited');
        }
        $this->em->flush();
        $this->zacl = new Zacl();
        $tmp_users = $this->em->getRepository("models\User")->findAll();
        $admin_role = $this->em->getRepository("models\AclRole")->findOneBy(array('name'=>'Administrator'));
        $admins = $admin_role->getMembers();
        $users_array = array();
        $users_objects = array();
        $actions = array('read','write','manage');
        $id_of_fed = 'f_'.$fed->getId();
        foreach($tmp_users as $u)
        {
            $users_objects[$u->getUsername()] = $u;
            foreach($actions as $a)
            {
                $users_array[$u->getUsername()][$a] = $this->zacl->check_acl_for_user($id_of_fed,$a,$u->getUsername(),$group);
            }
        }
        $row = array();
        $i = 0 ;
        $session_user = $_SESSION['username'];
        $isitowner = false;
        foreach($users_array as $key=>$value)
        {
           $is_me = "";
           $isitme = false;
         
           $is_owner = "";
           if($session_user == $key)
           {
                $is_me = '<span class="alert">'.lang('rr_you').'</span>';
                $isitme = true;
           }
           /*
           if(!$isitowner && $owner == $key)
           {
                $is_owner = "<span class=\"alert\">Owner".showBubbleHelp('Owner has always read/write permissions, no matter what ACLs are. Manage permision depends on ACL')."</span>";
                $isitowner = true;
           }
           */
           $u = $admins->contains($users_objects[$key]);
           if($u)
           {
                $k = "admin";
           }
           else
           {
                $k = "";
           }
           if($k)
           {
                $row[$i] = array(''.$is_me .' '. $key . ' (Administrator'.showBubbleHelp(''.lang('rhelp_admfullright').'').')  '.$is_owner.'',''.lang('rr_hasaccess').'',''.lang('rr_hasaccess').'',''.lang('rr_hasaccess').'');
           }
           else
           {    
                $row[$i][] = $is_me." ".$key." ".$is_owner;
                $hasAccess = lang('rr_hasaccess');
                $hasNoAccess = lang('rr_hasnoaccess');
                foreach($value as $ackey=>$acvalue)
                {
                        if($acvalue)
                        {
                            if(!$isitme)
                            {
                                $row[$i][] = $hasAccess . $this->display_form_chng('deny',$key,$ackey);
                            }
                            else
                            {
                                $row[$i][] = $hasAccess;
                            }
                        }
                        else
                        {
                            if(!$isitme)
                            {
                                 $row[$i][] = $hasNoAccess . $this->display_form_chng('allow',$key,$ackey);
                            }
                            else
                            {
                                 $row[$i][] = $hasNoAccess ;
                            }
                        }

                }
           }
           $i++;
        }
        $data['fedlink'] = base_url().'federations/manage/show/'. base64url_encode($fed->getName());
        $data['resourcename'] = $fed->getName() ;
        $data['row'] = $row;
        $data['readlegend'] = lang('fedaclreadinfo');
        $data['content_view'] = 'manage/fedaccess_manage_view';
        $this->load->view('page',$data);
        
    
        
    }
}
