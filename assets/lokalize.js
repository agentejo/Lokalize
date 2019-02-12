window.LokalizeTranslator = {

    init: function(config) {

        this.config = config || {};

        switch (this.config.provider) {
            case 'Yandex':
                this.translate = LokalizeTranslatorYandex(config.apikey);
                break;
            case 'Google':
                this.translate = LokalizeTranslatorGoogle(config.apikey);
                break;
            default:
                this.translate = function() {

                    return (new Promise(function(resolve) {
                        resolve(false)
                    }));
                }
        }
    }

}


window.LokalizeTranslatorYandex = function(apikey) {

    return function(from, to, text) {

        return (new Promise(function(resolve) {

            App.$.get('https://translate.yandex.net/api/v1.5/tr.json/translate', {
                key: apikey,
                lang: from+'-'+to,
                text: text
            }, function(res) {
                if (res.text) resolve(res.text[0]);
            });
        }));
    }
}


window.LokalizeTranslatorGoogle = function(apikey) {

    return function(from, to, text) {

        return (new Promise(function(resolve) {

            App.$.get('https://translation.googleapis.com/language/translate/v2', {
                key: apikey,
                source: from,
                target: to,
                q: text
            }, function(res) {
                if (res.data && res.data.translations && res.data.translations.length) resolve(res.data.translations[0].translatedText);
            });
        }));
    }
}
