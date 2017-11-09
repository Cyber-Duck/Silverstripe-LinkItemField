<?php

namespace CyberDuck\LinkItemField\Model;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataObject;

class LinkItem extends DataObject 
{
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

    private static $has_one = [
        'File'         => File::class,
        'Image'        => Image::class,
        'InternalLink' => SiteTree::class
    ];
    
    private static $summary_fields = [
        'Title' => 'Title',
        'Link'  => 'Link'
    ];

    private static $table_name = 'LinkItem';

    private static $default_sort = 'SortOrder';

    private static $singular_name = 'Link Item';
    
    private static $plural_name = 'Link Items';

    public function getCMSFields()
    {
        return parent::getCMSFields();
    }

    public function getCMSValidator()
    {
        return new RequiredFields([
            'Title',
            'LinkType'
        ]);
    }

    public function Link()
    {
        switch($this->LinkType) {
            case 'anchor':
                return '#'.$this->Anchor;
            break;
            case 'internal':
                return $this->InternalLink()->Link();
            break;
            case 'external':
                return $this->ExternalLink;
            break;
            case 'email':
                return 'mailto:'.$this->Email;
            break;
            case 'telephone':
                return 'tel:+'.$this->Email;
            break;
            case 'file':
                return $this->File()->URL;
            break;
            case 'image':
                return $this->Image()->URL;
            break;
        }
    }

    public function getMenuItems()
    {
        return [
            'anchor'    => 'Anchor link',
            'internal'  => 'Internal Link',
            'external'  => 'External Link',
            'email'     => 'Email',
            'telephone' => 'Telephone',
            'file'      => 'File',
            'image'     => 'Image'
        ];
    }
    
    public function getTargets()
    {
        return [
            '_blank' => 'New tab',
            '_top'   => 'New window'
        ];
    }
}