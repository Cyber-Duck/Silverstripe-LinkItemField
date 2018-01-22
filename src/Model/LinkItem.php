<?php

namespace CyberDuck\LinkItemField\Model;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataObject;

/**
 * LinkItem
 *
 * Link Item object class for use as $has_one relation
 *
 * @package silverstripe-linkitemfield
 * @license MIT License https://github.com/cyber-duck/silverstripe-linkitemfield/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class LinkItem extends DataObject 
{
    /**
     * db field config
     * 
     * @since version 4.0.0
     *
     * @var array $db
     **/
    private static $db = [
        'Title'        => 'Varchar(512)',
        'LinkType'     => 'Varchar(20)',
        'Target'       => 'Varchar(512)',
        'Anchor'       => 'Varchar(512)',
        'ExternalLink' => 'Varchar(512)',
        'Email'        => 'Varchar(512)',
        'Telephone'    => 'Varchar(512)',
        'SortOrder'    => 'Int'
    ];

    /**
     * has_one relation config
     * 
     * @since version 4.0.0
     *
     * @var array $has_one
     **/
    private static $has_one = [
        'File'         => File::class,
        'Image'        => Image::class,
        'InternalLink' => SiteTree::class
    ];

    private static $owns = [
        'File',
        'Image'
    ];
    
    /**
     * summary_fields grid field config
     * 
     * @since version 4.0.0
     *
     * @var array $summary_fields
     **/
    private static $summary_fields = [
        'Title' => 'Title',
        'Link'  => 'Link'
    ];
    
    /**
     * table_name db table name
     * 
     * @since version 4.0.0
     *
     * @var array $table_name
     **/
    private static $table_name = 'LinkItem';
    
    /**
     * default_sort db table default sorting columns
     * 
     * @since version 4.0.0
     *
     * @var array $default_sort
     **/
    private static $default_sort = 'SortOrder';
    
    /**
     * singular_name Object singular name
     * 
     * @since version 4.0.0
     *
     * @var array $singular_name
     **/
    private static $singular_name = 'Link Item';
    
    /**
     * plural_name Object plural name
     * 
     * @since version 4.0.0
     *
     * @var array $plural_name
     **/
    private static $plural_name = 'Link Items';
    
    /**
     * Returns the object CMS fields
     * 
     * @since version 4.0.0
     *
     * @return SilverStripe\Forms\FieldList
     **/
    public function getCMSFields()
    {
        return parent::getCMSFields();
    }
    
    /**
     * Returns the object CMS fields validator
     * 
     * @since version 4.0.0
     *
     * @return SilverStripe\Forms\RequiredFields
     **/
    public function getCMSValidator()
    {
        return new RequiredFields([
            'Title',
            'LinkType'
        ]);
    }
    
    /**
     * Returns the formatted URL
     * 
     * @since version 4.0.0
     *
     * @return array
     **/
    public function Link()
    {
        $link = '';
        switch($this->LinkType) {
            case 'anchor':
                $link = '#'.$this->Anchor;
            break;
            case 'internal':
                $link = $this->InternalLink()->Link();
            break;
            case 'external':
                $link = $this->ExternalLink;
            break;
            case 'email':
                $link = 'mailto:'.$this->Email;
            break;
            case 'telephone':
                $link = 'tel:+'.$this->Telephone;
            break;
            case 'file':
                $link = $this->File()->URL;
            break;
            case 'image':
                $link = $this->Image()->URL;
            break;
        }
        $this->extend('updateLink', $link);
        return $link;
    }
    
    /**
     * Returns an array of Link types.
     * 
     * @since version 4.0.0
     *
     * @return array
     **/
    public function getMenuItems()
    {
        $items = [
            'anchor'    => 'Anchor link',
            'internal'  => 'Internal Link',
            'external'  => 'External Link',
            'email'     => 'Email',
            'telephone' => 'Telephone',
            'file'      => 'File',
            'image'     => 'Image'
        ];
        $this->extend('updateMenuItems', $items);
        return $items;
    }
    
    /**
     * Returns an array of Link targets.
     * 
     * @since version 4.0.0
     *
     * @return array
     **/
    public function getTargets()
    {
        return [
            '_blank' => 'New tab'
        ];
    }
}