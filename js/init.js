(function (Drupal, drupalSettings, tarteaucitron) {
  Drupal.behaviors.idix_rgpd = {
    attach: function (context, settings) {
      tarteaucitron.init(drupalSettings.idix_rgpd.options);
    }
  };
})(Drupal, drupalSettings, tarteaucitron);
