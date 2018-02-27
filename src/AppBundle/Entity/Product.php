<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table()
 *
 * @ExclusionPolicy("all")
 *
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_product_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      )
 * )
 * @Hateoas\Relation(
 *      "list",
 *      href = @Hateoas\Route(
 *          "app_product_list",
 *          absolute = true
 *      )
 * )
 *
 */
class Product
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Expose
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\DateTime()
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="text")
     * @Expose
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     * @Expose
     * @Assert\NotBlank()
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Image", mappedBy="product", cascade={"persist"})
     * @Expose
     */
    private $images;

    /**
     * @ORM\PrePersist
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get id
     * 
     * @return Integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     * 
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * 
     * @param String $name
     * 
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get updatedAt
     * 
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     * 
     * @param DateTime $updatedAt
     * 
     * @return Product
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get content
     * 
     * @return Text
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content
     * 
     * @param Text $content
     * 
     * @return Product
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get price
     * 
     * @return String
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     * 
     * @param String $price
     * 
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add image
     * 
     * @param \AppBundle\Entity\Image $image
     * 
     * @return Product
     */
    public function addImage(\AppBundle\Entity\Image $image)
    {
        $this->images[] = $image;
		$image->setProduct($this);

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\Image $image
     */
    public function removeImage(\AppBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }
}
