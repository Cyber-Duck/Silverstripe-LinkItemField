<?php

namespace CyberDuck\LinkItemField\Forms;

use CyberDuck\LinkItemField\Model\LinkItem;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\SSViewer;

/**
 * LinkItemField
 *
 * Link Item Form element class
 *
 * @package silverstripe-linkitemfield
 * @license MIT License https://github.com/cyber-duck/silverstripe-linkitemfield/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class LinkItemField extends FormField
{
    /**
     * allowed_actions config
     * 
     * @since version 4.0.0
     *
     * @var array $allowed_actions
     **/
    private static $allowed_actions = [
        'LinkItemForm',
        'LinkItemFormHTML',
        'doSubmit'
    ];

    /**
     * Hides the field. The actual form element is a hidden field.
     * 
     * @since version 4.0.0
     *
     * @return boolean
     **/
    public function IsHidden()
    {
        return true;
    }
    
    /**
     * Returns the relation ID for use in the template.
     * 
     * @since version 4.0.0
     *
     * @return int
     **/
    public function getLinkID()
    {
        return (int) $this->Value();
    }
    
    /**
     * Returns the LinkItem object Link for use in the template.
     * 
     * @since version 4.0.0
     *
     * @return string
     **/
    public function getLinkPath()
    {
        if($this->Value() > 0) return DataObject::get_by_id(LinkItem::class, $this->Value())->Link();
    }
    
    /**
     * Returns the LinkItemForm HTML for use in the AJAX request.
     * 
     * @since version 4.0.0
     *
     * @return string
     **/
    public function LinkItemFormHTML()
    {
        return $this->LinkItemForm()->forAjaxTemplate();
    }
    
    /**
     * Returns the modal Form object.
     * 
     * @since version 4.0.0
     *
     * @return SilverStripe\Forms\Form
     **/
    public function LinkItemForm()
    {
        $id = (int) $this->getRequest()->postVar('LinkID');
        $obj = $this->getLinkObject($id);

        $fields = FieldList::create([
            TextField::create('Title'),
            DropdownField::create('LinkType', 'Link Type')
                ->addExtraClass('link-item-switcher')
                ->setEmptyString('- select type -')
                ->setSource($obj->getMenuItems()),
            FieldGroup::create(
                TextField::create('Anchor', 'Anchor Link (without #)')
                    ->addExtraClass('link-hidden link-anchor'),
                TreeDropdownField::create('InternalLinkID', 'Internal Link', SiteTree::class)
                    ->addExtraClass('link-hidden link-internal'),
                TextField::create('ExternalLink')
                    ->addExtraClass('link-hidden link-external'),
                EmailField::create('Email', 'Email (without mailto:)')
                    ->addExtraClass('link-hidden link-email'),
                TextField::create('Telephone', 'Telephone (without +)')
                    ->addExtraClass('link-hidden link-telephone'),
                UploadField::create('File', 'File')
                    ->addExtraClass('link-hidden link-file')
                    ->setFolderName('Uploads')
                    ->setAllowedFileCategories('document'),
                UploadField::create('Image', 'Image')
                    ->addExtraClass('link-hidden link-image')
                    ->setFolderName('Uploads')
                    ->setAllowedFileCategories('image/supported')
            )->addExtraClass('link-items'),
            DropdownField::create('Target', 'Open in:')
                ->setEmptyString('- select type -')
                ->setSource($obj->getTargets())
        ]);
        $fields->push(HiddenField::create('Relation')->setValue($this->getRequest()->postVar('Name')));
        if($obj->ID > 0) {
            $fields->push(HiddenField::create('ID'));
        }
        $actions = FieldList::create(FormAction::create('doSubmit', 'Insert Link')
            ->addExtraClass('btn btn-primary action'));

        $validator = $obj->getCMSValidator();

        $form = Form::create($this, 'LinkItemForm', $fields, $actions, $validator);
        $form->setTemplate('forms/LinkItemField_holder');
        $form->addExtraClass('link-item-form');
        if($obj->ID > 0) {
            $form->loadDataFrom($obj);
        }
        return $form;
    }
    
    /**
     * Returns the modal Form object.
     * 
     * @since version 4.0.0
     *
     * @var array  $data
     * @var object $form
     * @return SilverStripe\Forms\Form
     **/
    public function doSubmit($data, Form $form)
    {
        $id = (int) $this->getRequest()->postVar('ID');
        $obj = $this->getLinkObject($id);
        $form->saveInto($obj);
        $obj->write();

        Controller::curr()->getResponse()->addHeader('Content-Type', 'application/json');
        return Convert::raw2json([
            'success' => true,
            'name'    => $this->getRequest()->postVar('Relation'),
            'id'      => $obj->ID,
            'url'     => $obj->Link()
        ]);
    }
    
    /**
     * Returns the rendered field object.
     * 
     * @since version 4.0.0
     *
     * @var array $properties
     * @return string
     **/
    public function Field($properties = [])
    {
        $context = $this;
        if(count($properties)) {
            $context = $context->customise($properties);
        }
        return $context->renderWith('forms/LinkItemField');
    }
    
    /**
     * Internal helper method to return or create a LinkItem object
     * 
     * @since version 4.0.0
     *
     * @var int $id
     * @return CyberDuck\LinkItemField\Model\LinkItem
     **/
    private function getLinkObject($id)
    {
        return $id > 0 ? DataObject::get_by_id(LinkItem::class, $id) : LinkItem::create();
    }
    
    /**
     * Validate the field ID
     * 
     * @since version 4.0.0
     *
     * @var mixed $validator
     * @return boolean
     **/
    public function validate($validator)
    {
        if(!$validator->fieldIsRequired($this->getName())) return true;
        
        if($this->Value() < 1) {
            $validator->validationError(
                $this->getName(),
                'This field is required. Please add a link.',
                'validation'
            );
            return false;
        }
        return true;
    }

    /**
     * Returns a human friendly label to use in the template.
     *
     * @return string
     **/
    public function getLinkLabel()
    {
        if ($this->Value() > 0) {
            $link = DataObject::get_by_id(LinkItem::class, $this->Value());
            return "{$link->Title} ({$link->Link()})";
        }
        return '';
    }

    /**
     * Show the label instead of the ID value when the field is readonly.
     *
     * @return string
     **/
    public function performReadonlyTransformation()
    {
        $clone = $this->castedCopy(ReadonlyField::class);
        $clone->setValue($this->getLinkLabel());
        $clone->setReadonly(true);

        return $clone;
    }
}