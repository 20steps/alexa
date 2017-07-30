<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\File\File;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\Annotation\Type;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;

use Bricks\AbstractCustomBundle\Entity\AbstractUser;

/**
 * User
 *
 * @ORM\Table(name="bricks_custom_twentysteps_alexa_user",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unique_email_idx", columns={"email"}),@ORM\UniqueConstraint(name="unique_username_idx", columns={"username"})})
 * @ORM\Entity(repositoryClass="Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 * @ApiResource(
 *      attributes={
 *          "force_eager" = false,
 *          "normalization_context"={"groups"={"all","user_public"}},
 *          "denormalization_context"={"groups"={"write_all"}},
 *          "filters"= {"filter.user_basic","filter.user_boolean", "filter.user_date", "filter.user_order", "filter.user_custom" },
 *          "ngAdmin"={
 *              "name" = "users",
 *              "label" = "Nutzer",
 *              "icon" = "user",
 *              "position" = 8,
 *              "listView" = {
 *                  "title" = "Registrierte Nutzer",
 *                  "filters" = {
 *                      { "name" = "email", "label" = "E-Mail", "pinned" = true }
 *                  }
 *              },
 *              "showView" = {
 *                  "title" = "Registrierter Nutzer #{{ entry.values.id }}"
 *              }
 *          },
 *          "ngAPI" = {
 *              "customCollectionOperations" = {
 *               }
 *          }
 *      },
 *      itemOperations={
 *          "get"={"method"="GET", "path"="/app/users/{id}", "normalization_context"={"groups"={"all","user_public", "user_details_public"}}},
 *          "put"={"method"="PUT", "path"="/app/authenticated/users/{id}"},
 *          "delete"={"method"="DELETE", "path"="/app/authenticated/users/{id}"}
 *      },
 *      collectionOperations={
 *          "post"={"method"="POST", "path"="/app/authenticated/users" },
 *          "get"={"method"="GET", "path"="/app/users"}
 *     }
 * )
 */
class User extends AbstractUser
{

	// generic properties

	/**
	 * @var integer
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @JMS\Groups({"public","public_list","admin","admin_list","staff","staff_list"})
	 * @Groups({"all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "views" = { "list", "show", "edit" },
	 *          "label" = "Id",
	 *          "editable" = false,
	 *          "position" = 1
	 *       }
	 *     }
	 * )
	 */
	protected $id;
	
	/**
	 * @var bool
	/**
	 * @JMS\Groups({"admin","admin_list","staff","staff_list"})
	 * @Groups({"user_details_public","write_all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 3,
	 *          "views" = { "create", "show", "edit" },
	 *          "label" = "aktiviert",
	 *          "required" = true,
	 *          "editable" = true
	 *       }
	 *     }
	 * )
	 */
	protected $enabled;
	
	/**
	 * @JMS\Groups({"self","admin","admin_list","staff","staff_list"})
	 * @Groups({"all","write_all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 3,
	 *          "views" = { "create", "show", "edit" },
	 *          "label" = "Nutzername",
	 *          "isDetailLink" = true
	 *       }
	 *     }
	 * )
	 */
	protected $username;

	/**
	 * @var string
	 * @JMS\Groups({"self","admin","admin_list","staff","staff_list"})
	 * @Groups({"all","user_public","write_all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 4,
	 *          "views" = { "list", "show", "edit","create" },
	 *          "label" = "E-Mail"
	 *       }
	 *     }
	 * )
	 */
	protected $email;

	/**
	 * @var string
	 * @JMS\Groups({"self","admin","admin_list","staff","staff_list"})
	 */
	protected $emailCanonical;

	/**
	 * @var string
	 * @ORM\Column(name="registration_type", type="string", nullable=false)
	 * @JMS\Groups({"admin","admin_list","staff","staff_list"})
	 */
	protected $registrationType;

	/**
	 * @var string
	 * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
	 * @JMS\Exclude
	 */
	protected $facebookId;
	/**
	 * @var string
	 * @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true)
	 * @JMS\Exclude
	 */
	protected $facebookAccessToken;
	/**
	 * @var string
	 * @ORM\Column(name="facebook_avatar_url", type="string", length=512, nullable=true)
	 */
	protected $facebookAvatarUrl;
	/**
	 * @var string
	 * @ORM\Column(name="twitter_id", type="string", length=255, nullable=true)
	 * @JMS\Exclude
	 */
	protected $twitterId;
	/**
	 * @var string
	 * @ORM\Column(name="twitter_access_token", type="string", length=255, nullable=true)
	 * @JMS\Exclude
	 */
	protected $twitterAccessToken;
	/**
	 * @var string
	 * @ORM\Column(name="twitter_avatar_url", type="string", length=512, nullable=true)
	 */
	protected $twitterAvatarUrl;
	/**
	 * @var string
	 * @ORM\Column(name="email_confirmation_token", type="string", nullable=true)
	 * @JMS\Exclude
	 */
	protected $emailConfirmationToken;
	/**
	 * @var string
	 * @ORM\Column(name="email_request", type="string", nullable=true)
	 * @JMS\Exclude
	 */
	protected $emailRequest;
	/**
	 * @var \DateTime
	 * @ORM\Column(name="email_requested_at", type="datetime", nullable=true)
	 * @Type("DateTime<'U'>")
	 */
	protected $emailRequestedAt;
	/**
	 * @Gedmo\Slug(fields={"username"})
	 * @ORM\Column(length=128, unique=true)
	 * @JMS\Groups({"public","public_list","admin","admin_list","staff","staff_list"})
	 * @Groups({"all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 2,
	 *          "views" = { "show","edit" },
	 *          "label" = "Slug",
	 *          "editable" = false
	 *       }
	 *     }
	 * )
	 **/
	protected $slug;

	/**
	 * @ORM\Column(name="saluation", type="string", nullable=true)
	 * @JMS\Groups({"public","public_list","admin","admin_list","staff","staff_list"})
	 * @JMS\AccessType("public_method")
	 * @Groups({"user_public","write_all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 5,
	 *          "views" = { "show","edit","create" },
	 *          "label" = "Anrede"
	 *       }
	 *     }
	 * )
	 **/
	protected $salutation;

	/**
	 * @ORM\Column(name="first_name", type="text", nullable=true)
	 * @JMS\Groups({"public","public_list","self","linked"})
	 * @Groups({"all","write_all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 6,
	 *          "views" = { "list","show","edit","create" },
	 *          "label" = "Vorname"
	 *       }
	 *     }
	 * )
	 **/
	protected $firstName;
	
	/**
	 * @ORM\Column(name="last_name", type="text", nullable=true)
	 * @JMS\Groups({"public","public_list","self","linked"})
	 * @Groups({"all","write_all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 7,
	 *          "views" = { "show","edit","create" },
	 *          "label" = "Nachname"
	 *       }
	 *     }
	 * )
	 **/
	protected $lastName;


	/**
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created_at", type="datetime")
	 * @Type("DateTime<'U'>")
	 * @JMS\Groups({"self","admin","admin_list","staff","staff_list"})
	 * @Groups({"user_public","user_details_public"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 24,
	 *          "views" = { "show","edit","create" },
	 *          "editable" = false,
	 *          "label" = "Registriert am"
	 *       }
	 *     }
	 * )
	 **/
	protected $createdAt;
	/**
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updated_at", type="datetime")
	 * @Type("DateTime<'U'>")
	 * @JMS\Groups({"self","admin","admin_list","staff","staff_list"})
	 * @Groups({"user_public","user_details_public"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 25,
	 *          "views" = { "show","edit","create" },
	 *          "editable" = false,
	 *          "label" = "Letzter Login"
	 *       }
	 *     }
	 * )
	 **/
	protected $updatedAt;
	/**
	 * @var string
	 * @ORM\Column(name="avatar", type="string", nullable=true)
	 * @JMS\AccessType("public_method")
	 * @JMS\Exclude
	 */
	protected $avatar;

	/**
	 *
	 * @var array
	 *
	 * @ORM\Column(name="settings", type="array", nullable=false)
     * @JMS\Groups({"public","public_list","self","linked"})
     * @Groups({"all","write_all"})
	 *
	 **/
	protected $settings;

	/**
	 * NOTE: This is not a mapped field of entity metadata, just a simple property.
	 *
	 * @Vich\UploadableField(mapping="TWENTYSTEPS_ALEXA_user_avatar", fileNameProperty="avatarFilename")
	 * @JMS\Exclude
	 *
	 * @var File|\Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	protected $avatarFile;

	/**
	 * @ORM\Column(type="string", name="avatar_filename", length=255, nullable=true)
	 * @JMS\Exclude
	 *
	 * @var string
	 */
	protected $avatarFilename;

	/**
	 * @ORM\Column(name="reset_password_token_requested_at", type="datetime", nullable=true)
	 * @Type("DateTime<'U'>")
	 * @JMS\Exclude
	 *
	 * @var \DateTime
	 *
	 */
	protected $resetPasswordTokenRequestedAt;

	/**
	 * @ORM\Column(type="string", name="reset_password_token", length=255, nullable=true)
	 * @JMS\Exclude
	 *
	 * @var string
	 *
	 */
	protected $resetPasswordToken;

	/**
	 * @ORM\Column(name="activation_token_requested_at", type="datetime", nullable=true)
	 * @Type("DateTime<'U'>")
	 * @JMS\Exclude
	 *
	 * @var \DateTime
	 *
	 */
	protected $activationTokenRequestedAt;

	/**
	 * @ORM\Column(type="string", name="activation_token", length=255, nullable=true)
	 * @JMS\Exclude
	 *
	 * @var string
	 *
	 */
	protected $activationToken;

	// virtual properties

	/**
	 * @var string
	 * @Groups({"all"})
	 * @JMS\Groups({"public"})
	 * @JMS\AccessType("public_method")
	 **/
	private $displayName;
	
	// relations
	
	/**
	 *
	 * @var array
	 *
	 * @ORM\OneToMany(targetEntity="AccessToken", mappedBy="user", cascade={"persist", "remove", "merge"})
	 * @JMS\Exclude
	 *
	 **/
	protected $accessTokens;
	
	/**
	 *
	 * @var array
	 *
	 * @ORM\OneToMany(targetEntity="AuthCode", mappedBy="user", cascade={"persist", "remove", "merge"})
	 * @JMS\Exclude
	 *
	 **/
	protected $authCodes;
	
	/**
	 *
	 * @var array
	 *
	 * @ORM\OneToMany(targetEntity="RefreshToken", mappedBy="user", cascade={"persist", "remove", "merge"})
	 * @JMS\Exclude
	 *
	 **/
	protected $refreshTokens;
	
	
	// constructor

	public function __construct()
	{
		parent::__construct();
		$this->accessTokens = new ArrayCollection();
		$this->authCodes = new ArrayCollection();
		$this->refreshTokens = new ArrayCollection();
	}
    
    public function setTranslatableLocale($locale) {
    	// nop
    }

    // overrides

    /**
     * Returns the name to be displayed for the user in the web interface and for communication with connected users, agencies and service providers
     * @return string
     * @JMS\VirtualProperty
     * @JMS\Groups({"public","public_list","admin","admin_list","staff","staff_list","self","linked"})
     */
    public function getDisplayName()
    {
        if($this->getFirstName()!='') {
            return $this->getFirstName();
        } else {
            return $this->getUsername();
        }
    }
    
    /** @return User */
    public function setDisplayName($name) {
    	// noop for JMS
    	return $this;
    }
	
    /**
     * @return string
     */
    public function getAvatar()
    {
        if (!$this->avatar) {
            if ($this->isMan()) {
                return 'avatar-1.png';
            } else {
                return 'avatar-6.png';
            }
        }
        return $this->avatar;
    }

    /**
     * @return File
     */
    public function getAvatarFile()
    {
        return $this->avatarFile;
    }

    /**
     * @param File $file
     * @return User
     */
    public function setAvatarFile(File $file)
    {
        $this->avatarFile = $file;
	    
        //if ($file instanceof UploadedFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime('now');
        //}
	    return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getAvatarFileName()
    {
        return $this->avatarFilename;
    }

    /**
     * @param string $fileName
     */
    public function setAvatarFilename($fileName)
    {
        $this->avatarFilename = $fileName;
    }


    protected function getDefaultSettings() {

        return array_merge(parent::getDefaultSettings(),[
            'mobile.infoPushNotifications' => true,
            'mobile.personalPushNotifications' => true
        ]);
    }

	/**
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"public","public_list","admin","admin_list","staff","staff_list"})
	 * @Type("DateTime<'U'>")
	 */
	public function getRegisteredAt() {
		return parent::getRegisteredAt();
	}

	// custom getters and setters (cp. AbstractUser for generic getters and setters)

	public function getIdPublic() {
		return md5($this->getId());
	}

	/**
	 * Returns the name to be displayed for the user in communication with non-connected users and externals.
	 * @return string
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"public","public_list","admin","admin_list","staff","staff_list"})
	 */
	public function getDisplayNamePublic() {
		if ($this->getFirstName()!='' && $this->getLastName()!='') {
			return $this->getFirstName() . ' ' . $this->getLastNamePublic();
		} else if ($this->getFirstName()!='') {
			return $this->getFirstName();
		} else if ($this->getLastName()) {
			return $this->getLastName();
		} else {
			return $this->getUsernameCanonical();
		}
	}

	public function getLastNamePublic() {
		$publicLastName='';
		$lastNameSegments = explode(' ',$this->getLastName());
		foreach ($lastNameSegments as $segment) {
			$publicLastName .= substr($segment,0,1).'.';
		}
		return $publicLastName;
	}


	/**
	 * @return string
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"public","public_list","admin","admin_list","staff","staff_list"})
	 */
	public function getType()
	{
		if ($this->isUser()) {
			return 'USER';
		}
		if ($this->isAdmin()) {
			return 'ADMIN';
		}
		return 'UNKNOWN';
	}

	/**
	 * @return array
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"self"})
	 */
	public function getSecondaryTypes() {
		return array_diff($this->getTypes(),[$this->getType()]);
	}

	/**
	 * @return array
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"admin","self","admin_list"})
	 */
	public function getTypes() {
		$types = array();
		if ($this->isAdmin()) {
			$types[]='ADMIN';
		}
		if ($this->isUser()) {
			$types[]='USER';
		}
		return $types;
	}

	/**
	 * @return boolean
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"self","admin","admin_list"})
	 */
	public function isAdmin() {
		$roles = $this->getRoles();
		if ($this->isSuperAdmin() || in_array('ROLE_TWENTYSTEPS_ALEXA_ADMIN',$roles)) {
			return true;
		}
		return false;
	}

	/**
	 * @return bool
	 * @JMS\VirtualProperty
	 */
	public function getIsAdmin() {
		return $this->isAdmin();
	}

	public function setIsAdmin($value) {
		// noop for jms
	}

	/**
	 * @return boolean
	 * @JMS\VirtualProperty
	 * @JMS\Groups({"self","admin","admin_list"})
	 */
	public function isUser() {
		$roles = $this->getRoles();
		if ($this->isAdmin() || in_array('ROLE_TWENTYSTEPS_ALEXA_USER',$roles)) {
			return true;
		}
		return false;
	}
	
	// relations
	
	/**
	 * @return array
	 */
	public function getAccessTokens() {
		return $this->accessTokens;
	}
	
	/**
	 * @param array $accessTokens
	 * @return User
	 */
	public function setAccessTokens($accessTokens) {
		$this->accessTokens = $accessTokens;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getAuthCodes() {
		return $this->authCodes;
	}
	
	/**
	 * @param array $authCodes
	 * @return User
	 */
	public function setAuthCodes($authCodes) {
		$this->authCodes = $authCodes;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getRefreshTokens() {
		return $this->refreshTokens;
	}
	
	/**
	 * @param array $refreshTokens
	 * @return User
	 */
	public function setRefreshTokens($refreshTokens) {
		$this->refreshTokens = $refreshTokens;
		
		return $this;
	}
	
	
}
