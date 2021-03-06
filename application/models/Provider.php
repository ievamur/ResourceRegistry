<?php

namespace models;

use \Doctrine\Common\Collections\ArrayCollection;

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
 * Provider Class
 * 
 * @package     RR3
 * @subpackage  Models
 * @author      Janusz Ulanowski <janusz.ulanowski@heanet.ie>
 */

/**
 * Provider Model
 *
 * This model for Identity and Service Providers definitions
 * 
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="provider",indexes={@Index(name="type_idx", columns={"type"}),@Index(name="pname_idx", columns={"name"}),@Index(name="islocal_idx", columns={"is_local"})})
 * @author janusz
 */
class Provider {

    protected $em;
    protected $logo_url;

    /**
     * @Id
     * @Column(type="bigint", nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string", length=255, nullable=true, unique=false)
     * it got from OrganizationName
     */
    protected $name;

    /**
     * @Column(type="text", nullable=true, unique=false)
     * it got from OrganizationName localized, serialized
     */
    protected $lname;

    /**
     * @Column(type="string", length=255,nullable=true, unique=false)
     * it got from OrganizationDisplayName
     */
    protected $displayname;

    /**
     * @Column(type="text",nullable=true, unique=false)
     * it got from OrganizationDisplayName localized
     */
    protected $ldisplayname;

    /**
     * @Column(type="string", length=128, nullable=false, unique=true)
     */
    protected $entityid;

    /**
     * obsolete - will be removed in the future
     * @Column(type="array",nullable=true)
     */
    protected $nameidformat;

    /**
     * new - replacing nameidformat
     * @Column(type="text",nullable=true)
     */
    protected $nameids;

    /**
     * obsolete
     * array of all values from protocolSupportEnumeration in IDP/SP SSODescription
     * @Column(type="array",nullable=true)
     */
    protected $protocol;

    /**
     * new of all values from protocolSupportEnumeration in idpsso,spsso,aa 
     * @Column(type="text",nullable=true)
     */
    protected $protocolsupport;

    /**
     * type - IDP,SP,BOTH
     * @Column(type="string", length=5, nullable=true)
     */
    protected $type;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $scope;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $homeurl;

    /**
     * helpdeskurl is used in metadata, it can be http(s) or mailto
     * @Column(type="string", length=255, nullable=true)
     */
    protected $helpdeskurl;

    /**
     * licalized lhelpdeskurl is used in metadata, it can be http(s) or mailto
     * @Column(type="text", nullable=true)
     */
    protected $lhelpdeskurl;

    /**
     * privacyurl is used in metadata as mdui:PrivacyStatementURL
     * @Column(type="string", length=255, nullable=true)
     */
    protected $privacyurl;

    /**
     * lprivacyurl is used in metadata as mdui:PrivacyStatementURL - localized
     * @Column(type="text", nullable=true)
     */
    protected $lprivacyurl;

    /**
     * @ManyToOne(targetEntity="Coc",inversedBy="provider")
     */
    protected $coc;

    /**
     * registrar is used in metadata for registrationAuthority in mdrpi:RegistrationInfo
     * @Column(type="string", length=255, nullable=true)
     */
    protected $registrar;

    /**
     * registerdate is used in metadata for registrationInstant
     * @Column(type="datetime",nullable=true)
     */
    protected $registerdate;

    /**
     * regpolicy is used in metadata for RegistrationPolicy
     * @Column(type="text",nullable=true)
     */
    protected $regpolicy;

    /**
     * @Column(type="datetime",nullable=true)
     */
    protected $validfrom;

    /**
     * @Column(type="datetime",nullable=true)
     */
    protected $validto;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $description;

    /**
     * localized description
     * @Column(type="text",nullable=true)
     */
    protected $ldescription;

    /**
     * @Column(type="string", length=2, nullable=true)
     */
    protected $country;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $wayflist;

    /**
     * serialized array containing entities to be escluded from ARP
     * @Column(type="text",nullable=true)
     */
    protected $excarps;

    /**
     * not used for the moment and default true
     * @Column(type="boolean")
     */
    protected $is_approved;

    /**
     * @Column(type="boolean")
     */
    protected $is_active;

    /**
     * @Column(type="boolean")
     */
    protected $is_locked;

    /**
     * if set then use static metadata
     *
     * @Column(type="boolean")
     */
    protected $is_static;

    /**
     * if true then it's not external entity
     * @Column(type="boolean")
     */
    protected $is_local;

    /**
     * it can be member of many federations
     * @ManyToMany(targetEntity="Federation", inversedBy="members")
     * @JoinTable(name="federation_members" )
     */
    protected $federations;

    /**
     * it can be member of many federations
     * @OneToMany(targetEntity="Contact", mappedBy="provider", cascade={"persist", "remove"})
     */
    protected $contacts;

    /**
     * it can be member of many federations
     *
     * @OneToMany(targetEntity="Certificate", mappedBy="provider", cascade={"persist", "remove"})
     */
    protected $certificates;

    /**
     * @OneToOne(targetEntity="Provider")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @OneToMany(targetEntity="ServiceLocation", mappedBy="provider", cascade={"persist", "remove"})
     */
    protected $serviceLocations;

    /**
     * @OneToMany(targetEntity="AttributeReleasePolicy", mappedBy="idp", cascade={"persist", "remove"})
     */
    protected $attributeReleaseIDP;

    /**
     * @OneToMany(targetEntity="AttributeRequirement", mappedBy="sp_id",cascade={"persist", "remove"})
     */
    protected $attributeRequirement;

    /**
     * @OneToOne(targetEntity="StaticMetadata", mappedBy="provider",cascade={"persist", "remove"})
     */
    protected $metadata;

    /**
     * @OneToMany(targetEntity="ExtendMetadata", mappedBy="provider",cascade={"persist", "remove"})
     */
    protected $extend;

    /**
     * @Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {

        $this->federations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->certificates = new \Doctrine\Common\Collections\ArrayCollection();
        $this->serviceLocations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->nameidformat = new \Doctrine\Common\Collections\ArrayCollection();
        $this->protocol = new \Doctrine\Common\Collections\ArrayCollection();
        $this->federations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->extend = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributeRequirement = new \Doctrine\Common\Collections\ArrayCollection();
        $this->updatedAt = new \DateTime("now");
        $this->is_approved = TRUE;
        $this->is_locked = FALSE;

        $this->ci = & get_instance();
        $this->em = $this->ci->doctrine->em;
    }

    public function diffProviderToArray($provider)
    {
        $differ = array();
        if ($provider->getName() != $this->getName())
        {
            $differ['Name']['before'] = $provider->getName();
            $differ['Name']['after'] = $this->getName();
        }
        if ($provider->getDisplayName() != $this->getDisplayName())
        {
            $differ['Displayname']['before'] = $provider->getDisplayName();
            $differ['Displayname']['after'] = $this->getDisplayName();
        }
        if ($provider->getHomeUrl() != $this->getHomeUrl())
        {
            $differ['Home URL']['before'] = $provider->getHomeUrl();
            $differ['Home URL']['after'] = $this->getHomeUrl();
        }
        if ($provider->getHelpdeskUrl() != $this->getHelpdeskUrl())
        {
            $differ['Helpdesk URL']['before'] = $provider->getHelpdeskUrl();
            $differ['Helpdesk URL']['after'] = $this->getHelpdeskUrl();
        }
        if ($provider->getPrivacyUrl() != $this->getPrivacyUrl())
        {
            $differ['PrivacyStatement URL']['before'] = $provider->getPrivacyUrl();
            $differ['ProvideStatement URL']['after'] = $this->getPrivacyUrl();
        }
        if ($provider->getRegistrationAuthority() != $this->getRegistrationAuthority())
        {
            $differ['Registration Authority']['before'] = $provider->getRegistrationAuthority();
            $differ['Registration Authority']['after'] = $this->getRegistrationAuthority();
        }
        if ($provider->getRegistrationDate() != $this->getRegistrationDate())
        {
            $rgbefore = $provider->getRegistrationDate();
            if (!empty($rgbefore))
            {
                $differ['Registration Date']['before'] = $rgbefore->format('Y-m-d');
            }
            else
            {
                $differ['Registration Date']['before'] = null;
            }
            $rgafter = $this->getRegistrationDate();
            if (!empty($rgafter))
            {
                $rgafter = $this->getRegistrationDate();
                $differ['Registration Date']['after'] = $rgafter->format('Y-m-d');
            }
            else
            {
                $differ['Registration Date']['after'] = null;
            }
        }

        if ($provider->getEntityId() != $this->getEntityId())
        {
            $differ['EntityID']['before'] = $provider->getEntityId();
            $differ['EntityID']['after'] = $this->getEntityId();
        }
        if (serialize($provider->getScope('idpsso')) != serialize($this->getScope('idpsso')))
        {
            $differ['Scope']['before'] = implode(',',$provider->getScope('idpsso'));
            $differ['Scope']['after'] = implode(',',$this->getScope('idpsso'));
        }
        $nameids_before = $provider->getNameIdToArray();
        $nameids_after = $this->getNameIdToArray();
        if ($nameids_before != $nameids_after)
        {
            $differ['nameids']['before'] = implode(', ', $nameids_before);
            $differ['nameids']['after'] = implode(', ', $nameids_after);
        }



        if ($provider->getCountry() != $this->getCountry())
        {
            $differ['Country']['before'] = $provider->getCountry();
            $differ['Country']['after'] = $this->getCountry();
        }

        $tmp_provider_validto = $provider->getValidTo();
        $tmp_this_validto = $this->getValidTo();
        if (!empty($tmp_provider_validto))
        {
            $validto_before = $provider->getValidTo()->format('Y-m-d');
        }
        else
        {
            $validto_before = '';
        }
        if (!empty($tmp_this_validto))
        {
            $validto_after = $this->getValidTo()->format('Y-m-d');
        }
        else
        {
            $validto_after = '';
        }

        if ($validto_before != $validto_after)
        {
            $differ['ValidTo']['before'] = $validto_before;
            $differ['ValidTo']['after'] = $validto_after;
        }
        $tmp_provider_validfrom = $provider->getValidFrom();
        $tmp_this_validfrom = $this->getValidFrom();
        if (!empty($tmp_provider_validfrom))
        {
            $validfrom_before = $provider->getValidFrom()->format('Y-m-d');
        }
        else
        {
            $validfrom_before = '';
        }
        if (!empty($tmp_this_validfrom))
        {
            $validfrom_after = $this->getValidFrom()->format('Y-m-d');
        }
        else
        {
            $validfrom_after = '';
        }
        if ($validfrom_before != $validfrom_after)
        {
            $differ['ValidFrom']['before'] = $validfrom_before;
            $differ['ValidFrom']['after'] = $validfrom_after;
            ;
        }
        if ($provider->getType() != $this->getType())
        {
            $differ['Type']['before'] = $provider->getType();
            $differ['Type']['after'] = $this->getType();
        }
        if ($provider->getActive() != $this->getActive())
        {
            $differ['Active']['before'] = $provider->getActive();
            $differ['Active']['after'] = $this->getActive();
        }
        if ($provider->getLocked() != $this->getLocked())
        {
            $differ['Locked']['before'] = $provider->getLocked();
            $differ['Locked']['after'] = $this->getLocked();
        }
        /**
         *  compare localized names
         */
        $ldisplayname_before = $provider->getLocalDisplayName();
        if ($ldisplayname_before == NULL)
        {
            $ldisplayname_before = array();
        }
        $ldisplayname_after = $this->getLocalDisplayName();
        if ($ldisplayname_after == NULL)
        {
            $ldisplayname_after = array();
        }
        $ldisplayname_diff1 = array_diff_assoc($ldisplayname_before, $ldisplayname_after);
        $ldisplayname_diff2 = array_diff_assoc($ldisplayname_after, $ldisplayname_before);
        if (count($ldisplayname_diff1) > 0 or count($ldisplayname_diff2) > 0)
        {
            $tmpstr = '';
            foreach ($ldisplayname_diff1 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['DisplayNameLocalized']['before'] = $tmpstr;
            $tmpstr = '';
            foreach ($ldisplayname_diff2 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['DisplayNameLocalized']['after'] = $tmpstr;
        }







        $lname_before = $provider->getLocalName();
        if ($lname_before == NULL)
        {
            $lname_before = array();
        }
        $lname_after = $this->getLocalName();
        if ($lname_after == NULL)
        {
            $lname_after = array();
        }
        $lname_diff1 = array_diff_assoc($lname_before, $lname_after);
        $lname_diff2 = array_diff_assoc($lname_after, $lname_before);
        if (count($lname_diff1) > 0 or count($lname_diff2) > 0)
        {
            $tmpstr = '';
            foreach ($lname_diff1 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['NameLocalized']['before'] = $tmpstr;
            $tmpstr = '';
            foreach ($lname_diff2 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['NameLocalized']['after'] = $tmpstr;
        }




        $lname_before = $provider->getLocalHelpdeskUrl();
        if ($lname_before == NULL)
        {
            $lname_before = array();
        }
        $lname_after = $this->getLocalHelpdeskUrl();
        if ($lname_after == NULL)
        {
            $lname_after = array();
        }
        $lname_diff1 = array_diff_assoc($lname_before, $lname_after);
        $lname_diff2 = array_diff_assoc($lname_after, $lname_before);
        if (count($lname_diff1) > 0 or count($lname_diff2) > 0)
        {
            $tmpstr = '';
            foreach ($lname_diff1 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['HelpdeskURLLocalized']['before'] = $tmpstr;
            $tmpstr = '';
            foreach ($lname_diff2 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['HelpdeskURLLocalized']['after'] = $tmpstr;
        }


        $lname_before = $provider->getLocalPrivacyUrl();
        if ($lname_before == NULL)
        {
            $lname_before = array();
        }
        $lname_after = $this->getLocalPrivacyUrl();
        if ($lname_after == NULL)
        {
            $lname_after = array();
        }
        $lname_diff1 = array_diff_assoc($lname_before, $lname_after);
        $lname_diff2 = array_diff_assoc($lname_after, $lname_before);
        if (count($lname_diff1) > 0 or count($lname_diff2) > 0)
        {
            $tmpstr = '';
            foreach ($lname_diff1 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['PrivacyStatementURLLocalized']['before'] = $tmpstr;
            $tmpstr = '';
            foreach ($lname_diff2 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['PrivacyStatementURLLocalized']['after'] = $tmpstr;
        }

        $lname_before = $provider->getLocalDescription();
        if ($lname_before == NULL)
        {
            $lname_before = array();
        }
        $lname_after = $this->getLocalDescription();
        if ($lname_after == NULL)
        {
            $lname_after = array();
        }
        $lname_diff1 = array_diff_assoc($lname_before, $lname_after);
        $lname_diff2 = array_diff_assoc($lname_after, $lname_before);
        if (count($lname_diff1) > 0 or count($lname_diff2) > 0)
        {
            $tmpstr = '';
            foreach ($lname_diff1 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['DescriptionLocalized']['before'] = $tmpstr;
            $tmpstr = '';
            foreach ($lname_diff2 as $k => $v)
            {
                $tmpstr .= $k . ':' . htmlentities($v) . '<br />';
            }
            $differ['DescriptionLocalized']['after'] = $tmpstr;
        }


        return $differ;
    }

    public function __toString()
    {
        return $this->entityid;
    }

    /**
     * @prePersist 
     */
    public function created()
    {
        $this->createdAt = new \DateTime("now");
        if (empty($this->nameidformat))
        {
            $this->setNameId();
        }
        if (empty($this->displayname))
        {
            $this->displayname = $this->getName();
        }
    }

    /**
     * @PostPersist
     */
    public function createAclResource()
    {
        $this->ci = &get_instance();
        $this->em = $this->ci->doctrine->em;
        $is_local = $this->is_local;
        if ($is_local)
        {
            $rescheck = $this->em->getRepository("models\AclResource")->findOneBy(array('resource' => $this->id));
            if (!empty($rescheck))
            {
                return true;
            }
            $parent = array();

            $parents = $this->em->getRepository("models\AclResource")->findBy(array('resource' => array('idp', 'sp', 'entity')));
            foreach ($parents as $p)
            {
                $parent[$p->getResource()] = $p;
            }
            $stype = $this->type;
            if ($stype === 'BOTH')
            {
                $types = array('entity');
            }
            elseif ($stype === 'IDP')
            {
                $types = array('idp');
            }
            else
            {
                $types = array('sp');
            }
            foreach ($types as $key)
            {
                $r = new AclResource;
                $resource_name = $this->id;
                $r->setResource($resource_name);
                $r->setDefaultValue('view');
                $r->setType('entity');
                if (array_key_exists($key, $parent))
                {
                    $r->setParent($parent[$key]);
                }
                $this->em->persist($r);
            }
            $this->em->flush();
        }
        log_message('debug', 'entity:' . $this->id . ' ::' . $this->type);
    }

    /**
     * @preRemove 
     */
    public function unsetOwner()
    {
        
    }

    /**
     * @PostRemove
     */
    public function removeRequester()
    {
        log_message('debug', 'Provider removed, not its time to remove all entries with that requester');
    }

    /**
     * @PreUpdate
     */
    public function updated()
    {
        \log_message('debug', 'GG update providers updated time');
        $this->updatedAt = new \DateTime("now");
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setLocalName(array $name = NULL)
    {
        if (!empty($name))
        {
            $this->lname = serialize($name);
        }
        else
        {
            $this->lname = NULL;
        }
    }

    public function setDisplayName($name)
    {
        $this->displayname = $name;
        return $this;
    }

    public function setCoc($coc = NULL)
    {
        if (empty($coc))
        {
            $this->coc = NULL;
        }
        else
        {
            $this->coc = $coc;
        }
        return $this;
    }

    public function setLocalDisplayName($name = NULL)
    {
        if (!empty($name) && is_array($name))
        {
            $this->ldisplayname = serialize($name);
        }
        else
        {
            $this->ldisplayname = NULL;
        }
    }
    public function setRegistrationPolicyFromArray($regarray, $reset = FALSE)
    {
         
         if($reset === TRUE)
         {
             $this->regpolicy = serialize($regarray);
         }
         else
         {
             $s = $this->getRegistrationPolicy();
             $n = array_merge($s,$regarray);
             $this->regpolicy = serialize($n);
    
         }
         return $this; 

    }
    public function setRegistrationPolicy($lang, $url)
    {
        $s = $this->getRegistrationPolicy();
        $s[''.$lang.''] = $url;
        $this->regpolicy = serialize($s);
        return $this;
    }
    public function resetRegistrationPolicy()
    {
         $this->regpolicy = serialize(array());
         return $this;
    }
    /**
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }
    */
    /**
     * type : idpsso, aa
     * $scope: array();
     */
    public function setScope($type,$scope)
    {
        $ex = @unserialize($this->scope);
        if($ex === 'b:0;' || $ex !== false)
        {
            $ex[''.$type.''] = $scope;
        }
        else
        {
            $ex = array();
            $ex[''.$type.''] = $scope;
        }
        $this->scope = serialize($ex);
        return $this;
    }

    public function overwriteScope($n, $provider)
    {
        $this->setScope($n, $provider->getScope($n));
        return $this;
    }

    public function setEntityId($entity)
    {
        $entity = trim($entity);
        if (!empty($entity))
        {
            $this->entityid = $entity;
            return $this;
        }
        else
        {
            return false;
        }
    }

    public function setCountry($country = null)
    {
        if (!empty($country))
        {
            $this->country = $country;
        }
    }

    /**
     * obsolete
     */
    public function resetNameId()
    {
        $this->nameidformat = new \Doctrine\Common\Collections\ArrayCollection();
        return $this;
    }

    /**
     * obsolete
     */
    public function setNameId($nameid = NULL)
    {
        if (empty($nameid))
        {
            $nameid = "urn:oasis:names:tc:SAML:2.0:nameid-format:transient";
        }
        //$this->nameidformat = $nameid;
        if (empty($this->nameidformat))
        {
            $this->nameidformat = new \Doctrine\Common\Collections\ArrayCollection();
        }
        $this->nameidformat->add($nameid);
        return $this;
    }

    /**
     * new
     */
    public function setNameIds($n, $data)
    {
        $t = $this->getNameIds();
        $t['' . $n . ''] = $data;
        $this->nameids = serialize($t);
        return $this;
    }

    public function resetProtocol()
    {
        $this->protocol = new \Doctrine\Common\Collections\ArrayCollection();
        return $this;
    }

    public function setProtocol($protocol = NULL)
    {
        if (empty($protocol))
        {
            $protocol = "urn:oasis:names:tc:SAML:2.0:protocol";
        }
        if (empty($this->protocol))
        {
            $this->protocol = new \Doctrine\Common\Collections\ArrayCollection();
        }
        //$this->getProtocol()->add($protocol);
        $this->protocol->add($protocol);
        return $this;
    }

    public function setProtocolSupport($n, $data)
    {
        $allowed = array('aa', 'idpsso', 'spsso');
        if (in_array($n, $allowed) && is_array($data))
        {
            $r = $this->getProtocolSupport();
            $r['' . $n . ''] = $data;
            $this->protocolsupport = serialize($r);
        }
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * setting entity as SP 
     */
    public function setSP()
    {
        $this->type = 'SP';
        return $this;
    }

    public function setAsSP()
    {
        $this->type = 'SP';
        return $this;
    }

    /**
     * setting entity as IDP
     */
    public function setIDP()
    {
        $this->type = 'IDP';
        return $this;
    }

    public function setAsIDP()
    {
        $this->type = 'IDP';
        return $this;
    }

    public function setAsBoth()
    {
        $this->type = 'BOTH';
        return $this;
    }

    public function setHelpdeskUrl($url)
    {
        $this->helpdeskurl = $url;
        return $this;
    }

    public function setLocalHelpdeskUrl(array $urls = NULL)
    {
        if (!empty($urls))
        {
            $this->lhelpdeskurl = serialize($urls);
        }
        else
        {
            $this->lhelpdeskurl = NULL;
        }
    }

    /**
     * set homeurl
     */
    public function setHomeUrl($url)
    {
        $this->homeurl = $url;
        return $this;
    }

    public function setPrivacyUrl($url = null)
    {
        $this->privacyurl = $url;
    }

    public function setLocalPrivacyUrl(array $url = null)
    {
        if (!empty($url))
        {
            $this->lprivacyurl = serialize($url);
        }
        else
        {
            $this->lprivacyurl = NULL;
        }
    }

    public function setRegistrationAuthority($reg = null)
    {
        $this->registrar = $reg;
        return $this;
    }

    public function setRegistrationDate($date = null)
    {
        $this->registerdate = $date;
        return $this;
    }

    /**
     * set time entity is valid to, if null then current time
     */
    public function setValidTo($date = NULL)
    {
        if (empty($date))
        {
            $this->validto = NULL;
        }
        else
        {
            // $date->setTime(23, 59, 59);
            $this->validto = $date;
        }
        return $this;
    }

    public function setValidFrom($date = NULL)
    {
        if (empty($date))
        {
            $this->validfrom = NULL;
        }
        else
        {
            //$date->setTime(00, 01, 00);
            $this->validfrom = $date;
        }
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setLocalDescription($descriptions = NULL)
    {
        if (!empty($descriptions) && is_array($descriptions))
        {
            $this->ldescription = serialize($descriptions);
        }
        else
        {
            $this->ldescription = NULL;
        }
    }

    /**
     * updateLocalizedMdui1 for elements: Description, DisplayName, PrivacyURL, InformationURL
     */
    public function updateLocalizedMdui1($elementName, $descriptions, $type)
    {
        $this->ci = &get_instance();
        $this->em = $this->ci->doctrine->em;
        $ex = $this->getExtendMetadata();
        $parent = null;
        foreach ($ex as $e)
        {
            if (!empty($parent))
            {
                break;
            }
            else
            {
                if (empty($p) && $e->getType() === $type && $e->getNameSpace() === 'mdui' && $e->getElement() === 'UIInfo')
                {
                    $parent = $e;
                }
            }
        }
        foreach ($ex as $e)
        {
            if ($e->getElement() === $elementName && $e->getType() === $type && $e->getNameSpace() === 'mdui')
            {
                $value = $e->getElementValue();
                $t = $e->getAttributes();
                $lvalue = $t['xml:lang'];
                if (array_key_exists($lvalue, $descriptions))
                {
                    if (!empty($descriptions[$lvalue]))
                    {
                        $e->setValue($descriptions[$lvalue]);
                    }
                    else
                    {
                        $ex->removeElement($e);
                        $this->em->remove($e);
                    }
                    unset($descriptions[$lvalue]);
                }
                else
                {
                    $ex->removeElement($e);
                    $this->em->remove($e);
                }
            }
        }
        if (count($descriptions) > 0)
        {
            foreach ($descriptions as $k => $v)
            {
                $nelement = new ExtendMetadata();
                $nelement->setType($type);
                $nelement->setNameSpace('mdui');
                $nelement->setElement($elementName);
                $nelement->setValue($v);
                $attr = array('xml:lang' => $k);
                $nelement->setAttributes($attr);
                if (empty($parent))
                {
                    $parent = new ExtendMetadata();
                    $parent->setType($type);
                    $parent->setNameSpace('mdui');
                    $parent->setElement('UIInfo');
                    $ex->add($parent);
                    $parent->setProvider($this);
                    $this->em->persist($parent);
                }
                $nelement->setParent($parent);
                $ex->add($nelement);
                $nelement->setProvider($this);
                $this->em->persist($nelement);
            }
        }
    }

    public function setWayfList($wayflist = null)
    {
        if (!empty($wayflist) && is_array($wayflist))
        {
            $this->wayflist = serialize($wayflist);
        }
    }

    public function setExcarps($excarps = null)
    {
        if (!empty($excarps) && is_array($excarps) && count($excarps) > 0)
        {
            $this->excarps = serialize($excarps);
        }
        else
        {
            $this->excarps = null;
        }
    }

    public function setDefaultState()
    {
        $this->is_approved = 1;
        $this->is_active = 1;
        $this->is_locked = 0;
        $this->is_static = 0;
        $this->is_local = 1;
        $this->setValidFrom();
        $this->setValidTo();
        return $this;
    }

    public function setLocal($is_local)
    {
        if ($is_local)
        {
            $this->is_local = true;
        }
        else
        {
            $this->is_local = false;
        }
        return $this;
    }

    public function setAsLocal()
    {
        $this->is_local = 1;
    }

    public function setAsExternal()
    {
        $this->is_local = 0;
    }

    public function setActive($val = NULL)
    {
        if (!empty($val))
        {
            $this->is_active = 1;
        }
        else
        {
            $this->is_active = 0;
        }
        return $this;
    }

    public function Disactivate()
    {
        $this->is_active = 0;
    }

    public function Activate()
    {
        $this->is_active = 1;
    }

    public function Lock()
    {
        $this->is_locked = 1;
    }

    public function Unlock()
    {
        $this->is_locked = 0;
    }

    public function setApproved($val = NULL)
    {
        if (!empty($val))
        {
            $this->is_approved = 1;
        }
        else
        {
            $this->is_approved = 0;
        }
        return $this;
    }

    public function setFederation(Federation $federation)
    {
        $already_there = $this->getFederations()->contains($federation);
        if (empty($already_there))
        {
            $this->getFederations()->add($federation);
        }
        return $this->federations;
    }

    public function removeFederation(Federation $federation)
    {
        $this->getFederations()->removeElement($federation);
        $federation->getMembers()->removeElement($this);
        return $this->federations;
    }

    public function setServiceLocation(ServiceLocation $service)
    {
        $this->getServiceLocations()->add($service);
        $service->setProvider($this);
        return $this->serviceLocations;
    }

    public function setExtendMetadata(ExtendMetadata $ext)
    {
        $this->getExtendMetadata()->add($ext);
        $ext->setProvider($this);
        return $this->extend;
    }

    public function removeServiceLocation(ServiceLocation $service)
    {
        $this->ci = & get_instance();
        $this->em = $this->ci->doctrine->em;
        $this->getServiceLocations()->removeElement($service);
        $this->em->remove($service);
        return $this->serviceLocations;
    }

    public function setStatic($static)
    {
        if ($static === true)
        {
            $this->is_static = true;
        }
        else
        {
            $this->is_static = false;
        }
        return $this;
    }

    public function setStaticMetadata(StaticMetadata $metadata)
    {
        $this->metadata = $metadata;
        $metadata->setProvider($this);

        return $this;
    }

    public function overwriteStaticMetadata(StaticMetadata $metadata = null)
    {
        $m = $this->getStaticMetadata();
        if (!empty($m))
        {
            $m->setMetadata($metadata->getMetadata());
        }
        else
        {
            $this->setStaticMetadata($metadata);
        }
        return $this;
    }

    public function setAttributesRequirement(AttributeRequirement $attribute)
    {
        $this->getAttributesRequirement()->add($attribute);
        return $this;
    }

    public function setContact(Contact $contact)
    {
        $this->getContacts()->add($contact);
        $contact->setProvider($this);
        return $this->contacts;
    }

    public function removeCertificate(Certificate $certificate)
    {
        $this->getCertificates()->removeElement($certificate);
        $certificate->unsetProvider($this);
        return $this->certificates;
    }

    public function removeContact(Contact $contact)
    {
        $this->ci = & get_instance();
        $this->em = $this->ci->doctrine->em;
        $this->getContacts()->removeElement($contact);
        $this->em->remove($contact);
        return $this->contacts;
    }

    public function removeAllContacts()
    {
        $this->ci = & get_instance();
        $this->em = $this->ci->doctrine->em;
        $contacts = $this->getContacts();
        foreach ($contacts->getValues() as $contact)
        {
            $contacts->removeElement($contact);
            $this->em->remove($contact);
        }
        return $this;
    }

    public function setCertificate(Certificate $certificate)
    {
        $this->getCertificates()->add($certificate);
        $certificate->setProvider($this);
        return $this->certificates;
    }

    /**
     * this object state will be overwriten by $provider object
     */
    public function overwriteByProvider(Provider $provider)
    {
        $this->ci = & get_instance();
        $this->em = $this->ci->doctrine->em;



        $this->setName($provider->getName());
        $this->setLocalName($provider->getLocalName());
        $this->setDisplayName($provider->getDisplayName());
        $this->setLocalDisplayName($provider->getLocalDisplayName());
        $this->setScope('idpsso',$provider->getScope('idpsso'));
        $this->setScope('aa',$provider->getScope('aa'));
        $this->setEntityId($provider->getEntityId());
        $this->setRegistrationAuthority($provider->getRegistrationAuthority());
        $this->setRegistrationDate($provider->getRegistrationDate());
        $this->setRegistrationPolicyFromArray($provider->getRegistrationPolicy(), TRUE);

        $this->overwriteWithNameid($provider);
        log_message('debug','GKS :'.serialize($this->getNameIds())); 

        $prototypes = array('idpsso','aa','spsso');
        foreach($prototypes as $a)
        {
            $this->setProtocolSupport($a, $provider->getProtocolSupport($a));
        }
        $this->setType($provider->getType());
        $this->setHelpdeskUrl($provider->getHelpdeskUrl());
        $homeurl = $provider->getHomeUrl();
        if (empty($homeurl))
        {
            $homeurl = $provider->getHelpdeskUrl();
        }
        $this->setHomeUrl($homeurl);
        $this->setValidFrom($provider->getValidFrom());
        $this->setValidTo($provider->getValidTo());
        $this->setDescription($provider->getDescription());
        $this->setLocalDescription($provider->getLocalDescription());
        $smetadata = $provider->getStaticMetadata();
        if (!empty($smetadata))
        {
            $this->overwriteStaticMetadata($smetadata);
        }
        foreach ($this->getServiceLocations() as $s)
        {
            $this->removeServiceLocation($s);
        }
        foreach ($provider->getServiceLocations() as $r)
        {
            $this->setServiceLocation($r);
            if (!$r->getOrder())
            {
                $r->setOrder(1);
            }
        }
        foreach ($this->getCertificates() as $c)
        {
            $this->removeCertificate($c);
        }
        foreach ($provider->getCertificates() as $r)
        {
            $this->setCertificate($r);
        }
        foreach ($this->getExtendMetadata() as $f)
        {
            if (!empty($f))
            {
                $this->removeExtendWithChildren($f);
            }
        }
        foreach ($provider->getExtendMetadata() as $gg)
        {
            $this->setExtendMetadata($gg);
        }
        return $this;
    }

    private function removeExtendWithChildren($e)
    {
        $this->ci = & get_instance();
        $this->em = $this->ci->doctrine->em;

        $children = $e->getChildren();
        if (!empty($children) && $children->count() > 0)
        {

            foreach ($children->getValues() as $c)
            {

                $this->removeExtendWithChildren($c);
            }
        }
        $this->getExtendMetadata()->removeElement($e);
        $this->em->remove($e);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRegistrationAuthority()
    {
        return $this->registrar;
    }

    public function getRegistrationDate()
    {
        return $this->registerdate;
    }

    /**
     * get collection of contacts which are used in metada
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    public function getCertificates()
    {
        return $this->certificates;
    }

    /**
     * obsolete
     */
    public function getNameIdToArray()
    {
        return $this->getNameId()->toArray();
    }

    /**
     * obsolete
     */
    public function getNameId()
    {
        return $this->nameidformat;
    }

    /**
     * new replacing getNameId()
     * $n one of : idpsso,spsso,aa
     */
    public function getNameIds($n = null)
    {
        if (empty($n))
        {
            if (!empty($this->nameids))
            {
                return unserialize($this->nameids);
            }
            else
            {
                return array();
            }
        }
        $default = array();
        if (!empty($this->nameids))
        {
            $r = unserialize($this->nameids);
            if (isset($r['' . $n . '']))
            {
                return $r['' . $n . ''];
            }
        }
        return $default;
    }

    public function getActive()
    {
        return $this->is_active;
    }

    public function getCoc()
    {
        return $this->coc;
    }

    public function getProtocol()
    {
        $col = new \Doctrine\Common\Collections\ArrayCollection();
        $tmp = $this->protocol;
        if (!empty($tmp))
        {
            return $this->protocol;
        }
        else
        {
            return $col;
        }
    }

    public function getProtocolSupport($n = null)
    {
        if (empty($n))
        {
            $t = $this->protocolsupport;
            if (empty($t))
            {
                return array();
            }
            else
            {
                return unserialize($t);
            }
        }
        $default = array('urn:oasis:names:tc:SAML:2.0:protocol');
        $t = $this->protocolsupport;
        if (!empty($t))
        {
            $r = unserialize($t);
            if (isset($r[$n]))
            {
                return $r[$n];
            }
        }
        return $default;
    }

    public function getRegistrationPolicy()
    {
        $s = @unserialize($this->regpolicy);
        if(empty($s))
        {
           return array();
        }
        return $s;
    }
    public function getScope($n)
    {
        $s = @unserialize($this->scope);
        if(isset($s[$n]))
        {
            return $s[$n];
        }
        else
        {
            return array();
        }
    }
    /**
     * used for convert strings to array
     */
    public function convertScope()
    {
        $s = $this->scope;
        if(!empty($s))
        {
           $s2 = @unserialize($s);
           if(empty($s2))
           {
              $y = explode(',',$this->scope);
              $z = array('idpsso'=>$y,'aa'=>$y);
              $this->scope=(serialize($z));
              return $this;
           }
        }
    }


    public function getAttributeReleasePolicies()
    {
        return $this->attributeReleaseIDP;
    }

    public function getServiceLocations()
    {
        return $this->serviceLocations;
    }

    public function getAttributesRequirement()
    {
        return $this->attributeRequirement;
    }

    public function getAttributesRequirementToArray_V1()
    {
        $result = array();
        $req = $this->getAttributesRequirement();
        foreach ($req as $r)
        {
            $result[$r->getAttribute()->getName()] = $r->getStatus();
        }
        return $result;
    }

    public function getFederations()
    {
        return $this->federations;
    }

    public function getFederationNames()
    {
        $feders = array();
        foreach ($this->federations as $entry)
        {
            $feder[] = $entry;
        }
        return $feder;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLocalName()
    {
        $p = unserialize($this->lname);
        if (empty($p))
        {
            return array();
        }
        else
        {
            return $p;
        }
    }

    public function getNameLocalized()
    {
        $t['en'] = $this->name;
        $p = unserialize($this->lname);
        if (is_array($p))
        {
            if (!array_key_exists('en', $p))
            {
                $p['en'] = $t['en'];
            }
        }
        else
        {
            $p = $t;
        }
        return $p;
    }

    public function getDisplayName($length = null)
    {
        if (empty($length) or !is_integer($length) or strlen($this->displayname) <= $length)
        {
            return $this->displayname;
        }
        else
        {
            return substr($this->displayname, 0, $length) . "...";
        }
    }

    public function getLocalDisplayName()
    {
        return unserialize($this->ldisplayname);
    }

    public function getLocalDisplayNamesToArray($type)
    {
        $result = array();
        $ex = $this->getExtendMetadata();
        foreach ($ex as $v)
        {
            if ($v->getType() == $type && $v->getNameSpace() == 'mdui' && $v->getElement() == 'DisplayName')
            {
                $l = $v->getAttributes();
                $result[$l['xml:lang']] = $v->getElementValue();
            }
        }
        return $result;
    }

    public function getDisplayNameLocalized()
    {
        $t['en'] = $this->displayname;
        $p = unserialize($this->ldisplayname);
        if (is_array($p))
        {
            if (!array_key_exists('en', $p))
            {
                $p['en'] = $t['en'];
            }
        }
        else
        {
            $p = $t;
        }
        return $p;
    }

    public function findOneSPbyName($name)
    {
        return $this->_em->createQuery('SELECT u FROM Models\Provider u WHERE name = "' . $name . '"')->getResult();
    }

    public function getValidTo()
    {
        return $this->validto;
    }

    public function getValidFrom()
    {
        return $this->validfrom;
    }

    /**
     * return boolean if entity is between validfrom and validto dates
     */
    public function getIsValidFromTo()
    {
        /**
         * @todo fix broken time for the momemnt reurns true
         */
        $currentTime = new \DateTime("now");
        $validAfter = TRUE;
        $validBefore = TRUE;
        if (!empty($this->validfrom))
        {

            if ($currentTime < $this->validfrom)
            {
                $validBefore = FALSE;
            }
        }
        if (!empty($this->validto))
        {
            if ($currentTime > $this->validto)
            {
                $validAfter = FALSE;
            }
        }

        return ($validAfter && $validBefore);
    }

    public function getEntityId()
    {
        return $this->entityid;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCountry()
    {
        return $this->country;
    }

    /*
     * return boolean if want to use static metadata
     */

    public function getStatic()
    {
        return $this->is_static;
    }

    /*
     * return static metadata body
     */

    public function getStaticMetadata()
    {
        return $this->metadata;
    }

    public function getExtendMetadata()
    {
        return $this->extend;
    }

    public function getIsStaticMetadata()
    {
        $c = $this->getStatic();
        $d = $this->getStaticMetadata();
        if ($c && !empty($d))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getHomeUrl()
    {
        return $this->homeurl;
    }

    public function getHelpdeskUrl()
    {
        return $this->helpdeskurl;
    }

    public function getLocalHelpdeskUrl()
    {
        return unserialize($this->lhelpdeskurl);
    }

    public function getHelpdeskUrlLocalized()
    {
        $t['en'] = $this->helpdeskurl;
        $p = unserialize($this->lhelpdeskurl);
        if (is_array($p))
        {
            if (!array_key_exists('en', $p))
            {
                $p['en'] = $t['en'];
            }
        }
        else
        {
            $p = $t;
        }
        return $p;
    }

    public function getPrivacyUrl()
    {
        return $this->privacyurl;
    }

    public function getLocalPrivacyUrl()
    {
        return unserialize($this->lprivacyurl);
    }

    public function getLocalPrivacyStatementsToArray($type)
    {
        $result = array();
        $ex = $this->getExtendMetadata();
        foreach ($ex as $v)
        {
            if ($v->getType() == $type && $v->getNameSpace() == 'mdui' && $v->getElement() == 'PrivacyStatementURL')
            {
                $l = $v->getAttributes();
                $result[$l['xml:lang']] = $v->getElementValue();
            }
        }
        return $result;
    }

    public function getPrivacyUrlLocalized()
    {
        $t['en'] = $this->privacyurl;
        $p = unserialize($this->lprivacyurl);
        if (is_array($p))
        {
            if (!array_key_exists('en', $p))
            {
                $p['en'] = $t['en'];
            }
        }
        else
        {
            $p = $t;
        }
        return $p;
    }

    public function getApproved()
    {
        return $this->is_approved;
    }

    public function getLocked()
    {

        return $this->is_locked;
    }

    public function getAvailable()
    {

        return ($this->is_active && $this->is_approved && $this->getIsValidFromTo());
    }

    public function getLocal()
    {
        return $this->is_local;
    }

    public function getLocalAvailable()
    {
        return ( $this->is_local && $this->is_active && $this->is_approved && $this->getIsValidFromTo());
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @todo to remove in 2.x 
     */
    public function getLocalDescription()
    {
        return unserialize($this->ldescription);
    }

    public function getLocalDescriptionsToArray($type)
    {
        $result = array();
        $ex = $this->getExtendMetadata();
        foreach ($ex as $v)
        {
            if ($v->getType() == $type && $v->getNameSpace() == 'mdui' && $v->getElement() == 'Description')
            {
                $l = $v->getAttributes();
                $result[$l['xml:lang']] = $v->getElementValue();
            }
        }
        return $result;
    }

    public function getDescriptionLocalized()
    {
        if (empty($this->description))
        {
            $t['en'] = 'description not provided';
        }
        else
        {
            $t['en'] = $this->description;
        }
        $p = unserialize($this->ldescription);
        if (is_array($p))
        {
            if (!array_key_exists('en', $p))
            {
                $p['en'] = $t['en'];
            }
        }
        else
        {
            $p = $t;
        }
        return $p;
    }

    /**
     * localized description into mdui
     */
    public function getExtendedDescription($type = NULL)
    {

        if (empty($type))
        {
            $type = strtolower($this->getType);
        }
        else
        {
            $type = strtolower($type);
        }
        $extends = $this->getExtendMetadata();
        if (!empty($extends))
            foreach ($extends as $e)
            {
                if (($e->getType() == $type) && ($e->getNamespace() == 'mdui') && ($e->getElement() == 'Description'))
                {
                    continue;
                }
                else
                {
                    $extends->removeElement($e);
                }
            }
        return $extends;
    }

    public function getMduiDiscoHintToXML(\DOMElement $parent, $type = NULL)
    {
        if (empty($type))
        {
            $type = strtolower($this->type);
        }
        $ext = $this->getExtendMetadata();
        $extarray = array();
        $e = NULL;
        foreach ($ext as $v)
        {
            if (($v->getType() === $type) && ($v->getNamespace() === 'mdui') && ($v->getElement() === 'GeolocationHint'))
            {
                $extarray[] = $v;
            }
        }
        if(count($extarray)>0)
        {
           $e = $parent->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:DiscoHints'); 
           foreach($extarray as $dm)
           {
               $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:GeolocationHint');
               $dnode->appendChild($e->ownerDocument->createTextNode($dm->getElementValue()));
               $e->appendChild($dnode);
           }
 
        }
        return $e;
       
    }

    /**
     * $type should be sp or idp
     */
    public function getMduiToXML(\DOMElement $parent, $type = NULL)
    {
        if (empty($type))
        {
            $type = strtolower($this->type);
        }
        $this->ci = & get_instance();
        $this->em = $this->ci->doctrine->em;
        $this->ci->load->helper('url');

        $ext = $this->getExtendMetadata();
        /**
         * leave only elements matching criteria
         */
        $extarray = array();
        foreach ($ext as $v)
        {
            if (($v->getType() === $type) && ($v->getNamespace() === 'mdui'))
            {
                $extarray[''.$v->getElement().''][] = $v;
            }
        }
        if (isset($extarray['Logo']) || array_key_exists('Logo', $extarray))
        {
            $this->logo_basepath = $this->ci->config->item('rr_logouriprefix');
            $this->logo_baseurl = $this->ci->config->item('rr_logobaseurl');

            if (empty($this->logo_baseurl))
            {
                $this->logo_baseurl = base_url();
            }
            $this->logo_url = $this->logo_baseurl . $this->logo_basepath;
        }

        $en_displayname = FALSE;
        $en_description = FALSE;
        $en_informationurl = FALSE;
        $en_privacyurl = FALSE;
        $e = $parent->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:UIInfo');

        foreach ($extarray as $key => $value)
        {
            if ($key === 'DisplayName')
            {
                foreach ($value as $dm)
                {
                    $lang = $dm->getAttributes();
                    if (isset($lang['xml:lang']))
                    {
                        $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:DisplayName');
                        $dnode->setAttribute('xml:lang', '' . $lang['xml:lang'] . '');
                        $dnode->appendChild($e->ownerDocument->createTextNode($dm->getElementValue()));
                        if ($lang['xml:lang'] == 'en')
                        {
                            $en_displayname = TRUE;
                        }
                        $e->appendChild($dnode);
                    }
                }
            }
            elseif ($key === 'Description')
            {
                foreach ($value as $dm)
                {
                    $lang = $dm->getAttributes();
                    if (isset($lang['xml:lang']))
                    {
                        $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:Description');
                        $dnode->setAttribute('xml:lang', '' . $lang['xml:lang'] . '');
                        $dnode->appendChild($e->ownerDocument->createTextNode($dm->getElementValue()));
                        if ($lang['xml:lang'] === 'en')
                        {
                            $en_description = TRUE;
                        }
                        $e->appendChild($dnode);
                    }
                }
            }
            elseif ($key === 'PrivacyStatementURL')
            {
                foreach ($value as $dm)
                {
                    $lang = $dm->getAttributes();
                    if (isset($lang['xml:lang']))
                    {
                        $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:PrivacyStatementURL', $dm->getElementValue());
                        $dnode->setAttribute('xml:lang', '' . $lang['xml:lang'] . '');
                        if ($lang['xml:lang'] === 'en')
                        {
                            $en_privacyurl = TRUE;
                        }
                        $e->appendChild($dnode);
                    }
                }
            }
            elseif ($key === 'InformationURL')
            {
                foreach ($value as $dm)
                {
                    $lang = $dm->getAttributes();
                    if (isset($lang['xml:lang']))
                    {
                        $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:InformationURL');
                        $dnode->appendChild($e->ownerDocument->createTextNode($dm->getElementValue()));
                        $dnode->setAttribute('xml:lang', '' . $lang['xml:lang'] . '');
                        if ($lang['xml:lang'] === 'en')
                        {
                            $en_informationurl = TRUE;
                        }
                        $e->appendChild($dnode);
                    }
                }
            }
            elseif ($key === 'Logo')
            {
                foreach ($value as $dm)
                {
                    if (!(preg_match_all("#(^|\s|\()((http(s?)://)|(www\.))(\w+[^\s\)\<]+)#i", $dm->getElementValue(), $matches)))
                    {
                        $ElementValue = $this->logo_url . $dm->getElementValue();
                    }
                    else
                    {
                        $ElementValue = $dm->getElementValue();
                    }
                    $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:Logo', $ElementValue);
                    $attrs = $dm->getAttributes();
                    if (!empty($attrs))
                    {

                        foreach ($attrs as $akey => $avalue)
                        {
                            if(!empty($avalue))
                            {
                               $dnode->setAttribute($akey, $avalue);
                            }
                        }
                    }
                    
                    $e->appendChild($dnode);
                }
            }
        }
        if ($en_description !== TRUE)
        {
            $gd = $this->getDescription();
            if (!empty($gd))
            {
               $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:Description');
               $dnode->setAttribute('xml:lang', 'en');
               $dnode->appendChild($e->ownerDocument->createTextNode($gd));
               $e->appendChild($dnode);
            }
        }


        if ($en_displayname !== TRUE)
        {
            $gd = $this->getDisplayName();
            if (empty($gd))
            {
                $gd = $this->getName();
            }
            if (empty($gd))
            {
                $gd = $this->getEntityId();
            }
            $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:DisplayName');
            $dnode->setAttribute('xml:lang', 'en');
            $dnode->appendChild($e->ownerDocument->createTextNode($gd));
            $e->appendChild($dnode);
        }
        if ($en_informationurl !== TRUE)
        {
            $gd = $this->getHelpdeskURL();
            if (!empty($gd))
            {
                $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:InformationURL');
                $dnode->setAttribute('xml:lang', 'en');
                $dnode->appendChild($e->ownerDocument->createTextNode($gd));
                $e->appendChild($dnode);
            }
        }
        if ($en_privacyurl !== TRUE)
        {
            $gd = $this->getPrivacyUrl();
            if (!empty($gd))
            {
                $dnode = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:ui', 'mdui:PrivacyStatementURL');
                $dnode->setAttribute('xml:lang', 'en');
                $dnode->appendChild($e->ownerDocument->createTextNode($gd));
                $e->appendChild($dnode);
            }
        }

        return $e;
    }

    public function getWayfList()
    {
        $w = $this->wayflist;
        if (!empty($w))
        {
            return unserialize($w);
        }
        else
        {
            return null;
        }
    }

    public function getExcarps()
    {
        $w = $this->excarps;
        if (!empty($w))
        {
            return unserialize($w);
        }
        else
        {
            return array();
        }
    }

    public function getSupportedAttributes()
    {
        if ($this->type === 'IDP')
        {
            $finalArp = new \Doctrine\Common\Collections\ArrayCollection();

            $arp = $this->attributeReleaseIDP;
            foreach ($arp as $a)
            {
                if ($a->getIsDefault())
                {
                    $finalArp->add($a);
                }
            }
        }
        else
        {
            return false;
        }
        return $finalArp;
    }

    public function getLastModified()
    {
        if (empty($this->updatedAt))
        {
            return $this->createdAt;
        }
        else
        {
            return $this->updatedAt;
        }
    }

    public function replaceContactCollection(Provider $provider)
    {
        $existingContacts = $this->getContacts();
        $no_existingContacts = count($existing_Contacts);
        $newContacts = $provider->getContacts();
        $no_newContacts = count($newContacts);
    }

    public function overwriteWithNameid(Provider $provider)
    {
        $this->nameids = serialize($provider->getNameIds());
  
    }

    public function convertToArray()
    {
        $r = array();
        $r['id'] = $this->getId();
        $r['name'] = $this->getName();
        $r['displayname'] = $this->getDisplayname();
        $r['entityid'] = $this->getEntityid();

        $r['nameid'] = array();
        $nameids = $this->getNameid()->getValues();
        if (!empty($nameids))
        {
            $r['nameid'] = $nameids;
        }
        $r['protocol'] = array();
        $protocols = $this->getProtocol()->getValues();
        if (!empty($protocols))
        {
            $r['protocol'] = $protocols;
        }
        $r['type'] = $this->getType();
        $r['scope'] = $this->getScope('idpsso');
        $r['aascope'] = $this->getScope('aa');
        $r['homeurl'] = $this->getHomeUrl();
        $r['helpdeskurl'] = $this->getHelpdeskUrl();
        $r['privacyurl'] = $this->getPrivacyUrl();
        $r['validfrom'] = $this->getValidFrom();
        $r['validto'] = $this->getValidTo();
        $r['description'] = $this->getDescription();
        $r['is_approved'] = $this->getApproved();
        $r['is_active'] = $this->getActive();
        $r['is_locked'] = $this->getLocked();
        $r['is_static'] = $this->getStatic();
        $r['is_local'] = $this->getLocal();
        $r['contacts'] = array();
        $contacts = $this->getContacts();
        if (!empty($contacts))
        {
            foreach ($contacts->getValues() as $c)
            {
                $r['contacts'][] = $c->convertToArray();
            }
        }

        $r['certificates'] = array();
        $certs = $this->getCertificates();
        if (!empty($certs))
        {
            foreach ($certs->getValues() as $crt)
            {
                $r['certificates'][] = $crt->convertToArray();
            }
        }
        $services = $this->getServiceLocations();
        $r['services'] = array();
        if (!empty($services))
        {
            foreach ($services->getValues() as $s)
            {
                $r['services'][] = $s->convertToArray();
            }
        }

        $r['federations'] = array();
        $feds = $this->getFederations();
        if (!empty($feds))
        {
            foreach ($feds->getValues() as $f)
            {
                $r['federations'][] = $f->convertToArray();
            }
        }

        return $r;
    }

    public function importFromArray(array $r)
    {
        $etype = strtoupper($r['type']);
        $this->setName($r['name']);
        if (!empty($r['displayname']))
        {
            $this->setDisplayname($r['displayname']);
        }
        else
        {
            $this->setDisplayname($r['name']);
        }
        $this->setEntityid($r['entityid']);
        if (is_array($r['nameid']) && count($r['nameid'] > 0))
        {
            foreach ($r['nameid'] as $n)
            {
                $this->setNameid($n);
            }
        }

        if (is_array($r['protocol']) && count($r['protocol']) > 0)
        {
            foreach ($r['protocol'] as $p)
            {
                $this->setProtocol($p);
            }
        }

        // $this->setProtocol($r['protocol']);
        $this->setType($r['type']);
        $this->setScope('idpsso',$r['scope']);
        $this->setScope('aa',$r['aascope']);
        $this->setHomeUrl($r['homeurl']);
        $this->setHelpdeskUrl($r['helpdeskurl']);
        $this->setPrivacyUrl($r['privacyurl']);
        $this->setValidFrom($r['validfrom']);
        $this->setValidTo($r['validto']);
        $this->setDescription($r['description']);
        $this->setApproved($r['is_approved']);
        $this->setActive($r['is_active']);
        //$this->setLocked($r['is_locked']);
        $this->setStatic($r['is_static']);
        $this->setLocal($r['is_local']);
        if (count($r['contacts']) > 0)
        {
            foreach ($r['contacts'] as $v)
            {
                $c = new Contact;
                $c->importFromArray($v);
                $this->setContact($c);
                $c->setProvider($this);
            }
        }
        if (count($r['certificates']) > 0)
        {
            foreach ($r['certificates'] as $v)
            {
                if (is_array($v))
                {
                    $c = new Certificate;
                    $c->importFromArray($v);
                    $this->setCertificate($c);
                    $c->setProvider($this);
                }
            }
        }
        if ($etype !== 'IDP')
        {
            if(isset($r['details']['spssodescriptor']['extensions']['idpdisc']) && is_array($r['details']['spssodescriptor']['extensions']['idpdisc']))
            { 
                foreach ($r['details']['spssodescriptor']['extensions']['idpdisc'] as $idpdisc)
                {
                   $c = new ServiceLocation;
                   $c->setDiscoveryResponse($idpdisc['url'], $idpdisc['order']);
                   $c->setProvider($this);
                }
            }
            if(isset($r['details']['spssodescriptor']['extensions']['init']) && is_array($r['details']['spssodescriptor']['extensions']['init']))
            {
                foreach ($r['details']['spssodescriptor']['extensions']['init'] as $initreq)
                {
                    $c = new ServiceLocation;
                    $c->setRequestInitiator($initreq['url'], $initreq['binding']);
                    $c->setProvider($this);
                }
            }
        }
        if (count($r['services']) > 0)
        {
            foreach ($r['services'] as $v)
            {
                $c = new ServiceLocation;
                $c->importFromArray($v);
                $this->setServiceLocation($c);
                $c->setProvider($this);
            }
        }
        if (count($r['federations']) > 0)
        {
            foreach ($r['federations'] as $f)
            {
                $c = new Federation;
                $c->importFromArray($f);
                $this->setFederation($c);
                $c->addMember($this);
            }
        }
    }

    public function getOrganizationToXML(\DOMElement $parent)
    {
        $ns_md = 'urn:oasis:names:tc:SAML:2.0:metadata';
        $e = $parent->ownerDocument->createElementNS($ns_md, 'md:Organization');

        $lorgnames = $this->getNameLocalized();
        foreach ($lorgnames as $k => $v)
        {
            if(!empty($v))
            {
               $OrganizationName_Node = $e->ownerDocument->createElementNS($ns_md, 'md:OrganizationName');
               $OrganizationName_Node->setAttribute('xml:lang', '' . $k . '');
               $OrganizationName_Node->appendChild($e->ownerDocument->createTextNode($v));
               $e->appendChild($OrganizationName_Node);
            }
        }
        $ldorgnames = $this->getDisplayNameLocalized();
        foreach ($ldorgnames as $k => $v)
        {
            if(!empty($v))
            {
               $OrganizationDisplayName_Node = $e->ownerDocument->createElementNS($ns_md, 'md:OrganizationDisplayName');
               $OrganizationDisplayName_Node->setAttribute('xml:lang', '' . $k . '');
               $OrganizationDisplayName_Node->appendChild($e->ownerDocument->createTextNode($v));
               $e->appendChild($OrganizationDisplayName_Node);
            }
        }
        $lurls = $this->getHelpdeskUrlLocalized();
        foreach ($lurls as $k => $v)
        {
            if(!empty($v))
            {
               $OrganizationURL_Node = $e->ownerDocument->createElementNS($ns_md, 'md:OrganizationURL');
               $OrganizationURL_Node->setAttribute('xml:lang', '' . $k . '');
               $OrganizationURL_Node->appendChild($e->ownerDocument->createTextNode($v));
               $e->appendChild($OrganizationURL_Node);
            }
        }

        return $e;
    }
    public function getIDPAADescriptorToXML(\DOMElement $parent, $options = null)
    {
        $this->ci = & get_instance();
        $doFilter = array('IDPAttributeService');
        $services = $this->getServiceLocations()->filter(
                    function($entry) use ($doFilter) {
                        return in_array($entry->getType(), $doFilter);
                    });
        $doCertFilter = array('aa');
        $certs = $this->getCertificates()->filter(
                    function($entry) use ($doCertFilter) {
                        return in_array($entry->getType(), $doCertFilter);
                    });

        
        /**
         * do dont generate <AttributeAuthoritydescriptor if no service found
         */
        $noservices = $services->count();
        if ($noservices < 1)
        {
            return null;
        }

        $ns_md = 'urn:oasis:names:tc:SAML:2.0:metadata';
        $e = $parent->ownerDocument->createElementNS($ns_md, 'md:AttributeAuthorityDescriptor');
        $protocol = $this->getProtocolSupport('aa');
        $protocols = implode(' ', $protocol);
        $e->setAttribute('protocolSupportEnumeration', $protocols);
        $Extensions_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:Extensions');

        $scs = $this->getScope('aa');
        if(is_array($scs))
        {
          foreach ($scs as $sc)
          {
            $Scope_Node = $Extensions_Node->ownerDocument->createElementNS('urn:mace:shibboleth:metadata:1.0', 'shibmd:Scope', trim($sc));
            $Scope_Node->setAttribute('regexp', 'false');
            $Extensions_Node->appendChild($Scope_Node);
          }
        }
        $e->appendChild($Extensions_Node);
        $certs = $this->getCertificates();
        log_message('debug', gettype($certs));
        if (!empty($certs))
        {
            $ncerts = $certs->count();
        }
        else
        {
            $ncerts = 0;
        }
        if ($ncerts === 0)
        {
            log_message('debug', 'Provider '.$this->id.': no certificates found for AA ');
            return NULL;
        }
        else
        {
            $tmp_certs = array();
            foreach ($certs as $cert)
            {
                $type = $cert->getType();
                if ($type === 'aa')
                {
                    $certusage = $cert->getCertUse();
                    if (empty($certusage))
                    {
                        $tmp_certs['all'][] = $cert;
                    }
                    else
                    {
                        $tmp_certs[$certusage] = $cert;
                    }
                    $KeyDescriptor_Node = $cert->getCertificateToXML($e);
                    if($KeyDescriptor_Node !== NULL)
                    {
                       $e->appendChild($KeyDescriptor_Node);
                    }
                }
            }
        }
        /**
         * @todo finish for rollover
         */
        /**
          if(array_key_exists('all', $tmp_certs) && count($tmp_certs) == 1)
          {
          if(count($tmp_certs['all']) == 1)
          {
          $KeyDescriptor_Node = $cert->getCertificateToXML($e);
          $e->appendChild($KeyDescriptor_Node);
          }
          else
          {

          }
          }
         */
        foreach ($services as $srv)
        {
           $ServiceLocation_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:AttributeService');
           $ServiceLocation_Node->setAttribute("Binding", $srv->getBindingName());
           $ServiceLocation_Node->setAttribute("Location", $srv->getUrl());
           $e->appendChild($ServiceLocation_Node);
        }
        $nameid = $this->getNameIds('aa');
        foreach ($nameid as $key)
        {
            $NameIDFormat_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:NameIDFormat', $key);
            $e->appendChild($NameIDFormat_Node);
        }




        return $e;
    }

    public function getIDPSSODescriptorToXML(\DOMElement $parent, $options = null)
    {
        $this->ci = & get_instance();
        $services = $this->getServiceLocations();
        if (empty($services))
        {
            return null;
        }
        $this->logo_basepath = $this->ci->config->item('rr_logouriprefix');
        $this->logo_baseurl = $this->ci->config->item('rr_logobaseurl');
        if (empty($this->logo_baseurl))
        {
            $this->logo_baseurl = base_url();
        }
        $this->logo_url = $this->logo_baseurl . $this->logo_basepath;

        $ns_md = 'urn:oasis:names:tc:SAML:2.0:metadata';
        $e = $parent->ownerDocument->createElementNS($ns_md, 'md:IDPSSODescriptor');
        $protocol = $this->getProtocolSupport('idpsso');
        $protocols = implode(" ", $protocol);
        if(empty($protocols))
        {
            $protocols = 'urn:oasis:names:tc:SAML:2.0:protocol'; 
        }
        $e->setAttribute('protocolSupportEnumeration', $protocols);
        $Extensions_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:Extensions');
        $scs = $this->getScope('idpsso');
        if(is_array($scs))
        {
           foreach ($scs as $sc)
           {
               $Scope_Node = $Extensions_Node->ownerDocument->createElementNS('urn:mace:shibboleth:metadata:1.0', 'shibmd:Scope', trim($sc));
               $Scope_Node->setAttribute('regexp', 'false');
               $Extensions_Node->appendChild($Scope_Node);
           }
        }

        /* UIInfo */
        $UIInfo_Node = $this->getMduiToXML($Extensions_Node, 'idp');
        if (!empty($UIInfo_Node))
        {
            $Extensions_Node->appendChild($UIInfo_Node);
        }
        $DiscoHints_Node =  $this->getMduiDiscoHintToXML($Extensions_Node, 'idp');
        if(!empty($DiscoHints_Node))
        {
           $Extensions_Node->appendChild($DiscoHints_Node);
        }



        $e->appendChild($Extensions_Node);
        $certs = $this->getCertificates();
        log_message('debug', gettype($certs));
        if (!empty($certs))
        {
            $ncerts = $certs->count();
        }
        else
        {
            $ncerts = 0;
            log_message('debug', "Provider model: no local certificates may cause problems");
            return NULL;
        }

        $tmp_certs = array();
        if ($ncerts > 0)
        {
            foreach ($certs as $cert)
            {
                $type = $cert->getType();
                if ($type === 'idpsso')
                {
                    $certusage = $cert->getCertUse();
                    if (empty($certusage))
                    {
                        $tmp_certs['all'][] = $cert;
                    }
                    else
                    {
                        $tmp_certs[$certusage] = $cert;
                    }
                    $KeyDescriptor_Node = $cert->getCertificateToXML($e);
                    if($KeyDescriptor_Node !== NULL)
                    {
                       $e->appendChild($KeyDescriptor_Node);
                    }
                }
            }
        }
        /**
         * @todo finish for rollover
         */
        /**
          if(array_key_exists('all', $tmp_certs) && count($tmp_certs) == 1)
          {
          if(count($tmp_certs['all']) == 1)
          {
          $KeyDescriptor_Node = $cert->getCertificateToXML($e);
          $e->appendChild($KeyDescriptor_Node);
          }
          else
          {

          }
          }
         */

        $tmpserorder = array('logout'=>array(),'sso'=>array(),'artifact'=>array());




        foreach ($services as $srv)
        {
            if (strcmp($srv->getType(), 'SingleSignOnService') == 0)
            {
                $ServiceLocation_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:SingleSignOnService');
                $ServiceLocation_Node->setAttribute("Binding", $srv->getBindingName());
                $ServiceLocation_Node->setAttribute("Location", $srv->getUrl());
                $tmpserorder['sso'][] = $ServiceLocation_Node;
            }
            elseif ($srv->getType() === 'IDPSingleLogoutService')
            {
                $ServiceLocation_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:SingleLogoutService');
                $ServiceLocation_Node->setAttribute("Binding", $srv->getBindingName());
                $ServiceLocation_Node->setAttribute("Location", $srv->getUrl());
                $tmpserorder['logout'][] = $ServiceLocation_Node;
            }
            elseif($srv->getType() === 'IDPArtifactResolutionService')
            {
                $ServiceLocation_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:ArtifactResolutionService');
                $ServiceLocation_Node->setAttribute("Binding", $srv->getBindingName());
                $ServiceLocation_Node->setAttribute("Location", $srv->getUrl());
                $ServiceLocation_Node->setAttribute("index", $srv->getOrder());
                $tmpserorder['artifact'][] = $ServiceLocation_Node;
 
            }
        }
        foreach($tmpserorder['artifact'] as $p)
        {
            $e->appendChild($p);
        }
        
        foreach($tmpserorder['logout'] as $p)
        {
            $e->appendChild($p);
        }
        $nameid = $this->getNameIds('idpsso');
        foreach ($nameid as $key)
        {
            $NameIDFormat_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:NameIDFormat', $key);
            $e->appendChild($NameIDFormat_Node);
        }
        foreach($tmpserorder['sso'] as $p)
        {
            $e->appendChild($p);
        }

        return $e;
    }

    public function getSPSSODescriptorToXML(\DOMElement $parent, $options = null)
    {
        $this->ci = & get_instance();
        $this->em = $this->ci->doctrine->em;
        $this->ci->load->helper('url');
        $services = $this->getServiceLocations();

        $this->logo_basepath = $this->ci->config->item('rr_logouriprefix');
        $this->logo_baseurl = $this->ci->config->item('rr_logobaseurl');
        if (empty($this->logo_baseurl))
        {
            $this->logo_baseurl = base_url();
        }
        $this->logo_url = $this->logo_baseurl . $this->logo_basepath;

        $e = $parent->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:SPSSODescriptor');
        $protocols = implode(" ", $this->getProtocolSupport('spsso'));
        if(empty($protocols))
        {
            $protocols = 'urn:oasis:names:tc:SAML:2.0:protocol';
        }
        $e->setAttribute('protocolSupportEnumeration', $protocols);

        $Extensions_Node = $parent->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:Extensions');
        $e->appendChild($Extensions_Node);

        /* DiscoveryResponse */
        //$tmplocations = $this->getServiceLocations();
        foreach ($services as $t)
        {
            $loc_type = $t->getType();
            if ($loc_type === 'RequestInitiator')
            {
                $disc_node = $parent->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:profiles:SSO:request-init', 'init:RequestInitiator');
                $disc_node->setAttribute('Binding', $t->getBindingName());
                $disc_node->setAttribute('Location', $t->getUrl());
                $Extensions_Node->appendChild($disc_node);
            }
            elseif ($loc_type === 'DiscoveryResponse')
            {
                $disc_node = $parent->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol', 'idpdisc:DiscoveryResponse');
                $disc_node->setAttribute('Binding', $t->getBindingName());
                $disc_node->setAttribute('Location', $t->getUrl());
                $disc_node->setAttribute('index', $t->getOrder());
                $Extensions_Node->appendChild($disc_node);
            }
        }
        /* UIInfo */
        $UIInfo_Node = $this->getMduiToXML($Extensions_Node, 'sp');
        if (!empty($UIInfo_Node))
        {
            $Extensions_Node->appendChild($UIInfo_Node);
        }
        $DiscoHints_Node = $this->getMduiDiscoHintToXML($Extensions_Node, 'sp');
        if (!empty($DiscoHints_Node))
        {
            $Extensions_Node->appendChild($DiscoHints_Node);
        }


        /**
         * @todo check if certificates as rtquired fo SP 
         */
       // $certs = $this->getCertificates();

        foreach ($this->getCertificates() as $cert)
        {
            if ($cert->getType() === 'spsso')
            {

                $KeyDescriptor_Node = $cert->getCertificateToXML($e);
                if($KeyDescriptor_Node !== NULL)
                {
                   $e->appendChild($KeyDescriptor_Node);
                }
            }
        }

        // $services = $this->getServiceLocations();

        $tmpserorder = array('logout'=>array(),'assert'=>array(),'art'=>array());
        foreach ($services as $srv)
        {
            $stype = $srv->getType();
            if ($srv->getType() === 'AssertionConsumerService')
            {
                $ServiceLocation_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:AssertionConsumerService');
                $ServiceLocation_Node->setAttribute("Binding", $srv->getBindingName());
                $ServiceLocation_Node->setAttribute("Location", $srv->getUrl());
                $ServiceLocation_Node->setAttribute("index", $srv->getOrder());
                $is_defaultsrc = $srv->getDefault();
                if (!empty($is_defaultsrc))
                {
                    $ServiceLocation_Node->setAttribute("isDefault", 'true');
                }
                $tmpserorder['assert'][] = $ServiceLocation_Node;
            }
            elseif ($srv->getType() === 'SPSingleLogoutService')
            {
                $ServiceLocation_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:SingleLogoutService');
                $ServiceLocation_Node->setAttribute("Binding", $srv->getBindingName());
                $ServiceLocation_Node->setAttribute("Location", $srv->getUrl());
                $tmpserorder['logout'][] =  $ServiceLocation_Node;
            }
            elseif ($srv->getType() === 'SPArtifactResolutionService')
            {
                $ServiceLocation_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:ArtifactResolutionService');
                $ServiceLocation_Node->setAttribute("Binding", $srv->getBindingName());
                $ServiceLocation_Node->setAttribute("Location", $srv->getUrl());
                $ServiceLocation_Node->setAttribute("index", $srv->getOrder());
                $tmpserorder['art'][] =  $ServiceLocation_Node;
            }
        }
        foreach($tmpserorder['art'] as $p)
        {
            $e->appendChild($p);
        }
        foreach($tmpserorder['logout'] as $p)
        {
            $e->appendChild($p);
        }
        foreach ($this->getNameIds('spsso') as $v)
        {
            $NameIDFormat_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:NameIDFormat', $v);
            $e->appendChild($NameIDFormat_Node);
        }
        
        foreach($tmpserorder['assert'] as $p)
        {
            $e->appendChild($p);
        }
        if (!empty($options) and is_array($options) and array_key_exists('attrs', $options) and !empty($options['attrs']))
        {
            $sp_reqattrs = $this->getAttributesRequirement();
            $sp_reqattrs_count = $sp_reqattrs->count();
            if ($sp_reqattrs_count > 0)
            {
                foreach ($sp_reqattrs->getValues() as $v)
                {
                    $in = $v->getAttribute()->showInMetadata();
                    if ($in === FALSE)
                    {

                        $sp_reqattrs->removeElement($v);
                    }
                }
            }
            $sp_reqattrs_count = $sp_reqattrs->count();
            if ($sp_reqattrs_count > 0)
            {
                $Attrconsumingservice_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:AttributeConsumingService');
                $Attrconsumingservice_Node->setAttribute('index', '0');
                $e->appendChild($Attrconsumingservice_Node);
                $t_name = $this->getName();
                if (empty($t_name))
                {
                    $t_name = $this->getEntityId();
                }
                $srvname_node = $Attrconsumingservice_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:ServiceName', $t_name);
                $srvname_node->setAttribute('xml:lang', 'en');
                $Attrconsumingservice_Node->appendChild($srvname_node);
                $t_displayname = $this->getDisplayName();
                if (!empty($t_displayname))
                {
                    $srvdisplay_node = $Attrconsumingservice_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:ServiceDescription', $t_displayname);
                    $srvdisplay_node->setAttribute('xml:lang', 'en');
                    $Attrconsumingservice_Node->appendChild($srvdisplay_node);
                }
                foreach ($sp_reqattrs->getValues() as $v)
                {
                    $attr_node = $Attrconsumingservice_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:RequestedAttribute');
                    $attr_node->setAttribute('FriendlyName', $v->getAttribute()->getName());
                    $attr_node->setAttribute('Name', $v->getAttribute()->getOid());
                    $attr_node->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri');
                    if ($v->getStatus() == 'required')
                    {
                        $attr_node->setAttribute('isRequired', 'true');
                    }
                    else
                    {
                        $attr_node->setAttribute('isRequired', 'false');
                    }
                    $Attrconsumingservice_Node->appendChild($attr_node);
                }
            }
            else
            {

                if (array_key_exists('fedreqattrs', $options) && is_array($options['fedreqattrs']))
                {
                    $Attrconsumingservice_Node = $e->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:AttributeConsumingService');
                    $Attrconsumingservice_Node->setAttribute('index', '0');
                    $e->appendChild($Attrconsumingservice_Node);
                    $t_name = $this->getName();
                    if (empty($t_name))
                    {
                        $t_name = $this->getEntityId();
                    }
                    $srvname_node = $Attrconsumingservice_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:ServiceName', $t_name);
                    $srvname_node->setAttribute('xml:lang', 'en');
                    $Attrconsumingservice_Node->appendChild($srvname_node);
                    $t_displayname = $this->getDisplayName();
                    if (!empty($t_displayname))
                    {
                        $srvdisplay_node = $Attrconsumingservice_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:ServiceDescription', $t_displayname);
                        $srvdisplay_node->setAttribute('xml:lang', 'en');
                        $Attrconsumingservice_Node->appendChild($srvdisplay_node);
                    }
                    foreach ($options['fedreqattrs'] as $v)
                    {
                        $attr_node = $Attrconsumingservice_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:RequestedAttribute');
                        $attr_node->setAttribute('FriendlyName', $v->getAttribute()->getName());
                        $attr_node->setAttribute('Name', $v->getAttribute()->getOid());
                        $attr_node->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri');
                        if ($v->getStatus() == 'required')
                        {
                            $attr_node->setAttribute('isRequired', 'true');
                        }
                        else
                        {
                            $attr_node->setAttribute('isRequired', 'false');
                        }
                        $Attrconsumingservice_Node->appendChild($attr_node);
                    }
                }
            }
        }


        return $e;
    }

    /**
     * $conditions as array with keys :
     *  attr_inc - add required attributes into sp
     *  only_allowed - return if entity is active/valid etc
     * @todo add attr requirements in sp if required
     * @param type $conditions
     */
    public function getProviderToXML(\DOMElement $parent = NULL, $options = NULL)
    {
        log_message('debug', "Provider model: gen xml for " . $this->getEntityId());
        $comment = "\"" . $this->getEntityId() . "\" \n";
        $l = 1;

        /**
         * defauls values
         */
        $attrs_in_sp = FALSE;
        $only_allowed = FALSE;
        $type = $this->type;
        /**
         * condition when XML may be returned
         */
        if (!empty($options) && is_array($options) && count($options) > 0)
        {

            if (array_key_exists('attr_inc', $options))
            {
                $attrs_in_sp = $options['attr_inc'];
            }
       //     if (array_key_exists('only_allowed', $options))
       //     {
       //         $only_allowed = $options['only_allowed'];
       //     }
        }

        /**
         * do not return if active required and entity disabled
         */
      //  $p_active = $this->getAvailable();

      //  if ($only_allowed && empty($p_active))
      //  {
      //      log_message('debug', "skip gen xml for inactive provider with id:" . $this->id);
      //      return \NULL;
      //  }


        $p_entityID = $this->entityid;
        $p_static = $this->getStatic();
        $s_metadata = null;
        $valid_until = null;
        $p_validUntil = $this->getValidTo();
        if (!empty($p_validUntil))
        {
            $valid_until = $p_validUntil->format('Y-m-d') . "T00:00:00Z";
        }



        if ($p_static)
        {
            $static_meta = $this->getStaticMetadata();
            if (!empty($static_meta))
            {
                $s_metadata = $this->getStaticMetadata()->getMetadata();
                $comment .= "static meta\n";
            }
            else
            {
                log_message('error', 'Static metadata is enabled but doesnt exist for entity (id: ' . $this->id . '):' . $this->entityid);
                return null;
            }
        }
        if ($parent === NULL)
        {
            $docXML = new \DOMDocument();
            $xpath = new \DomXPath($docXML);
            $namespaces = h_metadataNamespaces();
            foreach ($namespaces as $namekey => $namevalue)
            {
                $xpath->registerNamespace($namekey, $namevalue);
            }
            $c = $docXML->createComment(str_replace('--', '-' . chr(194) . chr(173) . '-', $comment));
            $docXML->appendChild($c);
            /**
             * trying to get static 
             */
            if (!empty($s_metadata))
            {
                //$node = $this->getStaticMetadata()->getMetadataToXML();
                $node = $static_meta->getMetadataToXML();
                if (!empty($node))
                {
                    $node = $docXML->importNode($node, true);
                    $docXML->appendChild($node);
                }
                return $docXML;
            }

            $EntityDesc_Node = $docXML->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:EntityDescriptor');
            if (!empty($valid_until))
            {
                $EntityDesc_Node->setAttribute('validUntil', $valid_until);
            }
            $docXML->appendChild($EntityDesc_Node);
        }
        else
        {
            $c = $parent->ownerDocument->createComment(str_replace('--', '-' . chr(194) . chr(173) . '-', $comment));
            $parent->appendChild($c);
            if (!empty($s_metadata))
            {
                $node = $this->getStaticMetadata()->getMetadataToXML();
                if (!empty($node))
                {
                    $node = $parent->ownerDocument->importNode($node, true);
                    $parent->appendChild($node);
                    return $node;
                }
                else
                {
                    log_message('error', 'Provider model: ' . $this->entityid . ' static metadata active but is empty - null returned');
                    return null;
                }
            }
            $EntityDesc_Node = $parent->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:EntityDescriptor');
            if ($valid_until)
            {
                $EntityDesc_Node->setAttribute('validUntil', $valid_until);
            }
        }


        $EntityDesc_Node->setAttribute('entityID', $this->getEntityId());
        $ci = & get_instance();
        if (!empty($this->registrar))
        {
            $EntExtension_Node = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:Extensions');
            $EntityDesc_Node->appendChild($EntExtension_Node);
            $RegistrationInfo_Node = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:rpi', 'mdrpi:RegistrationInfo');
            $RegistrationInfo_Node->setAttribute('registrationAuthority', htmlspecialchars($this->registrar));
            if (!empty($this->registerdate))
            {
                $RegistrationInfo_Node->setAttribute('registrationInstant', $this->registerdate->format('Y-m-d') . 'T' . $this->registerdate->format('H:i:s') . 'Z');
            }
            $EntExtension_Node->appendChild($RegistrationInfo_Node);
        }
        elseif ($this->is_local === TRUE)
        {
            $configRegistrar = $ci->config->item('registrationAutority');
            $configRegistrationPolicy = $ci->config->item('registrationPolicy');
            $configRegistrarLoad = $ci->config->item('load_registrationAutority');
            if(!empty($configRegistrarLoad) && !empty($configRegistrar))
            {
                $EntExtension_Node = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:Extensions');
                $EntityDesc_Node->appendChild($EntExtension_Node);
                $RegistrationInfo_Node = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:rpi', 'mdrpi:RegistrationInfo');
                $RegistrationInfo_Node->setAttribute('registrationAuthority', $configRegistrar);
                if (!empty($this->registerdate))
                {
                   $RegistrationInfo_Node->setAttribute('registrationInstant', $this->registerdate->format('Y-m-d') . 'T' . $this->registerdate->format('H:i:s') . 'Z');
                }
                $EntExtension_Node->appendChild($RegistrationInfo_Node);
            }
        }
        if(!empty($RegistrationInfo_Node))
        {
           $regpolicies = $this->getRegistrationPolicy();
           if(count($regpolicies)>0)
           {
              foreach($regpolicies as $rkey=>$rvalue)
              {
                  $RegPolicyNode = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:rpi', 'mdrpi:RegistrationPolicy');
                  $RegPolicyNode->setAttribute('xml:lang',$rkey);
                  $RegPolicyNode->appendChild($RegistrationInfo_Node->ownerDocument->createTextNode($rvalue));
                  $RegistrationInfo_Node->appendChild($RegPolicyNode);
              }
           }
           elseif($this->is_local === TRUE && empty($this->registrar) && !empty($configRegistrationPolicy) && !empty($configRegistrarLoad))
           {
                  $RegPolicyNode = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:rpi', 'mdrpi:RegistrationPolicy');
                  $RegPolicyNode->setAttribute('xml:lang','en');
                  $RegPolicyNode->appendChild($EntityDesc_Node->ownerDocument->createTextNode($configRegistrationPolicy));
                  $RegistrationInfo_Node->appendChild($RegPolicyNode);

           }
        }


        if ($type !== 'SP')
        {
            $SSODesc_Node = $this->getIDPSSODescriptorToXML($EntityDesc_Node);
            if (!empty($SSODesc_Node))
            {
                $EntityDesc_Node->appendChild($SSODesc_Node);
            }
            else
            {
                \log_message('error', "Provider model: IDP type but IDPSSODescriptor is null. Metadata for " . $this->getEntityId() . " couldnt be generated");
                return null;
            }
            $AA_Node = $this->getIDPAADescriptorToXML($EntityDesc_Node);
            if(!empty($AA_Node))
            {
                $EntityDesc_Node->appendChild($AA_Node);
            }
           
        }
        if ($type !== 'IDP')
        {
            $dataprotection = $this->getCoc();

            if (!empty($dataprotection))
            {
                $dataprotenabled = $dataprotection->getAvailable();
                if ($dataprotenabled === TRUE)
                {
                    $AttributesGroup_Node = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:attribute', 'mdattr:EntityAttributes');
                    $EntExtension_Node->appendChild($AttributesGroup_Node);
                    $Attribute_Node = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:Attribute');
                    $Attribute_Node->setAttribute('Name', 'http://macedir.org/entity-category');
                    $Attribute_Node->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri');
                    $AttributesGroup_Node->appendChild($Attribute_Node);
                    $Attribute_Value = $EntityDesc_Node->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion', 'saml:AttributeValue');
                    $Attribute_Value->appendChild($Attribute_Node->ownerDocument->createTextNode($dataprotection->getUrl()));
                    $Attribute_Node->appendChild($Attribute_Value);
                }
            }
            $SSODesc_Node = $this->getSPSSODescriptorToXML($EntityDesc_Node, $options);
            if (!empty($SSODesc_Node))
            {
                $EntityDesc_Node->appendChild($SSODesc_Node);
            }
            else
            {
                log_message('error', "Provider model: SP type but SPSSODescriptor is null - null returned");
                return null;
            }
        }

        $Organization_Node = $this->getOrganizationToXML($EntityDesc_Node);
        if($Organization_Node->hasChildNodes())
        {
           $EntityDesc_Node->appendChild($Organization_Node);
        }
        $contacts = $this->getContacts();

        foreach ($contacts as $v)
        {
            $Contact_Node = $v->getContactToXML($EntityDesc_Node);
            $EntityDesc_Node->appendChild($Contact_Node);
        }

        if ($parent === NULL)
        {
            return $docXML;
        }
        else
        {
            $parent->appendChild($EntityDesc_Node);
            return $EntityDesc_Node;
        }
    }

    /**
     *
     * extensions inside IDPSSODEscriptor (idp) or SPSODescriptor (sp)
     */
    private function SSODescriptorExtensionsFromArray($ext, $type)
    {
        $parentUIInfo  = new ExtendMetadata;
        $parentUIInfo->setNamespace('mdui');
        $parentUIInfo->setElement('UIInfo');
        $parentUIInfo->setAttributes(array());
        $parentUIInfo->setType($type);
        $parentUIInfo->setProvider($this);
        $this->setExtendMetadata($parentUIInfo);


        if (array_key_exists('scope', $ext))
        {
            $this->setScope('idpsso',$ext['scope']);
        }
        if (array_key_exists('aascope', $ext))
        {
            $this->setScope('aa',$ext['aascope']);
        }
        if (array_key_exists('geo', $ext) && is_array($ext['geo']))
        {
            \log_message('debug','GK : geo');
            $parentgeo = new ExtendMetadata;
            $parentgeo->setNamespace('mdui');
            $parentgeo->setElement('DiscoHints');
            $parentgeo->setAttributes(array());
            $parentgeo->setType($type);
            $parentgeo->setProvider($this);
            $this->setExtendMetadata($parentgeo);
            foreach ($ext['geo'] as $g)
            {
                $geo = new ExtendMetadata;
                $geo->setGeoLocation('' . $g[0] . ',' . $g[1] . '', $this, $parentgeo, $type);
                $geo->setProvider($this);
                $this->setExtendMetadata($geo);
            }
        }
        if (array_key_exists('desc', $ext) && is_array($ext['desc']))
        {
            $ldesc = array();
            foreach ($ext['desc'] as $k => $p)
            {
                if ($p['lang'] === 'en')
                {
                    $this->setDescription($p['val']);
                }
                $ldesc[$p['lang']] = $p['val'];
                $this->setLocalDescription($ldesc);
                $extdesc = new ExtendMetadata;
                $extdesc->setNamespace('mdui');
                $extdesc->setType($type);
                $extdesc->setElement('Description');
                $extdesc->setValue($p['val']);
                $extdesc->setAttributes(array('xml:lang'=>$p['lang']));
                $extdesc->setProvider($this);
                $this->setExtendMetadata($extdesc);
                $extdesc->setParent($parentUIInfo);
            }
            
            
            
        }
        if (array_key_exists('displayname', $ext) && is_array($ext['displayname']))
        {
            foreach ($ext['displayname'] as $k => $p)
            {
                $extdesc = new ExtendMetadata;
                $extdesc->setNamespace('mdui');
                $extdesc->setType($type);
                $extdesc->setElement('DisplayName');
                $extdesc->setValue($p['val']);
                $extdesc->setAttributes(array('xml:lang'=>$p['lang']));
                $extdesc->setProvider($this);
                $this->setExtendMetadata($extdesc);
                $extdesc->setParent($parentUIInfo);
            }
        }
        if (array_key_exists('privacyurl', $ext) && is_array($ext['privacyurl']))
        {
            foreach ($ext['privacyurl'] as $k => $p)
            {
                $extdesc = new ExtendMetadata;
                $extdesc->setNamespace('mdui');
                $extdesc->setType($type);
                $extdesc->setElement('PrivacyStatementURL');
                $extdesc->setValue($p['val']);
                $extdesc->setAttributes(array('xml:lang'=>$p['lang']));
                $extdesc->setProvider($this);
                $this->setExtendMetadata($extdesc);
                $extdesc->setParent($parentUIInfo);
            }
        }
        if (array_key_exists('informationurl', $ext) && is_array($ext['informationurl']))
        {
            foreach ($ext['informationurl'] as $k => $p)
            {
                $extdesc = new ExtendMetadata;
                $extdesc->setNamespace('mdui');
                $extdesc->setType($type);
                $extdesc->setElement('InformationURL');
                $extdesc->setValue($p['val']);
                $extdesc->setAttributes(array('xml:lang'=>$p['lang']));
                $extdesc->setProvider($this);
                $this->setExtendMetadata($extdesc);
                $extdesc->setParent($parentUIInfo);
            }
        }
        if (array_key_exists('logo', $ext) && is_array($ext['logo']))
        {
            \log_message('debug','GK logo provider');
            foreach ($ext['logo'] as $k => $p)
            {
                $extdesc = new ExtendMetadata;
                $extdesc->setLogo($p['val'], $this, $parentUIInfo, array('width'=>$p['width'],'height'=>$p['height'],'xml:lang'=>$p['xml:lang']), $type);
                $this->setExtendMetadata($extdesc);

            }
        }
        if ($type == 'sp')
        {
            if (array_key_exists('idpdisc', $ext) && is_array($ext['idpdisc']))
            {
                foreach ($ext['idpdisc'] as $idpdiscs)
                {
                    $disc = new ServiceLocation;
                    $disc->setDiscoveryResponse($idpdiscs['url'], @$idpdiscs['index)']);
                    $disc->setProvider($this);
                    $this->setServiceLocation($disc);
                }
            }
            if (array_key_exists('init', $ext) && is_array($ext['init']))
            {
                foreach ($ext['init'] as $inits)
                {
                    $rinit = new ServiceLocation;
                    $rinit->setRequestInitiator($inits['url']);
                    $rinit->setProvider($this);
                    $this->setServiceLocation($rinit);
                }
            }
        }
    }

    private function  AADescriptorFromArray($b)
    {
        if(array_key_exists('protocols',$b))
        {
           $this->setProtocolSupport('aa',$b['protocols']);
        }
        if (array_key_exists('extensions', $b))
        {
            $this->SSODescriptorExtensionsFromArray($b['extensions'], 'aa');
        }
        if (array_key_exists('nameid', $b) && is_array($b['nameid']))
        {
           $this->setNameIds('aa',$b['nameid']);
        }
        if (array_key_exists('attributeservice', $b))
        {
           foreach($b['attributeservice'] as $aval)
           {
              $aa =  new ServiceLocation;
              $aa->setType('IDPAttributeService');
              $aa->setBindingName($aval['binding']);
              $aa->setUrl($aval['location']);
              $aa->setProvider($this);
              $this->setServiceLocation($aa);
           }
        }
        if (array_key_exists('certificate', $b) && count($b['certificate']) > 0)
        {

            foreach ($b['certificate'] as $c)
            {
                $cert = new Certificate();
                if (array_key_exists('x509data', $c))
                {
                    $cert->setCertType('x509');
                    if (array_key_exists('x509certificate', $c['x509data']))
                    {
                        $cert->setCertdata($c['x509data']['x509certificate']);
                    }
                }

                $cert->setType('aa');
                $cert->setCertUse($c['use']);
                if (!empty($c['keyname']))
                {
                    if (is_array($c['keyname']))
                    {
                        $cert->setKeyname(implode(',', $c['keyname']));
                    }
                    else
                    {
                        $cert->setKeyname($c['keyname']);
                    }
                }
                $cert->setProvider($this);
                $this->setCertificate($cert);
            }
        }
        
    }
    private function IDPSSODescriptorFromArray($b)
    {
        if (array_key_exists('extensions', $b))
        {
            $this->SSODescriptorExtensionsFromArray($b['extensions'], 'idp');
        }

        if (array_key_exists('nameid', $b) && is_array($b['nameid']))
        {
           $this->setNameIds('idpsso',$b['nameid']);
        }
        if (array_key_exists('servicelocations', $b))
        {
            if(isset($b['servicelocations']['singlesignonservice']) && is_array($b['servicelocations']['singlesignonservice']))
            {
                 foreach ($b['servicelocations']['singlesignonservice'] as $s)
                 {
                     $sso = new ServiceLocation;
                     $sso->setType('SingleSignOnService');
                     $sso->setBindingName($s['binding']);
                     $sso->setUrl($s['location']);
                     $sso->setProvider($this);
                     $this->setServiceLocation($sso);

                 }
            }            
            if(isset($b['servicelocations']['singlelogout']) && is_array($b['servicelocations']['singlelogout']))
            {
                 foreach ($b['servicelocations']['singlelogout'] as $s)
                 {
                     $sso = new ServiceLocation;
                     $sso->setType('IDPSingleLogoutService');
                     $sso->setBindingName($s['binding']);
                     $sso->setUrl($s['location']);
                     $sso->setProvider($this);
                     $this->setServiceLocation($sso);

                 }
            }            
            if(isset($b['servicelocations']['artifactresolutionservice']) && is_array($b['servicelocations']['artifactresolutionservice']))
            {
                 foreach ($b['servicelocations']['artifactresolutionservice'] as $s)
                 {
                     $srv = new ServiceLocation;
                     $srv->setType('IDPArtifactResolutionService');
                     $srv->setBindingName($s['binding']);
                     $srv->setUrl($s['location']);
                     $srv->setOrder($s['order']);
                     $srv->setProvider($this);
                     $this->setServiceLocation($srv);

                 }
            }            
        }
        $this->setProtocolSupport('idpsso',$b['protocols']);
        if (array_key_exists('certificate', $b) && count($b['certificate']) > 0)
        {

            foreach ($b['certificate'] as $c)
            {
                $cert = new Certificate();
                if (array_key_exists('x509data', $c))
                {
                    $cert->setCertType('x509');
                    if (array_key_exists('x509certificate', $c['x509data']))
                    {
                        $cert->setCertdata($c['x509data']['x509certificate']);
                    }
                }

                $cert->setType('idpsso');
                $cert->setCertUse($c['use']);
                if (!empty($c['keyname']))
                {
                    if (is_array($c['keyname']))
                    {
                        $cert->setKeyname(implode(',', $c['keyname']));
                    }
                    else
                    {
                        $cert->setKeyname($c['keyname']);
                    }
                }
                $cert->setProvider($this);
                $this->setCertificate($cert);
            }
        }
        return $this;
    }

    private function SPSSODescriptorFromArray($b)
    {
        if (array_key_exists('extensions', $b))
        {
            $this->SSODescriptorExtensionsFromArray($b['extensions'], 'sp');
        }
        if (array_key_exists('nameid', $b) && is_array($b['nameid']))
        {
           $this->setNameIds('spsso',$b['nameid']);
        }
        if (array_key_exists('protocols', $b))
        {
            $this->setProtocolSupport('spsso',$b['protocols']);
        }
        if(isset($b['servicelocations']['assertionconsumerservice']) && is_array($b['servicelocations']['assertionconsumerservice']))
        {

            foreach ($b['servicelocations']['assertionconsumerservice'] as $s)
            {
                $sso = new ServiceLocation;
                $sso->setType('AssertionConsumerService');
                $sso->setBindingName($s['binding']);
                $sso->setUrl($s['location']);
                if (isset($s['order']))
                {
                    $sso->setOrder($s['order']);
                }
                if (!empty($s['isdefault']))
                {
                    $sso->setDefault(true);
                }
                $sso->setProvider($this);
                $this->setServiceLocation($sso);
            }
        }
        if(isset($b['servicelocations']['artifactresolutionservice']) && is_array($b['servicelocations']['artifactresolutionservice']))
        {

            foreach ($b['servicelocations']['artifactresolutionservice'] as $s)
            {
                $sso = new ServiceLocation;
                $sso->setType('SPArtifactResolutionService');
                $sso->setBindingName($s['binding']);
                $sso->setUrl($s['location']);
                if (isset($s['order']))
                {
                    $sso->setOrder($s['order']);
                }
                if (!empty($s['isdefault']))
                {
                    $sso->setDefault(true);
                }
                $sso->setProvider($this);
                $this->setServiceLocation($sso);
            }
        }
       
        if(isset($b['servicelocations']['singlelogout']) && is_array($b['servicelocations']['singlelogout']))
        {

            foreach ($b['servicelocations']['singlelogout'] as $s)
            {
                $slo = new ServiceLocation;
                $slo->setType('SPSingleLogoutService');
                $slo->setBindingName($s['binding']);
                $slo->setUrl($s['location']);
                $slo->setProvider($this);
                $this->setServiceLocation($slo);
            }
        }
        if (array_key_exists('certificate', $b) && is_array($b['certificate']))
        {

            foreach ($b['certificate'] as $c)
            {
                $cert = new Certificate();
                if (array_key_exists('x509data', $c))
                {
                    $cert->setCertType('x509');
                    if (array_key_exists('x509certificate', $c['x509data']))
                    {
                        $cert->setCertdata($c['x509data']['x509certificate']);
                    }
                }
                $cert->setType('spsso');
                $cert->setCertUse($c['use']);
                if (!empty($c['keyname']))
                {
                    if (is_array($c['keyname']))
                    {
                        $cert->setKeyname(implode(',', $c['keyname']));
                    }
                    else
                    {
                        $cert->setKeyname($c['keyname']);
                    }
                }
                $cert->setProvider($this);
                $this->setCertificate($cert);
            }
        }
        return $this;
    }

    public function setProviderFromArray($a)
    {
        if (!is_array($a))
        {
            return null;
        }
        $this->setType($a['type']);
        $this->setEntityId($a['entityid']);
        if(!empty($a['coc']))
        {
            /**
             * @todo set CodeOfConduct
             */ 
        }
        if (!empty($a['validuntil']))
        {
            $p = explode("T", $a['validuntil']);
            $this->setValidTo(\DateTime::createFromFormat('Y-m-d', $p[0]));
        }
        if (!empty($a['registrar']))
        {
            $this->setRegistrationAuthority($a['registrar']);
            if (!empty($a['regdate']))
            {
                $p = explode("T", $a['regdate']);
                $ptime = str_replace('Z', '', $p['1']);
                $this->setRegistrationDate(\DateTime::createFromFormat('Y-m-d H:i:s', $p[0] . ' ' . $ptime));
            }
        }
        if (!empty($a['metadata']))
        {
            $m = new StaticMetadata;
            $m->setMetadata($a['metadata']);
            $this->setStaticMetadata($m);
        }
        if (array_key_exists('details', $a))
        {
            foreach ($a['details']['org'] as $k => $o)
            {
                if($k === 'OrganizationName')
                {
                   $lorgname = array();
                   foreach($o as $k1=>$v1)
                   {
                       if($k1 === 'en')
                       {
                           $this->setName($v1);
                       }
                       else
                       {
                           $lorgname[''.$k1.''] = $v1;
                       }
                   }
                   $this->setLocalName($lorgname);
                }
                elseif($k === 'OrganizationDisplayName')
                {
                    $lorgname = array();
                    foreach($o as $k1=>$v1)
                    {
                       if($k1 === 'en')
                       {
                           $this->setDisplayName($v1);
                       }
                       else
                       {
                           $lorgname[''.$k1.''] = $v1;
                       }
                    
                    }
                    $this->setLocalDisplayName($lorgname);
          
                }
                elseif ($k === 'OrganizationURL')
                {
                    $lorgname = array();
                    foreach ($o as $k1=>$v1)
                    {
                       if ( $k1 === 'en')
                       {
                           $this->setHelpdeskUrl($v1);
                       }
                       else
                       {
                           $lorgname[$k1] = $v1;
                       }
                    }
                    $this->setLocalHelpdeskUrl($lorgname);
                }
            }

            foreach ($a['details']['regpolicy'] as $rp)
            {
                /**
                 * extend regpolicy 
                 */
            }
            if(array_key_exists('regpolicy',$a['details']))
            {
                if(is_array($a['details']['regpolicy']))
                {
                     $this->setRegistrationPolicyFromArray($a['details']['regpolicy'], TRUE);
                }
                else
                {
                     $this->resetRegistrationPolicy();
                }

            }

            foreach ($a['details']['contacts'] as $c)
            {
                $tc = new Contact;
                $tc->setType($c['type']);
                $tc->setEmail($c['email']);
                $tc->setSurName($c['surname']);
                $tc->setGivenName($c['givenname']);
                $tc->setProvider($this);
                $this->setContact($tc);
            }
            if ($a['type'] !== 'SP')
            {
                if (array_key_exists('idpssodescriptor', $a['details']))
                {

                    $this->IDPSSODescriptorFromArray($a['details']['idpssodescriptor']);
                }
                if (array_key_exists('aadescriptor', $a['details']))
                {
                    \log_message('debug' , 'GKL import aa');

                    $this->AADescriptorFromArray($a['details']['aadescriptor']);
                }
            }
            if ($a['type'] !== 'IDP')
            {
                if (array_key_exists('spssodescriptor', $a['details']))
                {
                    $this->SPSSODescriptorFromArray($a['details']['spssodescriptor']);
                }
            }
        }
        return $this;
    }

}
