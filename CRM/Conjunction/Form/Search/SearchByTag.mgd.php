<?php
// This file declares a managed database record of type "CustomSearch".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Conjunction_Form_Search_SearchByTag',
    'entity' => 'CustomSearch',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Search By Tag',
      'description' => 'Search contacts by tag using AND operator (not OR)',
      'class_name' => 'CRM_Conjunction_Form_Search_SearchByTag',
    ),
  ),
);
