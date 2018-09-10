<?php
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name:'),
    '#required' => TRUE,
  );

  $form['phone'] = array(
    '#type' => 'tel',
    '#placeholder' => 'XXX-XXXX',
    '#title' => t('Phone:'),
  );

  $form['email'] = array(
    '#type' => 'email',
    '#title' => t('Email:'),
    '#required' => TRUE,
  );

  $form['exp_date'] = array (
    '#type' => 'date',
    '#title' => t('Expired at'),
//    '#date_date_format' => 'm/d/Y',
//    '#min_date' => '',
//    '#attributes' => array('type'=> 'date', 'min_date'=> 'now', 'max' => 'now' ),
    '#required' => TRUE,
  );

  $form['category'] = array (
    '#type' => 'textfield',
    '#title' => ('Category'),
    '#required' => true
  );

  $form['system_messages'] = [
    '#markup' => '<div id="form-system-messages"></div>',
    '#weight' => -100,
  ];

  $form['submit'] = [
    '#type' => 'submit',
    '#value' => 'save',
    '#ajax' => [
      'callback' => '::ajaxSubmitCallback',
      'event' => 'click',
      'progress' => [
        'type' => 'throbber',
      ],
    ],
  ];

  return $form;