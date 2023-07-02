const cookiesBanner = document.getElementsByClassName('cookie-banner')[0];
const cookiesButton = document.querySelectorAll('.cookie-button');
let cookieBannerState = getCookie("cookie-banner");

const disableCookieBanner = () => {
  cookiesBanner.classList.toggle('disabled');
  setCookie("cookie-banner", "disabled", 365); // Set cookie to expire in 365 days
};

const enableCookieBanner = () => {
  cookiesBanner.classList.toggle('disabled');
  setCookie("cookie-banner", "enabled", 365); // Set cookie to expire in 365 days
};

if (cookieBannerState === "disabled") {
  disableCookieBanner();
}

cookiesButton.forEach(function(elem) {
  elem.addEventListener("click", () => {
    cookieBannerState = getCookie("cookie-banner");
    if (cookieBannerState === "enabled") {
      disableCookieBanner();
    } else {
      enableCookieBanner();
    }
  });
});

// Helper function to get a cookie by name
function getCookie(name) {
  const cookieName = name + "=";
  const cookieArray = document.cookie.split(';');
  for (let i = 0; i < cookieArray.length; i++) {
    let cookie = cookieArray[i];
    while (cookie.charAt(0) === ' ') {
      cookie = cookie.substring(1);
    }
    if (cookie.indexOf(cookieName) === 0) {
      return cookie.substring(cookieName.length, cookie.length);
    }
  }
  return "";
}

// Helper function to set a cookie
function setCookie(name, value, days) {
  let expires = "";
  if (days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + value + expires + "; path=/";
}
