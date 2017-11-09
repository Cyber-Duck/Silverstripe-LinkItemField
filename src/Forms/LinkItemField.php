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
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\SSViewer;

class LinkItemField extends FormField
{
    private static $allowed_actions = [
        'LinkItemForm',
        'LinkItemFormHTML',
        'doSubmit'
    ];

    public function IsHidden()
    {
        return true;
    }

    public function getLinkID()
    {
        return (int) $this->Value();
    }

    public function getLinkPath()
    {
        if($this->Value() > 0) return DataObject::get_by_id(LinkItem::class, $this->Value())->Link();
    }
    
    public function LinkItemFormHTML()
    {
        return $this->LinkItemForm()->forAjaxTemplate();
    }

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
                Helpers::upload('File', 'File', 'Uploads', 'document')
                    ->addExtraClass('link-hidden link-file'),
                Helpers::upload('Image', 'Image', 'Uploads')
                    ->addExtraClass('link-hidden link-image')
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
        $form->setTemplate('Forms/'.self::class.'_holder');
        $form->addExtraClass('link-item-form');
        $form->debug();
        if($obj->ID > 0) {
            $form->loadDataFrom($obj);
        }
        return $form;
    }

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

    public function Field($properties = array())
    {
        $context = $this;

        if(count($properties)) {
            $context = $context->customise($properties);
        }
        $result = $context->renderWith('forms/LinkItemField');

        return $result;
    }
    
    private function getLinkObject($id)
    {
        return $id > 0 ? DataObject::get_by_id(LinkItem::class, $id) : LinkItem::create();
    }
}