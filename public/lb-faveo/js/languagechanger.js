function changeLang(lang) {
    // window.faveoBaseUrl is set by the layouts that include this file. Without it
    // the path stays relative and resolves against the current page's directory,
    // which 404s on any page nested below the application root (e.g. workflow/create).
    var base = window.faveoBaseUrl ? String(window.faveoBaseUrl).replace(/\/+$/, '') + '/' : '';
    location.href = base + 'swtich-language/' + lang;
}
