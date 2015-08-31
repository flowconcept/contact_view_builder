<?php

/**
 * @file
 * Contains Drupal\contact\ContactFormViewBuilder.
 */

namespace Drupal\contact_view_builder;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Render controller for contact forms.
 */
class ContactFormViewBuilder extends EntityViewBuilder {

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new EntityViewBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   * @param \Drupal\Core\Config\ConfigFactoryInterface
   *   The configuration object factory.
   * @param \Drupal\Core\Render\RendererInterface
   *   The renderer.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, EntityFormBuilderInterface $entity_form_builder, ConfigFactoryInterface $config_factory, RendererInterface $renderer) {
    parent::__construct($entity_type, $entity_manager, $language_manager);
    $this->entityFormBuilder = $entity_form_builder;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('entity.form_builder'),
      $container->get('config.factory'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function isViewModeCacheable($view_mode) {
    // Configuration entities do not support revisions.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $message = \Drupal::entityManager()
      ->getStorage('contact_message')
      ->create(array(
        'contact_form' => $entity->id(),
      ));
    $form = $this->entityFormBuilder->getForm($message);
    $form['#title'] = SafeMarkup::checkPlain($entity->label());
    $form['#cache']['contexts'][] = 'user.permissions';
    $config = $this->configFactory->get('contact.settings');
    $this->renderer->addCacheableDependency($form, $config);
    return $form;
  }

}
