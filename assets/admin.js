(function () {
    'use strict';

    function mediaPicker(wrapper) {
        var input = wrapper.querySelector('.eb-media-url');
        var preview = wrapper.querySelector('.eb-media-preview');
        var clear = wrapper.querySelector('.eb-media-clear');
        var frame;

        function show(url) {
            input.value = url;
            preview.innerHTML = url ? '<img src="' + encodeURI(url) + '" alt="">' : '';
            clear.hidden = !url;
        }

        wrapper.querySelector('.eb-media-select').addEventListener('click', function () {
            if (!frame) {
                frame = wp.media({
                    title: 'Bild auswählen',
                    button: { text: 'Übernehmen' },
                    library: { type: 'image' },
                    multiple: false
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    // "full" ist die Originaldatei - die Portale skalieren selbst.
                    show(attachment.url);
                });
            }
            frame.open();
        });

        clear.addEventListener('click', function () {
            show('');
        });

        input.addEventListener('change', function () {
            show(input.value.trim());
        });
    }

    function berufSearch(wrapper) {
        var idInput = wrapper.querySelector('.eb-beruf-id');
        var search = wrapper.querySelector('.eb-beruf-search');
        var button = wrapper.querySelector('.eb-beruf-go');
        var spinner = wrapper.querySelector('.spinner');
        var results = wrapper.querySelector('.eb-beruf-results');

        // Ausbildungs- und Tätigkeitsberufe haben unterschiedliche IDs - die Stellenart entscheidet.
        function berufType() {
            var art = document.getElementById('eb-art');
            if (art && (art.value === 'ausbildung' || art.value === 'duales_studium')) {
                return art.value;
            }
            return 'taetigkeit';
        }

        function message(text) {
            results.hidden = false;
            results.textContent = text;
        }

        function run() {
            var query = search.value.trim();
            if (!query) {
                return;
            }

            spinner.classList.add('is-active');
            results.hidden = true;

            // Bei einfachen Permalinks enthält restUrl bereits ein "?rest_route=".
            var separator = customFieldsRss.restUrl.indexOf('?') === -1 ? '?' : '&';
            var url = customFieldsRss.restUrl + separator +
                'q=' + encodeURIComponent(query) +
                '&type=' + encodeURIComponent(berufType());

            fetch(url, { headers: { 'X-WP-Nonce': customFieldsRss.nonce } })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function (items) {
                    if (!items.length) {
                        message('Keine Berufe gefunden.');
                        return;
                    }
                    results.innerHTML = '';
                    results.hidden = false;
                    items.forEach(function (item) {
                        var entry = document.createElement('button');
                        entry.type = 'button';
                        entry.className = 'eb-beruf-hit';
                        entry.innerHTML = '<code></code> <span></span>';
                        entry.querySelector('code').textContent = item.id;
                        entry.querySelector('span').textContent = item.label;
                        entry.addEventListener('click', function () {
                            idInput.value = item.id;
                            results.hidden = true;
                            search.value = item.label;
                        });
                        results.appendChild(entry);
                    });
                })
                .catch(function (error) {
                    message('Berufssuche fehlgeschlagen (' + error.message + '). DKZ-ID bitte manuell eintragen.');
                })
                .finally(function () {
                    spinner.classList.remove('is-active');
                });
        }

        button.addEventListener('click', run);
        search.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                run();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.eb-media').forEach(mediaPicker);
        document.querySelectorAll('.eb-beruf').forEach(berufSearch);
    });
}());
