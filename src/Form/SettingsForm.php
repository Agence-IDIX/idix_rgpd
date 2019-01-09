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
    return ['idix_rgpd.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('idix_rgpd.settings');
    $definition = \Drupal::service('config.typed')->getDefinition('idix_rgpd.settings');
    $optionsDefinition = $definition['mapping']['configuration']['mapping'];
    $servicesDefinition = $definition['mapping']['services']['mapping'];
    // ksm($servicesDefinition);

    // Activation globale
    $form['enabled'] = [
      '#title' => 'Activer le consentement',
      '#type' => 'checkbox',
      '#default_value' => $config->get('enabled')
    ];

    // Configuration options
    $form['configuration'] = array(
      '#type' => 'details',
      '#title' => 'Options',
      '#open' => FALSE
    );
    $optionFields = $this->getOptionFields();
    foreach ($optionFields as $option => $field) {
      $element_key = 'configuration_' . $option;
      $form['configuration'][$element_key] = array(
        '#default_value' => $config->get('configuration.' . $option),
        '#description' => $optionsDefinition[$option]['label']
      );
      $form['configuration'][$element_key] += $field;
    }

    $form['services'] = array(
      '#type' => 'vertical_tabs',
      '#title' => 'Liste des services'
    );
    $serviceFields = $this->getServiceFields();
    foreach ($serviceFields as $service => $fields) {
      $form[$service] = [
        '#type' => 'details',
        '#title' => $servicesDefinition[$service]['label'],
        '#group' => 'services'
      ];
      foreach ($fields as $key => $field) {
        $element_key = 'service_' . $service . '_' . $key;
        $form[$service][$element_key] = array(
          '#default_value' => $config->get('services.' . $service . '.' . $key),
          '#title' => $servicesDefinition[$service]['mapping'][$key]['label']
        );
        $form[$service][$element_key] += $field;
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('idix_rgpd.settings');
    $config->set('enabled', $form_state->getValue('enabled'));

    $optionFields = $this->getOptionFields();
    foreach ($optionFields as $option => $field) {
      $config->set('configuration.' . $option, $form_state->getValue('configuration_' . $option));
    }

    $serviceFields = $this->getServiceFields();
    foreach ($serviceFields as $service => $fields) {
      foreach ($fields as $key => $field) {
        $config->set('services.' . $service . '.' . $key, $form_state->getValue('service_' . $service . '_' . $key));
      }
    }
    $config->save();

    // Génération du fichier javascript : services autoloader
    _idix_rgpd_generate_services();

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

  private function getServiceFields () {
    return [
      'analytics' => [
        'enabled' => [
          '#type' => 'checkbox'
        ],
        'analyticsUa' => [
          '#type' => 'textfield',
          '#size' => 12
        ],
        'analyticsUaCreate' => [
          '#type' => 'textarea',
          '#description' => "Objet javascript correspondant au 3e paramètre pour la méthode `create` (<a href=\"https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference\" target=\"_blank\">documentation Google</a>).<br>
          <code>ga('create', 'UA-XXXX-Y', {'cookieExpires': 34128000});</code>"
        ],
        'analyticsPrepare' => [
          '#type' => 'textarea'
        ],
        'analyticsPageView' => [
          '#type' => 'textarea'
        ],
        'analyticsMore' => [
          '#type' => 'textarea'
        ],
        'analyticsAnonymizeIp' => [
          '#type' => 'checkbox'
        ]
      ]
    ];
  }
}
