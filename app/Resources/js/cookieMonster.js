/* eslint-disable */
// @flow

const GA_COOKIE_NAMES = [
  '__utma',
  '__utmb',
  '__utmc',
  '__utmz',
  '_ga',
  '_gat',
  '_gid',
  '_pk_ref',
  '_pk_cvar',
  '_pk_id',
  '_pk_ses',
  '_pk_hsr',
];
const SCROLL_VALUE_TO_CONSENT = 2000;

class CookieMonster {
  cookieBanner: any;
  cookieConsent: any;

  constructor() {
    if (!!(typeof window !== 'undefined' && window.document && window.document.createElement)) {
      const document = window.document;
    } else {
      return;
    }
    this.cookieBanner = document.getElementById('cookie-banner');
    this.cookieConsent = document.getElementById('cookie-consent');
  }

  isDoNotTrackActive = () => {
    const doNotTrack = navigator.doNotTrack;
    return doNotTrack === 'yes' || doNotTrack === '1';
  };

  processCookieConsent = () => {
    const consentCookie = Cookies.getJSON('hasFullConsent');
    const analyticConsent = Cookies.getJSON('analyticConsentValue');
    const adsConsent = Cookies.getJSON('adCookieConsentValue');

    if (consentCookie === true) {
      this.executeAnalyticScript();
      return;
    }

    if (consentCookie === false) {
      if (analyticConsent === true) {
        this.executeAnalyticScript();
        return;
      }
      // so do Not Track Is Activated
      this.hideBanner();
      return;
    }

    // we dont have a consent cookie so, we show the banner
    if (this.isDoNotTrackActive()) {
      if (typeof analyticConsent === 'undefined') {
        Cookies.set('analyticConsentValue', false, { expires: 395 });
        Cookies.set('adCookieConsentValue', false, { expires: 395 });
      }
      if (consentCookie) {
        Cookies.set('hasFullConsent', false, { expires: 395 });
      }
    }

    this.cookieBanner.classList.add('active');
    this.cookieConsent.addEventListener('click', this.removeCookieConsent, false);
    document.addEventListener('click', this.onDocumentClick, false);
    document.addEventListener('scroll', this.onDocumentScroll, false);
  };

  onDocumentScroll = (event: Event) => {
    if (window.location.pathname === '/confidentialite') {
      return;
    }

    if (
      (document.body && document.body.scrollTop > SCROLL_VALUE_TO_CONSENT) ||
      (document.documentElement && document.documentElement.scrollTop > SCROLL_VALUE_TO_CONSENT)
    ) {
      if (this.isDoNotTrackActive()) {
        this.hideBanner();
        Cookies.set('hasFullConsent', false, { expires: 395 });

        return;
      }
      if (!Cookies.getJSON('hasFullConsent')) {
        this.considerFullConsent();
      }
    }
  };

  hideBanner = () => {
    this.cookieBanner.className = this.cookieBanner.className.replace('active', '').trim();
    document.removeEventListener('click', this.onDocumentClick, false);
    document.removeEventListener('scroll', this.onDocumentClick, false);
  };

  onDocumentClick = (event: Event) => {
    const target = event.target;
    if (
      // $FlowFixMe
      target.id === 'cookie-banner' ||
      // $FlowFixMe
      target.parentNode.id === 'cookie-banner' ||
      target.parentNode.parentNode.id === 'cookie-banner' ||
      target.id === 'cookie-more-button'
    ) {
      return;
    }
    if (this.isDoNotTrackActive()) {
      Cookies.set('hasFullConsent', false, { expires: 395 });
      this.hideBanner();
      return;
    }

    // user clicked on close cookie banner
    // $FlowFixMe
    if (target.id === 'cookie-consent') {
      this.considerFullConsent();
      return;
    }
    if (window.location.pathname === '/confidentialite' && target.id !== 'main-navbar') {
      return;
    }

    this.considerFullConsent();
  };

  considerFullConsent = () => {
    Cookies.set('hasFullConsent', true, { expires: 395 });
    this.executeAnalyticScript();
    this.hideBanner();
  };

  toggleCookie = (value: boolean, type: string) => {
    Cookies.set('hasFullConsent', false, { expires: 395 });
    this.hideBanner();
    Cookies.set(type, value, { expires: 395 });
    if (type === 'analyticConsentValue') {
      if (value) {
        this.executeAnalyticScript();
      } else {
        GA_COOKIE_NAMES.forEach(name => {
          if (typeof Cookies.get(name) !== 'undefined') {
            document.cookie =
              name + '=; expires=' + +new Date() + '; domain=.' + window.location.host + '; path=/';
          }
        });
      }
    } else if (type === 'adCookieConsentValue') {
      if (value) {
        // do something; waiting for instruction
      }
    }
  };

  analyticCookieValue = () => {
    return Cookies.getJSON('analyticConsentValue');
  };

  adCookieConsentValue = () => {
    return Cookies.getJSON('adCookieConsentValue');
  };

  removeCookieConsent = (event: Event) => {
    var cookieChoiceElement = document.getElementById(this.cookieConsent);
    if (cookieChoiceElement != null && cookieChoiceElement.parentNode) {
      cookieChoiceElement.parentNode.removeChild(cookieChoiceElement);
    }
  };

  executeAnalyticScript() {
    window.executeAnalyticScript();
  }
}

export default new CookieMonster();
