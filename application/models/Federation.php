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
 * Federation Class
 * 
 * @package     RR3
 * @subpackage  Models
 * @author      Janusz Ulanowski <janusz.ulanowski@heanet.ie>
 */

/**
 * Federation Model
 *
 * This model for federations definitions
 * 
 * @Entity
 * @Table(name="federation")
 * @author janusz
 */
class Federation
{

    /**
     * @Id
     * @Column(type="integer", nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string", length=128, nullable=false, unique=true)
     */
    protected $name;

    /**
     * @Column(type="string", length=255, nullable=false, unique=true)
     */
    protected $urn;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $is_active;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $is_protected;

    /**
     * @Column(name="is_public",type="boolean", nullable=false)
     */
    protected $is_public;
   
    /**
     * if true then additional metadata can be generated with local only entities
     * example usecase - export metadata for Edugain
     *
     * @Column(name="is_lexport", type="boolean", nullable=false)
     */
    protected $is_lexport = FALSE;

    /**
     * set if federation is localy created or external like edugain
     * @Column(name="is_local",type="boolean", nullable=false)
     */
    protected $is_local;

     /**
      * add attribute requirements into generated metadata
      * @Column(name="attrreq_inmeta", type="boolean", nullable=false)
      */
     protected $attrreq_inmeta = FALSE;

    /**
     * optional terms of use for federation it can be included in metadata as a comment
     * @Column(name="tou",type="text", nullable=true)
     */
    protected $tou;

    /**
     * @OneToMany(targetEntity="AttributeRequirement",mappedBy="fed_id",cascade={"persist","remove"})
     */
    protected $attributeRequirement;

    /**
     * @ManyToMany(targetEntity="Provider", mappedBy="federations", indexBy="entityid")
     * @JoinTable(name="federation_members" )
     * @OrderBy({"name"="ASC"})
     * @var eProvider[]
     */
    protected $members;

    /**
     * @ManyToMany(targetEntity="Partner", mappedBy="pfederations")
     * @JoinTable(name="federation_partners" )
     */
    protected $partners;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $owner;

    public function __construct()
    {
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attributeRequirement = new \Doctrine\Common\Collections\ArrayCollection();
        $this->is_protected = FALSE;
        $this->is_local = TRUE;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    

    public function setUrn($urn)
    {
        $this->urn = $urn;
        return $this;
    }

    public function setDescription($description = null)
    {
        $this->description = $description;
        return $this;
    }
    
    public function setLocalExport($a = FALSE)
    {
        $this->is_lexport = (boolean) $a;
    }

    public function setAsActive()
    {
        $is_active = TRUE;
        $this->setActive($is_active);
        return $this;
    }

    public function setAsDisactive()
    {
        $is_active = FALSE;
        $this->setActive($is_active);
        return $this;
    }

    public function setActive($is_active = null)
    {
        if (!empty($is_active))
        {
            $this->is_active = '1';
        } else
        {
            $this->is_active = '0';
        }
        return $this;
    }

    public function setPublic($is_public = null)
    {
        $this->is_public = $is_public;
        return $this;
    }

    public function publish()
    {
        $public = TRUE;
        $this->setPublic($public);
        return $this;
    }

    public function unPublish()
    {
        $public = FALSE;
        $this->setPublic($public);
        return $this;
    }

    public function setProtected($is_protected = null)
    {
        $this->is_protected = $is_protected;
        return $this;
    }

    public function setLocal($l = null)
    {
        if(!empty($l))
        {
            $this->is_local = 1;
        }
        else
        {
            $this->is_local = 0;
        }
        
    }
    public function setAsLocal()
    {
        $this->is_local = 1;
        return $this;
    }

    public function setAsExternal()
    {
        $this->is_local = 0;
        return $this;
    }

    public function setTou($txt = null)
    {
        $this->tou = $txt;
        return $this;
    }

    public function setOwner($username)
    {
        $this->owner = $username;
        return $this;
    }

    public function setAttributesRequirement(AttributeRequirement $attribute)
    {
        $this->getAttributesRequirement()->add($attribute);
        return $this;
    }

    public function addMember(Provider $provider)
    {
        $already_there = $this->getMembers()->contains($provider);
        if (empty($already_there))
        {
            $this->getMembers()->add($provider);
        }
        return $this->getMembers()->toArray();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }


    public function getLocal()
    {
        return $this->is_local;
    }

    public function getUrn()
    {
        return $this->urn;
    }

    public function getActive()
    {
        return $this->is_active;
    }
    public function getAttrsInmeta()
    {
        return $this->attrreq_inmeta;
    }
    public function setAttrsInmeta($r)
    {
        if($r === TRUE)
        {
           $this->attrreq_inmeta=true;
        }
        elseif($r === FALSE)
        {
           $this->attrreq_inmeta=false;
        }
    }

    public function getLocalExport()
    {
        return $this->is_lexport;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function getAttributesRequirement()
    {
        return $this->attributeRequirement;
    }

    public function getPublic()
    {
        return $this->is_public;
    }
    public function getProtected()
    {
        return $this->is_protected;
    }

    public function getTou()
    {
        return $this->tou;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function importFromArray(array $r)
    {
       $this->setName($r['name']);
       $this->setUrn($r['urn']);
       $this->setDescription($r['description']);
       $this->setActive($r['is_active']);
       $this->setPublic($r['is_public']);
       $this->setProtected($r['is_protected']); 
       $this->setLocal($r['is_local']);
       $this->setTou($r['tou']);
    }
    public function convertToArray()
    {
       $r = array();
       $r['name'] = $this->getName();
       $r['urn'] = $this->getUrn();
       $r['description'] = $this->getDescription();
       $r['is_active'] = $this->getActive();
       $r['is_public'] = $this->getPublic();
       $r['is_protected'] = $this->getProtected();
       $r['is_local'] = $this->getLocal();
       $r['tou'] = $this->getTou();
       return $r;
    }

}

