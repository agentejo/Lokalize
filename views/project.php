<script type="text/javascript" src="@base('lokalize:assets/lokalize.js')"></script>
<script>

  window.__lokalizeProject = {{ json_encode($project) }}

</script>

<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/lokalize')">@lang('Lokalize')</a></li>
        <li class="uk-active"><span>{{ $project['name'] }}</span></li>
    </ul>
</div>

@if(isset($project['info']) && $project['info'])
<div class="uk-margin-large-bottom uk-text-small uk-text-muted">
    <div>
        {{ nl2br(htmlspecialchars($project['info'])) }}
    </div>
</div>
@endif


<div riot-view>

    <style>
        .flag {
            border-radius: 50%;
            box-shadow: 0 0 2px rgba(0,0,0,.5);
        }
        .lang-badge { width: 70px }
        .lang-suggestion {
            position: relative;
            padding: 6px;
            cursor: pointer;
        }
        .lang-input {
            height: inherit !important;
        }

        .lokalize-keys input,
        .lokalize-keys textarea {
            padding: 0 !important;
            height: 1.3em !important;
        }

        .lokalize-keys textarea:focus {
            box-shadow: 0 0 1px rgba(0,0,0,.3);
            min-height: 120px;
            padding: 4px !important;
            transition: none;
        }

        .field-info {
            all: unset;
            white-space: pre;
            line-height: 1.1;
        }

        .filter-selector {
            position:absolute;
            top:50%;
            right:0;
            transform:translateY(-50%)
        }
    </style>

    <div ref="uploadprogress" class="uk-margin uk-hidden">
        <div class="uk-progress">
            <div ref="progressbar" class="uk-progress-bar" style="width: 0%;">&nbsp;</div>
        </div>
    </div>


    <div class="uk-grid uk-flex-middle">

        <div class="uk-width-1-4">
            <div class="uk-form-icon uk-form uk-text-muted uk-display-block">
                <a class="uk-icon-filter" style="pointer-events: all" onclick="{ clearfilter }"></a>
                <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter...')" onkeyup="{ updatefilter }">

                <div class="uk-form-select filter-selector">
                    <span class="uk-badge uk-badge-outline uk-text-muted">in {filterTarget || 'keys'}</span>
                    <select bind="filterTarget">
                        <option value="keys">Keys</option>
                        <option value="values">Values</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="uk-flex uk-flex-middle uk-flex-item-1">

            <span class="uk-margin-right" data-uk-dropdown="mode:'click'">
                <a><img class="flag" riot-src="{App.route('/lokalize/getFlagIcon/'+project.lang)}" title="{project.lang}" width="25" height="25"></a>
                <div class="uk-dropdown">

                    <span class="uk-text-small uk-text-uppercase">Done</span>
                    <div class="uk-h3">{ project.done[project.lang] || 0 }%</div>
                    <hr>
                    <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                        <li class="uk-nav-header">{ languages[project.lang] }</li>
                    </ul>
                </div>
            </span>

            <span class="uk-margin-small-right" each="{lang in project.languages}" data-uk-dropdown="mode:'click'">

                <a><img class="flag" riot-src="{App.route('/lokalize/getFlagIcon/'+lang)}" title="{lang}" width="25" height="25"></a>
                <div class="uk-dropdown">

                    <span class="uk-text-small uk-text-uppercase">Done</span>
                    <div class="uk-h3">{ parent.project.done[lang] || 0 }%</div>

                    <hr>

                    <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                        <li class="uk-nav-header">{ parent.languages[lang] }</li>
                        <li><a onclick="{parent.removeLanguage}">@lang('Remove')</a></li>
                    </ul>
                </div>
            </span>

            <span class="uk-margin-small-left" title="@lang('Add language')" data-uk-dropdown="mode:'click'">

                <a class="uk-icon-plus-circle uk-text-large"></a>

                <div class="uk-dropdown uk-dropdown-scrollable">
                    <ul class="uk-nav uk-nav-dropdown uk-dropdown-close">
                        <li each="{label,lang in languages}" show="{lang !== project.lang && project.languages.indexOf(lang) == -1}">
                            <a onclick="{parent.addLanguage}">
                                <img class="flag uk-margin-small-right" riot-src="{App.route('/lokalize/getFlagIcon/'+lang)}" title="{lang}" width="12" height="12">
                                {label}
                            </a>
                        </li>
                    </ul>
                </div>
            </span>

        </div>

        <div class="uk-flex uk-flex-middle">
            <a class="uk-text-large uk-margin-right uk-form-file" title="@lang('Import')">
                <i class="uk-icon-cloud-upload" style="display:inline-block;width:38px;"></i>
                <input id="upload-field" class="js-upload-select" type="file">
            </a>
            <a class="uk-text-large uk-margin-right" href="{App.route('/lokalize/export/'+project._id)}" download="{project.name}.csv" title="@lang('Export')"><i class="uk-icon-download"></i></a>
            <a class="uk-button uk-button-primary uk-width-1-1" onclick="{ addKey }">@lang('Add Key')</a>
        </div>

    </div>
    

    <div class="uk-width-medium-1-1 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(project.keys) }">

        <div class="uk-animation-scale uk-text-muted">
            <p>
                <img class="uk-svg-adjust" src="@url('lokalize:icon.svg')" width="40" height="40" alt="Collections" data-uk-svg />
            </p>
            <span class="uk-text-large">@lang('No keys created')</span>
        </div>

    </div>

    <div class="uk-panel uk-panel-box uk-panel-card uk-margin lokalize-keys" each="{key in getVisibleKeys() }">

        <div class="uk-grid">
            <div class="uk-width-medium-1-4">
                <a class="uk-display-block uk-text-truncate {!key && 'uk-text-danger'}" onclick="{ parent.editKey }" title="{key || 'n/a' }">{key || 'n/a' }</a>
                <div class="uk-text-small uk-text-muted uk-margin-small-top"><pre class="field-info">{parent.project.keys[key].info}</pre></div>
            </div>

            <div class="uk-flex-item-1 uk-form">

                <div class="uk-flex">
                    <div><span class="lang-badge uk-badge uk-badge-outline uk-text-primary uk-margin-right uk-text-truncate" title="{parent.project.lang}">{parent.languages[parent.project.lang]}</span></div>
                    <div class="uk-flex-item-1">
                        <input class="uk-width-1-1 uk-form-blank uk-text-primary" placeholder="😱 @lang('Empty')" bind="project.values.{parent.project.lang}['{key}']" if="{!parent.project.keys[key].multiline}">

                        @if($app['config/lokalize/multilineMarkdown'])
                        <cp-field type="markdown" class="uk-width-1-1 uk-display-block" placeholder="😱 @lang('Empty')" bind="project.values.{parent.project.lang}['{key}']" opts="{ {height:150, } }" if="{parent.project.keys[key].multiline}"></cp-field>
                        @else
                        <textarea class="uk-width-1-1 uk-form-blank uk-text-primary" placeholder="😱 @lang('Empty')" bind="project.values.{parent.project.lang}['{key}']" if="{parent.project.keys[key].multiline}"></textarea>
                        @endif

                    </div>
                </div>

                <div class="uk-flex uk-margin-small-top" each="{lang in parent.project.languages}">
                    <div><span class="lang-badge uk-badge uk-badge-outline uk-text-muted uk-margin-right uk-text-truncate" title="{lang}">{parent.languages[lang]}</span></div>
                    <div class="uk-flex-item-1">

                        <input class="uk-width-1-1 uk-form-blank lang-input" placeholder="😱 @lang('Empty')" bind="project.values.{lang}['{key}']" onfocus="{parent.suggestTranslation}" onblur="{parent.hideTranslation}" lang="{lang}" key="{key}" if="{!parent.project.keys[key].multiline}">
                        @if($app['config/lokalize/multilineMarkdown'])
                        <cp-field type="markdown" class="uk-width-1-1 uk-display-block" placeholder="😱 @lang('Empty')" bind="project.values.{lang}['{key}']" opts="{ {height:150, placeholder:'😱 @lang('Empty')'} }" if="{parent.project.keys[key].multiline}"></cp-field>
                        @else
                        <textarea class="uk-width-1-1 uk-form-blank" placeholder="😱 @lang('Empty')" bind="project.values.{lang}['{key}']" onfocus="{parent.suggestTranslation}" onblur="{parent.hideTranslation}" lang="{lang}" key="{key}" if="{parent.project.keys[key].multiline}"></textarea>
                        @endif

                        <div class="lang-suggestion uk-panel-framed uk-margin-small-top uk-margin-small-bottom uk-text-small uk-animation-fade uk-flex" if="{parent.$suggestion.key && parent.$suggestion.key==key && parent.$suggestion.lang==lang && parent.$suggestion.trans !== false }">
                            <strong>@lang('Suggestion'):</strong>
                            <div class="uk-flex-item-1 uk-margin-small-left">
                                <span class="uk-text-muted" if="{parent.$suggestion.trans}">{parent.$suggestion.trans}</span>
                                <span class="uk-icon-spin uk-icon-spinner" show="{!parent.$suggestion.trans}"></span>
                            </div>
                            <a class="uk-position-cover select-translation" show="{parent.$suggestion.trans}"></a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="uk-margin-small-left">
                <a class="uk-display-block" onclick="{ parent.duplicateKey }" title="@lang('Duplicate Key')"><i class="uk-icon-copy uk-icon-button"></i></a>
                <a class="uk-display-block uk-margin-small-top" onclick="{ parent.removeKey }" title="@lang('Delete Key')"><i class="uk-icon-trash-o uk-icon-button"></i></a>
            </div>

        </div>

    </div>

    <div ref="scrollIndicator"></div>

    <div ref="modalkey" class="uk-modal">

        <div class="uk-modal-dialog" if="{ $key }">

            <h3 class="uk-text-bold">@lang('Key Editor')</h3>

            <form class="uk-form" onsubmit="{saveKey}">

                <div class="uk-form-row">
                    <label class="uk-text-small">@lang('Key')</label>
                    <input ref="keyfield" class="uk-width-1-1" type="text" placeholder="@lang('Key name')" bind-event="input" bind="$key.name" required>
                </div>

                <div class="uk-form-row">
                    <label class="uk-text-small">@lang('Key info')</label>
                    <textarea class="uk-width-1-1 uk-form-large" name="description" placeholder="..." bind-event="input" bind="$key.info" rows="3"></textarea>
                </div>

                <div class="uk-form-row">
                    <field-boolean bind="$key.multiline" label="@lang('Multiline')" onchange="{ changeMultiline }"></field-boolean>
                </div>


                <div class="uk-modal-footer uk-text-right">

                    <button class="uk-button uk-button-large uk-button-primary uk-margin-right">@lang('Save')</button>
                    <a class="uk-modal-close">@lang('Cancel')</a>
                </div>

            </form>


        </div>

    </div>

    <cp-actionbar>
        <div class="uk-container uk-container-center">

            <button class="uk-button uk-button-large uk-button-primary" onclick="{ submit }">@lang('Save')</button>

            <a class="uk-button uk-button-large uk-button-link" href="@route('/lokalize')">
                @lang('Close')
            </a>
        </div>
    </cp-actionbar>


    <script type="view/script">

        var $this = this, _suggestions = {}, suggestionIdle;

        this.mixin(RiotBindMixin);

        this.languages = {{ json_encode($languages) }};
        this.project = window.__lokalizeProject;

        this.$key = null;
        this.$suggestion = {key:null};

        // checks
        if (Array.isArray(this.project.keys)) {
            this.project.keys = {};
        }

        // convert legacy key settings - remove later

        Object.keys(this.project.keys).forEach(function(key) {

            if (!$this.project.keys[key] || typeof($this.project.keys[key]) == 'string') {

                $this.project.keys[key] = {
                    info: $this.project.keys[key] || '',
                    multiline: false
                }
            }
        })


        if (Array.isArray(this.project.values)) {
            this.project.values = {};
        }

        if (!this.project.done || Array.isArray(this.project.done)) {
            this.project.done = {};
        }

        if (App.Utils.count(this.project.values)) {

            Object.keys(this.project.values).forEach(function(lang) {
                if (Array.isArray($this.project.values[lang])) {
                    $this.project.values[lang] = {};
                }
            });
        }

        LokalizeTranslator.init({{ json_encode($app->retrieve('lokalize/translationService', null)) }});

        this.keys = Object.keys(this.project.keys).sort();
        this.visible = 20;

        this.on('mount', function() {

            App.assets.require(['/assets/lib/uikit/js/components/upload.js'], function() {

                var uploadSettings = {

                    action: App.route('/lokalize/import/'+$this.project._id),
                    type: 'json',
                    allow : '*.csv',
                    param : 'file',
                    loadstart: function() {
                        $this.refs.uploadprogress.classList.remove('uk-hidden');
                    },
                    progress: function(percent) {
                        percent = Math.ceil(percent) + '%';
                        $this.refs.progressbar.innerHTML   = '<span>'+percent+'</span>';
                        $this.refs.progressbar.style.width = percent;
                    },
                    allcomplete: function(response) {

                        $this.refs.uploadprogress.classList.add('uk-hidden');
                        App.$('#upload-field').replaceWith(App.$('#upload-field')[0].outerHTML);
                        uploadselect = UIkit.uploadSelect(App.$('#upload-field')[0], uploadSettings);

                        if (response && response._id) {

                            $this.project = response;

                            if (!$this.project.done || Array.isArray($this.project.done)) {
                                $this.project.done = {};
                            }
                            $this.update();
                        }
                    }
                };

                uploadselect = UIkit.uploadSelect(App.$('.js-upload-select', $this.root)[0], uploadSettings);
            });

            App.$(this.root).on('pointerdown', '.select-translation', function() {

                if ($this.$suggestion.key && $this.$suggestion.lang) {
                    _.set($this.project.values, [$this.$suggestion.lang, $this.$suggestion.key].join('.'), $this.$suggestion.trans);
                    setTimeout(function() { $this.update(); }, 200);
                }
            });

            this.trigger('update');

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                e.preventDefault();
                $this.submit();
                return false;
            });

            // lock resource
            var idle = setInterval(function() {
                if (!$this.project._id) return;
                App.request('/cockpit/utils/lockResourceId/'+$this.project._id, {});
            }, 120000);

            // unlock resource
            window.addEventListener("beforeunload", function (event) {

                clearInterval(idle);

                if (!$this.project._id) return;

                if (navigator.sendBeacon) {
                    navigator.sendBeacon(App.route('/cockpit/utils/unlockResourceId/'+$this.project._id));
                } else {
                    App.request('/cockpit/utils/unlockResourceId/'+$this.project._id, {});
                }
            });

            var observer = new IntersectionObserver(function(entries, observer) {

                if (!$this.keys.length || $this.visible >= $this.keys.length) return;

                $this.visible += ($this.visible+20) > $this.keys.length ? ($this.keys.length - $this.visible) : 20;
                $this.update();

            }, {
                rootMargin: '20px 0px',
                threshold: 0.01
            });

            setTimeout(function() {
                observer.observe($this.refs.scrollIndicator);
            }, 50);
        });

        getVisibleKeys() {

            if (!this.visible) return;

            var keys = this.keys;

            if (this.refs.txtfilter.value) {
                keys = keys.filter(function(key) {
                    return $this.infilter(key);
                })
            }

            return keys.slice(0, this.visible);
        }

        addLanguage(e) {
            this.project.languages.push(e.item.lang);
        }

        removeLanguage(e) {

            App.ui.confirm("Are you sure?", function() {

                if ($this.project.values && $this.project.values[e.item.lang]) {
                    delete $this.project.values[e.item.lang];
                }

                $this.project.languages.splice($this.project.languages.indexOf(e.item.lang), 1);
                $this.update();
            })
        }

        addKey() {

            this.$key = {
                name: '',
                info: '',
                multiline: App.session.get('lokalize.new.multiline', false)
            };

            setTimeout(function() {
                UIkit.modal($this.refs.modalkey).show();
                $this.refs.keyfield.focus();
            }, 100);
        }

        editKey(e) {

            this.$key = {
                _: e.item.key,
                name: e.item.key,
                info: this.project.keys[e.item.key].info || '',
                multiline: this.project.keys[e.item.key].multiline || false
            };

            setTimeout(function() {
                UIkit.modal($this.refs.modalkey).show();
            }, 100);
        }

        duplicateKey(e) {

            var key = e.item.key;

            App.ui.prompt('New key name', key+'_copy', function(name) {

                name = name.trim();

                if (!name) return;

                $this.project.keys[name] = _.extend({}, $this.project.keys[key]);

                Object.keys($this.project.values || {}).forEach(function(lang){
                    if ($this.project.values[lang][key]) {
                        $this.project.values[lang][name] = $this.project.values[lang][key];
                    }
                });

                $this.keys = Object.keys($this.project.keys).sort();
                $this.update();
            });
        }

        removeKey(e) {

            var key = e.item.key;

            App.ui.confirm("Are you sure?", function() {

                Object.keys($this.project.values || {}).forEach(function(lang){

                    if ($this.project.values[lang][key]) {
                        delete $this.project.values[lang][key];
                    }
                })

                delete $this.project.keys[key];

                $this.keys = Object.keys($this.project.keys).sort();
                $this.update();
            })
        }

        saveKey(e) {

            e.preventDefault();

            UIkit.modal($this.refs.modalkey).hide();

            this.keys = Object.keys(this.project.keys).sort();

            this.project.keys[this.$key.name] = {
                info: this.$key.info,
                multiline: this.$key.multiline
            };

            if (this.$key._ && this.$key._ != this.$key.name) {

                Object.keys(this.project.values || {}).forEach(function(lang){

                    if ($this.project.values[lang][$this.$key._]) {
                        $this.project.values[lang][$this.$key.name] = $this.project.values[lang][$this.$key._];
                        delete $this.project.values[lang][$this.$key._];
                    }
                });

                delete this.project.keys[this.$key._];
            }

            this.keys.unshift(this.$key.name);

            this.$key = null;
        }

        changeMultiline(e) {
            App.session.set('lokalize.new.multiline', e.target.checked);
        }

        suggestTranslation(e) {

            if (suggestionIdle) {
                clearTimeout(suggestionIdle);
            }

            var key = e.target.getAttribute('key'),
                lang = e.target.getAttribute('lang'),
                defval = _.get(this.project.values, [this.project.lang, key].join('.'));

            if (!navigator.onLine || !defval) {
                this.$suggestion = {key:null};
                return;
            }

            this.$suggestion = {
                key: key,
                lang: lang,
                trans: null
            };

            if (_suggestions[defval] && _suggestions[defval][lang]) {
                $this.$suggestion.trans = _suggestions[defval][lang];
                return;
            }

            LokalizeTranslator.translate(this.project.lang, lang, defval).then(function(txt) {

                if ($this.$suggestion) {

                    if (txt) {
                        _.set(_suggestions, [defval, lang].join('.'), txt);
                    }

                    $this.$suggestion.trans = txt;
                    $this.update();
                }
            });
        }

        hideTranslation() {

            suggestionIdle = setTimeout(function() {
                $this.$suggestion.key = null;
                $this.update();
            }, 250);
        }

        clearfilter() {
            this.refs.txtfilter.value = "";
        }

        updatefilter(e) {
            if (e.key === "Escape") {
                this.clearfilter();
            } else {
                $this.visible = 20;
            }
        }

        infilter(key) {

            if (!this.refs.txtfilter.value) {
                return true;
            }

            var target = this.filterTarget || 'keys';

            if (target == 'keys') {
                return key.toLowerCase().indexOf(this.refs.txtfilter.value.toLowerCase()) !== -1;
            }

            var langs = Object.keys(this.project.values), keys = Object.keys(this.keys);

            for (var i=0;i<langs.length;i++) {
                
                if (this.project.values[langs[i]] && this.project.values[langs[i]][key] && this.project.values[langs[i]][key].toLowerCase().indexOf(this.refs.txtfilter.value.toLowerCase()) !== -1) {
                    return true;
                }
            }

            return false;
        }

        submit(e) {

            if (e) e.preventDefault();

            App.Utils.worker.execute(function(project) {

                var langs = [project.lang].concat(project.languages),
                    keys  = Object.keys(project.keys),
                    done  = 0;

                project.done = {}
                project.values = project.values || {};

                langs.forEach(function(lang) {
                    project.done[lang] = 0;
                })

                keys.forEach(function(key) {

                    langs.forEach(function(lang) {

                        if (project.values[lang] && project.values[lang][key] && project.values[lang][key].trim()) {
                            project.done[lang]++
                        }
                    })

                })

                langs.forEach(function(lang) {
                    project.done[lang] =  Math.ceil((keys.length ? project.done[lang]/keys.length : 0) * 100);
                    done += project.done[lang];
                });

                project.done._all = Math.ceil(done/langs.length);

                return project;

            }, this.project).then(function(project) {


                App.request('/lokalize/save_project', {project: project}).then(function(project) {

                    App.ui.notify("Project saved.", "success");

                    $this.project.done = project.done;
                    $this.update();

                }).catch(function() {
                    App.ui.notify("Saving failed.", "danger");
                });
            });

        }

    </script>
</div>
