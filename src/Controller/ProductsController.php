<?php

namespace Drupal\products\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ProductsController.
 *
 * @package Drupal\products\Controller
 */
class ProductsController extends ControllerBase {

  protected $formBuilder;

  protected $db;

  protected $request;


  public function __construct(FormBuilder $form_builder,
    Connection $con,
    RequestStack $request) {
    $this->formBuilder = $form_builder;
    $this->db = $con;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('database'),
      $container->get('request_stack')
    );
  }

  /**
   * Modal window not working why?
   * Callback for opening the employee quick edit form in modal.
   */
  public function openQuickEditModalForm($id = NULL) {

    $query = \Drupal::database();
    //echo $this->id; die;
    $query->delete('products')
      //->fields($field)
      ->condition('id',$id)
      ->execute();

    if($query)
      $this->messenger()->addMessage("succesfully deleted");

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

    $options = [
      'dialogClass' => 'popup-dialog-class',
      'width' => '50%',
    ];

    $response = new AjaxResponse();
    //    $modal_form = $this->formBuilder
    //      ->getForm('Drupal\products\Form\EmployeeMailForm', $employee);
    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(
      new OpenModalDialogCommand(t('Quick Edit Employee #@id',
        ['@id' => 1]), 'ffsdfsdf', $options
      ));

    $response->addCommand(new HtmlCommand('#form-system-messages', $messages));
    $response->addCommand(new RemoveCommand('#row_'.$id));
    return $response;
  }

}
