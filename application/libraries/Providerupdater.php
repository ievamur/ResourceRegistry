<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * ResourceRegistry3
 * 
 * @package     RR3
 * @author      Middleware Team HEAnet 
 * @copyright   Copyright (c) 2013, HEAnet Limited (http://www.heanet.ie)
 * @license     MIT http://www.opensource.org/licenses/mit-license.php
 *  
 */

/**
 * ProviderUpdater Class
 * 
 * @package     RR3
 * @subpackage  Libraries
 * @author      Janusz Ulanowski <janusz.ulanowski@heanet.ie>
 */
class Providerupdater {

    protected $ci;
    protected $em;

    function __construct()
    {
        $this->ci = &get_instance();
        $this->em = $this->ci->doctrine->em;
       
        $this->ci->load->library('tracker');
    }

    public function getChangeProposal(models\Provider $ent, $chg)
    {
        $p['entityid'] = $ent->getEntityId();
    }

    public function updateProvider(models\Provider $ent, array $ch)
    {
        // $m - array for modifications
        $m  = array();
        $type = $ent->getType();
        $langCodes = languagesCodes();
        $ex = $ent->getExtendMetadata();
        $idpMDUIparent = null;
        $spMDUIparent = null;
        $extend = array();
        $allowedAABind = getAllowedSOAPBindings();
        $spartidx = array();
        $idpartidx = array('-1');
        $acsidx = array();
        foreach ($ex as $e)
        {
            $extend['' . $e->getType() . '']['' . $e->getNamespace() . '']['' . $e->getElement() . ''][] = $e;
            if ($e->getElement() == 'UIInfo' && $e->getNamespace() == 'mdui')
            {
                if ($e->getType() === 'idp')
                {
                    $idpMDUIparent = $e;
                }
                elseif ($e->getType() === 'sp')
                {
                    $spMDUIparent = $e;
                }
            }
        }
        if ($type !== 'SP')
        {
            if (empty($idpMDUIparent))
            {
                $idpMDUIparent = new models\ExtendMetadata;
                $idpMDUIparent->setType('sp');
                $idpMDUIparent->setNamespace('mdui');
                $idpMDUIparent->setElement('UIInfo');
                $ent->setExtendMetadata($idpMDUIparent);
                $this->em->persist($idpMDUIparent);
            }

            /**
             * set scopes
             */
            
            if(array_key_exists('scopes', $ch))
            {
               $origscopesso = implode(',',$ent->getScope('idpsso'));
               $origscopeaa = implode(',',$ent->getScope('aa'));
               if(array_key_exists('idpsso',$ch['scopes']) && !empty($ch['scopes']['idpsso']))
               {
                     $idpssoscopes=array_filter(explode(',',$ch['scopes']['idpsso']));
                     $ent->setScope('idpsso', array_unique($idpssoscopes));
                     if($origscopesso != implode(',',$idpssoscopes))
                     {
                        $m['Scope IDPSSO'] = array('before'=>$origscopesso,'after'=>implode(',',$idpssoscopes));
                     } 
               }
               else
               {
                       $ent->setScope('idpsso', array());
                       if(!empty($origscopesso))
                       {
                          $m['Scope IDPSSO'] = array('before'=>$origscopesso,'after'=>'');
                       }
               }
               if(array_key_exists('aa',$ch['scopes']) && !empty($ch['scopes']['aa']))
               {
                       
                       $aascopes = array_filter(explode(',',$ch['scopes']['aa']));
                       $ent->setScope('aa',array_unique($aascopes));
                       if($origscopeaa != implode(',',$aascopes))
                       {
                          $m['Scope AA'] = array('before'=>$origscopeaa,'after'=>implode(',',$aascopes));
                       } 
                      
               }
               else
               {
                      $ent->setScope('aa', array());
                      if(!empty($origscopeaa))
                      {
                         $m['Scope AA'] = array('before'=>$origscopeaa,'after'=>'');
                      }
               }
               $origscopesso = null;
            }
        }
        if ($type !== 'IDP')
        {
            $spMDUIparent = new models\ExtendMetadata;
            $spMDUIparent->setType('sp');
            $spMDUIparent->setNamespace('mdui');
            $spMDUIparent->setElement('UIInfo');
            $ent->setExtendMetadata($spMDUIparent);
            $this->em->persist($spMDUIparent);
        }
        if (array_key_exists('entityid', $ch) && !empty($ch['entityid']))
        {
            if($ent->getEntityId() != $ch['entityid'])
            {
               $m['EntityID'] = array('before'=>$ent->getEntityId(),'after'=>$ch['entityid']);
               $this->ci->tracker->renameProviderResourcename($ent->getEntityId(),$ch['entityid']); 
            }
            $ent->setEntityId($ch['entityid']);
        }
        if (array_key_exists('orgname', $ch) && !empty($ch['orgname']))
        {
            if($ent->getName() != $ch['orgname'])
            {
              $m['Name'] = array('before'=>$ent->getName(),'after'=>$ch['orgname']);
            }
            $ent->setName($ch['orgname']);
        }
        /**
         * @todo track lname
         */
        if (array_key_exists('lname', $ch) && is_array($ch['lname']))
        {
            $origs = $ent->getLocalName();
            $langs = array_keys(languagesCodes());
            foreach ($ch['lname'] as $key => $value)
            {
                if (in_array($key, $langs))
                {
                    if (empty($value) && array_key_exists($key, $origs))
                    {
                        unset($origs['' . $key . '']);
                    }
                    else
                    {
                        $origs['' . $key . ''] = $value;
                    }
                }
            }
            $ent->setLocalName($origs);
        }
        /**
         * @todo add trck regpolicy
         */
        if (array_key_exists('regpolicy', $ch) && is_array($ch['regpolicy']))
        {
            $origs = $ent->getRegistrationPolicy();
            $origcopy = $origs;
            $langs = array_keys(languagesCodes());
            foreach ($ch['regpolicy'] as $key => $value)
            {
                if (in_array($key, $langs))
                {
                    if (empty($value) && array_key_exists($key, $origs))
                    {
                        unset($origs['' . $key . '']);
                    }
                    elseif(!empty($value))
                    {
                        $origs['' . $key . ''] = $value;
                    }
                }
            }
            if($origs != $origcopy)
            {
               $tmpbefore =  str_replace(array("{","}",":","\/"), array("","",":","/"), json_encode($origcopy));
               $tmpafter = str_replace(array("{","}",":","\/"), array("","",":","/"), json_encode($origs));
               $m['RegPolicy'] = array('before'=>$tmpbefore,'after'=>$tmpafter);
            }
            $ent->setRegistrationPolicyFromArray($origs, TRUE);
        }
        if (array_key_exists('displayname', $ch) && !empty($ch['displayname']))
        {
            if($ent->getDisplayName() !== $ch['displayname'])
            {
               $m['DisplayName']  = array('before'=>$ent->getDisplayName(),'after'=>$ch['displayname']);
            }
            $ent->setDisplayName($ch['displayname']);
        }
        /**
         * @todo track ldisplayname
         */
        if (array_key_exists('ldisplayname', $ch) && is_array($ch['ldisplayname']))
        {
            $origs = $ent->getLocalDisplayname();
            $origcopy = $origs;
            $langs = array_keys(languagesCodes());
            foreach ($ch['ldisplayname'] as $key => $value)
            {
                if (in_array($key, $langs))
                {
                    if (empty($value) && array_key_exists($key, $origs))
                    {
                        unset($origs['' . $key . '']);
                    }
                    else
                    {
                        $origs['' . $key . ''] = $value;
                    }
                }
            }
            if($origs != $origcopy)
            {
               $tmpbefore =  str_replace(array("{","}",":","\/"), array("","",":","/"), json_encode($origcopy));
               $tmpafter = str_replace(array("{","}",":","\/"), array("","",":","/"), json_encode($origs));
               $m['Localized DisplayName'] = array('before'=>$tmpbefore,'after'=>$tmpafter);
            }
            $ent->setLocalDisplayName($origs);
        }
        if (array_key_exists('regauthority', $ch))
        {
            if($ent->getRegistrationAuthority() !== $ch['regauthority'])
            {
                $m['RegistrationAuthority'] = array('before'=>$ent->getRegistrationAuthority(),'after'=> $ch['regauthority']);
            }
            $ent->setRegistrationAuthority($ch['regauthority']);
        }
        if (array_key_exists('registrationdate', $ch))
        {
            $prevregdate = $ent->getRegistrationDate();
            if(isset($prevregdate))
            {
               $prevregdate = $prevregdate->format('Y-m-d');
            }
            else
            {
               $prevregdate = '';
            }
            if($prevregdate !== $ch['registrationdate'])
            {
               $m['RegistrationDate'] = array('before'=>$prevregdate,'after'=>$ch['registrationdate']);
            }
            if (!empty($ch['registrationdate']))
            {
                $ent->setRegistrationDate(\DateTime::createFromFormat('Y-m-d H:i:s', $ch['registrationdate'] . ' 00:00:00'));
            }
            else
            {
                $ent->setRegistrationDate(null);
            }
        }
        if (array_key_exists('validfrom', $ch))
        {
            $prevvalidfrom = $ent->getValidFrom();
            if(isset($prevvalidfrom))
            {
               $prevvalidfrom = $prevvalidfrom->format('Y-m-d');
            }
            else
            {
               $prevvalidfrom = '';
            }
            if($prevvalidfrom !== $ch['validfrom'])
            {
               $m['ValidFrom'] = array('before'=>$prevvalidfrom,'after'=>$ch['validfrom']);
            }
            if (!empty($ch['validfrom']))
            {
                $ent->setValidFrom(\DateTime::createFromFormat('Y-m-d H:i:s', $ch['validfrom'] . ' 00:00:00'));
            }
            else
            {
                $ent->setValidFrom(null);
            }
        }
        if (array_key_exists('validto', $ch))
        {
            $prevvalidto = $ent->getValidTo();
            if(isset($prevvalidto))
            {
               $prevvalidto = $prevvalidto->format('Y-m-d');
            }
            else
            {
               $prevvalidto = '';
            }
            if($prevvalidto !== $ch['validto'])
            {
               $m['ValidTo'] = array('before'=>$prevvalidto,'after'=>$ch['validto']);
            }
            if (!empty($ch['validto']))
            {
                $ent->setValidTo(\DateTime::createFromFormat('Y-m-d H:i:s', $ch['validto'] . ' 00:00:00'));
            }
            else
            {
                $ent->setValidTo(null);
            }
        }
        if (array_key_exists('homeurl', $ch))
        {
            if($ent->getHomeUrl() !== $ch['homeurl'])
            {
               $m['HomeURL'] = array('before'=>$ent->getHomeUrl(),'after'=>$ch['homeurl']);
            }
            $ent->setHomeUrl($ch['homeurl']);
        }
        if (array_key_exists('helpdeskurl', $ch))
        {
            if($ent->getHelpdeskUrl() !== $ch['helpdeskurl'])
            {
               $m['HelpdeskURL'] = array('before'=>$ent->getHelpdeskUrl(),'after'=>$ch['helpdeskurl']);
            }
            $ent->setHelpdeskUrl($ch['helpdeskurl']);
        }
        if (array_key_exists('lhelpdesk', $ch) && is_array($ch['lhelpdesk']))
        {
            $origs = $ent->getLocalHelpdeskUrl();
            $origcopy = $origs;
            $langs = array_keys(languagesCodes());
            foreach ($ch['lhelpdesk'] as $key => $value)
            {
                if (in_array($key, $langs))
                {
                    if (empty($value) && array_key_exists($key, $origs))
                    {
                        unset($origs['' . $key . '']);
                    }
                    elseif(!empty($value))
                    {
                        $origs['' . $key . ''] = $value;
                    }
                }
            }
            if($origs != $origcopy)
            {
               $tmpbefore =  str_replace(array("{","}",":","\/"), array("","",":","/"), json_encode($origcopy));
               $tmpafter = str_replace(array("{","}",":","\/"), array("","",":","/"), json_encode($origs));
               $m['Localized HelpdeskURL'] = array('before'=>$tmpbefore,'after'=>$tmpafter);
            }
            $ent->setLocalHelpdeskUrl($origs);
        }

        if (array_key_exists('description', $ch))
        {
            if($ent->getDescription() !== $ch['description'])
            {
               $m['Description'] = array('before'=>$ent->getDescription(),'after'=>$ch['description']);
            }
            $ent->setDescription($ch['description']);
        }
        if (array_key_exists('ldesc', $ch) && is_array($ch['ldesc']))
        {
            $origs = $ent->getLocalDescription();
            $origcopy = $origs;
            $langs = array_keys(languagesCodes());
            foreach ($ch['ldesc'] as $key => $value)
            {
                if (in_array($key, $langs))
                {
                    if (empty($value) && array_key_exists($key, $origs))
                    {
                        unset($origs['' . $key . '']);
                    }
                    elseif(!empty($value))
                    {
                        $origs['' . $key . ''] = $value;
                    }
                }
            }
            if($origs != $origcopy)
            {
               $tmpbefore =  str_replace(array("{","}",":","\/"), array("","",":","/"), json_encode($origcopy));
               $tmpafter = str_replace(array("{","}",":","\/"), array("","",":","/"), json_encode($origs));
               $m['Localized Description'] = array('before'=>$tmpbefore,'after'=>$tmpafter);
            }
            $ent->setLocalDescription($origs);
        }
        /**
         * @todo track coc changes
         */
        if (array_key_exists('coc', $ch))
        {
            if (!empty($ch['coc']))
            {
                $c = $this->em->getRepository("models\Coc")->findOneBy(array('id' => $ch['coc']));
                $ent->setCoc($c);
            }
            else
            {
                $ent->setCoc();
            }
        }
        if (array_key_exists('privacyurl', $ch))
        {
            if($ent->getPrivacyURL() !== $ch['privacyurl'])
            {
               $m['PrivacyURL general'] = array('before'=>$ent->getPrivacyURL(),'after'=>$ch['privacyurl']);
            }
            $ent->setPrivacyUrl($ch['privacyurl']);
        }
        /**
         * @todo  track prvurl
         */
        if (array_key_exists('prvurl', $ch))
        {
            if ($type !== 'IDP')
            {
                $origex = array();
                if (isset($extend['sp']['mdui']['PrivacyStatementURL']))
                {
                    foreach ($extend['sp']['mdui']['PrivacyStatementURL'] as $v)
                    {
                        $l = $v->getAttributes();
                        $origex['' . $l['xml:lang'] . ''] = $v;
                    }
                }
                if (isset($ch['prvurl']['spsso']))
                {
                    foreach ($origex as $key => $value)
                    {
                        if (array_key_exists($key, $ch['prvurl']['spsso']))
                        {
                            if (empty($ch['prvurl']['spsso']['' . $key . '']))
                            {
                                $value->setProvider(NULL);
                                $ex->removeElement($value);
                                $this->em->remove($value);
                            }
                            else
                            {
                                $value->setValue($ch['prvurl']['spsso']['' . $key . '']);
                                $this->em->persist($value);
                            }
                            unset($ch['prvurl']['spsso']['' . $key . '']);
                        }
                    }

                    foreach ($ch['prvurl']['spsso'] as $key2 => $value2)
                    {
                        if (!empty($value2))
                        {
                            $nprvurl = new models\ExtendMetadata();
                            $nprvurl->setType('sp');
                            $nprvurl->setNamespace('mdui');
                            $nprvurl->setElement('PrivacyStatementURL');
                            $nprvurl->setAttributes(array('xml:lang' => $key2));
                            $nprvurl->setValue($value2);
                            $ent->setExtendMetadata($nprvurl);
                            $nprvurl->setParent($spMDUIparent);
                            $this->em->persist($nprvurl);
                        }
                    }
                }
            }
            if ($type !== 'SP')
            {
                $origex = array();
                if (isset($extend['idp']['mdui']['PrivacyStatementURL']))
                {
                    foreach ($extend['idp']['mdui']['PrivacyStatementURL'] as $v)
                    {
                        $l = $v->getAttributes();
                        $origex['' . $l['xml:lang'] . ''] = $v;
                    }
                }
                if (isset($ch['prvurl']['idpsso']))
                {
                    foreach ($origex as $key => $value)
                    {
                        if (array_key_exists($key, $ch['prvurl']['idpsso']))
                        {
                            if (empty($ch['prvurl']['idpsso']['' . $key . '']))
                            {
                                $value->setProvider(NULL);
                                $ex->removeElement($value);
                                $this->em->remove($value);
                            }
                            else
                            {
                                $value->setValue($ch['prvurl']['idpsso']['' . $key . '']);
                                $this->em->persist($value);
                            }
                            unset($ch['prvurl']['idpsso']['' . $key . '']);
                        }
                    }

                    foreach ($ch['prvurl']['idpsso'] as $key2 => $value2)
                    {
                        if (!empty($value2))
                        {
                            $nprvurl = new models\ExtendMetadata();
                            $nprvurl->setType('idp');
                            $nprvurl->setNamespace('mdui');
                            $nprvurl->setElement('PrivacyStatementURL');
                            $nprvurl->setAttributes(array('xml:lang' => $key2));
                            $nprvurl->setValue($value2);
                            $ent->setExtendMetadata($nprvurl);
                            $nprvurl->setParent($idpMDUIparent);
                            $this->em->persist($nprvurl);
                        }
                    }
                }
            }
        }

        /**
         * START update protocols enumeration
         */
        if (array_key_exists('prot', $ch) && !empty($ch['prot']) && is_array($ch['prot']))
        {
            if (isset($ch['prot']['aa']) && is_array($ch['prot']['aa']))
            {
                $ent->setProtocolSupport('aa', $ch['prot']['aa']);
            }
            if (isset($ch['prot']['idpsso']) && is_array($ch['prot']['idpsso']))
            {
                $ent->setProtocolSupport('idpsso', $ch['prot']['idpsso']);
            }
            if (isset($ch['prot']['spsso']) && is_array($ch['prot']['spsso']))
            {
                $ent->setProtocolSupport('spsso', $ch['prot']['spsso']);
            }
        }

        if (!array_key_exists('nameids', $ch))
        {
            if ($type !== 'SP')
            {
                $ent->setNameIds('idpsso', array());
                $ent->setNameIds('aa', array());
            }
            if ($type !== 'IDP')
            {
                $ent->setNameIds('spsso', array());
            }
        }
        if ($type !== 'SP')
        {
            if (isset($ch['nameids']['idpsso']) && is_array($ch['nameids']['idpsso']))
            {
                $ent->setNameIds('idpsso', $ch['nameids']['idpsso']);
            }
            else
            {
                $ent->setNameIds('idpsso', array());
            }
            if (isset($ch['nameids']['idpaa']) && is_array($ch['nameids']['idpaa']))
            {
                $ent->setNameIds('aa', $ch['nameids']['idpaa']);
            }
            else
            {
                $ent->setNameIds('aa', array());
            }
        }
        if ($type !== 'IDP')
        {
            if (isset($ch['nameids']['spsso']) && is_array($ch['nameids']['spsso']))
            {
                $ent->setNameIds('spsso', $ch['nameids']['spsso']);
            }
            else
            {
                $ent->setNameIds('spsso', array());
            }
        }



        /**
         * START update service locations
         */
        $ssobinds = array();
        $idpslobinds = array();
        $spslobinds = array();
        $idpaabinds = array();
        // acsidx - array to collect indexes of AssertionConsumerService
        $acsidx = array('-1');
        $acsdefaultset = false;
        // dridx  - array to collect indexes of DiscoveryResponse
        $dridx = array('-1');
        if (array_key_exists('srv', $ch) && !empty($ch['srv']) && is_array($ch['srv']))
        {
            $srvs = $ch['srv'];
            $orgsrvs = $ent->getServiceLocations();
            foreach ($orgsrvs as $v)
            {
                $srvtype = $v->getType();
                if (array_key_exists($srvtype, $srvs))
                {
                    if ($srvtype === 'SingleSignOnService')
                    {
                        if ($type === 'SP')
                        {
                            $ent->removeServiceLocation($v);
                        }
                        else
                        {
                            if (array_key_exists($v->getId(), $srvs[$srvtype]))
                            {
                                if ($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind'] == $v->getBindingName())
                                {
                                    if (empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']))
                                    {
                                        $ent->removeServiceLocation($v);
                                    }
                                    else
                                    {
                                        if (!in_array($v->getBindingName(), $ssobinds))
                                        {
                                            $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                            $this->em->persist($v);
                                            $ssobinds[] = $v->getBindingName();
                                        }
                                        else
                                        {
                                            log_message('error', 'Found more than one SingSignOnService with the same binding protocol for entity:' . $ent->getEntityId());
                                            log_message('debug', 'Removing duplicate entry');
                                            $ent->removeServiceLocation($v);
                                        }
                                        unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                                    }
                                }
                            }
                        }
                    }
                    elseif ($srvtype === 'IDPSingleLogoutService')
                    {
                        log_message('debug', 'GG:IDPSingleLogoutService type found');
                        if ($type === 'SP')
                        {
                            log_message('debug', 'GG:IDPSingleLogoutService entity SP removein service');
                            $ent->removeServiceLocation($v);
                        }
                        elseif (in_array($v->getBindingName(), $idpslobinds))
                        {
                            log_message('debug', 'GG: found bind:' . $v->getBindingName() . ' in array idpslobinds');
                            log_message('debug', 'GG current values in idpslobinds: ' . serialize($idpslobinds));
                            $ent->removeServiceLocation($v);
                        }
                        else
                        {
                            log_message('debug', 'GG: step 2');
                            if (array_key_exists($v->getId(), $srvs['' . $srvtype . '']))
                            {
                                log_message('debug', 'GG:IDPSingleLogoutService: found id in form:' . $v->getId() . ' with url: ' . $v->getUrl());
                                $idpslobinds[] = $v->getBindingName();
                                if ($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind'] === $v->getBindingName())
                                {
                                    if (empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']))
                                    {
                                        $ent->removeServiceLocation($v);
                                    }
                                    else
                                    {
                                        $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                        $this->em->persist($v);
                                    }
                                    unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                                }
                            }
                        }
                    }
                    elseif ($srvtype === 'SPSingleLogoutService')
                    {
                        log_message('debug', 'GG:SPSingleLogoutService type found');
                        if ($type == 'IDP')
                        {
                            log_message('debug', 'GG:SPSingleLogoutService entity SP removein service');
                            $ent->removeServiceLocation($v);
                        }
                        elseif (in_array($v->getBindingName(), $spslobinds))
                        {
                            log_message('debug', 'GG: found bind:' . $v->getBindingName() . ' in array idpslobinds');
                            log_message('debug', 'GG current values in spslobinds: ' . serialize($spslobinds));
                            $ent->removeServiceLocation($v);
                        }
                        else
                        {
                            log_message('debug', 'GG: step 2');
                            if (array_key_exists($v->getId(), $srvs['' . $srvtype . '']))
                            {
                                log_message('debug', 'GG:SPSingleLogoutService: found id in form:' . $v->getId() . ' with url: ' . $v->getUrl());
                                $spslobinds[] = $v->getBindingName();
                                if ($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind'] === $v->getBindingName())
                                {
                                    if (empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']))
                                    {
                                        $ent->removeServiceLocation($v);
                                    }
                                    else
                                    {
                                        $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                        $this->em->persist($v);
                                    }
                                    unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                                }
                            }
                        }
                    }
                    elseif ($srvtype === 'IDPAttributeService')
                    {
                        log_message('debug', 'GG:IDPAttributeService type found');
                        if ($type == 'SP')
                        {
                            log_message('debug', 'GG:IDPAttributeService entity SP removein service');
                            $ent->removeServiceLocation($v);
                        }
                        elseif (in_array($v->getBindingName(), $idpaabinds) or !in_array($v->getBindingName(), $allowedAABind))
                        {
                            log_message('debug', 'GG: found bind:' . $v->getBindingName() . ' in array idpslobinds');
                            log_message('debug', 'GG current values in spslobinds: ' . serialize($idpaabinds));
                            $ent->removeServiceLocation($v);
                        }
                        else
                        {
                            log_message('debug', 'GG: step 2');
                            if (array_key_exists($v->getId(), $srvs['' . $srvtype . '']))
                            {
                                log_message('debug', 'GG:SPSingleLogoutService: found id in form:' . $v->getId() . ' with url: ' . $v->getUrl());
                                $idpaabinds[] = $v->getBindingName();
                                if ($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind'] === $v->getBindingName())
                                {
                                    if (empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']))
                                    {
                                        $ent->removeServiceLocation($v);
                                    }
                                    else
                                    {
                                        $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                        $this->em->persist($v);
                                    }
                                    unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                                }
                            }
                        }
                    }
                    elseif ($srvtype === 'IDPArtifactResolutionService')
                    {
                        log_message('debug', 'GG:IDPArtifactResolutionService type found');
                        if ($type === 'SP')
                        {
                            log_message('debug', 'GG:IDPArtifactResolutionService entity recognized as SP removin service');
                            $ent->removeServiceLocation($v);
                            unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                        }
                        else
                        {
                            if (array_key_exists($v->getId(), $srvs['' . $srvtype . '']))
                            {
                                if (empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']) or empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind']))
                                {
                                    $ent->removeServiceLocation($v);
                                }
                                else
                                {
                                    $v->setDefault(FALSE);
                                    $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                    if (isset($srvs['' . $srvtype . '']['' . $v->getId() . '']['order']) && !in_array($srvs['' . $srvtype . '']['' . $v->getId() . '']['order'], $idpartidx))
                                    {
                                        $v->setOrder($srvs['' . $srvtype . '']['' . $v->getId() . '']['order']);
                                        $idpartidx[] = $srvs['' . $srvtype . '']['' . $v->getId() . '']['order'];
                                    }
                                    else
                                    {
                                        $maxidpartindex = max($idpartidx) + 1;
                                        $v->setOrder($maxidpartindex);
                                        $idpartidx[] = $maxidpartindex;
                                    }
                                    $v->setBindingName($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind']);
                                    $this->em->persist($v);
                                }
                            }
                            unset($srvs[$srvtype][$v->getId()]);
                        }
                    }
                    elseif ($srvtype === 'AssertionConsumerService')
                    {
                        log_message('debug', 'GG:AssertionConsumerService type found');
                        if ($type == 'IDP')
                        {
                            log_message('debug', 'GG:AssertionConsumerService entity recognized as IDP removin service');
                            $ent->removeServiceLocation($v);
                            unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                        }
                        else
                        {
                            if (array_key_exists($v->getId(), $srvs['' . $srvtype . '']))
                            {
                                if (empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']) or empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind']))
                                {
                                    $ent->removeServiceLocation($v);
                                }
                                else
                                {
                                    if ($acsdefaultset or empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['default']))
                                    {
                                        $v->setDefault(FALSE);
                                    }
                                    else
                                    {
                                        $v->setDefault(TRUE);
                                        $acsdefaultset = TRUE;
                                    }
                                    $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                    if (isset($srvs['' . $srvtype . '']['' . $v->getId() . '']['order']) && !in_array($srvs['' . $srvtype . '']['' . $v->getId() . '']['order'], $acsidx))
                                    {
                                        $v->setOrder($srvs['' . $srvtype . '']['' . $v->getId() . '']['order']);
                                        $acsidx[] = $srvs['' . $srvtype . '']['' . $v->getId() . '']['order'];
                                    }
                                    else
                                    {
                                        $maxacsindex = max($acsidx) + 1;
                                        $v->setOrder($maxacsindex);
                                        $acsidx[] = $maxacsindex;
                                    }
                                    $v->setBindingName($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind']);
                                    $this->em->persist($v);
                                }
                            }
                            unset($srvs[$srvtype][$v->getId()]);
                        }
                    }
                    elseif ($srvtype === 'SPArtifactResolutionService')
                    {
                        log_message('debug', 'GG:SPArtifactResolutionService type found');
                        if ($type === 'IDP')
                        {
                            log_message('debug', 'GG:SPArtifactResolutionService entity recognized as IDP removin service');
                            $ent->removeServiceLocation($v);
                            unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                        }
                        else
                        {
                            if (array_key_exists($v->getId(), $srvs['' . $srvtype . '']))
                            {
                                if (empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']) or empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind']))
                                {
                                    $ent->removeServiceLocation($v);
                                }
                                else
                                {
                                    $v->setDefault(FALSE);
                                    $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                    if (isset($srvs['' . $srvtype . '']['' . $v->getId() . '']['order']) && !in_array($srvs['' . $srvtype . '']['' . $v->getId() . '']['order'], $spartidx))
                                    {
                                        $v->setOrder($srvs['' . $srvtype . '']['' . $v->getId() . '']['order']);
                                        $spartidx[] = $srvs['' . $srvtype . '']['' . $v->getId() . '']['order'];
                                    }
                                    else
                                    {
                                        $maxspartindex = max($spartidx) + 1;
                                        $v->setOrder($maxspartindex);
                                        $spartidx[] = $maxspartindex;
                                    }
                                    $v->setBindingName($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind']);
                                    $this->em->persist($v);
                                }
                            }
                            unset($srvs[$srvtype][$v->getId()]);
                        }
                    }
                    elseif ($srvtype === 'DiscoveryResponse')
                    {
                        log_message('debug', 'GG:DiscoveryResponse type found');
                        if ($type === 'IDP')
                        {
                            log_message('debug', 'GG:DiscoveryResponse entity recognized as IDP removin service');
                            $ent->removeServiceLocation($v);
                            unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                        }
                        else
                        {
                            if (array_key_exists($v->getId(), $srvs['' . $srvtype . '']))
                            {
                                if (empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']) or empty($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind']))
                                {
                                    $ent->removeServiceLocation($v);
                                }
                                else
                                {
                                    $v->setDefault(FALSE);

                                    $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                    if (isset($srvs['' . $srvtype . '']['' . $v->getId() . '']['order']) && !in_array($srvs['' . $srvtype . '']['' . $v->getId() . '']['order'], $acsidx))
                                    {
                                        $v->setOrder($srvs['' . $srvtype . '']['' . $v->getId() . '']['order']);
                                        $dridx[] = $srvs['' . $srvtype . '']['' . $v->getId() . '']['order'];
                                    }
                                    else
                                    {
                                        $maxdrindex = max($dridx) + 1;
                                        $v->setOrder($maxdrindex);
                                        $dridx[] = $maxdrindex;
                                    }
                                    //$v->setBindingName($srvs['' . $srvtype . '']['' . $v->getId() . '']['bind']);
                                    $v->setBindingName('urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol');
                                    $this->em->persist($v);
                                }
                            }
                            unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                        }
                    }
                    elseif ($srvtype === 'RequestInitiator')
                    {
                        log_message('debug', 'GG:RequestInitiator type found');
                        if ($type === 'IDP')
                        {
                            log_message('debug', 'GG:RequestInitiator entity recognized as IDP removin service');
                            $ent->removeServiceLocation($v);
                            unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                        }
                        else
                        {
                            if (array_key_exists($v->getId(), $srvs['' . $srvtype . '']))
                            {
                                $v->setDefault(FALSE);
                                $v->setUrl($srvs['' . $srvtype . '']['' . $v->getId() . '']['url']);
                                $v->setOrderNull();
                                $v->setBindingName('urn:oasis:names:tc:SAML:profiles:SSO:request-init');
                                $this->em->persist($v);
                            }
                            unset($srvs['' . $srvtype . '']['' . $v->getId() . '']);
                        }
                    }
                }
            }
            /**
             * adding new service locations from form
             */
            foreach ($srvs as $k => $v)
            {
                if ($k === 'SingleSignOnService' && $type != 'SP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        if (!empty($v1['bind']) && !empty($v1['url']))
                        {
                            log_message('debug', 'GGG new sso');
                            if (!in_array($v1['bind'], $ssobinds))
                            {
                                $newservice = new models\ServiceLocation();
                                $newservice->setBindingName($v1['bind']);
                                $newservice->setUrl($v1['url']);
                                $newservice->setType('SingleSignOnService');
                                $newservice->setProvider($ent);
                                $ent->setServiceLocation($newservice);
                                $this->em->persist($newservice);
                                $ssobinds[] = $v1['bind'];
                            }
                            else
                            {
                                log_message('error', 'SingSignOnService url already set for binding proto: ' . $v1['bind'] . ' for entity' . $ent->getEntityId());
                            }
                        }
                    }
                }
                elseif ($k === 'IDPSingleLogoutService' && $type != 'SP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        if (!empty($v1['bind']) && !empty($v1['url']))
                        {
                            log_message('debug', 'GGG new IDP SingleLogout');
                            if (!in_array($v1['bind'], $idpslobinds))
                            {
                                $newservice = new models\ServiceLocation();
                                $newservice->setBindingName($v1['bind']);
                                $newservice->setUrl($v1['url']);
                                $newservice->setType('IDPSingleLogoutService');
                                $newservice->setProvider($ent);
                                $ent->setServiceLocation($newservice);
                                $this->em->persist($newservice);
                                $idpslobinds[] = $v1['bind'];
                            }
                            else
                            {
                                log_message('error', 'IDP SingLogout url already set for binding proto: ' . $v1['bind'] . ' for entity' . $ent->getEntityId());
                            }
                        }
                    }
                }
                elseif ($k === 'IDPAttributeService' && $type != 'SP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        if (!empty($v1['bind']) && !empty($v1['url']) && in_array($v1['bind'], $allowedAABind))
                        {
                            log_message('debug', 'GGG new IDP IDPAttributeService');
                            if (!in_array($v1['bind'], $idpaabinds))
                            {
                                $newservice = new models\ServiceLocation();
                                $newservice->setBindingName($v1['bind']);
                                $newservice->setUrl($v1['url']);
                                $newservice->setType('IDPAttributeService');
                                $newservice->setProvider($ent);
                                $ent->setServiceLocation($newservice);
                                $this->em->persist($newservice);
                                $idpaabinds[] = $v1['bind'];
                            }
                            else
                            {
                                log_message('error', 'IDP AttributeService url already set for binding proto: ' . $v1['bind'] . ' for entity' . $ent->getEntityId());
                            }
                        }
                    }
                }
                elseif ($k === 'SPSingleLogoutService' && $type != 'IDP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        if (!empty($v1['bind']) && !empty($v1['url']))
                        {
                            log_message('debug', 'GGG new SP SingleLogout');
                            if (!in_array($v1['bind'], $spslobinds))
                            {
                                $newservice = new models\ServiceLocation();
                                $newservice->setBindingName($v1['bind']);
                                $newservice->setUrl($v1['url']);
                                $newservice->setType('SPSingleLogoutService');
                                $newservice->setProvider($ent);
                                $ent->setServiceLocation($newservice);
                                $this->em->persist($newservice);
                                $spslobinds[] = $v1['bind'];
                            }
                            else
                            {
                                log_message('error', 'SP SingLogout url already set for binding proto: ' . $v1['bind'] . ' for entity' . $ent->getEntityId());
                            }
                        }
                    }
                }
                elseif ($k === 'AssertionConsumerService' && $type != 'IDP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        if (!empty($v1['bind']) && !empty($v1['url']))
                        {
                            log_message('debug', 'GGG new SP AsserttionConsumerService');
                            $newservice = new models\ServiceLocation();
                            $newservice->setBindingName($v1['bind']);
                            $newservice->setUrl($v1['url']);
                            $newservice->setType('AssertionConsumerService');
                            if ($acsdefaultset)
                            {
                                $newservice->setDefault(FALSE);
                            }
                            elseif (isset($v1['default']) && $v1['default'] == 1)
                            {
                                $newservice->setDefault(TRUE);
                            }
                            else
                            {
                                $newservice->setDefault(FALSE);
                            }
                            if (isset($v1['order']) && is_numeric($v1['order']))
                            {
                                if (in_array($v1['order'], $acsidx))
                                {
                                    $maxacsindex = max($acsidx) + 1;
                                    $newservice->setOrder($maxacsindex);
                                }
                                else
                                {
                                    $newservice->setOrder($v1['order']);
                                }
                            }
                            else
                            {
                                $maxacsindex = max($acsidx) + 1;
                                $newservice->setOrder($maxacsindex);
                            }
                            $newservice->setProvider($ent);
                            $ent->setServiceLocation($newservice);
                            $this->em->persist($newservice);
                        }
                    }
                }
                elseif ($k === 'IDPArtifactResolutionService' && $type != 'SP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        if (!empty($v1['bind']) && !empty($v1['url']))
                        {
                            log_message('debug', 'GGG new  IDP ArtifactResolutionService');
                            $newservice = new models\ServiceLocation();
                            $newservice->setBindingName($v1['bind']);
                            $newservice->setUrl($v1['url']);
                            $newservice->setType('IDPArtifactResolutionService');
                            $newservice->setDefault(FALSE);
                            if (isset($v1['order']) && is_numeric($v1['order']))
                            {
                                if (in_array($v1['order'], $idpartidx))
                                {
                                    $maxidpartindex = max($idpartidx) + 1;
                                    $newservice->setOrder($maxidpartindex);
                                }
                                else
                                {
                                    $newservice->setOrder($v1['order']);
                                }
                            }
                            else
                            {
                                $maxidpartindex = max($idpartidx) + 1;
                                $newservice->setOrder($maxidpartindex);
                            }
                            $newservice->setProvider($ent);
                            $ent->setServiceLocation($newservice);
                            $this->em->persist($newservice);
                        }
                    }
                }
                elseif ($k === 'SPArtifactResolutionService' && $type != 'IDP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        if (!empty($v1['bind']) && !empty($v1['url']))
                        {
                            log_message('debug', 'GGG new SP SPArtifactResolutionService');
                            $newservice = new models\ServiceLocation();
                            $newservice->setBindingName($v1['bind']);
                            $newservice->setUrl($v1['url']);
                            $newservice->setType('SPArtifactResolutionService');
                            $newservice->setDefault(FALSE);
                            if (isset($v1['order']) && is_numeric($v1['order']))
                            {
                                if (in_array($v1['order'], $spartidx))
                                {
                                    $maxspartindex = max($spartidx) + 1;
                                    $newservice->setOrder($maxspartindex);
                                }
                                else
                                {
                                    $newservice->setOrder($v1['order']);
                                }
                            }
                            else
                            {
                                $maxspartindex = max($spartidx) + 1;
                                $newservice->setOrder($maxspartindex);
                            }
                            $newservice->setProvider($ent);
                            $ent->setServiceLocation($newservice);
                            $this->em->persist($newservice);
                        }
                    }
                }
                elseif ($k === 'DiscoveryResponse' && $type != 'IDP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        if (!empty($v1['bind']) && !empty($v1['url']))
                        {
                            log_message('debug', 'GGG new SP DiscoveryResponse');
                            $newservice = new models\ServiceLocation();
                            $newservice->setBindingName('urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol');
                            $newservice->setUrl($v1['url']);
                            $newservice->setType('DiscoveryResponse');
                            $newservice->setDefault(FALSE);
                            if (isset($v1['order']) && is_numeric($v1['order']))
                            {
                                if (in_array($v1['order'], $dridx))
                                {
                                    $maxdrindex = max($dridx) + 1;
                                    $newservice->setOrder($maxdrindex);
                                }
                                else
                                {
                                    $newservice->setOrder($v1['order']);
                                }
                            }
                            else
                            {
                                $maxdrindex = max($dridx) + 1;
                                $newservice->setOrder($maxdrindex);
                            }
                            $newservice->setProvider($ent);
                            $ent->setServiceLocation($newservice);
                            $this->em->persist($newservice);
                        }
                    }
                }
                elseif ($k === 'RequestInitiator' && $type != 'IDP')
                {
                    foreach ($srvs[$k] as $k1 => $v1)
                    {
                        log_message('debug', 'GGG new SP RequestInitiator');
                        $newservice = new models\ServiceLocation();
                        $newservice->setBindingName('urn:oasis:names:tc:SAML:profiles:SSO:request-init');
                        $newservice->setUrl($v1['url']);
                        $newservice->setType('RequestInitiator');
                        $newservice->setDefault(FALSE);
                        $newservice->setOrderNull();
                        $newservice->setProvider($ent);
                        $ent->setServiceLocation($newservice);
                        $this->em->persist($newservice);
                    }
                }
            }
        }
        /**
         * END update service locations
         */
        /**
         * BEGIN update certs
         */
        if (array_key_exists('crt', $ch) && !empty($ch['crt']) && is_array($ch['crt']))
        {
            $crts = $ch['crt'];
            $origcrts = array();
            $tmpcrt = $ent->getCertificates();
            $allowedusecase = array('signing', 'encryption', 'both');
            foreach ($tmpcrt as $v)
            {
                if (isset($ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']))
                {
                    if (array_key_exists('remove', $ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']) && $ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']['remove'] === 'yes')
                    {
                        $ent->removeCertificate($v);
                    }
                    else
                    {
                        $crtusecase = $ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']['usage'];
                        if (!empty($crtusecase) && in_array($crtusecase, $allowedusecase))
                        {
                            $v->setCertUse($crtusecase);
                        }
                        if (isset($ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']['keyname']))
                        {
                            $v->setKeyname($ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']['keyname']);
                        }
                        if (isset($ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']['certdata']))
                        {
                            $v->setCertData($ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']['certdata']);
                        }
                        $this->em->persist($v);
                    }
                    unset($ch['crt']['' . $v->getType() . '']['' . $v->getId() . '']);
                }
            }
            /**
             * setting new certs 
             */
            foreach ($ch['crt'] as $k1 => $v1)
            {
                if ($k1 === 'spsso' && $type !== 'IDP')
                {
                    foreach ($v1 as $k2 => $v2)
                    {
                        log_message('debug', 'GG --- ncert spsso ' . serialize(array_keys($v2)));
                        $ncert = new models\Certificate();
                        $ncert->setType('spsso');
                        $ncert->setCertType();
                        $ncert->setCertUse($v2['usage']);
                        $ent->setCertificate($ncert);
                        $ncert->setProvider($ent);
                        $ncert->setKeyname($v2['keyname']);
                        $ncert->setCertData($v2['certdata']);
                        $this->em->persist($ncert);
                    }
                }
                elseif ($k1 === 'idpsso' && $type !== 'SP')
                {
                    foreach ($v1 as $k2 => $v2)
                    {
                        log_message('debug', 'GG --- ncert idpsso ' . serialize(array_keys($v2)));
                        $ncert = new models\Certificate();
                        $ncert->setType('idpsso');
                        $ncert->setCertType();
                        $ncert->setCertUse($v2['usage']);
                        $ent->setCertificate($ncert);
                        $ncert->setProvider($ent);
                        $ncert->setKeyname($v2['keyname']);
                        $ncert->setCertData($v2['certdata']);
                        $this->em->persist($ncert);
                    }
                }
                elseif ($k1 === 'aa' && $type !== 'SP')
                {
                    foreach ($v1 as $k2 => $v2)
                    {
                        log_message('debug', 'GG --- ncert aa ' . serialize(array_keys($v2)));
                        $ncert = new models\Certificate();
                        $ncert->setType('aa');
                        $ncert->setCertType();
                        $ncert->setCertUse($v2['usage']);
                        $ent->setCertificate($ncert);
                        $ncert->setProvider($ent);
                        $ncert->setKeyname($v2['keyname']);
                        $ncert->setCertData($v2['certdata']);
                        $this->em->persist($ncert);
                    }
                }
            }
        }
        /**
         * END update certs
         */
        if (array_key_exists('contact', $ch) && !empty($ch['contact']) && is_array($ch['contact']))
        {
            $ncnt = $ch['contact'];
            $orgcnt = $ent->getContacts();
            foreach ($orgcnt as $v)
            {
                $i = $v->getId();
                if (array_key_exists($i, $ncnt))
                {
                    if (empty($ncnt['' . $i . '']['email']))
                    {
                        $ent->removeContact($v);
                        $this->em->remove($v);
                    }
                    else
                    {
                        $v->setType($ncnt['' . $i . '']['type']);
                        $v->setGivenname($ncnt['' . $i . '']['fname']);
                        $v->setSurname($ncnt['' . $i . '']['sname']);
                        $v->setEmail($ncnt['' . $i . '']['email']);
                        $this->em->persist($v);
                        unset($ncnt['' . $i . '']);
                    }
                }
            }
            foreach ($ncnt as $cc)
            {
                if (!empty($cc['email']) && !empty($cc['type']))
                {
                    $ncontact = new models\Contact();
                    $ncontact->setEmail($cc['email']);
                    $ncontact->setType($cc['type']);
                    $ncontact->setSurname($cc['sname']);
                    $ncontact->setGivenname($cc['fname']);
                    $ent->setContact($ncontact);
                    $ncontact->setProvider($ent);
                    $this->em->persist($ncontact);
                }
            }
        }

        /**
         * start update UII
         */
        if ($type !== 'SP')
        {
            $typeFilter = array('idp');
            $idpextend = $ent->getExtendMetadata()->filter(
                    function($entry) use ($typeFilter) {
                        return in_array($entry->getType(), $typeFilter);
                    });




            $doFilter = array('t' => array('idp'), 'n' => array('mdui'), 'e' => array('DisplayName', 'Description', 'InformationURL'));
            $e = $ent->getExtendMetadata()->filter(
                    function($entry) use ($doFilter) {
                        return in_array($entry->getType(), $doFilter['t']) && in_array($entry->getNamespace(), $doFilter['n']) && in_array($entry->getElement(), $doFilter['e']);
                    });
            $exarray = array();
            foreach ($e as $v)
            {
                $l = $v->getAttributes();
                if (isset($l['xml:lang']))
                {
                    $exarray['' . $v->getElement() . '']['' . $l['xml:lang'] . ''] = $v;
                }
                else
                {
                    log_message('error', 'ExentedMetadata element with id:' . $v->getId() . ' doesnt contains xml:lang attr');
                }
            }
            $mduiel = array('displayname' => 'DisplayName', 'desc' => 'Description', 'helpdesk' => 'InformationURL');
            foreach ($mduiel as $elkey => $elvalue)
            {
                if (isset($ch['uii']['idpsso']['' . $elkey . '']) && is_array($ch['uii']['idpsso']['' . $elkey . '']))
                {
                    foreach ($ch['uii']['idpsso']['' . $elkey . ''] as $key3 => $value3)
                    {

                        if (!isset($exarray['' . $elvalue . '']['' . $key3 . '']) && !empty($value3) && array_key_exists($key3, $langCodes))
                        {
                            $newelement = new models\ExtendMetadata;
                            $newelement->setParent($idpMDUIparent);
                            $newelement->setType('idp');
                            $newelement->setNamespace('mdui');
                            $newelement->setValue($value3);
                            $newelement->setElement($elvalue);
                            $newelement->setAttributes(array('xml:lang' => $key3));
                            $ent->setExtendMetadata($newelement);
                            $this->em->persist($newelement);
                        }
                        elseif (isset($exarray['' . $elvalue . '']['' . $key3 . '']))
                        {
                            if (empty($value3))
                            {
                                $exarray['' . $elvalue . '']['' . $key3 . '']->setProvider(NULL);
                                $ent->getExtendMetadata()->removeElement($exarray['' . $elvalue . '']['' . $key3 . '']);
                                $this->em->remove($exarray['' . $elvalue . '']['' . $key3 . '']);
                            }
                            else
                            {
                                $exarray['' . $elvalue . '']['' . $key3 . '']->setValue($value3);
                                $this->em->persist($exarray['' . $elvalue . '']['' . $key3 . '']);
                            }
                        }
                    }
                }
            }
        }
        if ($type !== 'IDP')
        {
            $typeFilter = array('sp');
            $spextend = $ent->getExtendMetadata()->filter(
                    function($entry) use ($typeFilter) {
                        return in_array($entry->getType(), $typeFilter);
                    });
            $doFilter = array('t' => array('sp'), 'n' => array('mdui'), 'e' => array('DisplayName', 'Description', 'InformationURL'));
            $e = $ent->getExtendMetadata()->filter(
                    function($entry) use ($doFilter) {
                        return in_array($entry->getType(), $doFilter['t']) && in_array($entry->getNamespace(), $doFilter['n']) && in_array($entry->getElement(), $doFilter['e']);
                    });
            $exarray = array();
            foreach ($e as $v)
            {
                $l = $v->getAttributes();
                if (isset($l['xml:lang']))
                {
                    $exarray['' . $v->getElement() . '']['' . $l['xml:lang'] . ''] = $v;
                }
                else
                {
                    log_message('error', 'ExentedMetadata element with id:' . $v->getId() . ' doesnt contains xml:lang attr');
                }
            }
            $mduiel = array('displayname' => 'DisplayName', 'desc' => 'Description', 'helpdesk' => 'InformationURL');
            foreach ($mduiel as $elkey => $elvalue)
            {
                if (isset($ch['uii']['spsso']['' . $elkey . '']) && is_array($ch['uii']['spsso']['' . $elkey . '']))
                {
                    foreach ($ch['uii']['spsso']['' . $elkey . ''] as $key3 => $value3)
                    {

                        if (!isset($exarray['' . $elvalue . '']['' . $key3 . '']) && !empty($value3) && array_key_exists($key3, $langCodes))
                        {
                            $newelement = new models\ExtendMetadata;
                            $newelement->setParent($spMDUIparent);
                            $newelement->setType('sp');
                            $newelement->setNamespace('mdui');
                            $newelement->setValue($value3);
                            $newelement->setElement($elvalue);
                            $newelement->setAttributes(array('xml:lang' => $key3));
                            $ent->setExtendMetadata($newelement);
                            $this->em->persist($newelement);
                        }
                        elseif (isset($exarray['' . $elvalue . '']['' . $key3 . '']))
                        {
                            if (empty($value3))
                            {
                                $exarray['' . $elvalue . '']['' . $key3 . '']->setProvider(NULL);
                                $ent->getExtendMetadata()->removeElement($exarray['' . $elvalue . '']['' . $key3 . '']);
                                $this->em->remove($exarray['' . $elvalue . '']['' . $key3 . '']);
                            }
                            else
                            {
                                $exarray['' . $elvalue . '']['' . $key3 . '']->setValue($value3);
                                $this->em->persist($exarray['' . $elvalue . '']['' . $key3 . '']);
                            }
                        }
                    }
                }
            }
        }
        /**
         * end update UII
         */
        if(!array_key_exists('usestatic',$ch))
        {
            $ent->setStatic(false);
        }
        if(array_key_exists('static',$ch))
        {
            $exmeta = $ent->getStaticMetadata();
            if(empty($exmeta))
            {
               $exmeta = new models\StaticMetadata;
            }
            $exmeta->setMetadata($ch['static']);
            $exmeta->setProvider($ent);
            $ent->setStaticMetadata($exmeta);
            $this->em->persist($exmeta);

            $exmetaAfter = $ent->getStaticMetadata();
            if(!empty($exmetaAfter))
            {
                  if(array_key_exists('usestatic',$ch) && ($ch['usestatic'] === 'accept'))
                  {
                       $ent->setStatic(true);
                  }

            }
            
            
       

        }

        if(array_key_exists('use_static',$ch) && $ch['usestatic'] === 'accept')
        {
         


        }
        
        if(count($m)>0)
        {        
           $this->ci->tracker->save_track('ent', 'modification', $ent->getEntityId(),serialize($m),FALSE);
        }
        return TRUE;
    }
   

}
