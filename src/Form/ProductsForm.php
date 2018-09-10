<?php

namespace Drupal\products\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Class ProductsForm.
 *
 * @package Drupal\products\Form
 */
class ProductsForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'products_form';
  }

  /**
   * @param $form
   */
  public function prepareForm(&$form){
    $form = require_once 'form.php';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $this->prepareForm($form);
    $this->prepareTable($form);

    return $form;
  }

  /**
   * @param $form
   */
  public function prepareTable(&$form){
    $header_table = array(
      'id'=>    t('No'),
      'name' => t('Name'),
      'phone' => t('Phone'),
      'email' => t('Email'),
      'exp_date' => t('Expired at'),
      'category' => t('category'),
      'opt' => t('operations'),
    );

    $query = \Drupal::database()->select('products', 'm');
    $query->fields('m', ['id','name','phone','email','category','exp_date']);
    $results = $query->execute()->fetchAll();
    $rows = [];
    foreach($results as $data){
      $ajax_link_attributes = [
        'attributes' => [
          'class' => 'use-ajax',
          'data-dialog-type' => 'modal',
          'data-dialog-options' => ['width' => 700, 'height' => 400],
        ],
      ];
      $delete = Url::fromRoute('products.quickdelete', ['id' => $data->id],
        $ajax_link_attributes);

      $rows[] = [
        'data' => [
          'id' =>$data->id,
          'name' => $data->name,
          'phone' => $data->phone,
          'email' => $data->email,
          'exp_date' => $data->exp_date,
          'category' => $data->category,
          'link' => $this->getLinkGenerator()->generate('Delete', $delete),
        ],
        'id' => 'row_'.$data->id
      ];

    }

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => t('No users found'),
      '#prefix' => '<div id="second_field_wrapper">',
      '#suffix' => '</div>'
    ];
  }

  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {

    $ajax_response = new AjaxResponse();
    $message = [
      '#theme' => 'status_messages',
      '#message_list' => $this->messenger()->all(),
      '#status_headings' => [
        'status' => t('Status message'),
        'error' => t('Error message'),
        'warning' => t('Warning message'),
      ],
    ];
    $messages = \Drupal::service('renderer')->render($message);

    $ajax_response->addCommand(new HtmlCommand('#form-system-messages', $messages));
    $ajax_response->addCommand(new InsertCommand('#second_field_wrapper', $form['table']));
    $this->messenger()->deleteAll();


    return $ajax_response;
  }

  public static function isUniqueName($name) {
    $query = \Drupal::database()->select('products', 'm')
      ->fields('m', ['name']);
    $query->condition('name', $name, '=');
    $result = $query->execute();
    if (empty($result->fetchObject())) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  public static function isBiggerDate($date) {

    $dateTime = strtotime($date);
    if($dateTime > time()){
      return true;
    }
    return false;

  }

  /**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {

     $name = $form_state->getValue('name');

      if(!self::isUniqueName($name)){
        $form_state->setErrorByName('name', $this->t('This name already taken'));
      }

      if(preg_match('/[^A-Za-z]/', $name)) {
         $form_state->setErrorByName('name', $this->t('your name must in characters without space'));
      }
      if (!self::isBiggerDate($form_state->getValue('exp_date'))) {
         $form_state->setErrorByName('exp_date', $this->t('Exp date must be bigger than now'));
        }
      $phone = $form_state->getValue('phone');
      if (!preg_match("/^[0-9]{3}-[0-9]{4}$/", $phone)) {
        $form_state->setErrorByName('phone', $this->t('your mobile number must in be in format XXX-XXXX'));
       }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $field    = $form_state->getValues();
    $name     = $field['name'];
    $number   = $field['phone'];
    $email    = $field['email'];
    $age      = $field['exp_date'];
    $gender   = $field['category'];

    $field = [
      'name'         => $name,
      'phone' => $number,
      'email'        => $email,
      'exp_date'          => $age,
      'category'       => $gender,
    ];

    $query = \Drupal::database();
    $id = $query->insert('products')
      ->fields($field)
      ->execute();

    $ajax_link_attributes = [
      'attributes' => [
        'class' => 'use-ajax',
        'data-dialog-type' => 'modal',
        'data-dialog-options' => ['width' => 700, 'height' => 400],
      ],
    ];

    $delete = Url::fromRoute('products.quickdelete', ['id' => $id],
      $ajax_link_attributes);

    $row = [
      'data' => [
        'id' =>$id,
        'name' => $field['name'],
        'phone' => $field['phone'],
        'email' => $field['email'],
        'exp_date' => $field['exp_date'],
        'category' => $field['category'],
        \Drupal::l('Delete', $delete),
      ],
      'id' => 'row_'.$id
    ];

    $form['table']['#rows'][] = $row;

    $this->messenger()->addMessage("succesfully saved");
  }

}
