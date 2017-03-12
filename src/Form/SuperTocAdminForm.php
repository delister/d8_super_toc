<?php

namespace Drupal\super_toc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Renderer;
use Drupal\node\Entity\NodeType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;


/**
 * Class SuperTocAdminForm.
 *
 * @package Drupal\super_toc\Form
 */
class SuperTocAdminForm extends ConfigFormBase {

  private $renderer;

  public function __construct(ConfigFactoryInterface $config_factory, Renderer $renderer) {
    parent::__construct($config_factory);
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'super_toc.supertocadmin',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'super_toc_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $module_path = drupal_get_path('module', 'super_toc');
    $config = $this->config('super_toc.supertocadmin');

    $form['settings'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => 50,
    );

    // Common settings.
    $form['common'] = array(
      '#type' => 'details',
      '#title' => t('Basic settings'),
      '#group' => 'settings',
    );

    $types = NodeType::loadMultiple();
    $options = array();

    foreach ($types as $type) {
      $options[$type->id()] = $type->get('name');
    }

    $form['common']['super_toc_nodetypes'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Node Types'),
      '#description' => t('Enable table of contents only to the following node types.'),
      '#default_value' => $config->get('super_toc_nodetypes') ?: [],
      '#options' => $options,
    );

    $options = array(
      SUPER_TOC_POSITION_BEFORE => t('Before first heading (default)'),
      SUPER_TOC_POSITION_AFTER => t('After first heading'),
      SUPER_TOC_POSITION_TOP => t('Top'),
      SUPER_TOC_POSITION_BOTTOM => t('Bottom'),
    );
    $form['common']['super_toc_position'] = array(
      '#type' => 'select',
      '#title' => t('Position'),
      '#description' => t('Choose where to show table of contents.'),
      '#default_value' => $config->get('super_toc_position') ?: SUPER_TOC_POSITION_BEFORE,
      '#options' => $options,
    );

    $super_toc_start_values = array(2, 3, 4, 5, 6, 7, 8, 9, 10);
    $options = array_combine($super_toc_start_values, $super_toc_start_values);

    $form['common']['super_toc_start'] = array(
      '#type' => 'select',
      '#title' => t('Minimum headings'),
      '#description' => t('Show table of contents when the number of headings is greater than or equal to the minimum value.'),
      '#default_value' => $config->get('super_toc_start') ?: 4,
      '#options' => $options,
    );

    $form['common']['super_toc_heading_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Heading text'),
      '#default_value' => $config->get('super_toc_heading_text') ?: t('Contents'),
      '#size' => 30,
      '#maxlength' => 30,
      '#required' => TRUE,
    );

    $default_value = $config->get('super_toc_visibility');
    $form['common']['super_toc_visibility'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow the user to toggle the visibility of the table of contents'),
      '#default_value' => isset($default_value) ? $default_value : TRUE,
    );

    $form['common']['super_toc_visibility_show'] = array(
      '#type' => 'textfield',
      '#title' => t('Show text'),
      '#default_value' => $config->get('super_toc_visibility_show') ?: t('show'),
      '#size' => 20,
      '#maxlength' => 20,
      '#states' => array(
        'visible' => array(
          ':input[name="super_toc_visibility"]' => array('checked' => TRUE),
        ),
        'required' => array(
          ':input[name="super_toc_visibility"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['common']['super_toc_visibility_hide'] = array(
      '#type' => 'textfield',
      '#title' => t('Hide text'),
      '#default_value' => $config->get('super_toc_visibility_hide') ?: t('hide'),
      '#size' => 20,
      '#maxlength' => 20,
      '#states' => array(
        'visible' => array(
          ':input[name="super_toc_visibility"]' => array('checked' => TRUE),
        ),
        'required' => array(
          ':input[name="super_toc_visibility"]' => array('checked' => TRUE),
        ),
      ),
    );

    $default_value = $config->get('super_toc_visibility_hide_by_default');
    $form['common']['super_toc_visibility_hide_by_default'] = array(
      '#type' => 'checkbox',
      '#title' => t('Hide table of contents initially'),
      '#default_value' => isset($default_value) ? $default_value : FALSE,
      '#states' => array(
        'visible' => array(
          ':input[name="super_toc_visibility"]' => array('checked' => TRUE),
        ),
      ),
    );

    $default_value = $config->get('super_toc_show_hierarchy');
    $form['common']['super_toc_show_hierarchy'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show hierarchy'),
      '#default_value' => isset($default_value) ? $default_value : TRUE,
    );

    $default_value = $config->get('super_toc_ordered_list');
    $form['common']['super_toc_ordered_list'] = array(
      '#type' => 'checkbox',
      '#title' => t('Number list items'),
      '#default_value' => isset($default_value) ? $default_value : TRUE,
    );

    $default_value = $config->get('super_toc_smooth_scroll');
    $form['common']['super_toc_smooth_scroll'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable smooth scroll effect'),
      '#description' => t('Scroll rather than jump to the anchor link.'),
      '#default_value' => isset($default_value) ? $default_value : FALSE,
    );

    /* Appearance */
    $form['appearance'] = array(
      '#type' => 'details',
      '#title' => t('Appearance'),
      '#group' => 'settings',
    );

    $options = array(
      '' => t('<none>'),
      'toc_wrap_left' => t('Left'),
      'toc_wrap_right' => t('Right'),
    );

    $form['appearance']['super_toc_wrapping'] = array(
      '#type' => 'select',
      '#title' => t('Wrapping'),
      '#default_value' => $config->get('super_toc_wrapping') ?: '',
      '#options' => $options,
    );

    $options = [];

    $image = [
      '#theme' => 'image',
      '#uri' => $module_path . '/images/grey.png',
      '#width' => 150,
      '#height' => 100,
      '#alt' => $this->t('Grey (default)'),
    ];
    $options[''] = $this->t('Grey (default)') . '<p>' . $this->renderer->render($image) . '</p>';

    $image = [
      '#theme' => 'image',
      '#uri' => $module_path . '/images/blue.png',
      '#width' => 150,
      '#height' => 100,
      '#alt' => $this->t('Light blue'),
    ];
    $options['toc_light_blue'] = $this->t('Light blue') . '<p>' . $this->renderer->render($image) . '</p>';

    $image = [
      '#theme' => 'image',
      '#uri' => $module_path . '/images/white.png',
      '#width' => 150,
      '#height' => 100,
      '#alt' => $this->t('Light blue'),
    ];
    $options['toc_white'] = $this->t('White') . '<p>' . $this->renderer->render($image) . '</p>';

    $image = [
      '#theme' => 'image',
      '#uri' => $module_path . '/images/black.png',
      '#width' => 150,
      '#height' => 100,
      '#alt' => $this->t('Light blue'),
    ];
    $options['toc_black'] = $this->t('Black') . '<p>' . $this->renderer->render($image) . '</p>';

    $image = [
      '#theme' => 'image',
      '#uri' => $module_path . '/images/transparent.png',
      '#width' => 150,
      '#height' => 100,
      '#alt' => $this->t('Light blue'),
    ];
    $options['toc_transparent'] = $this->t('Transparent') . '<p>' . $this->renderer->render($image) . '</p>';

    $form['appearance']['super_toc_theme'] = array(
      '#type' => 'radios',
      '#title' => t('Presentation'),
      '#default_value' => $config->get('super_toc_theme') ?: '',
      '#options' => $options,
    );
    $form['appearance']['super_toc_theme']['#attributes']['class'][] = 'clearfix';

    // Advanced settings.
    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Advanced settings'),
      '#group' => 'settings',
    );

    $default_value = $config->get('super_toc_bullet_spacing');
    $form['advanced']['super_toc_bullet_spacing'] = array(
      '#type' => 'checkbox',
      '#title' => t('Preserve theme bullets'),
      '#description' => t('If your theme includes background images for unordered list elements, enable this to support them.'),
      '#default_value' => isset($default_value) ? $default_value : FALSE,
    );

    $heading_levels_values = $heading_levels_defaults = array(1, 2, 3, 4, 5, 6);
    $options = array_map(
      function($element) {
        return 'heading ' . $element . ' - h' . $element;
      },
      array_combine($heading_levels_values, $heading_levels_values)
    );
    $form['advanced']['super_toc_heading_levels'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Heading levels'),
      '#description' => t('Include the following heading levels. Deselecting a heading will exclude it.'),
      '#default_value' => $config->get('super_toc_heading_levels') ?: $heading_levels_defaults,
      '#options' => $options,
    );

    $default_value = $config->get('super_toc_smooth_scroll_offset');
    $form['advanced']['super_toc_smooth_scroll_offset'] = array(
      '#type' => 'textfield',
      '#title' => t('Smooth scroll top offset'),
      '#description' => t('If you have a consistent menu across the top of your site, you can adjust the top offset to stop the headings from appearing underneath the top menu. A setting of 30 accommodates the Drupal admin bar. This setting appears after you have enabled smooth scrolling in Basic settings tab.'),
      '#default_value' => isset($default_value) ? $default_value : 30,
      '#element_validate' => [[$this, 'super_toc_validate_smooth_scroll_offset']],
      '#size' => 3,
      '#maxlength' => 3,
      '#states' => array(
        'visible' => array(
          ':input[name="super_toc_smooth_scroll"]' => array('checked' => TRUE),
        ),
        'required' => array(
          ':input[name="super_toc_smooth_scroll"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['#attached']['library'][] = 'super_toc/super_toc_admin';


    return parent::buildForm($form, $form_state);
  }

  /**
   * Form validate handler for Smooth scroll offset element.
   */
  public function super_toc_validate_smooth_scroll_offset(array &$element, FormStateInterface &$form_state) {
    if (!$form_state->getValue('super_toc_smooth_scroll')) {
      $form_state->setValue('super_toc_smooth_scroll_offset', NULL);
    }
    else {
      $value = $element['#value'];
      if ($value !== '' && (!is_numeric($value) || intval($value) != $value || $value < 0)) {
        $form_state->setError($element, t('%name must be a positive integer or zero.', array('%name' => $element['#title'])));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    foreach ($form_state->getValues() as $key => $value) {
      $this->config('super_toc.supertocadmin')->set($key, $value);
    }

    $this->config('super_toc.supertocadmin')->save();
  }

}
