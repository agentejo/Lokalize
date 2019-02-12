<script>

  window.__lokalizeProjects = {{ json_encode($projects) }}

</script>

<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Lokalize')</span></li>
    </ul>
</div>


<div riot-view>

    <div class="uk-margin uk-clearfix" if="{ App.Utils.count(projects) }">

        <div class="uk-form-icon uk-form uk-text-muted">

            <i class="uk-icon-filter"></i>
            <input class="uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter projects...')" onkeyup="{ updatefilter }">

        </div>

        @hasaccess?('lokalize', 'projects_create')
        <div class="uk-float-right">
            <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/lokalize/_project')">@lang('Add Project')</a>
        </div>
        @end

    </div>


    <div class="uk-width-medium-1-1 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-middle uk-flex-center" if="{ !App.Utils.count(projects) }">

        <div class="uk-animation-scale">

            <p>
                <img class="uk-svg-adjust uk-text-muted" src="@url('lokalize:icon.svg')" width="80" height="80" alt="Collections" data-uk-svg />
            </p>
            <hr>
            <span class="uk-text-large"><strong>@lang('No Projects').</strong>
            @hasaccess?('lokalize', 'projects_create')
            <a href="@route('/lokalize/_project')">@lang('Create one')</a></span>
            @end
        </div>

    </div>

    <div if="{ App.Utils.count(projects) }">

        <div class="uk-panel uk-panel-box uk-panel-card uk-panel-card-hover uk-margin" each="{project in projects}" show="{ infilter(project) }">

            <div class="uk-grid uk-grid-match uk-margin" data-uk-grid-margin>

                <div class="uk-width-medium-1-3">
                    <div class="uk-text-large">
                        <a class="uk-text-bold" href="{App.route('/lokalize/project/'+project._id)}" style="color:{project.color || ''}">{project.name}</a>
                    </div>
                    <div class="uk-text-small uk-text-muted" if="{project.info}">{project.info}</div>
                </div>

                <div class="uk-flex-item-1">
                    <div class="uk-grid">
                        <div class="uk-width-1-5">
                            <div class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Done')</div>
                            <div class="uk-margin-small-top uk-h2">{ _.get(project, 'done._all') || 0 }%</div>
                        </div>
                        <div class="uk-width-1-5">
                            <div class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Keys')</div>
                            <div class="uk-margin-small-top uk-h2">{App.Utils.count(project.keys)}</div>
                        </div>
                        <div>
                            <div class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Languages')</div>
                            <div class="uk-margin-small-top uk-h2">
                                <img class="uk-margin-right" riot-src="{App.route('/lokalize/getFlagIcon/'+project.lang)}" width="20" height="20" title="{project.lang}" style="vertical-align: baseline;">
                                <img class="uk-margin-small-right" riot-src="{App.route('/lokalize/getFlagIcon/'+lang)}" width="20" height="20" title="{lang}" style="vertical-align: baseline;" each="{lang in project.languages}">
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="uk-flex uk-flex-middle">
                        <a href="{App.route('/lokalize/_project/'+project._id)}"><i class="uk-icon-pencil uk-icon-button"></i></a>
                        <a class="uk-margin-small-left" onclick="{parent.clone}"><i class="uk-icon-copy uk-icon-button"></i></a>
                        <a class="uk-margin-small-left" href="{App.route('/lokalize/export/'+project._id)}" download="{project.name}.csv"><i class="uk-icon-download uk-icon-button"></i></a>
                        <a class="uk-margin-small-left" onclick="{ parent.remove }"><i class="uk-icon-trash-o uk-icon-button"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="view/script">

        var $this = this;

        this.ready  = true;
        this.projects = window.__lokalizeProjects;

        remove(e, project) {

            project = e.item.project;

            App.ui.confirm("Are you sure?", function() {

                App.callmodule('lokalize:removeProject', project).then(function(data) {

                    $this.projects.splice($this.projects.indexOf(project), 1);

                    App.ui.notify("Project removed", "success");
                    $this.update();
                });
            });
        }

        updatefilter(e) {}

        infilter(project) {

            if (!this.refs.txtfilter.value) {
                return true;
            }

            return project.name.toLowerCase().indexOf(this.refs.txtfilter.value.toLowerCase()) !== -1;
        }

        clone(e) {

            var project = _.extend({}, e.item.project);

            delete project._id;

            project.name = project.name+' '+'(copy)';

            App.request('/lokalize/save_project', {project: project}).then(function(project) {

                App.ui.notify("Project cloned", "success");
                $this.projects.push(project);

                $this.projects = _.sortBy($this.projects, ['name']);

                $this.update();

            }).catch(function() {
                App.ui.notify("Saving failed.", "danger");
            });

        }


    </script>

</div>
