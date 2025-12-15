window.showToast = function (message) {
  const toastLiveExample = document.getElementById("liveToast");
  if (!toastLiveExample) return;
  const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample);
  const toastBody = toastLiveExample.querySelector(".toast-body");
  const toastTime = toastLiveExample.querySelector("#toastTime");
  toastBody.textContent = message;
  const now = new Date();
  const jamMenit =
    now.getHours().toString().padStart(2, "0") +
    ":" +
    now.getMinutes().toString().padStart(2, "0");
  if (toastTime) toastTime.textContent = jamMenit;
  toastBootstrap.show();
};
