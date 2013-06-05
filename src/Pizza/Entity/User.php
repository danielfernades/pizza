<?php

namespace Pizza\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="username_idx", columns={"username"})
 *     }
 * )
 */
class User implements UserInterface
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $username
     * @ORM\Column(name="username", type="string")
     */
    protected $username;

    /**
     * @var string $password
     * @ORM\Column(name="password", type="string")
     */
    protected $password;

    /**
     * @var string $plainPassword
     */
    protected $plainPassword;

    /**
     * @var string $repeatedPassword
     */
    protected $repeatedPassword;

    /**
     * @var string $salt
     * @ORM\Column(name="salt", type="string")
     */
    protected $salt;

    /**
     * @var string $email
     * @ORM\Column(name="email", type="string")
     */
    protected $email;

    /**
     * @var array $roles
     * @ORM\Column(name="roles", type="json_array")
     */
    protected $roles;

    /**
     * @var boolean $enabled
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = false;

    /**
     * @var OrderItem[]
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="user", cascade={"persist"})
     */
    protected $orderitems;

    /**
     * roles
     */
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';

    public function __construct()
    {
        $this->roles = array();
        $this->orderitems = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param PasswordEncoderInterface $passwordencoder
     * @return bool
     */
    public function updatePassword(PasswordEncoderInterface $passwordencoder)
    {
        if(!empty($this->plainPassword))
        {
            $this->password = $passwordencoder->encodePassword($this->plainPassword, $this->getSalt());
        }
        if($this->getPassword())
        {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $plainPassword
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param $repeatedPassword
     * @return User
     */
    public function setRepeatedPassword($repeatedPassword)
    {
        $this->repeatedPassword = $repeatedPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepeatedPassword()
    {
        return $this->repeatedPassword;
    }

    /**
     * @param $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $role
     * @return User
     */
    public function addRole($role)
    {
        if(!in_array($role, $this->roles))
        {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * @param $role
     * @return User
     */
    public function removeRole($role)
    {
        $mixKey = array_search($role, $this->roles);
        if(is_numeric($mixKey))
        {
            unset($this->roles[$mixKey]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param $enabled
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param OrderItem $orderitem
     * @param bool $stopPropagation
     * @return $this
     */
    public function addOrderItem(OrderItem $orderitem, $stopPropagation = false)
    {
        $this->orderitems->add($orderitem);
        if(!$stopPropagation) {
            $orderitem->setUser($this, true);
        }
        return $this;
    }

    /**
     * @param OrderItem $orderitem
     * @param bool $stopPropagation
     * @return $this
     */
    public function removeOrderItem(OrderItem $orderitem, $stopPropagation = false)
    {
        $this->orderitems->removeElement($orderitem);
        if(!$stopPropagation) {
            $orderitem->setUser(null, true);
        }
        return $this;
    }

    /**
     * @return OrderItem[]
     */
    public function getOrderItems()
    {
        return $this->orderitems;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername();
    }

    public function eraseCredentials()
    {
        $this->plainPassword = '';
        $this->repeatedPassword = '';
    }

    /**
     * @param ClassMetadata $metadata
     */
    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Assert\Callback(array(
            'methods' => array(function(User $user, ExecutionContext $context)
            {
                if($user->getPlainPassword() && ($user->getPlainPassword() !== $user->getRepeatedPassword()))
                {
                    $context->addViolation("passwords doesn't match");
                }
            }),
        )));
    }

    static public function possibleRoles()
    {
        return array
        (
            self::ROLE_ADMIN => self::ROLE_ADMIN,
            self::ROLE_USER => self::ROLE_USER,
        );
    }
}