document.addEventListener("DOMContentLoaded", function () {
  /*
   * Automatically hide alerts after a few seconds.
   */
  const alerts = document.querySelectorAll(".auto-dismiss");

  alerts.forEach(function (alert) {
    setTimeout(function () {
      const bootstrapAlert = bootstrap.Alert.getOrCreateInstance(alert);

      bootstrapAlert.close();
    }, 5000);
  });
});
