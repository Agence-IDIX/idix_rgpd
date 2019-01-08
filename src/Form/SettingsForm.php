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

    // Configuration options
    $form['configuration'] = array(
      '#type' => 'details',
      '#title' => 'Options',
      '#open' => FALSE
    );
    $optionFields = $this->getOptionFields();
    foreach ($optionFields as $option => $field) {
      $form['configuration'][$option] = array(
        '#default_value' => $config->get('configuration.' . $option),
        '#description' => $optionsDefinition[$option]['label']
      );
      $form['configuration'][$option] += $field;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('idix_rgpd.settings');
    $optionFields = $this->getOptionFields();
    foreach ($optionFields as $option => $field) {
      $config->set('configuration.' . $option, $form_state->getValue($option));
    }
    $config->save();
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
        '#title' => 'CSS Tarteaucitron',
        '#type' => 'checkbox'
      ]
    ];
  }
}
