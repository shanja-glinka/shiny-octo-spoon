class Translator {
  constructor() {
    this.language = null;
    this.dictionary = null;
    this.requestPath = null;

    this.defaultLangPath = "/assets/var/";
    this.translateAttribute = "data-translate";
  }

  initTranslate(lang, callback = null) {
    this.language = lang;
    this.dictionary = null;
    this.requestPath =
      this.defaultLangPath + "translate-" + this.language + ".json?v=000001";

    if (callback !== null) this.requestToTranslate(callback);
  }

  translatePage() {
    let translateElements = document.querySelectorAll(
      "[" + this.translateAttribute + "]"
    );

    if (!translateElements)
      return console.log(
        "Elements [" + this.translateAttribute + "] is not fond"
      );

    translateElements.forEach((el) => {
      el.innerHTML = this.translate(el.getAttribute(this.translateAttribute));
    });
  }

  translate(translateKey) {
    this.throwIfNotInit();

    return typeof this.dictionary[translateKey] === "undefined"
      ? translateKey
      : this.dictionary[translateKey];
  }

  requestToTranslate(callback = null) {
    this.dictionary = {};

    this.throwIfNotInit();

    fetch(this.requestPath).then((response) => {
      if (response.ok) {
        response.json().then((resp) => {
          this.dictionary = resp;
          if (typeof callback === "function") callback(this.dictionary);
        });
      } else {
        this.dictionary = null;
        this.requestPath = null;
        throw 'Dictionary for "' + this.language + "' not found";
      }
    });
  }

  throwIfNotInit() {
    if (this.dictionary === null || this.requestPath === null)
      throw 'Do make call "Translator.initTranslate(lang)" for set translate dictionary';
  }
}

class LanguagePage {
  constructor() {
    this.dictionaries =
      typeof dictionaries === "undefined" ? ["RU-ru", "EN-en"] : dictionaries;

    this.installPageLanguage();

    this.translator = new Translator();
  }

  getPageLang() {
    return localStorage.getItem("user-language");
  }

  setPageLang(lang) {
    return localStorage.setItem("user-language", lang);
  }

  changeLanguage(lang, callback = null) {
    if (this.dictionaries.indexOf(lang) === -1)
      return console.log("Language '" + lang + "' is undefined");

    this.setPageLang(lang);

    this.translator.initTranslate(lang, () => {
      this.translator.translatePage();

      if (typeof callback === "function") callback();
    });

    return;

    location.reload();
  }

  installPageLanguage() {
    if (!this.getPageLang())
      localStorage.setItem("user-language", this.dictionaries[0]);

    document.documentElement.setAttribute("data-lang", this.getPageLang());
  }
}

class SelectDropDown {
  constructor() {
    this.container = null;
    this.items = null;
    this.callback = null;

    this.containerSelector = "page__language";
  }

  getCurrentLang() {
    return document.documentElement.getAttribute("data-lang");
  }

  init() {
    let currentLang = this.getCurrentLang();

    this.container = document.getElementById(this.containerSelector);

    for (let i = 0; i < this.container.options.length; i++) {
      let element = this.container.options[i];
      element.selected = false;
    }

    for (let i = 0; i < this.container.options.length; i++) {
      let element = this.container.options[i];
      if (element.value == currentLang) element.selected = true;
    }
  }

  run(callback = null) {
    if (!this.container) {
      this.init();
    }

    this.callback = callback;

    this.container.addEventListener("change", (e) => {
      this.onChange(e);
    });
  }

  onChange(e) {
    let lang = this.container.options[this.container.selectedIndex].value;
    if (typeof this.callback === "function") this.callback(lang);
  }
}

const dictionaries = ["RU-ru", "EN-en"];
const languagePage = new LanguagePage();
const customSelect = new SelectDropDown();

document.addEventListener("DOMContentLoaded", () => {
  customSelect.run((lang) => {
    languagePage.changeLanguage(lang);
  });

  languagePage.changeLanguage(languagePage.getPageLang(), () => {
    customSelect.init();
  });
});
