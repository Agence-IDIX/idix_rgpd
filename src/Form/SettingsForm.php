<?php

namespace Drupal\idix_rgpd\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase  {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'idix_rgpd_settings_form';
  }

  protected function getEditableConfigNames() {
    $configs = ['idix_rgpd.settings'];
    $servicesDefs = _idix_rgpd_get_services_defs();
    foreach ($servicesDefs as $key => $value) {
      $configs[] = 'idix_rgpd.service.' . $key;
    }
    return $configs;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Settings
    $settingsConfig = $this->config('idix_rgpd.settings');
    $definition = \Drupal::service('config.typed')->getDefinition('idix_rgpd.settings');
    $optionsDefinition = $definition['mapping']['configuration']['mapping'];

    $form['enabled'] = [
      '#title' => 'Activer le consentement',
      '#type' => 'checkbox',
      '#default_value' => $settingsConfig->get('enabled')
    ];

    $form['configuration'] = array(
      '#type' => 'details',
      '#title' => 'Options',
      '#open' => FALSE
    );
    $optionFields = $this->getOptionFields();
    foreach ($optionFields as $option => $field) {
      $element_key = 'configuration_' . $option;
      $form['configuration'][$element_key] = array(
        '#default_value' => $settingsConfig->get('configuration.' . $option),
        '#description' => $optionsDefinition[$option]['label']
      );
      $form['configuration'][$element_key] += $field;
    }

    // Services
    $form['services'] = array(
      '#type' => 'vertical_tabs',
      '#title' => 'Liste des services'
    );

    $servicesDefs = _idix_rgpd_get_services_defs();
    foreach ($servicesDefs as $service => $def) {
      $config = $this->config('idix_rgpd.service.' . $service);
      $fieldsCallback = '_idix_rgpd_service_fields_' . $service;
      if (function_exists($fieldsCallback)) {
        $fields = $fieldsCallback();
        $form[$service] = [
          '#type' => 'details',
          '#title' => $def['label'],
          '#group' => 'services'
        ];

        foreach ($fields as $key => $field) {
          $form_key = 'service_' . $service . '_' . $key;
          $form[$service][$form_key] = array(
            '#default_value' => $config->get($key),
            '#title' => $def['mapping'][$key]['label']
          );
          $form[$service][$form_key] += $field;
        }
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Settings
    $config = $this->configFactory->getEditable('idix_rgpd.settings');
    $config->set('enabled', $form_state->getValue('enabled'));
    $optionFields = $this->getOptionFields();
    foreach ($optionFields as $option => $field) {
      $config->set('configuration.' . $option, $form_state->getValue('configuration_' . $option));
    }
    $config->save();

    // Services
    $servicesDefs = _idix_rgpd_get_services_defs();
    foreach ($servicesDefs as $service => $def) {
      $config = $this->config('idix_rgpd.service.' . $service);
      $fieldsCallback = '_idix_rgpd_service_fields_' . $service;
      if (function_exists($fieldsCallback)) {
        $fields = $fieldsCallback();
        foreach ($fields as $key => $field) {
          $config->set($key, $form_state->getValue('service_' . $service . '_' . $key));
        }
      }
      $config->save();
    }

    // Génération du fichier javascript : services autoloader
    _idix_rgpd_generate_services_loader();

    parent::submitForm($form, $form_state);
  }

  private function getOptionFields () {
    return [
      'orientation' => [
        '#title' => 'Position du bandeau',
        '#type' => 'select',
        '#options' => [
          'bottom' => 'En bas',
          'top' => 'En haut'
        ]
      ],
      'hashtag' => [
        '#title' => 'Hashtag',
        '#type' => 'textfield',
        '#size' => 256,
        '#required' => true
      ],
      'cookieName' => [
        '#title' => 'Nom du cookie',
        '#type' => 'textfield',
        '#size' => 256,
        '#required' => true
      ],
      'cookieDomain' => [
        '#title' => 'Domaine du cookie',
        '#type' => 'textfield',
        '#size' => 256
      ],
      'privacyUrl' => [
        '#title' => 'Politique de confidentialité',
        '#type' => 'textfield',
        '#size' => 256
      ],
      'highPrivacy' => [
        '#title' => 'Consentement explicite',
        '#type' => 'checkbox'
      ],
      'adblocker' => [
        '#title' => 'AdBlock',
        '#type' => 'checkbox'
      ],
      'showAlertSmall' => [
        '#title' => 'Mini bandeau',
        '#type' => 'checkbox'
      ],
      'cookieslist' => [
        '#title' => 'Liste des cookies',
        '#type' => 'checkbox'
      ],
      'removeCredit' => [
        '#title' => 'Crédit Tarteaucitron',
        '#type' => 'checkbox'
      ],
      'handleBrowserDNTRequest' => [
        '#title' => 'Do Not Track',
        '#type' => 'checkbox'
      ],
      'AcceptAllCta' => [
        '#title' => 'Consentir à tous les services',
        '#type' => 'checkbox'
      ],
      'moreInfoLink' => [
        '#title' => 'Lien "En savoir plus"',
        '#type' => 'checkbox'
      ],
      'useExternalCss' => [
        '#title' => 'CSS Custom',
        '#type' => 'checkbox'
      ]
    ];
  }
}
