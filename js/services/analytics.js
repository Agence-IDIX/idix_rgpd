(function (drupalSettings) {
  var options = drupalSettings.idix_rgpd.services.analytics;
  if (options.analyticsUa) {
    for (var option in options) {
      if (options.hasOwnProperty(option) && option !== 'enabled') {
        var value = options[option];
        if (value) {
          tarteaucitron.user[option] = value;
        }
      }
    }
    (tarteaucitron.job = tarteaucitron.job || []).push('analytics');
  }
})(drupalSettings);
