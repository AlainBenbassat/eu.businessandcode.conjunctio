<?php
use CRM_Conjunction_ExtensionUtil as E;

class CRM_Conjunction_Form_Search_SearchByTag extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
  }

  function buildForm(&$form) {
    $elements = [];

    CRM_Utils_System::setTitle(E::ts('Search by tag'));

    // get the tagsets
    $result = civicrm_api3('Tag', 'get', [
      'is_tagset' => 1,
      'options' => ['limit' => 0],
    ]);

    // loop over all tagsets
    foreach ($result['values'] as $tagsetID => $tagset) {
      $taglist = [];
      CRM_Core_BAO_Tag::getTags('civicrm_contact', $taglist, $tagsetID);
      $elementName = 'tagset' . '-' . $tagsetID;
      $form->add('select', $elementName, $tagset['name'], $taglist, FALSE, [
        'class' => 'crm-select2 huge',
        'multiple' => 'multiple',
      ]);
      $elements[] = $elementName;
    }

    $form->assign('elements', $elements);
  }

  function &columns() {
    $columns = [
      /*E::ts('Contact Id') => 'contact_id',*/
      E::ts('Name') => 'sort_name',
      E::ts('Job Title') => 'job_title',
      E::ts('Organization') => 'organization_name',
      E::ts('Email') => 'email',
      E::ts('Tags') => 'tags',
    ];
    return $columns;
  }

  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    $sql = $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
    return $sql;
  }

  function select() {
    $select = "
      contact_a.id as contact_id,
      contact_a.id,
      contact_a.sort_name,
      contact_a.job_title,
      contact_a.organization_name,
      e.email,
      (
        SELECT
          group_concat(t.name)
        FROM
          civicrm_entity_tag ett
        INNER JOIN
            civicrm_tag t on t.id = ett.tag_id
        WHERE
          ett.entity_table = 'civicrm_contact'
          and ett.entity_id = contact_a.id
    	) tags	
    ";

    return $select;
  }

  function from() {
    $from = "
      FROM
        civicrm_contact contact_a
      LEFT OUTER JOIN
        civicrm_email e ON e.contact_id = contact_a.id AND e.is_primary = 1
    ";

    return $from;
  }

  function where($includeContactIDs = FALSE) {
    $params = [];
    $where = 'contact_a.is_deleted = 0 ';

    // check the selected tags
    $tagIDs = [];
    foreach ($this->_formValues as $k => $v) {
      if (strstr($k, 'tagset-') !== FALSE) {
        // add the tags in this tagset to the list
        foreach ($v as $tagID) {
          $tagIDs[] = $tagID;
        }
      }
    }

    // add the tags to the where clause
    foreach ($tagIDs as $tagID) {
      $where .= "
        AND EXISTS (
          SELECT
            et.id
          FROM
            civicrm_entity_tag et
          WHERE
            et.entity_table = 'civicrm_contact'
            and et.entity_id = contact_a.id
            and et.tag_id = $tagID
        )
      ";
    }

    return $this->whereClause($where, $params);
  }

  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  }
}
